<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id == 0) { echo "<p>Invalid User ID.</p>"; include __DIR__ . '/common/bottom.php'; exit(); }

// Fetch user's main details
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows == 0) { echo "<p>User not found.</p>"; include __DIR__ . '/common/bottom.php'; exit(); }
$user = $user_result->fetch_assoc();

// Fetch all associated data for the user
$addresses = $conn->query("SELECT * FROM user_addresses WHERE user_id = $user_id");
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
$tickets = $conn->query("SELECT * FROM support_tickets WHERE user_id = $user_id ORDER BY created_at DESC");
// THE FIX: Fetch reservations for this user
$reservations = $conn->query("SELECT * FROM reservations WHERE user_id = $user_id ORDER BY created_at DESC");
// All Reviews
$reviews = $conn->query("SELECT r.*, p.name as product_name FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.user_id = $user_id ORDER BY r.created_at DESC");

?>

<a href="user.php" class="text-indigo-600 hover:underline mb-6 inline-block">&larr; Back to All Users</a>
<h1 class="text-3xl font-bold text-gray-800">User Details</h1>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: User Info & Addresses -->
    <div class="lg:col-span-1 space-y-6">
        <!-- User Info Card -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex items-center space-x-4">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=E83E8C&color=fff&size=64" class="w-16 h-16 rounded-full">
                <div>
                    <h2 class="text-xl font-bold"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
            <hr class="my-4">
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <p><strong>Joined:</strong> <?php echo date('d M, Y', strtotime($user['created_at'])); ?></p>
        </div>

        <!-- Addresses Card -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">Saved Addresses</h2>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                <?php if ($addresses->num_rows > 0): while($addr = $addresses->fetch_assoc()): ?>
                    <div class="bg-gray-50 p-3 rounded-lg border">
                        <p class="font-semibold"><?php echo htmlspecialchars($addr['address_line']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($addr['address_type'].'- '.$addr['pincode']); ?></p>
                    </div>
                <?php endwhile; else: ?>
                    <p class="text-gray-400 text-center">No addresses saved by this user.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Orders, Tickets & Reservations -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Orders History -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">Order History</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if ($orders->num_rows > 0): while($order = $orders->fetch_assoc()): ?>
                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="block p-3 bg-gray-50 rounded-lg border hover:border-indigo-500">
                        <div class="flex justify-between items-center">
                            <p class="font-bold">Order #<?php echo $order['id']; ?></p>
                            <p class="font-semibold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($order['total_amount']); ?></p>
                            <span class="text-xs font-semibold rounded-full px-2 py-1 bg-blue-100 text-blue-800"><?php echo $order['status']; ?></span>
                        </div>
                    </a>
                <?php endwhile; else: ?>
                    <p class="text-gray-400 text-center">This user has not placed any orders.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- THE FIX: New Reservations Card -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">Reservation History</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if ($reservations->num_rows > 0): while($res = $reservations->fetch_assoc()): ?>
                    <div class="p-3 bg-gray-50 rounded-lg border">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-bold"><?php echo $res['num_guests']; ?> Guests</p>
                                <p class="text-sm text-gray-600"><?php echo date('d M, Y', strtotime($res['reservation_date'])); ?></p>
                            </div>
                            <span class="text-xs font-semibold rounded-full px-2 py-1 <?php echo $res['status'] == 'Confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $res['status']; ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <p class="text-gray-400 text-center">This user has not made any reservations.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Support Tickets -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">Support Tickets</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if ($tickets->num_rows > 0): while($ticket = $tickets->fetch_assoc()): ?>
                     <a href="support_detail.php?id=<?php echo $ticket['id']; ?>" class="block p-3 bg-gray-50 rounded-lg border hover:border-indigo-500">
                        <div class="flex justify-between items-center">
                            <p class="font-bold"><?php echo htmlspecialchars($ticket['subject']); ?></p>
                            <span class="text-xs font-semibold rounded-full px-2 py-1 bg-green-100 text-green-800"><?php echo $ticket['status']; ?></span>
                        </div>
                    </a>
                <?php endwhile; else: ?>
                    <p class="text-gray-400 text-center">This user has not created any support tickets.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- THE FIX: New Reviews Card -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-lg font-semibold border-b pb-2 mb-4">Review History</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if ($reviews->num_rows > 0): while($rev = $reviews->fetch_assoc()): ?>
                    <div class="p-3 bg-gray-50 rounded-lg border">
                        <div class="flex justify-between items-center">
                            <p class="font-bold"><?php echo htmlspecialchars($rev['product_name']); ?></p>
                            <div class="text-xs text-yellow-500 font-bold">
                                <?php echo $rev['rating']; ?> <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1 truncate" title="<?php echo htmlspecialchars($rev['comment']); ?>"><?php echo htmlspecialchars($rev['comment']); ?></p>
                    </div>
                <?php endwhile; else: ?>
                    <p class="text-gray-400 text-center">This user has not written any reviews.</p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<?php include __DIR__ . '/common/bottom.php'; ?>