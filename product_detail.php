<?php
require_once 'common/config.php';

// --- AJAX REQUEST HANDLER ---
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    if ($_POST['action'] == 'add_to_cart') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        if ($product_id > 0 && $quantity > 0) {
            if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
            $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
            echo json_encode(['status' => 'success', 'message' => 'Product added to cart!', 'cart_count' => (isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0)]);
        } else { echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity.']); }
    }
    // === NEW BUY NOW ACTION ===
    if ($_POST['action'] == 'buy_now_single') { // Changed action name for product_detail page
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        if ($product_id > 0 && $quantity > 0) {
            // Store the single product details for "Buy Now" without affecting the main cart
            $_SESSION['buy_now_product'] = ['product_id' => $product_id, 'quantity' => $quantity];
            echo json_encode(['status' => 'success', 'message' => 'Redirecting to checkout for this product.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity for Buy Now.']);
        }
    }
    // === END NEW BUY NOW ACTION ===

    if ($_POST['action'] == 'submit_review') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'You must be logged in to submit a review.']);
            exit();
        }
        $product_id = (int)($_POST['product_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($product_id > 0 && $rating >= 1 && $rating <= 5) {
            $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
            if ($stmt->execute()) {
                // Fetch user name for the response
                $user_name_result = $conn->query("SELECT name FROM users WHERE id = $user_id");
                $user_name = $user_name_result->fetch_assoc()['name'] ?? 'You';
                echo json_encode(['status' => 'success', 'message' => 'Thank you for your review!', 'review' => ['author' => $user_name, 'rating' => $rating, 'comment' => htmlspecialchars($comment), 'date' => 'Just now']]);
            } else { echo json_encode(['status' => 'error', 'message' => 'Could not submit review.']); }
        } else { echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']); }
    }
    exit();
}

include 'common/header.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id == 0) { echo "<p>Product not found.</p>"; include 'common/bottom.php'; exit(); }
$result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id WHERE p.id = $product_id");
if ($result->num_rows == 0) { echo "<p>Post not found.</p>"; include 'common/bottom.php'; exit(); }
$product = $result->fetch_assoc();
$old_price = number_format($product['price'] * 1.3, 2);
$nutrition = json_decode($product['nutrition_info'] ?? '[]', true);
$allergens = explode(',', $product['allergens'] ?? '');

$related_products_result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id WHERE p.cat_id = {$product['cat_id']} AND p.id != $product_id LIMIT 4");

// Fetch reviews
$reviews_result = $conn->query("SELECT r.*, u.name as author_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = $product_id ORDER BY r.created_at DESC");
$reviews = [];
$total_rating = 0;
while($row = $reviews_result->fetch_assoc()) { $reviews[] = $row; $total_rating += $row['rating']; }
$avg_rating = count($reviews) > 0 ? $total_rating / count($reviews) : 0;
?>

<div class="pt-8 pb-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
        <!-- 3D Image Gallery -->
        <div style="perspective: 1000px;">
            <div id="image-container" class="bg-gray-100 rounded-2xl p-4 shadow-sm mb-4 transition-transform duration-500" style="transform-style: preserve-3d;">
                <img id="main-product-image" src="<?php echo file_exists($product['image']) ? SITE_URL.$product['image'] : 'https://i.ibb.co/fH7Bw3t/product-pizza.png'; ?>" class="w-full h-auto object-contain drop-shadow-2xl">
            </div>
        </div>
        <!-- Product Details -->
        <div>
            <span class="bg-brand-light text-brand-primary text-xs font-semibold px-3 py-1 rounded-full"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mt-4"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="mt-4 flex items-center space-x-4">
                <div class="text-yellow-500 text-lg"> <?php for($i=1; $i<=5; $i++){ echo $i <= round($avg_rating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; } ?> </div>
                <span class="text-sm text-gray-500">(<?php echo count($reviews); ?> Customer Reviews)</span>
            </div>
            <p class="mt-6 text-4xl font-extrabold text-gray-900"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 2); ?> <s class="text-2xl text-gray-400 font-normal"><?php echo CURRENCY_SYMBOL; ?><?php echo $old_price; ?></s></p>
            <div class="mt-8 pt-6 border-t">
                <form id="detail-cart-form">
                    <input type="hidden" id="detail-product-id" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="flex items-center space-x-4 mb-4">
                        <label class="font-semibold">Quantity:</label>
                        <div class="flex items-center border border-gray-300 rounded-lg">
                            <button type="button" class="quantity-btn px-4 py-3 text-lg font-bold text-gray-700" data-change="-1">-</button>
                            <input type="number" id="detail-quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="w-16 text-center border-l border-r py-2 focus:outline-none">
                            <button type="button" class="quantity-btn px-4 py-3 text-lg font-bold text-gray-700" data-change="1">+</button>
                        </div>
                        <p id="detail-stock" class="text-sm font-semibold text-green-600"><?php echo $product['stock']; ?> In Stock</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button type="button" id="add-to-cart-btn" class="w-full bg-brand-light text-brand-primary font-bold py-3 rounded-lg border-2 border-brand-primary hover:bg-brand-primary hover:text-white transition-colors"><i class="fas fa-shopping-cart mr-2"></i>Add to Cart</button>
                        <button type="button" id="buy-now-btn" class="w-full bg-gray-800 text-white font-bold py-3 rounded-lg hover:bg-gray-900 transition-colors">Buy Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- New Tabbed Section -->
    <div class="mt-16">
        <div class="border-b border-gray-200">
            <nav id="info-tabs" class="flex space-x-8" aria-label="Tabs">
                <button class="tab-btn py-4 px-1 border-b-2 font-semibold text-brand-primary border-brand-primary" data-target="description">Description</button>
                <button class="tab-btn py-4 px-1 border-b-2 font-semibold text-gray-500 border-transparent hover:text-gray-700" data-target="nutrition">Nutritional Info</button>
                <button class="tab-btn py-4 px-1 border-b-2 font-semibold text-gray-500 border-transparent hover:text-gray-700" data-target="reviews">Reviews (<?php echo count($reviews); ?>)</button>
            </nav>
        </div>
        <div class="mt-8">
            <div id="description" class="tab-content glassmorphism rounded-2xl p-8 prose max-w-none"> <?php echo nl2br($product['description']); ?> </div>
            <div id="nutrition" class="tab-content hidden glassmorphism rounded-2xl p-8">
                <h3 class="text-xl font-bold mb-4">Nutritional Information</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <?php if(!empty($nutrition)): foreach($nutrition as $key => $value): ?>
                        <div><div class="font-semibold text-gray-700"><?php echo htmlspecialchars($key); ?></div><div class="text-lg text-gray-500"><?php echo htmlspecialchars($value); ?></div></div>
                    <?php endforeach; else: echo "<p class='col-span-full text-gray-500'>No nutritional information available.</p>"; endif; ?>
                </div>
                <!-- Allergen Warning -->
                <?php if(!empty(array_filter($allergens))): ?>
                <div class="mt-8 bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 rounded-r-lg">
                    <div class="flex"><div class="py-1"><i class="fas fa-exclamation-triangle mr-3"></i></div><div><p class="font-bold">Allergen Warning</p><p class="text-sm">Contains: <?php echo htmlspecialchars(implode(', ', array_filter($allergens))); ?></p></div></div>
                </div>
                <?php endif; ?>
            </div>
            <div id="reviews" class="tab-content hidden glassmorphism rounded-2xl p-8">
                <h3 class="text-xl font-bold mb-4">Customer Reviews</h3>
                <!-- Review Summary -->
                <div class="flex items-center mb-6">
                    <div class="text-4xl font-bold mr-4"><?php echo number_format($avg_rating, 1); ?><span class="text-2xl text-gray-400">/5</span></div>
                    <div><div class="text-yellow-500 text-xl"><?php for($i=1; $i<=5; $i++){ echo $i <= round($avg_rating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; } ?></div><p class="text-sm text-gray-500">Based on <?php echo count($reviews); ?> reviews</p></div>
                </div>
                <!-- Review Form (for logged in users) -->
                <?php if(isset($_SESSION['user_id'])): ?>
                <div class="mb-8 border-t pt-6">
                    <h4 class="font-semibold mb-2">Leave a Review</h4>
                    <form id="review-form">
                        <div class="flex items-center mb-2" id="star-rating">
                            <?php for($i=1; $i<=5; $i++): ?><i class="far fa-star text-2xl text-gray-300 cursor-pointer star" data-value="<?php echo $i; ?>"></i><?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating-value" value="0">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="action" value="submit_review">
                        <textarea name="comment" class="w-full border rounded-lg p-2" placeholder="Share your experience..."></textarea>
                        <button type="submit" class="mt-2 bg-brand-primary text-white font-semibold py-2 px-5 rounded-lg">Submit Review</button>
                    </form>
                </div>
                <?php endif; ?>
                <!-- Reviews List -->
                <div id="reviews-list" class="space-y-6">
                    <?php foreach($reviews as $review): ?>
                    <div class="border-t pt-6"><div class="flex items-center"><img src="https://randomuser.me/api/portraits/men/<?php echo $review['user_id'] % 100; ?>.jpg" class="w-10 h-10 rounded-full"><div class="ml-4"><div class="font-bold"><?php echo htmlspecialchars($review['author_name']); ?></div><div class="text-yellow-500 text-sm"><?php for($i=1; $i<=5; $i++){ echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; } ?></div></div></div><p class="mt-2 text-gray-600"><?php echo htmlspecialchars($review['comment']); ?></p></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section -->
<section class="mt-16">
    <h2 class="text-4xl font-extrabold text-gray-800 tracking-tighter mb-8">Related Products</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
    <?php while ($related = $related_products_result->fetch_assoc()): $old_price_rel = number_format($related['price'] * 1.3, 2); ?>
        <a href="product_detail.php?id=<?php echo $related['id']; ?>" class="block group">
            <div class="bg-white rounded-2xl p-6 product-card">
                <div class="relative mb-4">
                    <div class="bg-gray-100 rounded-full w-48 h-48 mx-auto overflow-hidden"><img src="<?php echo file_exists($related['image']) ? SITE_URL.$related['image'] : 'https://i.ibb.co/fH7Bw3t/product-pizza.png'; ?>" class="w-full h-full object-cover"></div>
                    <span class="absolute top-2 left-2 bg-brand-primary text-white text-xs font-semibold px-3 py-1 rounded-full"><?php echo htmlspecialchars($related['category_name'] ?? 'Food'); ?></span>
                </div>
                <div class="flex flex-col items-center">
                    <div class="text-yellow-500 text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div>
                    <h3 class="mt-2 text-xl font-bold text-gray-800 text-center truncate w-full"><?php echo htmlspecialchars($related['name']); ?></h3>
                    <p class="mt-2 font-semibold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($related['price'], 2); ?> <s class="text-gray-400 font-normal"><?php echo CURRENCY_SYMBOL; ?><?php echo $old_price_rel; ?></s></p>
                </div>
            </div>
        </a>
    <?php endwhile; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity Counter Logic
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const change = parseInt(this.dataset.change);
            const qtyInput = document.getElementById('detail-quantity');
            const maxStock = parseInt(qtyInput.max);
            let currentVal = parseInt(qtyInput.value);
            if (currentVal + change > 0 && currentVal + change <= maxStock) {
                qtyInput.value = currentVal + change;
            }
        });
    });

    // Add to Cart Logic
    document.getElementById('add-to-cart-btn').addEventListener('click', async function() {
        showLoader();
        const form = document.getElementById('detail-cart-form');
        const formData = new FormData(form);
        formData.append('action', 'add_to_cart');
        try {
            const response = await fetch('product_detail.php', { method: 'POST', body: formData });
            const result = await response.json();
            showModal(result.status === 'success' ? 'Success!' : 'Error', result.message);
            if(result.status === 'success') {
                setTimeout(() => location.reload(), 1500); // Reload to update cart count in header
            }
        } catch (error) {
            showModal('Error', 'An error occurred.');
        } finally {
            hideLoader();
        }
    });

    // === REVISED BUY NOW LOGIC ===
    document.getElementById('buy-now-btn').addEventListener('click', async function() {
        showLoader();
        const form = document.getElementById('detail-cart-form');
        const formData = new FormData(form);
        formData.append('action', 'buy_now_single'); // New action for Buy Now from product_detail page
        try {
            const response = await fetch('product_detail.php', { method: 'POST', body: formData });
            const result = await response.json();
            if(result.status === 'success') {
                window.location.href = 'checkout.php?mode=buy_now'; // Redirect with a specific mode
            } else {
                showModal('Error', result.message);
            }
        } catch (error) {
            showModal('Error', 'An error occurred.');
        } finally {
            hideLoader();
        }
    });
    // === END REVISED BUY NOW LOGIC ===

    // --- 3D Image Effect ---
    const container = document.getElementById('image-container');
    container.addEventListener('mousemove', (e) => {
        const { left, top, width, height } = container.getBoundingClientRect();
        const x = (e.clientX - left) / width - 0.5;
        const y = (e.clientY - top) / height - 0.5;
        container.style.transform = `rotateY(${x * 15}deg) rotateX(${-y * 15}deg) scale3d(1.05, 1.05, 1.05)`;
    });
    container.addEventListener('mouseleave', () => { container.style.transform = 'rotateY(0) rotateX(0) scale3d(1, 1, 1)'; });

    // --- Tab Switching Logic ---
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(clickedTab => {
        clickedTab.addEventListener('click', () => {
  // First, reset all tabs to their inactive state
            tabs.forEach(tab => {
                tab.classList.remove('text-brand-primary', 'border-brand-primary');
                tab.classList.add('text-gray-500', 'border-transparent');
            });
            // Then, activate only the clicked tab
            clickedTab.classList.add('text-brand-primary', 'border-brand-primary');
            clickedTab.classList.remove('text-gray-500', 'border-transparent');
            // Hide all content panels
            contents.forEach(content => {
                content.classList.add('hidden');
            });
            // Show the target content panel
            const target = document.getElementById(clickedTab.dataset.target);
            if (target) {
                target.classList.remove('hidden');
            }
        });
    });

    // --- Star Rating Logic ---
    const stars = document.querySelectorAll('#star-rating .star');
    const ratingValue = document.getElementById('rating-value');
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            resetStars();
            const val = this.dataset.value;
            for(let i=0; i < val; i++){ stars[i].classList.replace('far', 'fas'); stars[i].classList.add('text-yellow-400'); }
        });
        star.addEventListener('mouseout', resetStars);
        star.addEventListener('click', function() {
            ratingValue.value = this.dataset.value;
            // To make the selection permanent until another hover/click
            star.parentNode.removeEventListener('mouseout', resetStars);
        });
    });
    function resetStars(){
        const val = ratingValue.value;
        stars.forEach((star, index) => {
            if(index < val) { star.classList.replace('far', 'fas'); star.classList.add('text-yellow-500'); star.classList.remove('text-gray-300'); }
            else { star.classList.replace('fas', 'far'); star.classList.remove('text-yellow-400', 'text-yellow-500'); star.classList.add('text-gray-300'); }
        });
    }

    // --- Review Form Submission ---
    const reviewForm = document.getElementById('review-form');
    if(reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (ratingValue.value == 0) { showModal('Error', 'Please select a rating.'); return; }
            showLoader();
            const formData = new FormData(this);
            try {
                const response = await fetch('product_detail.php', { method: 'POST', body: formData });
                const result = await response.json();
                if(result.status === 'success') {
                    // Add new review to the list without reloading
                    const newReviewHTML = `<div class="border-t pt-6"><div class="flex items-center"><img src="https://randomuser.me/api/portraits/men/${Math.floor(Math.random()*100)}.jpg" class="w-10 h-10 rounded-full"><div class="ml-4"><div class="font-bold">${result.review.author}</div><div class="text-yellow-500 text-sm">${'<i class="fas fa-star"></i>'.repeat(result.review.rating)}${'<i class="far fa-star"></i>'.repeat(5-result.review.rating)}</div></div></div><p class="mt-2 text-gray-600">${result.review.comment}</p></div>`;
                    document.getElementById('reviews-list').insertAdjacentHTML('afterbegin', newReviewHTML);
                    showModal('Success!', result.message);
                    reviewForm.reset(); // Clear form
                    ratingValue.value = 0; // Reset rating
                    resetStars(); // Update star display
                    // setTimeout(()=>location.reload(), 1500); // Only reload if necessary for cart count etc.
                } else { showModal('Error', result.message); }
            } catch(error) { showModal('Error', 'An error occurred.'); }
            finally { hideLoader(); }
        });
    }
});
</script>

<?php
include 'common/bottom.php';
?>