<?php
require_once '../common/config.php';
check_login();
$user_id = $_SESSION['user_id'];
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();
?>
<h2 class="text-2xl font-bold mb-1">Profile</h2>
<p class="text-sm text-gray-500 mb-6">Update your personal details here.</p>
<form id="ajax-profile-form" class="space-y-4">
    <input type="hidden" name="action" value="update_profile">
    <div>
        <label class="text-xs font-semibold text-pink-500">NAME</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full p-2 border-b-2 focus:outline-none focus:border-pink-500">
    </div>
    <div>
        <label class="text-xs font-semibold text-pink-500">EMAIL</label>
        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="w-full p-2 border-b-2 bg-gray-100 cursor-not-allowed">
    </div>
    <div>
        <label class="text-xs font-semibold text-pink-500">PHONE</label>
        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="w-full p-2 border-b-2 focus:outline-none focus:border-pink-500">
    </div>
    <div class="text-right">
        <button type="submit" class="bg-pink-500 text-white font-bold px-6 py-2 rounded-lg hover:bg-pink-600">Save Changes</button>
    </div>
</form>
<div id="ajax-message" class="mt-4 text-center text-sm"></div>

<script>
document.getElementById('ajax-profile-form').addEventListener('submit', async function(e){
    e.preventDefault();
    showLoader();
    const formData = new FormData(this);
    const messageDiv = document.getElementById('ajax-message');
    try {
        const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData });
        const result = await response.json();
        messageDiv.textContent = result.message;
        messageDiv.className = 'mt-4 text-center text-sm ' + (result.status === 'success' ? 'text-green-500' : 'text-red-500');
    } catch (e) { console.error(e); } finally { hideLoader(); }
});
</script>