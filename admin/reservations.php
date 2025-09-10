<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';

// Handle status update when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $reservation_id = (int)$_POST['reservation_id'];
    $new_status = $_POST['status'];

    if ($reservation_id > 0) {
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $reservation_id);
        $stmt->execute();
        // Redirect to the same page to show the updated status
        redirect('reservations.php');
    }
}

// Fetch all reservations with user details
$reservations = $conn->query("SELECT r.*, u.name as user_name FROM reservations r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Reservations</h1>
</div>

<div class="bg-white shadow-xl rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">User</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date & Time</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Guests</th>
                    <!-- THE FIX: Added a new column for Price -->
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Total Price</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($reservations->num_rows > 0): while ($res = $reservations->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($res['user_name']); ?></td>
                    <td class="px-6 py-4 text-gray-600"><?php echo date('d M, Y', strtotime($res['reservation_date'])) . ' at ' . date('h:i A', strtotime($res['reservation_time'])); ?></td>
                    <td class="px-6 py-4 text-center font-bold"><?php echo $res['num_guests']; ?></td>
                    <!-- THE FIX: Display the formatted price -->
                    <td class="px-6 py-4 text-right font-semibold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($res['total_price'], 2); ?></td>
                    <td class="px-6 py-4 text-center">
                        <!-- THE FIX: Added a new color for the 'Completed' status badge -->
                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                            <?php 
                                if ($res['status'] == 'Pending') echo 'bg-yellow-100 text-yellow-800';
                                elseif ($res['status'] == 'Confirmed') echo 'bg-green-100 text-green-800';
                                elseif ($res['status'] == 'Cancelled') echo 'bg-red-100 text-red-800';
                                elseif ($res['status'] == 'Completed') echo 'bg-gray-200 text-gray-800';
                            ?>">
                            <?php echo $res['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form method="POST" class="flex items-center justify-end space-x-2">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                            <select name="status" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Pending" <?php if($res['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Confirmed" <?php if($res['status'] == 'Confirmed') echo 'selected'; ?>>Confirm</option>
                                <!-- THE FIX: Added 'Completed' option to the dropdown -->
                                <option value="Completed" <?php if($res['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                                <option value="Cancelled" <?php if($res['status'] == 'Cancelled') echo 'selected'; ?>>Cancel</option>
                            </select>
                            <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded-md text-sm font-semibold hover:bg-indigo-700">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center py-10 text-gray-500">No reservations found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>