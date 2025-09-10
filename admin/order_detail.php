<?php
include __DIR__ . '/../common/config.php';
// ... baaki ka code
// --- THE FIX: Part 1 ---
// Include config directly using the robust path method.

// --- THE FIX: Part 2 ---
// Handle AJAX request completely before any HTML is printed.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    header('Content-Type: application/json');
    $order_id = (int)($_GET['id'] ?? 0); // Get order_id from URL for AJAX
    $new_status = $_POST['status'];

    if ($order_id > 0) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Order status updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Order ID.']);
    }
    // Stop execution completely.
    exit();
}


// --- Regular Page Load (GET Request) ---
// Now we include the header with HTML output.
include 'common/header.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id == 0) {
    echo "<p>Invalid Order ID.</p>";
    include 'common/bottom.php';
    exit();
}

// Fetch order details for page display
$order_result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
if ($order_result->num_rows == 0) {
    echo "<p>Order not found.</p>";
    include 'common/bottom.php';
    exit();
}
$order = $order_result->fetch_assoc();

$items_result = $conn->query("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
?>

<!-- UI and HTML (No changes here) -->
<a href="order.php" class="text-red-600 hover:underline mb-6 inline-block">&larr; Back to Orders</a>
<h1 class="text-2xl font-bold text-gray-800">Order Detail: #<?php echo $order['id']; ?></h1>
<div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold border-b pb-2 mb-4">Items Ordered</h2>
        <div class="space-y-4">
            <?php while($item = $items_result->fetch_assoc()): ?>
            <div class="flex items-center">
                <img src="<?php echo SITE_URL . ($item['image'] ?? 'assets/images/placeholder.png'); ?>" class="w-16 h-16 object-cover rounded-md">
                <div class="flex-grow ml-4">
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></p>
                    <p class="text-sm text-gray-500">Qty: <?php echo $item['quantity']; ?> &times; <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price']); ?></p>
                </div>
                <p class="font-bold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['quantity'] * $item['price']); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="border-t mt-4 pt-4 text-right">
            <p class="text-lg font-bold">Total: <span class="text-red-600"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($order['total_amount']); ?></span></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold border-b pb-2 mb-4">Customer & Shipping</h2>
        <div class="space-y-2 text-sm text-gray-700">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
        </div>
        <h2 class="text-lg font-semibold border-b pb-2 mb-4 mt-6">Order Status</h2>
        <form id="status-form">
            <input type="hidden" name="action" value="update_status">
            <select name="status" id="status" class="w-full mt-1 px-3 py-2 border rounded-md">
                <option value="Placed" <?php if($order['status'] == 'Placed') echo 'selected'; ?>>Placed</option>
                <option value="Dispatched" <?php if($order['status'] == 'Dispatched') echo 'selected'; ?>>Dispatched</option>
                <option value="Delivered" <?php if($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                <option value="Completed" <?php if($order['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                <option value="Cancelled" <?php if($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
            </select>
            <button type="submit" class="w-full mt-4 bg-red-600 text-white font-semibold py-2 rounded-md hover:bg-red-700">
                Update Status
            </button>
            <div id="status-message" class="mt-2 text-sm text-center"></div>
        </form>
    </div>
</div>

<script>
document.getElementById('status-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    showLoader();
    const formData = new FormData(e.target);
    const messageDiv = document.getElementById('status-message');

    try {
        // Post to the same page URL, which is correct
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json(); // This will now work perfectly
        
        messageDiv.textContent = result.message;
        if (result.status === 'success') {
            messageDiv.className = 'text-green-600';
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.className = 'text-red-600';
        }
    } catch (error) {
        console.error("Status update error:", error);
        messageDiv.textContent = 'A network error occurred.';
        messageDiv.className = 'text-red-600';
    } finally {
        // This will only run if the page doesn't reload (e.g., on error)
        // ensuring the loader never gets stuck.
        hideLoader();
    }
});
</script>

<?php include 'common/bottom.php'; ?>