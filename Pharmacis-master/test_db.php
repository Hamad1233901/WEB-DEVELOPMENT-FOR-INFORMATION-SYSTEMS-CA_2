<?php
require_once "config/database.php";

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ DB Connected successfully!";
} else {
    echo "❌ DB Connection failed!";
}
