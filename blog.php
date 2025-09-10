<?php
include 'common/header.php';

// Fetch the latest post to feature it
$latest_post_result = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC LIMIT 1");
$latest_post = $latest_post_result->num_rows > 0 ? $latest_post_result->fetch_assoc() : null;

// Fetch the rest of the posts
$other_posts_result = null;
if ($latest_post) {
    $other_posts_result = $conn->query("SELECT * FROM blogs WHERE id != {$latest_post['id']} ORDER BY created_at DESC");
} else {
    // If there's no latest post, just fetch all posts
    $other_posts_result = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
}
?>

<!-- Immersive Hero Section -->
<section class="relative h-80 flex items-center justify-center text-center text-white rounded-2xl overflow-hidden mb-16">
    <div class="absolute inset-0 bg-black opacity-50 z-10"></div>
    <img src="https://images.unsplash.com/photo-1490818387583-1b48b90b284a?w=500" class="absolute inset-0 w-full h-full object-cover">
    <div class="relative z-20">
        <h1 class="text-5xl md:text-6xl font-extrabold tracking-tighter">The QuickKart Blog</h1>
        <p class="mt-4 text-lg text-gray-200">Your daily dose of delicious stories and culinary inspiration.</p>
    </div>
</section>

<!-- Main Blog Content -->
<div class="pb-16">
    <!-- Featured Post Section -->
    <?php if ($latest_post): ?>
    <a href="blog_detail.php?id=<?php echo $latest_post['id']; ?>" class="block group mb-16" data-animate>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 card-hover">
            <div class="overflow-hidden rounded-xl">
                <img src="<?php echo SITE_URL . ($latest_post['image'] ?? 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=500'); ?>" class="w-full h-80 object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <div>
                <span class="bg-brand-primary text-white text-xs font-bold px-3 py-1 rounded-full">Featured</span>
                <h2 class="mt-4 text-3xl font-bold text-gray-800 group-hover:text-brand-primary transition-colors"><?php echo htmlspecialchars($latest_post['title']); ?></h2>
                <div class="flex items-center space-x-3 text-sm text-gray-500 mt-4">
                    <img src="https://randomuser.me/api/portraits/men/11.jpg" class="w-8 h-8 rounded-full">
                    <span>By <strong><?php echo htmlspecialchars($latest_post['author']); ?></strong></span>
                    <span>&bull;</span>
                    <span><?php echo date('M j, Y', strtotime($latest_post['created_at'])); ?></span>
                </div>
                <p class="mt-4 text-gray-600"><?php echo substr(strip_tags($latest_post['content']), 0, 150) . '...'; ?></p>
                <span class="mt-6 inline-block font-semibold text-brand-primary group-hover:underline">Read Full Story &rarr;</span>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <!-- Other Blog Posts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php while ($post = $other_posts_result->fetch_assoc()): ?>
        <a href="blog_detail.php?id=<?php echo $post['id']; ?>" class="block bg-white rounded-2xl shadow-lg overflow-hidden group card-hover">
            <div class="overflow-hidden h-48">
                <img src="<?php echo SITE_URL . ($post['image'] ?? 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=500'); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <div class="p-6">
                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full">Article</span>
                <h2 class="mt-3 text-xl font-bold text-gray-800 group-hover:text-brand-primary transition-colors h-14"><?php echo htmlspecialchars($post['title']); ?></h2>
                <div class="mt-4 pt-4 border-t flex items-center space-x-3 text-xs text-gray-500">
                    <img src="https://randomuser.me/api/portraits/women/12.jpg" class="w-6 h-6 rounded-full">
                    <span>By <strong><?php echo htmlspecialchars($post['author']); ?></strong></span>
                    <span>&bull;</span>
                    <span><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                </div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</div>
<?php
include 'common/bottom.php';
?>