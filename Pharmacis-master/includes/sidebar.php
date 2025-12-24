<!-- Sidebar Navigation -->
<div class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white transition-transform transform -translate-x-full md:translate-x-0 z-30" id="sidebar">
    <!-- Logo Area -->
    <div class="flex items-center justify-center h-20 bg-gray-800 border-b border-gray-700">
        <h1 class="text-xl font-bold tracking-wider flex items-center gap-2">
            🏥 PharmaCIS <span class="text-blue-400 text-xs">PRO</span>
        </h1>
    </div>
    
    <div class="p-4">
        <!-- User Badge -->
        <div class="flex items-center gap-3 mb-8 p-3 bg-gray-800 rounded border-l-4 
            <?php echo ($_SESSION['role'] == 'admin') ? 'border-red-500' : 'border-green-500'; ?>">
            
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                <?php echo ($_SESSION['role'] == 'admin') ? 'bg-red-600' : 'bg-green-600'; ?>">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <div>
                <p class="font-medium text-sm">Welcome,</p>
                <p class="font-bold text-white"><?php echo ucfirst($_SESSION['username']); ?></p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-2">
            
            <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="index.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded transition group">
                <span class="group-hover:translate-x-1 transition-transform">📊 Dashboard</span>
            </a>
            <?php endif; ?>

            <a href="pos.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded transition group">
                <span class="group-hover:translate-x-1 transition-transform">💰 Point of Sale</span>
            </a>

            <a href="inventory.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded transition group">
                <span class="group-hover:translate-x-1 transition-transform">📦 Inventory List</span>
            </a>

            <?php if($_SESSION['role'] == 'admin'): ?>            
            <a href="sales_report.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded transition group">
                <span class="group-hover:translate-x-1 transition-transform">📈 Sales Reports</span>
            </a>
            <?php endif; ?>

        </nav>
    </div>
    
    <!-- Bottom Footer (Optional) -->
    <div class="absolute bottom-0 w-full p-4 text-center text-xs text-gray-500 bg-gray-800">
        &copy; 2024 PharmaCIS
    </div>
</div>