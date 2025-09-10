<?php

require_once 'common/config.php'; // config.php 
if (isset($_REQUEST['action'])) {
    header('Content-Type: application/json');
    // Handle GET actions
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($_GET['action'] == 'get_products') {
            $cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
            $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id";
            if ($cat_id > 0) { $sql .= " WHERE p.cat_id = $cat_id"; }
            $sql .= " ORDER BY RAND() LIMIT 8";
            $products_result = $conn->query($sql);
            $output = '';
            if ($products_result->num_rows > 0) {
                while ($product = $products_result->fetch_assoc()) {
                    $old_price = number_format($product['price'] * 1.3, 2);
                    $output .= '<div class="group product-card-clickable" data-productid="' . $product['id'] . '"><div class="bg-white rounded-2xl p-6 product-card"><div class="relative mb-4"><div class="bg-gray-100 rounded-full w-48 h-48 mx-auto overflow-hidden"><img src="' . (file_exists($product['image']) ? SITE_URL.$product['image'] : 'https://i.ibb.co/fH7Bw3t/product-pizza.png') . '" class="w-full h-full object-cover"></div><span class="absolute top-2 left-2 bg-brand-primary text-white text-xs font-semibold px-3 py-1 rounded-full">' . htmlspecialchars($product['category_name'] ?? 'Food') . '</span></div><div class="flex flex-col items-center"><div class="text-yellow-500 text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div><h3 class="mt-2 text-xl font-bold text-gray-800 text-center truncate w-full">' . htmlspecialchars($product['name']) . '</h3><p class="mt-2 font-semibold text-gray-800 text-center">' . CURRENCY_SYMBOL . number_format($product['price'], 2) . ' <s class="text-gray-400 font-normal">' . CURRENCY_SYMBOL . $old_price . '</s></p></div><div class="mt-4 flex justify-center space-x-2"><div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-shopping-cart"></i></div><div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-heart"></i></div><div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-eye"></i></div></div></div></div>';
                }
            } else { $output = '<p class="col-span-full text-center text-gray-500 py-10">No products found in this category.</p>'; }
            echo json_encode(['html' => $output]);
            exit(); // IMPORTANT: Exit after sending JSON response
        }

        if ($_GET['action'] == 'get_product_detail') {
            $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($product_id > 0) {
                $result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id WHERE p.id = $product_id"); // Added category_name
                if ($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $product['image_url'] = file_exists($product['image']) ? SITE_URL.$product['image'] : 'https://i.ibb.co/fH7Bw3t/product-pizza.png';
                    echo json_encode(['status' => 'success', 'data' => $product]);
                } else { echo json_encode(['status' => 'error', 'message' => 'Product not found.']); }
            } else { echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']); }
            exit(); // IMPORTANT: Exit after sending JSON response
        }
    }

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 if ($_POST['action'] == 'add_to_cart') {
  $product_id = (int)($_POST['product_id'] ?? 0);
 $quantity = (int)($_POST['quantity'] ?? 1);
 if ($product_id > 0 && $quantity > 0) {
 if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
 // Check current stock before adding to cart
 $stock_check = $conn->query("SELECT stock FROM products WHERE id = $product_id")->fetch_assoc();
 if($stock_check && ($_SESSION['cart'][$product_id] ?? 0) + $quantity > $stock_check['stock']) {
 echo json_encode(['status' => 'error', 'message' => 'Not enough stock available!']); } else {
 $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
 echo json_encode(['status' => 'success', 'message' => 'Product added to cart!', 'cart_count' => count($_SESSION['cart'])]);  }
 } else { echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity.']); }  exit(); 
        }
    
    
    
    //  buy_now action  
        if ($_POST['action'] == 'buy_now') {
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($product_id > 0 && $quantity > 0) {
                // Check stock before allowing buy now
                $stock_check = $conn->query("SELECT stock FROM products WHERE id = $product_id")->fetch_assoc();
                if($stock_check && $quantity > $stock_check['stock']) {
                    echo json_encode(['status' => 'error', 'message' => 'Not enough stock available for Buy Now!']);
                } else {
                    $_SESSION['buy_now_product'] = [ // This is the variable used in checkout.php
                        'product_id' => $product_id,
                        'quantity' => $quantity
                    ];
                    echo json_encode(['status' => 'success', 'message' => 'Redirecting to checkout!']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity for buy now.']);
            }
            exit(); // IMPORTANT: Exit after sending JSON response
        }

        if ($_POST['action'] == 'book_table') {
            $user_id = $_SESSION['user_id'] ?? null;
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $date = $_POST['date'] ?? '';
            $time = $_POST['time'] ?? '';
            $guests = $_POST['guests'] ?? 0;
            if (!empty($name) && !empty($phone) && !empty($date) && !empty($time) && $guests > 0) {
                $stmt = $conn->prepare("INSERT INTO bookings (user_id, name, phone, booking_date, booking_time, guests) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssi", $user_id, $name, $phone, $date, $time, $guests);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Your table has been booked successfully! We will contact you shortly for confirmation.']);
                } else { echo json_encode(['status' => 'error', 'message' => 'Sorry, something went wrong. Please try again.']); }
            } else { echo json_encode(['status' => 'error', 'message' => 'Please fill all the fields correctly.']); }
            exit(); // IMPORTANT: Exit after sending JSON response
        }
    }
    // If an action was set but not handled by any specific action,
    // this would be a good place to return a generic error.
    echo json_encode(['status' => 'error', 'message' => 'Unknown AJAX action.']);
    exit(); // Exit once any AJAX action (GET or POST) has been handled
}

$is_homepage = true;
include 'common/header.php';
$offers_result = $conn->query("SELECT * FROM offers WHERE is_active = 1 ORDER BY id DESC LIMIT 6");
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$initial_products_result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id ORDER BY RAND() LIMIT 8");
$blogs_result = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC LIMIT 3");
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_result = $conn->query("SELECT name, phone FROM users WHERE id = $user_id");
    if ($user_result->num_rows > 0) { $current_user = $user_result->fetch_assoc(); }
}
?>

<!-- Hero Section -->
<section class="flex flex-col lg:flex-row items-center pt-8 pb-16">
    <div class="lg:w-1/2 text-center lg:text-left">
        <h1 class="text-5xl md:text-7xl font-extrabold text-gray-800 leading-tight tracking-tighter">
            All Fast Food is Available at <span class="text-brand-primary relative">QuickKart
            <span class="absolute -bottom-2 left-0 w-full h-2 bg-yellow-300"></span></span>
        </h1>
        <p class="mt-8 text-gray-600 max-w-lg mx-auto lg:mx-0">We are just a click away when you crave for delicious fast food. Delivered fresh and fast right to your doorstep.</p>
        <div class="mt-10 flex justify-center lg:justify-start items-center space-x-6">
            <a href="product.php" class="bg-brand-primary text-white font-bold py-4 px-8 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"> <i class="fas fa-shopping-bag mr-2"></i> Order Now </a>
           <a href="profile.php?action=reservation" class="bg-brand-primary text-white font-bold py-4 px-8 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-calendar-check mr-2"></i> Book a Table
            </a>
        </div>
    </div>
    <div class="lg:w-1/2 mt-12 lg:mt-0 flex justify-center">
        <img src="/assets/images/hero/hero.webp" alt="Delicious Sandwich" class="w-full max-w-lg h-auto drop-shadow-2xl">
    </div>
</section>

<!-- Daily Offer Section -->
<section class="mt-8 mb-20">
    <div class="text-center"> <p class="text-brand-primary font-semibold">Daily Offer</p> <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800 tracking-tighter">Up To 75% Off For This Day</h2> </div>
    <div id="offer-slider" class="mt-10 relative overflow-hidden">
        <div id="offer-slider-track" class="flex">
            <?php 
                $offer_slides = []; while($offer = $offers_result->fetch_assoc()){ $offer_slides[] = $offer; }
                if(empty($offer_slides)){ echo '<p class="w-full text-center text-gray-500">No active offers right now. Check back soon!</p>'; }
                else {
                    foreach($offer_slides as $offer){ echo '<div class="flex-shrink-0 w-full lg:w-1/3 p-4"><div class="bg-brand-light rounded-2xl p-6 flex items-center space-x-4 h-full"><div><p class="font-bold text-brand-primary">'.htmlspecialchars($offer['discount_text']).'</p><h3 class="text-2xl font-bold text-gray-800 mt-1">'.htmlspecialchars($offer['title']).'</h3><p class="text-sm text-gray-500 mt-2">'.htmlspecialchars($offer['description']).'</p><div class="mt-4 flex space-x-2"><button class="w-8 h-8 bg-white text-brand-primary rounded-md shadow-sm"><i class="fas fa-shopping-cart text-sm"></i></button><button class="w-8 h-8 bg-white text-brand-primary rounded-md shadow-sm"><i class="fas fa-heart text-sm"></i></button><button class="w-8 h-8 bg-white text-brand-primary rounded-md shadow-sm"><i class="fas fa-eye text-sm"></i></button></div></div><img src="'.SITE_URL.($offer['image'] ?? 'assets/images/placeholder.png').'" class="w-32 h-32 rounded-full object-cover flex-shrink-0"></div></div>'; }
                    foreach($offer_slides as $offer){ echo '<div class="flex-shrink-0 w-full lg:w-1/3 p-4"><div class="bg-brand-light rounded-2xl p-6 flex items-center space-x-4 h-full"><div><p class="font-bold text-brand-primary">'.htmlspecialchars($offer['discount_text']).'</p><h3 class="text-2xl font-bold text-gray-800 mt-1">'.htmlspecialchars($offer['title']).'</h3><p class="text-sm text-gray-500 mt-2">'.htmlspecialchars($offer['description']).'</p><div class="mt-4 flex space-x-2"><button class="w-8 h-8 bg-white text-brand-primary rounded-md shadow-sm"><i class="fas fa-shopping-cart text-sm"></i></button><button class="w-8 h-8 bg-white text-brand-primary rounded-md shadow-sm"><i class="fas fa-heart text-sm"></i></button><button class="w-8 h-8 bg-white text-brand-primary rounded-md shadow-sm"><i class="fas fa-eye text-sm"></i></button></div></div><img src="'.SITE_URL.($offer['image'] ?? 'assets/images/placeholder.png').'" class="w-32 h-32 rounded-full object-cover flex-shrink-0"></div></div>'; }
                }
            ?>
        </div>
    </div>
</section>

<!-- Food Menu Section -->
<section id="menu" class="mt-24">
    <div class="relative z-20">
        <div class="flex justify-between items-end mb-8">
            <div class="text-left"> <p class="text-brand-primary font-semibold">Food Menu</p> <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800 tracking-tighter">Our Popular Delicious Foods</h2> </div>
            <a href="product.php" class="hidden sm:inline-block bg-brand-light text-brand-primary font-bold py-3 px-6 rounded-lg hover:bg-red-200 transition-colors">See All</a>
        </div>
        <div class="flex justify-center items-center flex-wrap gap-3">
            <a href="#" class="category-filter py-2 px-6 bg-brand-primary text-white rounded-full font-semibold border-2 border-brand-primary" data-catid="0">All</a>
            <?php mysqli_data_seek($categories_result, 0); while ($cat = $categories_result->fetch_assoc()): ?>
            <a href="#" class="category-filter py-2 px-6 bg-white text-gray-700 rounded-full font-semibold border-2 border-gray-200 hover:border-brand-primary transition-colors" data-catid="<?php echo $cat['id']; ?>"> <?php echo htmlspecialchars($cat['name']); ?> </a>
            <?php endwhile; ?>
        </div>
    </div>
    <div id="product-grid" class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php while ($product = $initial_products_result->fetch_assoc()): $old_price = number_format($product['price'] * 1.3, 2); ?>
            <div class="group product-card-clickable" data-productid="<?php echo $product['id']; ?>">
                <div class="bg-white rounded-2xl p-6 product-card">
                    <div class="relative mb-4">
                        <div class="bg-gray-100 rounded-full w-48 h-48 mx-auto overflow-hidden"> <img src="<?php echo file_exists($product['image']) ? SITE_URL.$product['image'] : 'https://i.ibb.co/fH7Bw3t/product-pizza.png'; ?>" class="w-full h-full object-cover"> </div>
                        <span class="absolute top-2 left-2 bg-brand-primary text-white text-xs font-semibold px-3 py-1 rounded-full"><?php echo htmlspecialchars($product['category_name'] ?? 'Food'); ?></span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="text-yellow-500 text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div>
                        <h3 class="mt-2 text-xl font-bold text-gray-800 text-center truncate w-full"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="mt-2 font-semibold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 2); ?> <s class="text-gray-400 font-normal"><?php echo CURRENCY_SYMBOL; ?><?php echo $old_price; ?></s></p>
                    </div>
                    <div class="mt-4 flex justify-center space-x-2">
                        <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-shopping-cart"></i></div>
                        <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-heart"></i></div>
                        <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-eye"></i></div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- Promotional Banners Section -->
<section class="mt-24 grid grid-cols-1 md:grid-cols-3 gap-8">
    <a href="#" class="block rounded-2xl shadow-lg overflow-hidden relative group h-64">
        <img src="https://images.unsplash.com/photo-1569058242253-92a9c55520cd?w=500" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-end p-6"> <h3 class="text-white text-2xl font-bold">Fried Chicken</h3> <p class="text-gray-300 text-sm mt-1">Lorem ipsum dolor sit amet consectetur.</p> </div>
    </a>
    <a href="#" class="block rounded-2xl shadow-lg overflow-hidden relative group h-64">
        <img src="https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=500" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-end p-6"> <h3 class="text-white text-2xl font-bold">Spicy Burger</h3> <p class="text-gray-300 text-sm mt-1">Lorem ipsum dolor sit amet consectetur.</p> </div>
    </a>
    <a href="#" class="block rounded-2xl shadow-lg overflow-hidden relative group h-64">
        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-end p-6"> <h3 class="text-white text-2xl font-bold">New Year</h3> <p class="text-gray-300 text-sm mt-1">Lorem ipsum dolor sit amet consectetur.</p> </div>
    </a>
</section>

<!-- Customer Feedback Section -->
<section class="mt-24">
    <div class="text-center"> <h2 class="text-5xl font-extrabold text-gray-800 tracking-tighter">Our Customer Feedback</h2> </div>
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-2xl shadow-lg"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-16 h-16 rounded-full object-cover"> <div class="ml-4"> <h4 class="font-bold text-gray-800">Samuel L.</h4> <div class="text-yellow-500 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div> </div> </div> <p class="mt-4 text-gray-600 italic">"The best food delivery service I've ever used. The burgers are to die for!"</p> </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-16 h-16 rounded-full object-cover"> <div class="ml-4"> <h4 class="font-bold text-gray-800">Jessica P.</h4> <div class="text-yellow-500 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i></div> </div> </div> <p class="mt-4 text-gray-600 italic">"Amazing quality and super fast delivery. The pizza was still piping hot."</p> </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg"> <div class="flex items-center"> <img src="https://randomuser.me/api/portraits/men/46.jpg" class="w-16 h-16 rounded-full object-cover"> <div class="ml-4"> <h4 class="font-bold text-gray-800">Mike R.</h4> <div class="text-yellow-500 text-sm"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div> </div> </div> <p class="mt-4 text-gray-600 italic">"Great menu variety and the prices are reasonable. Highly recommended."</p> </div>
    </div>
</section>

<!-- Latest Blog Section -->
<section class="mt-24">
    <!-- THE FIX: Title and Button on the same line -->
    <div class="flex justify-between items-end mb-12">
        <div class="text-center">
             <h2 class="text-5xl font-extrabold text-gray-800 tracking-tighter">Our Latest Blog</h2>
        </div>
        <a href="blog.php" class="hidden sm:inline-block bg-brand-light text-brand-primary font-bold py-3 px-6 rounded-lg hover:bg-red-200 transition-colors">See All Posts</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php while ($post = $blogs_result->fetch_assoc()): ?>
            <!-- THE FIX: The entire card is now a clickable link -->
            <a href="blog_detail.php?id=<?php echo $post['id']; ?>" class="block bg-white rounded-2xl shadow-lg overflow-hidden group">
                <img src="<?php echo SITE_URL . ($post['image'] ?? 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=500'); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-brand-primary transition-colors"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="mt-2 text-sm text-gray-600"><?php echo substr(strip_tags($post['content']), 0, 100) . '...'; ?></p>
                    <span class="mt-4 inline-block font-semibold text-brand-primary group-hover:underline">Read More &rarr;</span>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</section>

<!-- Booking Modal HTML -->
<div id="booking-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
    <div id="booking-modal-content" class="bg-white rounded-2xl shadow-2xl p-8 w-11/12 max-w-lg transform transition-all duration-300 scale-95">
        <div class="flex justify-between items-center border-b pb-3 mb-6"> <h2 class="text-2xl font-bold text-gray-800">Book Your Table</h2> <button onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button> </div>
        <form id="booking-form" class="space-y-4">
            <div> <label for="booking-name" class="block text-sm font-medium text-gray-700">Full Name</label> <input type="text" id="booking-name" name="name" value="<?php echo htmlspecialchars($current_user['name'] ?? ''); ?>" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"> </div>
            <div> <label for="booking-phone" class="block text-sm font-medium text-gray-700">Phone Number</label> <input type="tel" id="booking-phone" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"> </div>
            <div class="grid grid-cols-2 gap-4">
                <div> <label for="booking-date" class="block text-sm font-medium text-gray-700">Date</label> <input type="date" id="booking-date" name="date" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg" min="<?php echo date('Y-m-d'); ?>"> </div>
                <div> <label for="booking-time" class="block text-sm font-medium text-gray-700">Time</label> <input type="time" id="booking-time" name="time" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"> </div>
            </div>
            <div> <label for="booking-guests" class="block text-sm font-medium text-gray-700">Number of Guests</label> <select id="booking-guests" name="guests" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg"> <?php for ($i = 1; $i <= 10; $i++) { echo "<option value='$i'>$i Person" . ($i > 1 ? 's' : '') . "</option>"; } ?> </select> </div>
            <div class="pt-4"> <button type="submit" class="w-full bg-brand-primary text-white font-bold py-3 rounded-lg hover:bg-red-600 transition-colors">Confirm Booking</button> </div>
        </form>
    </div>
</div>

<?php
include 'common/bottom.php';
?>