<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

include_once 'config/database.php';
$db = (new Database())->getConnection();

// Stats Logic
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$low_stock = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 20")->fetchColumn();
$today_sales = 0;
if($_SESSION['role'] == 'admin'){
    $today_sales = $db->query("SELECT SUM(total_price) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn();
}

// Page Title Variable for Topbar
$page_title = "Overview Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PharmaCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <?php include 'includes/sidebar.php'; ?>

    <div class="md:ml-64 min-h-screen">
        <!-- Include Topbar Here -->
        <?php include 'includes/topbar.php'; ?>

        <div class="px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Cards -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-sm font-medium uppercase">Total Inventory</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $total_products; ?> Items</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-red-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-sm font-medium uppercase">Low Stock Alerts</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo $low_stock; ?> Items</p>
                </div>

                <?php if($_SESSION['role'] == 'admin'): ?>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-sm font-medium uppercase">Today's Sales</p>
                    <p class="text-3xl font-bold text-green-600">$<?php echo number_format($today_sales ?? 0, 2); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="bg-white p-8 rounded shadow text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h3>
                <a href="pos.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg shadow hover:bg-blue-700 transition font-bold">
                    Go to Billing (POS)
                </a>
            </div>
        </div>
    </div>
</body>
</html>