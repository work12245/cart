<?php
include __DIR__ . '/../common/config.php';


// Handle Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_id']);
    session_destroy();
    redirect(SITE_URL . 'admin/login.php');
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    redirect(SITE_URL . 'admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            redirect(SITE_URL . 'admin/index.php');
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Invalid username or password.';
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 flex items-center justify-center h-screen">
    <div class="w-full max-w-sm p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h1 class="text-3xl font-bold text-center text-gray-800">Admin Login</h1>
        <form method="POST" class="space-y-4">
            <div>
                <label for="username" class="text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="username" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>
            <?php if ($error): ?>
                <p class="text-red-500 text-sm"><?php echo $error; ?></p>
            <?php endif; ?>
            <button type="submit" class="w-full px-4 py-2 text-lg font-semibold text-white bg-red-600 rounded-md hover:bg-red-700">
                Login
            </button>
        </form>
    </div>
</body>
</html>