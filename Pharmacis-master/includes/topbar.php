<!-- Top Navigation Bar -->
<header class="bg-white shadow px-6 py-4 flex justify-between items-center mb-6">
    <!-- Left: Page Title -->
    <h2 class="text-2xl font-semibold text-gray-800">
        <?php echo isset($page_title) ? $page_title : 'PharmaCIS'; ?>
    </h2>

    <!-- Right: User Info & Logout -->
    <div class="flex items-center gap-6">
        <div class="text-right hidden md:block">
            <p class="text-sm text-gray-600">Logged in as</p>
            <p class="font-bold text-gray-800 capitalize"><?php echo $_SESSION['username']; ?></p>
        </div>
        
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow transition flex items-center gap-2 text-sm font-bold">
            Logout
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </a>
    </div>
</header>