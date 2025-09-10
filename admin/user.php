<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code
// Handle delete user
if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    header('Content-Type: application/json');
    $user_id = (int)$_POST['user_id'];
    $conn->query("DELETE FROM users WHERE id = $user_id");
    echo json_encode(['status' => 'success']);
    exit();
}

$users_result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Users</h1>

<div class="bg-white shadow-xl rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Contact</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Joined On</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($user = $users_result->fetch_assoc()): ?>
                <tr id="user-<?php echo $user['id']; ?>" class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($user['name']); ?></td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-800"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['phone']); ?></p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                    <td class="px-6 py-4 text-right space-x-4">
                        <!-- THE FIX: New "View Details" link -->
                        <a href="user_detail.php?id=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-bold">View Details</a>
                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This will also delete all their associated data like orders, addresses, etc.')) {
        showLoader();
        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('user_id', id);
        try {
            await fetch('user.php', { method: 'POST', body: formData });
            document.getElementById(`user-${id}`).remove();
        } catch(e) { console.error(e); }
        finally { hideLoader(); }
    }
}
</script>

<?php include __DIR__ . '/common/bottom.php'; ?>