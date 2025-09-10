<!-- Sidebar Overlay -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white shadow-xl z-50 transform -translate-x-full transition-transform duration-300 ease-in-out">
    <div class="p-4 border-b">
        <a href="<?php echo SITE_URL; ?>index.php" class="text-2xl font-bold text-red-600">Quick<span class="text-gray-800">Kart</span></a>
    </div>
    <nav class="mt-4">
        <a href="<?php echo SITE_URL; ?>index.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-home w-6 text-center"></i><span class="ml-3">Home</span>
        </a>
        <a href="<?php echo SITE_URL; ?>product.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-box-open w-6 text-center"></i><span class="ml-3">All Products</span>
        </a>
        <a href="<?php echo SITE_URL; ?>order.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-receipt w-6 text-center"></i><span class="ml-3">My Orders</span>
        </a>
        <a href="<?php echo SITE_URL; ?>profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-user-circle w-6 text-center"></i><span class="ml-3">Profile</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
             <a href="<?php echo SITE_URL; ?>login.php?logout=true" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
                <i class="fas fa-sign-out-alt w-6 text-center"></i><span class="ml-3">Logout</span>
            </a>
        <?php else: ?>
             <a href="<?php echo SITE_URL; ?>login.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
                <i class="fas fa-sign-in-alt w-6 text-center"></i><span class="ml-3">Login / Sign Up</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>