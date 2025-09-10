<?php
// THE FIX: All PHP logic, including config, runs BEFORE any HTML.
include __DIR__ . '/../common/config.php';

// Handle ALL form submissions at the top.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $admin_id = $_SESSION['admin_id'];

    // Handle AJAX actions for info and password
    if ($action === 'update_info' || $action === 'change_password') {
        header('Content-Type: application/json');
        if ($action === 'update_info') {
            $username = $_POST['username'] ?? '';
            $stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $username, $admin_id);
            if ($stmt->execute()) { echo json_encode(['status' => 'success', 'message' => 'Info updated!']); } 
            else { echo json_encode(['status' => 'error', 'message' => 'Update failed.']); }
        }
        if ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? ''; $new_password = $_POST['new_password'] ?? '';
            $result = $conn->query("SELECT password FROM admin WHERE id = $admin_id"); $admin = $result->fetch_assoc();
            if (password_verify($current_password, $admin['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $admin_id);
                if ($stmt->execute()) { echo json_encode(['status' => 'success', 'message' => 'Password changed!']); }
                else { echo json_encode(['status' => 'error', 'message' => 'Update failed.']); }
            } else { echo json_encode(['status' => 'error', 'message' => 'Current password incorrect.']); }
        }
        exit(); // Stop script for AJAX requests
    }

    // Handle normal POST action for general settings
    if ($action === 'update_settings') {
        // Save reservation price
        $price_per_person = $_POST['price_per_person'] ?? '100';
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('price_per_person', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("ss", $price_per_person, $price_per_person);
        $stmt->execute();
        
        // --- THE FIX: Save serviceable pincodes ---
        $pincodes_text = $_POST['serviceable_pincodes'] ?? '';
        $pincodes_array = array_filter(array_map('trim', preg_split('/[\s,]+/', $pincodes_text)));
        $pincodes_json = json_encode(array_values($pincodes_array));
        
        $stmt_pincode = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('serviceable_pincodes', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt_pincode->bind_param("ss", $pincodes_json, $pincodes_json);
        $stmt_pincode->execute();
    }
}

// Now include the header AFTER all logic is done.
include __DIR__ . '/common/header.php';

// Fetch current data for displaying in forms
$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$admin_info = $conn->query("SELECT * FROM admin WHERE id = $admin_id")->fetch_assoc();

// Fetch reservation price
$price_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'price_per_person'");
$price_per_person = $price_result->num_rows > 0 ? $price_result->fetch_assoc()['setting_value'] : '100';

// Fetch serviceable pincodes
$pincodes_json_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'serviceable_pincodes'");
$pincodes_text = '';
if ($pincodes_json_res->num_rows > 0) {
    $pincodes_array = json_decode($pincodes_json_res->fetch_assoc()['setting_value'], true);
    if (is_array($pincodes_array)) {
        $pincodes_text = implode(', ', $pincodes_array);
    }
}
?>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Admin Settings</h1>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Update Information</h2>
        <form id="info-form" class="space-y-4">
            <input type="hidden" name="action" value="update_info">
            <input type="text" name="username" required value="<?php echo htmlspecialchars($admin_info['username']); ?>" class="mt-1 block w-full px-3 py-2 border rounded-md">
            <button type="submit" class="w-full bg-red-600 text-white font-semibold py-2 rounded-md">Save Changes</button>
            <div id="info-message" class="mt-2 text-sm text-center"></div>
        </form>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Change Password</h2>
        <form id="password-form" class="space-y-4">
            <input type="hidden" name="action" value="change_password">
            <input type="password" name="current_password" required placeholder="Current Password" class="mt-1 block w-full px-3 py-2 border rounded-md">
            <input type="password" name="new_password" required placeholder="New Password" class="mt-1 block w-full px-3 py-2 border rounded-md">
            <button type="submit" class="w-full bg-gray-700 text-white font-semibold py-2 rounded-md">Update Password</button>
            <div id="password-message" class="mt-2 text-sm text-center"></div>
        </form>
    </div>
</div>

<h1 class="text-2xl font-bold text-gray-800 my-6">General Settings</h1>
<div class="bg-white p-6 rounded-lg shadow-md">
    <form method="POST">
        <input type="hidden" name="action" value="update_settings">
        <h2 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Reservation Settings</h2>
        <label for="price_per_person" class="block text-sm font-medium text-gray-700">Price Per Person (<?php echo CURRENCY_SYMBOL; ?>)</label>
        <input type="number" name="price_per_person" id="price_per_person" value="<?php echo htmlspecialchars($price_per_person); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">

        <h2 class="text-lg font-semibold text-gray-900 border-b pb-2 my-4">Delivery Area Settings</h2>
        <label for="serviceable_pincodes" class="block text-sm font-medium text-gray-700">Serviceable Pincodes</label>
        <textarea name="serviceable_pincodes" id="serviceable_pincodes" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g., 482001, 482002, 482005"><?php echo htmlspecialchars($pincodes_text); ?></textarea>
        <p class="text-xs text-gray-500 mt-1">Customers will only be able to order to these pincodes.</p>
        
        <button type="submit" class="mt-4 w-full bg-blue-600 text-white font-semibold py-2 rounded-md hover:bg-blue-700">Save All Settings</button>
    </form>
</div>

<script>
document.getElementById('info-form').addEventListener('submit', handleFormSubmit);
document.getElementById('password-form').addEventListener('submit', handleFormSubmit);

async function handleFormSubmit(e) {
    e.preventDefault();
    showLoader();
    const form = e.target;
    const formData = new FormData(form);
    const messageDivId = form.id === 'info-form' ? 'info-message' : 'password-message';
    const messageDiv = document.getElementById(messageDivId);

    try {
        const response = await fetch('setting.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        messageDiv.textContent = result.message;
        messageDiv.className = 'text-sm text-center ' + (result.status === 'success' ? 'text-green-600' : 'text-red-600');

        if (result.status === 'success' && form.id === 'password-form') {
            form.reset();
        }
    } catch (error) {
        console.error("Settings update error:", error);
    } finally {
        hideLoader();
    }
}
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>