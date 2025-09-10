<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code

// Fetch orders with user details
$orders_result = $conn->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
?>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Orders</h1>

<!-- Orders Table -->
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="w-full table-auto">
        <thead class="bg-gray-200 text-sm">
            <tr>
                <th class="px-4 py-2 text-left">Order ID</th>
                <th class="px-4 py-2 text-left">Customer</th>
                <th class="px-4 py-2 text-left">Date</th>
                <th class="px-4 py-2 text-right">Amount</th>
                <th class="px-4 py-2 text-center">Status</th>
                <th class="px-4 py-2 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders_result->num_rows > 0): ?>
                <?php while ($order = $orders_result->fetch_assoc()): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-sm">#<?php echo $order['id']; ?></td>
                    <td class="px-4 py-2 font-medium"><?php echo htmlspecialchars($order['user_name']); ?></td>
                    <td class="px-4 py-2 text-sm text-gray-600"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                    <td class="px-4 py-2 text-right font-semibold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($order['total_amount']); ?></td>
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            <?php if ($order['status'] == 'Placed') echo 'bg-blue-100 text-blue-800';
                                  elseif ($order['status'] == 'Dispatched') echo 'bg-yellow-100 text-yellow-800';
                                  elseif ($order['status'] == 'Delivered') echo 'bg-green-100 text-green-800';
                                  else echo 'bg-red-100 text-red-800'; ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="text-red-500 hover:text-red-700 font-semibold">
                            View Details
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>