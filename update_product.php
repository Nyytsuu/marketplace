<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sellerproductss.php");
    exit();
}

$seller_id = $_SESSION['user_id'] ?? null;
if (!$seller_id) {
    die("Seller not logged in.");
}

$product_id = (int)$_POST['product_id'];
$product_name = $_POST['product_name'];
$product_description = $_POST['product_description'];
$category = $_POST['category'];
$price = (float)$_POST['price'];
$stocks = (int)$_POST['stocks'];

// Database connection
$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify product belongs to seller
$checkStmt = $conn->prepare("SELECT seller_id FROM products WHERE product_id = ?");
$checkStmt->bind_param("i", $product_id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$productData = $result->fetch_assoc();

if (!$productData || $productData['seller_id'] != $seller_id) {
    die("Unauthorized access.");
}

// Start transaction
$conn->autocommit(FALSE);

try {
    // Update basic product information
    $updateStmt = $conn->prepare("UPDATE products SET product_name = ?, product_description = ?, category = ?, price = ?, stocks = ? WHERE product_id = ?");
    if (!$updateStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $updateStmt->bind_param("sssdii", $product_name, $product_description, $category, $price, $stocks, $product_id);
    if (!$updateStmt->execute()) {
        throw new Exception("Failed to update product: " . $updateStmt->error);
    }

    // Handle new product images - IMPROVED ERROR HANDLING
    if (!empty($_FILES['product_image']['name'][0])) {
        $uploadDir = 'uploads/productimages/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            throw new Exception("Upload directory is not writable");
        }

        foreach ($_FILES['product_image']['name'] as $key => $filename) {
            if (!empty($filename)) {
                // Check for upload errors
                if ($_FILES['product_image']['error'][$key] !== UPLOAD_ERR_OK) {
                    error_log("Upload error for file $filename: " . $_FILES['product_image']['error'][$key]);
                    continue;
                }

                $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                    error_log("Invalid file extension: $fileExtension for file $filename");
                    continue;
                }

                $newFilename = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFilename;

                if (move_uploaded_file($_FILES['product_image']['tmp_name'][$key], $targetPath)) {
                    // Insert image record
                    $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (?, ?, 0)");
                    if (!$imgStmt) {
                        error_log("Prepare failed for image insert: " . $conn->error);
                        // Delete the uploaded file if database insert fails
                        unlink($targetPath);
                        continue;
                    }
                    
                    $imgStmt->bind_param("is", $product_id, $targetPath);
                    if (!$imgStmt->execute()) {
                        error_log("Failed to insert image record: " . $imgStmt->error);
                        // Delete the uploaded file if database insert fails
                        unlink($targetPath);
                    }
                    $imgStmt->close();
                } else {
                    error_log("Failed to move uploaded file: $filename");
                }
            }
        }
    }

    // Handle existing variations updates
    if (!empty($_POST['existing_variation_id'])) {
        foreach ($_POST['existing_variation_id'] as $index => $variation_id) {
            $variation_name = $_POST['existing_variation_name'][$index] ?? '';
            $sub_category = $_POST['existing_sub_category'][$index] ?? '';
            $additional_info = $_POST['existing_additional_information'][$index] ?? '';
            $variation_price = !empty($_POST['existing_variation_price'][$index]) ? (float)$_POST['existing_variation_price'][$index] : null;
            $variation_stock = !empty($_POST['existing_variation_stock'][$index]) ? (int)$_POST['existing_variation_stock'][$index] : null;
            $variation_color = $_POST['existing_variation_color'][$index] ?? '';
            $variation_size = $_POST['existing_variation_size'][$index] ?? '';

            $varUpdateStmt = $conn->prepare("UPDATE product_variations SET variation_name = ?, sub_category = ?, additional_information = ?, variation_price = ?, variation_stock = ?, variation_color = ?, variation_size = ? WHERE variation_id = ?");
            $varUpdateStmt->bind_param("sssdissi", $variation_name, $sub_category, $additional_info, $variation_price, $variation_stock, $variation_color, $variation_size, $variation_id);
            $varUpdateStmt->execute();

            // Handle variation image update
            $imageKey = "existing_variation_image_" . $variation_id;
            if (!empty($_FILES[$imageKey]['name'])) {
                $uploadDir = 'uploads/variationimages/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES[$imageKey]['name'], PATHINFO_EXTENSION);
                $newFilename = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFilename;

                if (move_uploaded_file($_FILES[$imageKey]['tmp_name'], $targetPath)) {
                    $varImgUpdateStmt = $conn->prepare("UPDATE product_variations SET variation_image = ? WHERE variation_id = ?");
                    $varImgUpdateStmt->bind_param("si", $targetPath, $variation_id);
                    $varImgUpdateStmt->execute();
                }
            }
        }
    }

    // Handle new variations
    if (!empty($_POST['variation_name'])) {
        foreach ($_POST['variation_name'] as $index => $variation_name) {
            $sub_category = $_POST['sub_category'][$index] ?? '';
            $additional_info = $_POST['additional_information'][$index] ?? '';
            $variation_price = !empty($_POST['variation_price'][$index]) ? (float)$_POST['variation_price'][$index] : null;
            $variation_stock = !empty($_POST['variation_stock'][$index]) ? (int)$_POST['variation_stock'][$index] : null;
            $variation_color = $_POST['variation_color'][$index] ?? '';
            $variation_size = $_POST['variation_size'][$index] ?? '';

            $varInsertStmt = $conn->prepare("INSERT INTO product_variations (product_id, variation_name, sub_category, additional_information, variation_price, variation_stock, variation_color, variation_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $varInsertStmt->bind_param("isssdiis", $product_id, $variation_name, $sub_category, $additional_info, $variation_price, $variation_stock, $variation_color, $variation_size);
            $varInsertStmt->execute();

            // Handle new variation image
            if (!empty($_FILES['variation_image']['name'][$index])) {
                $uploadDir = 'uploads/variationimages/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES['variation_image']['name'][$index], PATHINFO_EXTENSION);
                $newFilename = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFilename;

                if (move_uploaded_file($_FILES['variation_image']['tmp_name'][$index], $targetPath)) {
                    $variation_id = $conn->insert_id;
                    $varImgStmt = $conn->prepare("UPDATE product_variations SET variation_image = ? WHERE variation_id = ?");
                    $varImgStmt->bind_param("si", $targetPath, $variation_id);
                    $varImgStmt->execute();
                }
            }
        }
    }

    // Commit transaction
    $conn->commit();
    
    // Set session variable for success message
    $_SESSION['product_updated'] = true;
    
    // Redirect with SweetAlert
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Success!',
                text: 'Product updated successfully!',
                icon: 'success',
                confirmButtonColor: '#004AAD',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.href = 'sellerproductss.php';
            });
        </script>
    </body>
    </html>";

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Product update error: " . $e->getMessage());
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Error!',
                text: 'Error updating product: " . addslashes($e->getMessage()) . "',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.history.back();
            });
        </script>
    </body>
    </html>";
}

$conn->close();
?>