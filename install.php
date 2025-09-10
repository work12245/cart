<?php
// --- CONFIGURATION ---
$db_host = '127.0.0.1'; $db_user = 'root'; $db_pass = 'root'; $db_name = 'quickkart_db';
$admin_user = 'admin'; $admin_pass = 'password123';
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass);
        if ($conn->connect_error) { throw new Exception("Connection failed: " . $conn->connect_error); }
        $message .= "<p class='text-green-500'>1. Successfully connected to MySQL server.</p>";

        $sql_create_db = "CREATE DATABASE IF NOT EXISTS `$db_name`";
        if ($conn->query($sql_create_db) === TRUE) {
            $message .= "<p class='text-green-500'>2. Database '$db_name' created or already exists.</p>";
            $conn->select_db($db_name);
        } else { throw new Exception("Error creating database: " . $conn->error); }

        $sql_tables = "
        CREATE TABLE IF NOT EXISTS `users` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL, `phone` VARCHAR(20) NOT NULL UNIQUE, `email` VARCHAR(100) NOT NULL UNIQUE, `password` VARCHAR(255) NOT NULL, `address` TEXT, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP );
        CREATE TABLE IF NOT EXISTS `admin` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(50) NOT NULL UNIQUE, `password` VARCHAR(255) NOT NULL );
        CREATE TABLE IF NOT EXISTS `categories` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL, `image` VARCHAR(255) );
        CREATE TABLE IF NOT EXISTS `products` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `cat_id` INT NOT NULL, `name` VARCHAR(255) NOT NULL, `description` TEXT, `price` DECIMAL(10, 2) NOT NULL, `stock` INT NOT NULL DEFAULT 0, `image` VARCHAR(255), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`cat_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE );
        CREATE TABLE IF NOT EXISTS `orders` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `name` VARCHAR(255) NOT NULL, `address` TEXT NOT NULL, `phone` VARCHAR(20) NOT NULL, `total_amount` DECIMAL(10, 2) NOT NULL, `status` VARCHAR(50) DEFAULT 'Placed', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE );
        CREATE TABLE IF NOT EXISTS `order_items` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `order_id` INT NOT NULL, `product_id` INT NOT NULL, `quantity` INT NOT NULL, `price` DECIMAL(10, 2) NOT NULL, FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE, FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE );
        
        -- THE FIX: New Tables for Profile Features --
        CREATE TABLE IF NOT EXISTS `user_addresses` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `full_name` VARCHAR(100), `phone` VARCHAR(20), `address_line` TEXT,`address_type` ENUM('Home', 'Work', 'Other'), `pincode` VARCHAR(10), `is_default` BOOLEAN DEFAULT FALSE, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE );
        
        
        CREATE TABLE IF NOT EXISTS `reservations` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `reservation_date` DATE NOT NULL, `reservation_time` TIME NOT NULL, `num_guests` INT NOT NULL, `notes` TEXT, `status` VARCHAR(50) DEFAULT 'Pending', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE );
        CREATE TABLE IF NOT EXISTS `reviews` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `product_id` INT NOT NULL, `rating` INT NOT NULL, `comment` TEXT, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE );
        CREATE TABLE IF NOT EXISTS `support_tickets` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `subject` VARCHAR(255) NOT NULL, `message` TEXT NOT NULL, `status` VARCHAR(50) DEFAULT 'Open', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE );
        ";

        if ($conn->multi_query($sql_tables)) {
            while ($conn->next_result()) {;}
            $message .= "<p class='text-green-500'>3. All tables created successfully.</p>";
        } else { throw new Exception("Error creating tables: " . $conn->error); }

        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        $check_admin = $conn->query("SELECT id FROM admin WHERE username = '$admin_user'");
        if ($check_admin->num_rows == 0) {
            $sql_admin = "INSERT INTO `admin` (username, password) VALUES ('$admin_user', '$hashed_password')";
            if ($conn->query($sql_admin) === TRUE) {
                $message .= "<p class='text-green-500'>4. Default admin user created.</p>";
                $message .= "<p class='font-bold'>Username: $admin_user</p>";
                $message .= "<p class='font-bold'>Password: $admin_pass</p>";
            } else { throw new Exception("Error creating admin user: " . $conn->error); }
        } else { $message .= "<p class='text-yellow-500'>4. Admin user already exists. Skipping.</p>"; }

        $upload_dir = 'assets/images/products';
        if (!is_dir($upload_dir)) {
            if (mkdir($upload_dir, 0777, true)) {
                $message .= "<p class='text-green-500'>5. Created directory: '$upload_dir'.</p>";
            } else { $message .= "<p class='text-red-500'>5. Failed to create directory: '$upload_dir'.</p>"; }
        } else { $message .= "<p class='text-yellow-500'>5. Directory '$upload_dir' already exists.</p>"; }

        $conn->close();
        $message .= "<p class='mt-4 text-xl font-bold text-blue-600'>Installation Complete!</p>";
        $message .= "<p>You can now delete this file.</p>";

    } catch (Exception $e) { $message = "<p class='text-red-600 font-bold'>An error occurred:</p><p class='text-red-500'>" . $e->getMessage() . "</p>"; }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Kart - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-2xl p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h1 class="text-3xl font-bold text-center text-gray-800">Quick Kart Web App Installer</h1>
        <?php if (empty($message)): ?>
        <p class="text-center text-gray-600">Click the button below to set up your database and application.</p>
        <form method="POST" action="install.php">
            <button type="submit" class="w-full px-4 py-2 text-lg font-semibold text-white bg-red-600 rounded-md hover:bg-red-700">Start Installation</button>
        </form>
        <?php else: ?>
        <div class="p-4 mt-4 text-sm text-left bg-gray-50 rounded-lg border border-gray-200"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>