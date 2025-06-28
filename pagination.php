<?php
function renderPagination($currentPage, $totalPages, $baseUrl = '?page=') {
  if ($totalPages <= 1) return;

  echo '<div class="pagination-circle">';
  for ($i = 1; $i <= $totalPages; $i++) {
    $active = ($i == $currentPage) ? 'active' : '';
    echo '<a href="' . $baseUrl . $i . '" class="circle-btn ' . $active . '">' . $i . '</a>';
  }
  echo '</div>';
}
?>
