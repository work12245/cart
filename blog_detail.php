<?php
include 'common/header.php';

// Fetch the current post
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id == 0) { echo "<p>Post not found.</p>"; include 'common/bottom.php'; exit(); }
$result = $conn->query("SELECT * FROM blogs WHERE id = $post_id");
if ($result->num_rows == 0) { echo "<p>Post not found.</p>"; include 'common/bottom.php'; exit(); }
$post = $result->fetch_assoc();

// Fetch recent posts for the sidebar
$recent_posts_result = $conn->query("SELECT id, title, created_at FROM blogs WHERE id != $post_id ORDER BY created_at DESC LIMIT 4");
?>
<!-- Tailwind Typography Plugin for beautiful article styling -->
<script src="https://cdn.tailwindcss.com/typography@0.5.x"></script>

<div class="pt-8 pb-16">
    <!-- Magazine Style Header -->
    <div class="relative h-96 flex items-end justify-center text-center text-white rounded-2xl overflow-hidden mb-12">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent z-10"></div>
        <img src="<?php echo SITE_URL . ($post['image'] ?? ''); ?>" class="absolute inset-0 w-full h-full object-cover">
        <div class="relative z-20 p-8">
            <span class="bg-brand-primary text-white text-xs font-bold px-3 py-1 rounded-full uppercase">Featured Story</span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tighter mt-4"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="mt-4 text-sm text-gray-300 flex justify-center items-center space-x-3">
                <img src="https://randomuser.me/api/portraits/men/11.jpg" class="w-8 h-8 rounded-full border-2 border-white">
                <span>By <strong><?php echo htmlspecialchars($post['author']); ?></strong></span>
                <span>&bull;</span>
                <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
        <!-- Main Blog Content -->
        <div class="lg:col-span-2 bg-white p-6 sm:p-8 rounded-2xl shadow-xl">
            <!-- Article content styled with Tailwind Typography -->
            <article class="prose lg:prose-xl max-w-none text-gray-700">
                <?php echo $post['content']; // The content from database which can have HTML tags ?>
            </article>

            <!-- Social Share Buttons -->
            <div class="mt-12 pt-6 border-t">
                <h3 class="font-bold text-lg text-gray-800">Share this post</h3>
                <div class="flex space-x-3 mt-4">
                    <a href="#" class="w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-transform hover:scale-110"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-12 h-12 flex items-center justify-center bg-sky-500 text-white rounded-full hover:bg-sky-600 transition-transform hover:scale-110"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="w-12 h-12 flex items-center justify-center bg-blue-700 text-white rounded-full hover:bg-blue-800 transition-transform hover:scale-110"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="w-12 h-12 flex items-center justify-center bg-red-600 text-white rounded-full hover:bg-red-700 transition-transform hover:scale-110"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white p-6 rounded-2xl shadow-xl">
                <h3 class="font-bold text-xl text-gray-800 border-b pb-3 mb-4">Search</h3>
                <div class="relative"><input type="text" placeholder="Search in blog..." class="w-full border-gray-300 rounded-lg"><i class="fas fa-search absolute right-3 top-3 text-gray-400"></i></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-xl">
                <h3 class="font-bold text-xl text-gray-800 border-b pb-3 mb-4">Recent Posts</h3>
                <ul class="space-y-4">
                    <?php while($recent = $recent_posts_result->fetch_assoc()): ?>
                    <li>
                        <a href="blog_detail.php?id=<?php echo $recent['id']; ?>" class="group block p-2 rounded-lg hover:bg-gray-50">
                            <p class="font-semibold text-gray-700 group-hover:text-brand-primary"><?php echo htmlspecialchars($recent['title']); ?></p>
                            <p class="text-xs text-gray-400"><?php echo date('M j, Y', strtotime($recent['created_at'])); ?></p>
                        </a>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
include 'common/bottom.php';
?>