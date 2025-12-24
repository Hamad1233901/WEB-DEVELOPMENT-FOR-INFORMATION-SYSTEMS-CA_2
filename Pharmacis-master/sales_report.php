<?php
session_start();
// Security: Sirf Admin access kar sakta hai
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: index.php"); exit; 
}

include_once 'config/database.php';
$db = (new Database())->getConnection();
$page_title = "Sales Reports";

// --- LOGIC CORRECTION ---
// Hum 'orders' table ko 'users' table ke sath JOIN kar rahe hain.
// 'u.username' wo banda hai jiski ID 'orders.sold_by' mein save hai.
$query = "SELECT o.id, o.total_amount, o.cash_given, o.change_return, o.created_at, u.username 
          FROM orders o
          JOIN users u ON o.sold_by = u.id 
          ORDER BY o.created_at DESC";
$stmt = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - PharmaCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'includes/sidebar.php'; ?>

    <div class="md:ml-64 min-h-screen">
        <?php include 'includes/topbar.php'; ?>

        <div class="px-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">📊 Order History (Receipts)</h2>

            <div class="bg-white rounded shadow overflow-hidden">
                <table class="min-w-full text-left">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4">Receipt ID</th>
                            <th class="py-3 px-4">Date & Time</th>
                            <th class="py-3 px-4">Sold By (Staff)</th>
                            <th class="py-3 px-4">Total Amount</th>
                            <th class="py-3 px-4">Cash / Change</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if($stmt->rowCount() > 0): ?>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="hover:bg-gray-50">
                                <!-- Receipt ID -->
                                <td class="py-3 px-4 font-mono text-blue-600">#<?php echo $row['id']; ?></td>
                                
                                <!-- Date -->
                                <td class="py-3 px-4 text-sm text-gray-600">
                                    <?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?>
                                </td>
                                
                                <!-- SOLD BY (Corrected) -->
                                <td class="py-3 px-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded font-bold capitalize">
                                        <?php echo $row['username']; // Ye database wala naam hai ?>
                                    </span>
                                </td>
                                
                                <!-- Total Amount -->
                                <td class="py-3 px-4 font-bold text-green-700">
                                    $<?php echo number_format($row['total_amount'], 2); ?>
                                </td>

                                <!-- Details -->
                                <td class="py-3 px-4 text-xs text-gray-500">
                                    Cash: $<?php echo $row['cash_given']; ?> <br>
                                    Change: $<?php echo $row['change_return']; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">No sales found yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>