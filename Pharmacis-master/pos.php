<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
include_once 'config/database.php';
$db = (new Database())->getConnection();

// Fetch Products for Searchable List
$products = $db->query("SELECT * FROM products WHERE stock_quantity > 0")->fetchAll(PDO::FETCH_ASSOC);
$page_title = "Point of Sale (POS)";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS - PharmaCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Print Styles: Hide everything except receipt when printing */
        @media print {
            body * { visibility: hidden; }
            #receipt-modal, #receipt-content, #receipt-content * { visibility: visible; }
            #receipt-modal { position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: white; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans h-screen flex flex-col overflow-hidden">
    
    <!-- Topbar & Sidebar (Hidden on Print) -->
    <div class="flex flex-1 overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="md:ml-64 w-full flex flex-col h-full">
            <?php include 'includes/topbar.php'; ?>

            <div class="flex-1 p-4 grid grid-cols-1 md:grid-cols-3 gap-4 overflow-hidden">
                
                <!-- LEFT: Product Selection -->
                <div class="md:col-span-2 bg-white rounded shadow-lg p-4 flex flex-col">
                    <h3 class="text-lg font-bold mb-4 text-gray-700 border-b pb-2">Select Products</h3>
                    
                    <!-- Searchable Input -->
                    <div class="flex gap-2 mb-4">
                        <input list="product-list" id="product-input" class="w-full border p-3 rounded bg-gray-50 focus:ring-2 focus:ring-blue-500" placeholder="Search product by name...">
                        <datalist id="product-list">
                            <?php foreach($products as $p): ?>
                                <option data-id="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" value="<?php echo $p['name']; ?>">
                                    Price: $<?php echo $p['price']; ?> | Stock: <?php echo $p['stock_quantity']; ?>
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                        <input type="number" id="qty-input" value="1" min="1" class="w-20 border p-3 rounded text-center" placeholder="Qty">
                        <button onclick="addToCart()" class="bg-blue-600 text-white px-6 rounded font-bold hover:bg-blue-700">+ Add</button>
                    </div>

                    <!-- Cart Table -->
                    <div class="flex-1 overflow-auto border rounded">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="p-3">Product</th>
                                    <th class="p-3">Price</th>
                                    <th class="p-3">Qty</th>
                                    <th class="p-3">Total</th>
                                    <th class="p-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="cart-body">
                                <!-- JS will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RIGHT: Payment Section -->
                <div class="bg-gray-800 text-white rounded shadow-lg p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-6 border-b border-gray-600 pb-2">Payment Details</h3>
                        
                        <div class="flex justify-between text-lg mb-2">
                            <span>Total Items:</span>
                            <span id="total-items">0</span>
                        </div>
                        <div class="flex justify-between text-3xl font-bold mb-6 text-green-400">
                            <span>Total:</span>
                            <span>$<span id="grand-total">0.00</span></span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm text-gray-400 mb-1">Cash Received ($)</label>
                            <input type="number" id="cash-given" oninput="calculateChange()" class="w-full p-3 rounded text-black font-bold text-xl" placeholder="0.00">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm text-gray-400 mb-1">Change Return</label>
                            <div class="text-2xl font-bold text-yellow-400">$<span id="change-return">0.00</span></div>
                        </div>
                    </div>

                    <button onclick="processSale()" id="pay-btn" class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded font-bold text-xl shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        CONFIRM & PRINT
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal (Hidden by default) -->
    <div id="receipt-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded shadow-xl w-80 text-center relative">
            <div id="receipt-content" class="text-left font-mono text-sm">
                <h2 class="text-center font-bold text-xl mb-1">PharmaCIS Pharmacy</h2>
                <p class="text-center text-xs mb-4">Official Receipt</p>
                <p class="border-b border-dashed mb-2"></p>
                <div id="receipt-items"></div>
                <p class="border-b border-dashed my-2"></p>
                <div class="flex justify-between font-bold"><span>Total:</span><span id="receipt-total"></span></div>
                <div class="flex justify-between"><span>Cash:</span><span id="receipt-cash"></span></div>
                <div class="flex justify-between"><span>Change:</span><span id="receipt-change"></span></div>
                <p class="text-center mt-4 text-xs">Thank you for your visit!</p>
                <p class="text-center text-xs text-gray-400" id="receipt-date"></p>
            </div>
            
            <div class="mt-4 flex gap-2 no-print">
                <button onclick="window.print()" class="flex-1 bg-blue-600 text-white py-2 rounded">Print</button>
                <button onclick="closeReceipt()" class="flex-1 bg-gray-300 py-2 rounded">Close</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        let cart = [];
        let products = <?php echo json_encode($products); ?>;

        function addToCart() {
            let inputVal = document.getElementById('product-input').value;
            let qty = parseInt(document.getElementById('qty-input').value);
            
            // Find product from datalist logic
            let product = products.find(p => p.name === inputVal);
            
            if (!product) { alert("Please select a valid product from the list."); return; }
            if (qty > product.stock_quantity) { alert("Insufficient Stock! Available: " + product.stock_quantity); return; }

            // Check if exists in cart
            let existing = cart.find(i => i.id === product.id);
            if (existing) {
                existing.qty += qty;
                existing.total = existing.qty * existing.price;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    qty: qty,
                    total: parseFloat(product.price) * qty
                });
            }
            
            document.getElementById('product-input').value = '';
            document.getElementById('qty-input').value = 1;
            renderCart();
        }

        function renderCart() {
            let tbody = document.getElementById('cart-body');
            tbody.innerHTML = '';
            let total = 0;
            let count = 0;

            cart.forEach((item, index) => {
                total += item.total;
                count += item.qty;
                tbody.innerHTML += `
                    <tr class="border-b">
                        <td class="p-3 font-medium">${item.name}</td>
                        <td class="p-3">$${item.price}</td>
                        <td class="p-3">${item.qty}</td>
                        <td class="p-3 font-bold">$${item.total.toFixed(2)}</td>
                        <td class="p-3">
                            <button onclick="removeFromCart(${index})" class="text-red-500 hover:text-red-700">Remove</button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('grand-total').innerText = total.toFixed(2);
            document.getElementById('total-items').innerText = count;
            calculateChange();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function calculateChange() {
            let total = parseFloat(document.getElementById('grand-total').innerText);
            let cash = parseFloat(document.getElementById('cash-given').value);
            let btn = document.getElementById('pay-btn');

            if (!isNaN(cash) && cash >= total && total > 0) {
                document.getElementById('change-return').innerText = (cash - total).toFixed(2);
                btn.disabled = false;
            } else {
                document.getElementById('change-return').innerText = "0.00";
                btn.disabled = true;
            }
        }

        async function processSale() {
            if(cart.length === 0) return;
            
            let totalAmount = parseFloat(document.getElementById('grand-total').innerText);
            let cashGiven = parseFloat(document.getElementById('cash-given').value);
            let changeAmount = parseFloat(document.getElementById('change-return').innerText);

            const payload = {
                cart: cart,
                totalAmount: totalAmount,
                cashGiven: cashGiven,
                changeAmount: changeAmount
            };

            try {
                const response = await fetch('api/save_order.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const result = await response.json();

                if(result.status === 'success') {
                    showReceipt(totalAmount, cashGiven, changeAmount);
                    cart = []; // Clear cart
                    renderCart();
                    document.getElementById('cash-given').value = '';
                } else {
                    alert("Error saving order: " + result.message);
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Transaction Failed.");
            }
        }

        function showReceipt(total, cash, change) {
            let itemsHtml = cart.map(item => `
                <div class="flex justify-between">
                    <span>${item.name} x${item.qty}</span>
                    <span>$${item.total.toFixed(2)}</span>
                </div>
            `).join('');

            document.getElementById('receipt-items').innerHTML = itemsHtml;
            document.getElementById('receipt-total').innerText = "$" + total.toFixed(2);
            document.getElementById('receipt-cash').innerText = "$" + cash.toFixed(2);
            document.getElementById('receipt-change').innerText = "$" + change.toFixed(2);
            document.getElementById('receipt-date').innerText = new Date().toLocaleString();

            document.getElementById('receipt-modal').classList.remove('hidden');
        }

        function closeReceipt() {
            document.getElementById('receipt-modal').classList.add('hidden');
            location.reload(); // Reload to update stock locally
        }
    </script>
</body>
</html>