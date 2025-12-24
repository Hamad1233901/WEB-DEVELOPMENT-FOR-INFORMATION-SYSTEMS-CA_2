<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

include_once 'config/database.php';
include_once 'models/Product.php';
$db = (new Database())->getConnection();
$product = new Product($db);
$page_title = "Inventory Management";

// Handle Search Logic
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM products WHERE name LIKE :search OR category LIKE :search ORDER BY id DESC";
$stmt = $db->prepare($query);
$searchTerm = "%$search%";
$stmt->bindParam(':search', $searchTerm);
$stmt->execute();

// Handle Actions (Only if Admin)
if($_POST && $_SESSION['role'] == 'admin'){
    // (Existing Add Logic)
    $product->name = $_POST['name'];
    $product->category = $_POST['category'];
    $product->price = $_POST['price'];
    $product->stock_quantity = $_POST['stock'];
    $product->create();
    header("Location: inventory.php");
}

if(isset($_GET['del']) && $_SESSION['role'] == 'admin'){
    $product->id = $_GET['del'];
    $product->delete();
    header("Location: inventory.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory - PharmaCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'includes/sidebar.php'; ?>

    <div class="md:ml-64 min-h-screen">
        <?php include 'includes/topbar.php'; ?>

        <div class="px-6">
            <header class="flex justify-between items-center mb-6">
                
                <!-- NEW: Search Form -->
                <form action="inventory.php" method="GET" class="flex gap-2 w-1/2">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products..." class="border p-2 rounded w-full shadow-sm">
                    <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded shadow hover:bg-gray-900">Search</button>
                    <?php if($search): ?>
                        <a href="inventory.php" class="bg-red-500 text-white px-4 py-2 rounded shadow">Reset</a>
                    <?php endif; ?>
                </form>

                <!-- Add Button (Admin Only) -->
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-bold">
                        + Add New Product
                    </button>
                <?php endif; ?>
            </header>

            <div class="bg-white rounded shadow overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-3 text-xs font-semibold uppercase">ID</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Product Name</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Category</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Price</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Stock</th>
                            <?php if($_SESSION['role'] == 'admin'): ?>
                            <th class="px-5 py-3 text-xs font-semibold uppercase">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($stmt->rowCount() > 0): ?>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-5 py-4 text-sm"><?php echo $row['id']; ?></td>
                                <td class="px-5 py-4 text-sm font-bold text-gray-700"><?php echo $row['name']; ?></td>
                                <td class="px-5 py-4 text-sm"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?php echo $row['category']; ?></span></td>
                                <td class="px-5 py-4 text-sm text-green-600 font-mono">$<?php echo $row['price']; ?></td>
                                <td class="px-5 py-4 text-sm">
                                    <?php if($row['stock_quantity'] < 20): ?>
                                        <span class="text-red-600 font-bold bg-red-100 px-2 py-1 rounded">Low: <?php echo $row['stock_quantity']; ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-700"><?php echo $row['stock_quantity']; ?></span>
                                    <?php endif; ?>
                                </td>
                                
                                <?php if($_SESSION['role'] == 'admin'): ?>
                                <td class="px-5 py-4 text-sm">
                                    <a href="inventory.php?del=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="text-red-600 hover:text-red-900 font-bold">Delete</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-5 py-4 text-center text-gray-500">No products found matching "<?php echo htmlspecialchars($search); ?>"</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Modal (Same as before) -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h3 class="text-xl font-bold mb-4">Add Product</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Name" class="w-full border p-2 mb-2 rounded" required>
                <input type="text" name="category" placeholder="Category" class="w-full border p-2 mb-2 rounded" required>
                <input type="number" step="0.01" name="price" placeholder="Price" class="w-full border p-2 mb-2 rounded" required>
                <input type="number" name="stock" placeholder="Stock" class="w-full border p-2 mb-4 rounded" required>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>