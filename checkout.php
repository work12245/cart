<?php
include 'common/header.php';
check_login();

// --- START: MODIFIED LOGIC FOR BUY NOW / CART DETECTION ---

// Standardize the buy now item. Prioritize product_detail's 'buy_now_product' if present.
$buy_now_data = null;
if (isset($_SESSION['buy_now_product']) && !empty($_SESSION['buy_now_product'])) {
    $buy_now_data = $_SESSION['buy_now_product'];
} elseif (isset($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item'])) {
    // If buy_now_product isn't set (e.g., from index.php), check buy_now_item
    $buy_now_data = $_SESSION['buy_now_item'];
}

// Check if we are in buy_now mode based on URL parameter and session data
$is_buy_now_mode = isset($_GET['mode']) && $_GET['mode'] === 'buy_now' && !empty($buy_now_data);

// Redirect if neither cart nor buy now item is present
// IMPORTANT: Only redirect to cart.php if NOT in buy_now mode and cart is empty.
if (empty($_SESSION['cart']) && !$is_buy_now_mode) {
    redirect(SITE_URL . 'cart.php');
}

$user_id = $_SESSION['user_id'];

// --- END: MODIFIED LOGIC FOR BUY NOW / CART DETECTION ---


// --- HANDLE ORDER PLACEMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    if ($payment_method !== 'COD') { die("Invalid payment method."); }

    $address_choice = $_POST['address_choice'] ?? '';
    $name = ''; $phone = ''; $full_address = '';

    if (is_numeric($address_choice) && $address_choice > 0) {
        $address_id = (int)$address_choice;
        $addr_stmt = $conn->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $addr_stmt->bind_param("ii", $address_id, $user_id);
        $addr_stmt->execute();
        $address_details = $addr_stmt->get_result()->fetch_assoc();
        if ($address_details) {
            $name = $address_details['full_name'];
            $phone = $address_details['phone'];
            $full_address = $address_details['address_line'] . ", " . $address_details['address_type'] . " . " . $address_details['pincode'];
        } else {
            // Handle case where address_id is invalid or doesn't belong to the user
            echo "<script>alert('Selected address is invalid.');</script>";
            exit(); // Stop further processing
        }
    } else {
        echo "<script>alert('Please select a shipping address.');</script>";
        exit(); // Stop further processing
    }
    
    $total_amount = (float)($_POST['total_amount'] ?? 0);

    // Ensure we have valid address and total amount
    if (!empty($name) && !empty($phone) && !empty($full_address) && $total_amount > 0) {
        $conn->begin_transaction();
        try {
            // --- START: MODIFIED ORDER ITEM PROCESSING ---

            $order_stmt = $conn->prepare("INSERT INTO orders (user_id, name, address, phone, total_amount, status) VALUES (?, ?, ?, ?, ?, 'Placed')");
            $order_stmt->bind_param("isssd", $user_id, $name, $full_address, $phone, $total_amount);
            $order_stmt->execute();
            $order_id = $order_stmt->insert_id;

            $items_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            
            // Check for buy now mode first (using the standardized $buy_now_data)
            if ($is_buy_now_mode && !empty($buy_now_data)) {
                $item = $buy_now_data; // Use the standardized buy now data
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];

                $product_res = $conn->query("SELECT price, stock FROM products WHERE id = $product_id");
                $product = $product_res->fetch_assoc();

                if (!$product) { throw new Exception("Product ID $product_id not found."); }
                if ($quantity > $product['stock']) { throw new Exception("Product '{$product['name']}' is out of stock. Available: {$product['stock']}, Requested: $quantity."); }
                
                $items_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);
                $items_stmt->execute();
                
                $stock_stmt->bind_param("ii", $quantity, $product_id);
                $stock_stmt->execute();
                
                // Unset the specific buy now session variable that was used
                if (isset($_SESSION['buy_now_product'])) {
                    unset($_SESSION['buy_now_product']);
                } elseif (isset($_SESSION['buy_now_item'])) {
                    unset($_SESSION['buy_now_item']);
                }

            } else { // Process regular cart
                if (empty($_SESSION['cart'])) {
                     throw new Exception("Your cart is empty. Cannot place order.");
                }

                $product_ids = implode(',', array_keys($_SESSION['cart']));
                $result = $conn->query("SELECT id, name, price, stock FROM products WHERE id IN ($product_ids)");
                
                $cart_products = [];
                while($p = $result->fetch_assoc()) {
                    $cart_products[$p['id']] = $p;
                }

                foreach ($_SESSION['cart'] as $pid => $qty) {
                    if (!isset($cart_products[$pid])) { throw new Exception("Product ID $pid not found in database."); }
                    if ($qty > $cart_products[$pid]['stock']) { throw new Exception("Product {$cart_products[$pid]['name']} is out of stock. Available: {$cart_products[$pid]['stock']}, Requested: $qty."); }
                    
                    $price = $cart_products[$pid]['price'];
                    $items_stmt->bind_param("iiid", $order_id, $pid, $qty, $price);
                    $items_stmt->execute();
                    
                    $stock_stmt->bind_param("ii", $qty, $pid);
                    $stock_stmt->execute();
                }
                unset($_SESSION['cart']);
            }
            // --- END: MODIFIED ORDER ITEM PROCESSING ---

            $conn->commit();
            redirect(SITE_URL . 'profile.php?action=orders&success=true&order_id=' . $order_id);

        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Order Failed: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('An unexpected error occurred during order placement. Please try again.');</script>";
    }
}


// --- START: MODIFIED DATA FETCHING LOGIC TO DISPLAY ON PAGE ---
$cart_items = [];
$subtotal = 0;

if ($is_buy_now_mode && !empty($buy_now_data)) {
    // If in buy now mode, fetch details for the single buy now product
    $item = $buy_now_data;
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];

    $result = $conn->query("SELECT id, name, price, image FROM products WHERE id = $product_id");
    if ($product = $result->fetch_assoc()) {
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        ];
        $subtotal = $product['price'] * $quantity;
    } else {
        // If product not found, clear buy now mode and redirect (or show error)
        unset($_SESSION['buy_now_product']);
        unset($_SESSION['buy_now_item']);
        redirect(SITE_URL . 'cart.php?error=product_not_found'); // Redirect to cart or homepage with error
    }
} elseif (!empty($_SESSION['cart'])) {
    // Otherwise, if regular cart has items, fetch them
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $result = $conn->query("SELECT id, name, price, image FROM products WHERE id IN ($product_ids)");
    while ($product = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$product['id']];
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        ];
        $subtotal += $product['price'] * $quantity;
    }
    // If cart is empty after fetching (e.g., product deleted from db), redirect
    if (empty($cart_items) && !$is_buy_now_mode) {
        redirect(SITE_URL . 'cart.php?error=cart_empty');
    }
} else {
    // This case should ideally not be hit if the initial redirect logic works
    // But as a fallback, if somehow cart is empty and not buy_now mode, redirect
    redirect(SITE_URL . 'cart.php?error=no_items_to_checkout');
}
// --- END: MODIFIED DATA FETCHING LOGIC TO DISPLAY ON PAGE ---


$shipping_fee = 0; // Assuming free shipping for now as per your code
$total_amount = $subtotal + $shipping_fee;

$addresses_result = $conn->query("SELECT * FROM user_addresses WHERE user_id = $user_id");
$user_info = $conn->query("SELECT name, phone FROM users WHERE id = $user_id")->fetch_assoc();
?>

<div class="py-8">
    <h1 class="text-3xl font-extrabold text-gray-800 text-center">Checkout</h1>
    <p class="text-center text-gray-500">Complete your order</p>

    <?php if ($addresses_result->num_rows == 0): ?>
        <div class="mt-8 max-w-2xl mx-auto text-center bg-white p-10 rounded-2xl shadow-lg">
            <h2 class="text-xl font-bold text-red-600">No Shipping Address Found</h2>
            <p class="text-gray-600 mt-4">You must have at least one saved address to place an order. Please go to your profile to add an address first.</p>
            <a href="<?php echo SITE_URL; ?>profile.php?action=address" class="mt-6 inline-block bg-brand-primary text-white font-bold py-3 px-8 rounded-lg hover:bg-red-600 transition-colors">
                Add Address
            </a>
        </div>
    <?php else: ?>
    <form method="POST" id="checkout-form" class="mt-8 max-w-4xl mx-auto">
        <?php if ($is_buy_now_mode): // Use the new $is_buy_now_mode variable ?>
            <input type="hidden" name="checkout_mode" value="buy_now">
        <?php endif; ?>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold mb-4">1. Select Shipping Information</h2>
                    <div class="space-y-3">
                        <?php $is_first_address = true; mysqli_data_seek($addresses_result, 0); while($addr = $addresses_result->fetch_assoc()): ?>
                        <label class="block border rounded-lg p-4 cursor-pointer has-[:checked]:bg-red-50 has-[:checked]:border-brand-primary transition-all">
                            <input type="radio" name="address_choice" value="<?php echo $addr['id']; ?>" class="mr-3" <?php if($is_first_address) { echo 'checked'; } ?>>
                            <div class="inline-block">
                                <p class="font-bold"><?php echo htmlspecialchars($addr['full_name'] ?? ''); ?> - <?php echo htmlspecialchars($addr['phone'] ?? ''); ?></p>
                                <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($addr['address_line'])); ?></p>
                                <p class="text-sm text-gray-600">Pincode: <?php echo htmlspecialchars($addr['pincode']); ?></p>
                            </div>
                        </label>
                        <?php $is_first_address = false; endwhile; ?>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold mb-4">2. Payment Method</h2>
                    <div class="border rounded-lg p-4"><label><input type="radio" name="payment_method" value="COD" checked><span class="font-medium ml-2">Cash on Delivery</span></label><p class="text-xs text-gray-500 mt-1 ml-6">Pay with cash upon delivery.</p></div>
                </div>
            </div>
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-2xl shadow-lg sticky top-24">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Order Summary</h2>
                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                        <?php if (empty($cart_items)): ?>
                            <p class="text-center text-gray-500 py-4">No items to display for checkout.</p>
                        <?php else: ?>
                            <?php foreach ($cart_items as $item): ?>
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <img src="<?php echo file_exists($item['image']) ? SITE_URL.$item['image'] : 'https://via.placeholder.com/50'; ?>" class="w-12 h-12 rounded-md object-cover mr-3">
                                    <div>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                </div>
                                <p class="font-semibold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <hr class="my-4">
                    <div class="space-y-2 text-gray-600">
                        <div class="flex justify-between"><p>Subtotal</p><p class="font-semibold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($subtotal, 2); ?></p></div>
                        <div class="flex justify-between"><p>Shipping Fee</p><p class="font-semibold text-green-600">FREE</p></div>
                    </div>
                    <hr class="my-4">
                    <div class="flex justify-between text-lg font-bold">
                        <p>Total</p>
                        <p><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($total_amount, 2); ?></p>
                    </div>
                    <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                    <button type="submit" class="mt-6 w-full bg-brand-primary text-white font-bold text-lg py-4 rounded-lg hover:bg-red-600 transition-colors flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>