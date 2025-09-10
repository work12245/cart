<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code

// Handle review actions (Approve/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $review_id = (int)$_POST['review_id'];
    if ($review_id > 0) {
        if ($_POST['action'] == 'approve_review') {
            $stmt = $conn->prepare("UPDATE reviews SET is_approved = TRUE WHERE id = ?");
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
        } elseif ($_POST['action'] == 'delete_review') {
            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
        }
    }
}

// Fetch all reviews with user and product details
$reviews = $conn->query("SELECT r.*, u.name as user_name, p.name as product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC");
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Reviews</h1>
</div>

<div class="bg-white shadow-xl rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Product & User</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Rating</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Comment</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($reviews->num_rows > 0): while ($rev = $reviews->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($rev['product_name']); ?></div>
                        <div class="text-sm text-gray-500">by <?php echo htmlspecialchars($rev['user_name']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-center text-yellow-500 font-bold">
                        <?php echo $rev['rating']; ?> <i class="fas fa-star"></i>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-sm" title="<?php echo htmlspecialchars($rev['comment']); ?>">
                        <p class="truncate"><?php echo htmlspecialchars($rev['comment']); ?></p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($rev['is_approved']): ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                        <?php else: ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center space-x-2">
                            <?php if (!$rev['is_approved']): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="approve_review">
                                <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                <button type="submit" class="text-green-600 hover:text-green-900 font-bold">Approve</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                <input type="hidden" name="action" value="delete_review">
                                <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="text-center py-10 text-gray-500">No reviews found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>