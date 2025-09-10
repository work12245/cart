<?php
include 'common/header.php';
check_login();

$user_id = $_SESSION['user_id'];
$active_orders_result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id AND status IN ('Placed', 'Dispatched') ORDER BY created_at DESC");
$past_orders_result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id AND status IN ('Delivered', 'Cancelled') ORDER BY created_at DESC");

function get_order_items($order_id, $conn) {
    $items = [];
    $result = $conn->query("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

function get_progress_width($status) {
    if ($status == 'Placed') return 'w-0';
    if ($status == 'Dispatched') return 'w-1/2';
    if ($status == 'Delivered') return 'w-full';
    return 'w-0';
}
?>

<h1 class="text-2xl font-bold text-gray-800 my-6">My Orders</h1>

<div class="border-b border-gray-200">
    <nav class="flex -mb-px space-x-6" id="order-tabs">
        <button onclick="switchOrderTab('active')" data-tab="active" class="tab-button py-4 px-1 border-b-2 font-medium text-sm text-brand-primary border-brand-primary">Active Orders</button>
        <button onclick="switchOrderTab('history')" data-tab="history" class="tab-button py-4 px-1 border-b-2 font-medium text-sm text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300">Order History</button>
    </nav>
</div>

<div id="order-content" class="mt-6">
    <div id="active-orders" class="space-y-6">
        <?php if ($active_orders_result->num_rows > 0): ?>
            <?php while($order = $active_orders_result->fetch_assoc()): 
                $items = get_order_items($order['id'], $conn);
                // THE FIX: Check if items exist before trying to display them
                if (empty($items)) continue; // Skip rendering this order if it has no items
                $first_item = $items[0];
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 border-b">
                    <div class="flex items-start">
                        <img src="<?php echo file_exists($first_item['image']) ? SITE_URL.$first_item['image'] : 'https://via.placeholder.com/100'; ?>" class="w-20 h-20 object-cover rounded-md">
                        <div class="ml-4 flex-grow">
                             <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($first_item['name'] ?? 'N/A'); ?></h3>
                             <?php if(count($items) > 1): ?><p class="text-sm text-gray-500">+ <?php echo count($items) - 1; ?> more item(s)</p><?php endif; ?>
                             <p class="text-sm text-gray-400 mt-1">Order #<?php echo $order['id']; ?></p>
                        </div>
                        <div class="text-right">
                           <p class="font-bold text-lg text-brand-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($order['total_amount']); ?></p>
                           <p class="text-sm font-semibold text-blue-600"><?php echo $order['status']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="relative w-full">
                        <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-200 -translate-y-1/2"></div>
                        <div class="absolute top-1/2 left-0 h-1 bg-green-500 -translate-y-1/2 transition-all duration-500 <?php echo get_progress_width($order['status']); ?>"></div>
                        <div class="relative flex justify-between items-start">
                            <div class="text-center"><div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white"><i class="fas fa-check"></i></div><p class="text-xs mt-1 font-semibold">Placed</p></div>
                            <div class="text-center"><div class="w-8 h-8 rounded-full flex items-center justify-center text-white <?php echo ($order['status'] == 'Dispatched' || $order['status'] == 'Delivered') ? 'bg-green-500' : 'bg-gray-400'; ?>"><i class="fas fa-truck"></i></div><p class="text-xs mt-1 font-semibold">Dispatched</p></div>
                            <div class="text-center"><div class="w-8 h-8 rounded-full flex items-center justify-center text-white <?php echo ($order['status'] == 'Delivered') ? 'bg-green-500' : 'bg-gray-400'; ?>"><i class="fas fa-box-check"></i></div><p class="text-xs mt-1 font-semibold">Delivered</p></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-gray-500 py-8">No active orders found.</p>
        <?php endif; ?>
    </div>

    <div id="order-history" class="space-y-6 hidden">
        <?php if ($past_orders_result->num_rows > 0): while($order = $past_orders_result->fetch_assoc()): 
            $items = get_order_items($order['id'], $conn);
            if (empty($items)) continue;
        ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 border-b"> <!-- Order Header -->
                    <p class="text-sm text-gray-500">Order #<?php echo $order['id']; ?> | <?php echo date('d M, Y', strtotime($order['created_at'])); ?></p>
                </div>
                <!-- Order Items -->
                <div class="divide-y">
                <?php foreach($items as $item): ?>
                    <div class="p-4 flex justify-between items-center">
                        <div class="flex items-center">
                            <img src="<?php echo file_exists($item['image']) ? SITE_URL.$item['image'] : 'https://via.placeholder.com/80'; ?>" class="w-16 h-16 object-cover rounded-md">
                            <div class="ml-4">
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price']); ?></p>
                            </div>
                        </div>
                        <?php if ($order['status'] == 'Delivered'): ?>
                        <!-- THE FIX: "Write a Review" button for delivered items -->
                        <button onclick="openReviewModal(<?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>')" class="bg-yellow-400 text-yellow-900 font-bold text-xs px-3 py-1 rounded-full hover:bg-yellow-500">
                            <i class="fas fa-star"></i> Write a Review
                        </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        <?php endwhile; else: ?>
            <p class="text-center text-gray-500 py-8">No past orders found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<div id="review-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-md p-6">
        <h2 class="text-2xl font-bold mb-2">Write a Review for</h2>
        <p id="review-product-name" class="text-gray-600 mb-4"></p>
        <form id="review-form">
            <input type="hidden" name="action" value="add_review">
            <input type="hidden" id="review-product-id" name="product_id">
            <div class="mb-4">
                <label class="font-semibold">Your Rating:</label>
                <div id="star-rating" class="flex items-center text-3xl text-gray-300 cursor-pointer">
                    <i class="fas fa-star" data-value="1"></i><i class="fas fa-star" data-value="2"></i>
                    <i class="fas fa-star" data-value="3"></i><i class="fas fa-star" data-value="4"></i>
                    <i class="fas fa-star" data-value="5"></i>
                </div>
                <input type="hidden" id="rating-value" name="rating" required>
            </div>
            <div class="mb-4">
                <label for="review-comment" class="font-semibold">Your Comment:</label>
                <textarea id="review-comment" name="comment" rows="4" required class="w-full mt-1 p-2 border rounded-md"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeReviewModal()" class="bg-gray-200 px-4 py-2 rounded-md">Cancel</button>
                <button type="submit" class="bg-brand-primary text-white px-4 py-2 rounded-md">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchOrderTab(tab) {
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('text-brand-primary', 'border-brand-primary');
        button.classList.add('text-gray-500', 'border-transparent');
    });
    document.querySelector(`[data-tab="${tab}"]`).classList.add('text-brand-primary', 'border-brand-primary');
    document.getElementById('active-orders').classList.toggle('hidden', tab !== 'active');
    document.getElementById('order-history').classList.toggle('hidden', tab !== 'history');
}

const reviewModal = document.getElementById('review-modal');
const ratingStars = [...document.querySelectorAll('#star-rating .fa-star')];

function openReviewModal(productId, productName) {
    document.getElementById('review-product-id').value = productId;
    document.getElementById('review-product-name').textContent = productName;
    reviewModal.classList.remove('hidden');
}
function closeReviewModal() { reviewModal.classList.add('hidden'); }

ratingStars.forEach(star => {
    star.onclick = () => {
        const rating = star.dataset.value;
        document.getElementById('rating-value').value = rating;
        ratingStars.forEach(s => s.classList.toggle('text-yellow-400', s.dataset.value <= rating));
    };
});

document.getElementById('review-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    showLoader();
    try {
        const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: new FormData(this) });
        const result = await response.json();
        if (result.status === 'success') {
            closeReviewModal();
            showModal('Success!', 'Your review has been submitted. Thank you!');
        } else { showModal('Error', result.message || 'Failed to submit review.'); }
    } catch(e) { console.error(e); } finally { hideLoader(); }
});
</script>

<?php include 'common/bottom.php'; ?>