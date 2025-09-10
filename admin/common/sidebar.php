<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Overlay for mobile view -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

<!-- The Redesigned Sidebar -->
<aside id="sidebar" class="bg-white text-gray-600 w-64 fixed inset-y-0 left-0 z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-lg">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <a href="<?php echo SITE_URL; ?>admin/index.php" class="text-2xl font-bold text-gray-800 flex items-center">
            <span class="text-purple-600 mr-2"><i class="fas fa-shopping-cart"></i></span>
            <span class="logo-text">AdminKart</span>
        </a>
    </div>
    
    <nav class="mt-6 p-2">
        <a href="<?php echo SITE_URL; ?>admin/index.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo ($currentPage == 'index.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-tachometer-alt w-6 text-center nav-icon <?php echo ($currentPage == 'index.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Dashboard</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/order.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'order.php' || $currentPage == 'order_detail.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-receipt w-6 text-center nav-icon <?php echo ($currentPage == 'order.php' || $currentPage == 'order_detail.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Orders</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/product.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'product.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-box w-6 text-center nav-icon <?php echo ($currentPage == 'product.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Products</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/category.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'category.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-tags w-6 text-center nav-icon <?php echo ($currentPage == 'category.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Categories</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/blog.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'blog.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-blog w-6 text-center nav-icon <?php echo ($currentPage == 'blog.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Blog</span>
        </a>
        <hr class="my-4 border-gray-200">
        <!-- THE FIX: New Management Links -->
        <a href="<?php echo SITE_URL; ?>admin/offers.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'offers.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-prize w-6 text-center nav-icon <?php echo ($currentPage == 'pages.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Offers</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/pages.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'pages.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-file-alt w-6 text-center nav-icon <?php echo ($currentPage == 'pages.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Pages</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/reservations.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'reservations.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-calendar-check w-6 text-center nav-icon <?php echo ($currentPage == 'reservations.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Reservations</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/reviews.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'reviews.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-star w-6 text-center nav-icon <?php echo ($currentPage == 'reviews.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Reviews</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/support.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'support.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-life-ring w-6 text-center nav-icon <?php echo ($currentPage == 'support.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Support Tickets</span>
        </a>
        <hr class="my-4 border-gray-200">
        <a href="<?php echo SITE_URL; ?>admin/user.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'user.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-users w-6 text-center nav-icon <?php echo ($currentPage == 'user.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Users</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/setting.php" class="flex items-center px-4 py-3 mt-1 rounded-lg transition-colors <?php echo ($currentPage == 'setting.php') ? 'sidebar-active' : 'hover:bg-gray-100'; ?>">
            <i class="fas fa-cog w-6 text-center nav-icon <?php echo ($currentPage == 'setting.php') ? '' : 'text-gray-400'; ?>"></i><span class="ml-3 nav-text">Settings</span>
        </a>
    </nav>
</aside>