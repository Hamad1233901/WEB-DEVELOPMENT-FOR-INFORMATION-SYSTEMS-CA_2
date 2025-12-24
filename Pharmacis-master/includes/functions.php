<?php
// Filename: includes/functions.php
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatCurrency($amount) {
    return "$" . number_format($amount, 2);
}
?>
