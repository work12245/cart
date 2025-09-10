<?php
// === THE FIX: Part 1 - Move all PHP logic to the very top ===
require_once 'common/config.php';

// Handle ALL AJAX Cart Actions BEFORE any HTML is printed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Product ID']);
        exit();
    }

    if ($_POST['action'] === 'update') {
        $quantity = (int)($_POST['quantity'] ?? 0);
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]); // Remove if quantity is 0 or less
            }
        }
    } elseif ($_POST['action'] === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    }
    
    // Recalculate totals for the JSON response
    $total = 0;
    $cart_items_count = 0;
    if (!empty($_SESSION['cart'])) {
        $product_ids = implode(',', array_keys($_SESSION['cart']));
        $cart_products_result = $conn->query("SELECT id, price FROM products WHERE id IN ($product_ids)");
        $products_data = [];
        while($row = $cart_products_result->fetch_assoc()) {
            $products_data[$row['id']] = $row;
        }
        foreach ($_SESSION['cart'] as $pid => $qty) {
            if (isset($products_data[$pid])) {
                $total += $products_data[$pid]['price'] * $qty;
                $cart_items_count++;
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'cart_count' => $cart_items_count,
        'total' => CURRENCY_SYMBOL . number_format($total, 2),
        'subtotal' => CURRENCY_SYMBOL . number_format($total, 2)
    ]);
    
    // IMPORTANT: Stop the script here to prevent sending HTML
    exit();
}

// --- REGULAR PAGE LOAD (GET Request) STARTS HERE ---
include 'common/header.php';

// Fetch cart items for page display
$cart_items = [];
$total_amount = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    if (!empty($product_ids)) {
        $result = $conn->query("SELECT id, name, price, image, stock FROM products WHERE id IN ($product_ids)");
        while ($product = $result->fetch_assoc()) {
            $quantity = $_SESSION['cart'][$product['id']];
            $cart_items[] = [
                'id' => $product['id'], 'name' => $product['name'], 'price' => $product['price'], 'image' => $product['image'], 'stock' => $product['stock'], 'quantity' => $quantity, 'subtotal' => $product['price'] * $quantity,
            ];
            $total_amount += $product['price'] * $quantity;
        }
    }
}
?>

<div class="pt-8 pb-16">
    <div class="text-center mb-12">
        <h1 class="text-5xl font-extrabold text-gray-800 tracking-tighter">Shopping Cart</h1>
        <p class="mt-4 text-gray-500 max-w-2xl mx-auto">Review your items and proceed to checkout.</p>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-20">
            <img src="https://i.ibb.co/k34kSGr/empty-cart.png" alt="Empty Cart Illustration" class="w-64 mx-auto mb-6">
            <h2 class="text-2xl font-bold text-gray-700">Your Cart is Empty</h2>
            <p class="mt-2 text-gray-500">Looks like you haven't added anything to your cart yet.</p>
            <a href="product.php" class="mt-6 inline-block bg-brand-primary text-white font-bold py-3 px-8 rounded-full hover:bg-red-600 transition-colors">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12 items-start">
            <!-- Cart Items List -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-4">Your Items (<span id="cart-item-count"><?php echo count($cart_items); ?></span>)</h2>
                <div id="cart-items-container" class="space-y-6">
                    <?php foreach ($cart_items as $item): ?>
                    <div id="cart-item-<?php echo $item['id']; ?>" class="flex items-center space-x-4">
                        <img src="<?php echo file_exists($item['image']) ? SITE_URL.$item['image'] : 'https://via.placeholder.com/100'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-24 h-24 object-cover rounded-xl shadow-sm">
                        <div class="flex-grow">
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price']); ?></p>
                            <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="text-xs text-red-500 hover:text-red-700 mt-1">Remove</button>
                        </div>
                        <div class="flex items-center border border-gray-200 rounded-lg">
                            <button onclick="updateQuantity(<?php echo $item['id']; ?>, -1)" class="px-3 py-2 text-lg font-bold text-gray-700 hover:bg-gray-100">-</button>
                            <input type="number" id="qty-<?php echo $item['id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="w-12 text-center border-l border-r py-2 focus:outline-none" readonly>
                            <button onclick="updateQuantity(<?php echo $item['id']; ?>, 1)" class="px-3 py-2 text-lg font-bold text-gray-700 hover:bg-gray-100">+</button>
                        </div>
                        <p class="font-bold text-lg text-gray-800 w-24 text-right"><?php echo CURRENCY_SYMBOL; ?><span id="subtotal-<?php echo $item['id']; ?>"><?php echo number_format($item['subtotal'], 2); ?></span></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1 lg:sticky top-28">
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-4">Order Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span id="summary-subtotal"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($total_amount, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span>FREE</span>
                        </div>
                        <div class="flex justify-between font-bold text-xl text-gray-800 border-t pt-4 mt-4">
                            <span>Total</span>
                            <span id="summary-total"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>
                    <a href="checkout.php" class="mt-6 block w-full text-center bg-brand-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-red-600 transition-colors">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// --- Helper function to update all dynamic parts of the page ---
function updatePageUI(result) {
    // Update summary totals
    document.getElementById('summary-subtotal').textContent = result.subtotal;
    document.getElementById('summary-total').textContent = result.total;
    
    // Update the item count in the header of the cart list
    const cartItemCountEl = document.getElementById('cart-item-count');
    if (cartItemCountEl) {
        cartItemCountEl.textContent = result.cart_count;
    }

    // Update header and bottom nav cart icons
    const topCartCounter = document.querySelector('header a[href*="cart.php"] span');
    const bottomCartCounter = document.querySelector('nav a[href*="cart.php"] span');
    
    if (result.cart_count > 0) {
        if (topCartCounter) topCartCounter.textContent = result.cart_count;
        if (bottomCartCounter) bottomCartCounter.textContent = result.cart_count;
    } else {
        if (topCartCounter) topCartCounter.remove();
        if (bottomCartCounter) bottomCartCounter.remove();
    }
}

// --- Main function to handle all cart actions ---
async function performCartAction(action, productId, quantity = 1) {
    showLoader();
    const formData = new FormData();
    formData.append('action', action);
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    try {
        const response = await fetch('cart.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.status === 'success') {
            if (action === 'remove' || (action === 'update' && quantity <= 0)) {
                const itemElement = document.getElementById(`cart-item-${productId}`);
                if (itemElement) {
                    itemElement.style.transition = 'opacity 0.3s ease';
                    itemElement.style.opacity = '0';
                    setTimeout(() => itemElement.remove(), 300);
                }
            } else {
                const subtotalElement = document.getElementById(`subtotal-${productId}`);
                const priceText = document.querySelector(`#cart-item-${productId} p.text-sm`).textContent;
                
                // === THE FIX IS HERE ===
                // This will remove both the currency symbol AND any commas before parsing.
                const price = parseFloat(priceText.replace(/[^0-9.]/g, '')); 
                
                if (subtotalElement && !isNaN(price)) {
                    subtotalElement.textContent = (price * quantity).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
            updatePageUI(result);
            if (result.cart_count === 0) {
                window.location.reload();
            }
        } else {
            showModal('Error', result.message || 'An unknown error occurred.');
        }
    } catch (e) {
        showModal('Error', 'A network error occurred.');
        console.error("Cart Action Error:", e);
    } finally {
        hideLoader();
    }
}

// --- Event handler functions ---
function updateQuantity(productId, change) {
    const qtyInput = document.getElementById(`qty-${productId}`);
    let currentVal = parseInt(qtyInput.value);
    let newVal = currentVal + change;
    if (newVal > 0) {
        qtyInput.value = newVal;
        performCartAction('update', productId, newVal);
    } else {
        removeFromCart(productId);
    }
}

function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item?')) {
        performCartAction('remove', productId);
    }
}
</script>

<?php
include 'common/bottom.php';
?>