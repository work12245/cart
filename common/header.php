<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quick Kart - Delicious Food Delivered Fast</title>
    <script src="https://cdn.tailwindcss.com"></script>
   <!--- <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />--->
   <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"/> --->
   <link rel="stylesheet" href="assets/css/all.min.css"/>
   <script src="<?php echo SITE_URL; ?>assets/css/all.min.css" defer></script>
    <script src="<?php echo SITE_URL; ?>assets/js/main.js" defer></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #FFFFFF; }
        .text-brand-primary { color: #E5242A; }
        .bg-brand-primary { background-color: #E5242A; }
        .border-brand-primary { border-color: #E5242A; }
        .bg-brand-light { background-color: #FFF5F5; }
        .product-card { border: 1px solid #F0F0F0; transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer; }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1); }
        .product-card img { transition: transform 0.4s ease; }
        .product-card:hover img { transform: scale(1.1); }
        #offer-slider-track { transition: transform 0.8s cubic-bezier(0.76, 0, 0.24, 1); }
        #product-grid { transition: opacity 0.4s ease-in-out; }
    </style>
</head>
<body class="antialiased">
    <div id="main-container">
        <header class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <a href="<?php echo SITE_URL; ?>index.php" class="text-2xl font-bold text-gray-800">
                    Quick<span class="text-brand-primary">Kart</span>
                </a>
                <nav class="hidden lg:flex items-center space-x-8 text-gray-700 font-semibold">
                    <a href="index.php" class="hover:text-brand-primary">Home</a>
                    <a href="blog.php" class="hover:text-brand-primary">Blogs</a> <!-- Offer is now Blogs -->
                    <a href="product.php" class="hover:text-brand-primary">Menu</a>
                    <a href="page.php?slug=about-us" class="hover:text-brand-primary">About Us</a>
                </nav>
                
                <!-- THE FIX: Login/Logout Button Logic -->
                <div class="hidden lg:flex items-center space-x-4">
                    <a href="cart.php" class="relative text-gray-700 hover:text-brand-primary">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <?php $cart_count_desk = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; if ($cart_count_desk > 0) { echo "<span id='cart-count' class='absolute -top-2 -right-2 bg-brand-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center'>".$cart_count_desk."</span>"; } else { echo "<span id='cart-count' class='absolute -top-2 -right-2 bg-brand-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden'>0</span>"; } ?>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="font-semibold text-gray-700">My Profile</a>
                        <a href="login.php?logout=true" class="font-semibold text-brand-primary border-2 border-brand-primary px-6 py-2 rounded-full hover:bg-brand-primary hover:text-white transition-colors">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="font-semibold text-gray-700">Login</a>
                        <a href="login.php" class="font-semibold text-brand-primary border-2 border-brand-primary px-6 py-2 rounded-full hover:bg-brand-primary hover:text-white transition-colors">Sign Up</a>
                    <?php endif; ?>
                </div>
                
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-800 focus:outline-none">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
            </div>
        </header>
        
        <?php include 'sidebar.php'; ?>

        <main class="container mx-auto px-4">  