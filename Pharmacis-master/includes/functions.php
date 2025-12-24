<?php
// Filename: includes/functions.php
// Yeh file chote functions ke liye hai jo pure project mein use ho sakte hain.

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