<?php
require_once '../common/config.php';
check_login();
$user_id = $_SESSION['user_id'];
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");

// Function to get color class based on order status
function get_status_badge($status) {
    switch ($status) {
        case 'Delivered':
            return 'bg-green-100 text-green-800';
        case 'Dispatched':
            return 'bg-blue-100 text-blue-800';
        case 'Cancelled':
            return 'bg-red-100 text-red-800';
        case 'Placed':
        default:
            return 'bg-yellow-100 text-yellow-800';
    }
}
?>

<div class="p-1">
    <h2 class="text-2xl font-bold text-gray-800 mb-1">My Orders</h2>
    <p class="text-sm text-gray-500 mb-6">Track your order history and view details.</p>
    
    <div class="space-y-4">
        <?php if ($orders->num_rows > 0): ?>
            <?php while($order = $orders->fetch_assoc()): ?>
                <!-- Order Card Starts -->
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <!-- Order Info -->
                        <div class="flex-grow">
                            <p class="font-bold text-gray-900 text-lg">Order #<?php echo $order['id']; ?></p>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-calendar-alt mr-1"></i> 
                                Placed on: <?php echo date('d M, Y, h:i A', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        
                        <!-- Status and Price -->
                        <div class="mt-3 sm:mt-0 flex flex-col items-start sm:items-end">
                            <span class="text-xs font-bold uppercase px-3 py-1 rounded-full <?php echo get_status_badge($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                            <p class="font-extrabold text-xl text-gray-800 mt-2"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($order['total_amount']); ?></p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="border-t mt-4 pt-3 flex items-center justify-end space-x-4"><button data-order-id="<?php echo $order['id']; ?>" class="view-order-details-btn text-sm font-semibold text-pink-600 hover:underline">
    <i class="fas fa-receipt mr-1"></i> View Details
</button>
                        
                        <?php if($order['status'] == 'Delivered' || $order['status'] == 'Completed'): ?>
                            <button class="text-sm font-semibold text-pink-600 hover:underline">
                                <i class="fas fa-redo-alt mr-1"></i> Reorder
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Order Card Ends -->
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-shopping-bag fa-3x text-gray-300"></i>
                <p class="text-gray-500 mt-4">You have no past orders.</p>
                <a href="<?php echo SITE_URL; ?>product.php" class="mt-6 inline-block bg-pink-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-700">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>