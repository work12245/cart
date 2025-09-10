<?php 
// THE FIX: The config file is NO LONGER included here.
// It is now the responsibility of the parent page (like setting.php) to include the config first.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard - Quick Kart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/js/main.js" defer></script>
    <style>
        body { background-color: #F8F9FB; font-family: 'Poppins', sans-serif; }
        .sidebar-active { background-color: #E8EFFF; color: #5846E8; font-weight: 600; }
        .sidebar-active .nav-icon { color: #5846E8; }
        #sidebar, #main-content { transition: all 0.3s ease-in-out; }
        .sidebar-mini #sidebar { width: 5rem; }
        .sidebar-mini #main-content { margin-left: 5rem; }
        .sidebar-mini .nav-text, .sidebar-mini .logo-text { display: none; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="">
    <div class="relative min-h-screen lg:flex">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div id="main-content" class="flex-1 lg:ml-64">
            <header class="bg-white shadow-sm sticky top-0 z-20">
                <div class="container mx-auto px-6 py-3 flex justify-between items-center">
                    <button id="sidebar-toggle" class="text-gray-600 focus:outline-none">
                        <i id="icon-arrow" class="fas fa-arrow-left fa-lg"></i>
                        <i id="icon-hamburger" class="fas fa-bars fa-lg hidden"></i>
                    </button>
                    <div class="relative flex-1 max-w-xl mx-4">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center"><i class="fas fa-search text-gray-400"></i></span>
                        <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Search Here">
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="text-gray-500 hover:text-purple-600"><i class="fas fa-bell"></i></button>
                        <button class="text-gray-500 hover:text-purple-600"><i class="fas fa-comment-dots"></i></button>
                        <div class="flex items-center bg-blue-500 text-white rounded-lg px-3 py-1">
                            <span class="font-semibold text-sm mr-2">Hello, <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
                            <img class="h-8 w-8 rounded-full object-cover" src="https://via.placeholder.com/50" alt="Admin Profile">
                        </div>
                    </div>
                </div>
            </header>
            <main class="w-full">
                <div class="container mx-auto p-4 md:p-6">