<?php
// This check prevents direct access to the file
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    exit('Direct access not allowed');
}
$user_id_sidebar = $_SESSION['user_id'];
$user_sidebar_result = $conn->query("SELECT name, email FROM users WHERE id = $user_id_sidebar");
$user_sidebar = $user_sidebar_result->fetch_assoc();
?>

<!-- Profile Sidebar -->
<div class="profile-sidebar w-full lg:w-1/4 p-6 border-r border-gray-200">
    <div class="text-center">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_sidebar['name']); ?>&background=E83E8C&color=fff&size=96" alt="User Avatar" class="w-24 h-24 rounded-full mx-auto border-4 border-white shadow-md">
        <h2 class="mt-4 text-xl font-bold"><?php echo htmlspecialchars($user_sidebar['name']); ?></h2>
        <p class="text-sm text-gray-500">@<?php echo htmlspecialchars(explode('@', $user_sidebar['email'])[0]); ?></p>
    </div>
    
    <nav class="mt-8 space-y-2">
        <!-- THE FIX: All 8 menu items are now included in the correct order -->
        <a href="#" class="profile-nav-link flex items-center px-4 py-2 text-gray-600 rounded-lg" data-target="ajax/_get_profile_form.php" data-title="Profile">
            <i class="fas fa-user-circle w-6 text-gray-400"></i><span>Profile</span>
        </a>
        <a href="#" class="profile-nav-link flex items-center px-4 py-2 text-gray-600 rounded-lg" data-target="ajax/_get_my_orders.php" data-title="My Orders">
            <i class="fas fa-receipt w-6 text-gray-400"></i><span>My Orders</span>
        </a>
        <a href="#" class="profile-nav-link flex items-center px-4 py-2 text-gray-600 rounded-lg" data-target="ajax/_get_address_form.php" data-title="Address">
            <i class="fas fa-map-marker-alt w-6 text-gray-400"></i><span>Address</span>
        </a>
        <a href="#" class="profile-nav-link flex items-center px-4 py-2 text-gray-600 rounded-lg" data-target="ajax/_get_reservation_form.php" data-title="Reservation">
            <i class="fas fa-calendar-check w-6 text-gray-400"></i><span>Reservation</span>
        </a>
        <a href="#" class="profile-nav-link flex items-center px-4 py-2 text-gray-600 rounded-lg" data-target="ajax/_get_my_reviews.php" data-title="My Reviews">
            <i class="fas fa-star w-6 text-gray-400"></i><span>My Reviews</span>
        </a>
        <a href="#" class="profile-nav-link flex items-center px-4 py-2 text-gray-600 rounded-lg" data-target="ajax/_get_support_form.php" data-title="Help & Support">
            <i class="fas fa-question-circle w-6 text-gray-400"></i><span>Help & Support</span>
        </a>
        <a href="#" class="profile-nav-link flex items-center px-4 py-2 text-gray-600 rounded-lg" data-target="ajax/_get_password_form.php" data-title="Password">
            <i class="fas fa-lock w-6 text-gray-400"></i><span>Password</span>
        </a>
        <a href="login.php?logout=true" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-red-50 hover:text-red-600">
            <i class="fas fa-sign-out-alt w-6 text-gray-400"></i><span>Logout</span>
        </a>
    </nav>
</div>