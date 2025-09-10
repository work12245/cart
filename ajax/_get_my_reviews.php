<?php
require_once '../common/config.php';
check_login();
$user_id = $_SESSION['user_id'];
$reviews = $conn->query("SELECT r.*, p.name as product_name FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.user_id = $user_id ORDER BY r.created_at DESC");
?>
<h2 class="text-2xl font-bold mb-1">My Reviews</h2>
<p class="text-sm text-gray-500 mb-6">Here are all the reviews you've written.</p>
<div class="space-y-4 max-h-96 overflow-y-auto">
    <?php if ($reviews->num_rows > 0): ?>
        <?php while($review = $reviews->fetch_assoc()): ?>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <div class="flex justify-between items-center">
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($review['product_name']); ?></p>
                    <div class="text-yellow-500">
                        <?php for($i=0; $i<$review['rating']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                        <?php for($i=0; $i<5-$review['rating']; $i++) echo '<i class="far fa-star"></i>'; ?>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-1">Reviewed on <?php echo date('d M, Y', strtotime($review['created_at'])); ?></p>
                <p class="text-sm text-gray-700 mt-2 p-2 bg-white border rounded"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-gray-400 text-center py-10">You haven't written any reviews yet. You can write reviews for products from your delivered orders.</p>
    <?php endif; ?>
</div>