<?php
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<div class="product-card">';
        echo '<a href="productview.php?id=' . $row['product_id'] . '" class="product-card">';
        echo '<img src="' . $row['main_image'] . '" alt="' . htmlspecialchars($row['product_name']) . '">';
        echo '<div class="product-name">';
        echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>' . '</div>';
        echo '<div class="product-price">';
        echo '<p>₱' . number_format($row['price'], 2) . '</p>';
        echo '</div>';  
        echo '</a>';
        echo '</div>';
    }
} else {
    echo '<p>No products found.</p>';
}
?>
