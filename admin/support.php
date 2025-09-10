<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code
$tickets = $conn->query("SELECT st.*, u.name as user_name FROM support_tickets st JOIN users u ON st.user_id = u.id ORDER BY st.created_at DESC");
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Help & Support Tickets</h1>
</div>

<div class="bg-white shadow-xl rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">User</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Subject</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date & Time</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($tickets->num_rows > 0): while ($tic = $tickets->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($tic['user_name']); ?></td>
                    <td class="px-6 py-4 text-gray-600 font-semibold"><?php echo htmlspecialchars($tic['subject']); ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                            <?php 
                                if ($tic['status'] == 'Open') echo 'bg-blue-100 text-blue-800';
                                elseif ($tic['status'] == 'Answered') echo 'bg-green-100 text-green-800';
                                else echo 'bg-gray-100 text-gray-800'; 
                            ?>">
                            <?php echo $tic['status']; ?>
                        </span>
                    </td>
                    <!-- THE FIX: Updated date format to include time -->
                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo date('d M, Y, h:i A', strtotime($tic['created_at'])); ?></td>
                    <td class="px-6 py-4 text-right">
                        <!-- THE FIX: Button is now a functional link -->
                        <a href="support_detail.php?id=<?php echo $tic['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-bold">View & Reply</a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="text-center py-10 text-gray-500">No support tickets found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>