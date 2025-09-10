<?php require_once '../common/config.php'; check_login(); ?>
<h2 class="text-2xl font-bold mb-1">Password</h2>
<p class="text-sm text-gray-500 mb-6">Change your password here.</p>
<form id="ajax-password-form" class="space-y-4">
    <input type="hidden" name="action" value="change_password">
    <div>
        <label class="text-xs font-semibold text-pink-500">CURRENT PASSWORD</label>
        <input type="password" name="current_password" required class="w-full p-2 border-b-2 focus:outline-none focus:border-pink-500">
    </div>
    <div>
        <label class="text-xs font-semibold text-pink-500">NEW PASSWORD</label>
        <input type="password" name="new_password" required class="w-full p-2 border-b-2 focus:outline-none focus:border-pink-500">
    </div>
    <div class="text-right">
        <button type="submit" class="bg-pink-500 text-white font-bold px-6 py-2 rounded-lg hover:bg-pink-600">Update Password</button>
    </div>
</form>
<div id="ajax-message" class="mt-4 text-center text-sm"></div>
<script>
document.getElementById('ajax-password-form').addEventListener('submit', async function(e){
    e.preventDefault();
    showLoader();
    const formData = new FormData(this);
    const messageDiv = document.getElementById('ajax-message');
    try {
        const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData });
        const result = await response.json();
        messageDiv.textContent = result.message;
        messageDiv.className = 'mt-4 text-center text-sm ' + (result.status === 'success' ? 'text-green-500' : 'text-red-500');
        if(result.status === 'success') this.reset();
    } catch (e) { console.error(e); } finally { hideLoader(); }
});
</script>