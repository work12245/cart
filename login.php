<?php
require_once 'common/config.php';

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect(SITE_URL . 'login.php');
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect(SITE_URL . 'index.php');
}

$response = ['status' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response = ['status' => 'error', 'message' => 'Email and password are required.'];
        } else {
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $response = ['status' => 'success', 'message' => 'Login successful! Redirecting...', 'redirect' => SITE_URL . 'index.php'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Invalid email or password.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid email or password.'];
            }
            $stmt->close();
        }
    } elseif ($action === 'signup') {
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
             $response = ['status' => 'error', 'message' => 'All fields are required.'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = ['status' => 'error', 'message' => 'Invalid email format.'];
        } else {
            // Check if email or phone already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Email or phone number already registered.'];
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id;
                    $response = ['status' => 'success', 'message' => 'Sign up successful! Redirecting...', 'redirect' => SITE_URL . 'index.php'];
                } else {
                    $response = ['status' => 'error', 'message' => 'An error occurred. Please try again.'];
                }
            }
            $stmt->close();
        }
    }
    echo json_encode($response);
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login / Sign Up - Quick Kart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?php echo SITE_URL; ?>assets/js/main.js" defer></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-6">
        <div class="text-center mb-8">
             <a href="index.php" class="text-4xl font-bold text-red-600">Quick<span class="text-gray-800">Kart</span></a>
        </div>
        
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="flex border-b">
                <button id="login-tab" onclick="switchTab('login')" class="flex-1 py-3 px-4 text-center font-semibold text-white bg-red-600">Login</button>
                <button id="signup-tab" onclick="switchTab('signup')" class="flex-1 py-3 px-4 text-center font-semibold text-gray-600 bg-gray-200">Sign Up</button>
            </div>
            <div class="p-6">
                <form id="login-form" class="space-y-4">
                     <input type="hidden" name="action" value="login">
                     <div>
                         <label for="login-email" class="text-sm font-medium text-gray-700">Email</label>
                         <input type="email" name="email" id="login-email" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                     </div>
                     <div>
                         <label for="login-password" class="text-sm font-medium text-gray-700">Password</label>
                         <input type="password" name="password" id="login-password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                     </div>
                     <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-md hover:bg-red-700 font-semibold transition-colors">Login</button>
                </form>

                <form id="signup-form" class="space-y-4 hidden">
                    <input type="hidden" name="action" value="signup">
                    <div>
                         <label for="signup-name" class="text-sm font-medium text-gray-700">Full Name</label>
                         <input type="text" name="name" id="signup-name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                     </div>
                    <div>
                         <label for="signup-phone" class="text-sm font-medium text-gray-700">Phone</label>
                         <input type="tel" name="phone" id="signup-phone" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                     </div>
                     <div>
                         <label for="signup-email" class="text-sm font-medium text-gray-700">Email</label>
                         <input type="email" name="email" id="signup-email" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                     </div>
                     <div>
                         <label for="signup-password" class="text-sm font-medium text-gray-700">Password</label>
                         <input type="password" name="password" id="signup-password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                     </div>
                     <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-md hover:bg-red-700 font-semibold transition-colors">Sign Up</button>
                </form>
                 <div id="form-message" class="mt-4 text-center text-sm"></div>
            </div>
        </div>
    </div>

<script>
function switchTab(tab) {
    const loginTab = document.getElementById('login-tab');
    const signupTab = document.getElementById('signup-tab');
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');

    if (tab === 'login') {
        loginTab.classList.add('bg-red-600', 'text-white');
        loginTab.classList.remove('bg-gray-200', 'text-gray-600');
        signupTab.classList.add('bg-gray-200', 'text-gray-600');
        signupTab.classList.remove('bg-red-600', 'text-white');
        loginForm.classList.remove('hidden');
        signupForm.classList.add('hidden');
    } else {
        signupTab.classList.add('bg-red-600', 'text-white');
        signupTab.classList.remove('bg-gray-200', 'text-gray-600');
        loginTab.classList.add('bg-gray-200', 'text-gray-600');
        loginTab.classList.remove('bg-red-600', 'text-white');
        signupForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    }
    document.getElementById('form-message').innerHTML = '';
}

document.getElementById('login-form').addEventListener('submit', handleFormSubmit);
document.getElementById('signup-form').addEventListener('submit', handleFormSubmit);

async function handleFormSubmit(event) {
    event.preventDefault();
    showLoader(); // Loader starts before anything else
    
    const form = event.target;
    const formData = new FormData(form);
    const messageDiv = document.getElementById('form-message');

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            messageDiv.className = 'text-green-600';
            messageDiv.textContent = result.message;
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
        } else {
            messageDiv.className = 'text-red-600';
            messageDiv.textContent = result.message;
        }
    } catch (error) {
        messageDiv.className = 'text-red-600';
        messageDiv.textContent = 'A network error occurred. Please try again.';
    } finally {
        // THE FIX: This will run NO MATTER WHAT, ensuring the loader always hides.
        hideLoader();
    }
}
</script>
</body>
</html>