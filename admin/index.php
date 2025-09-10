<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code
$new_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Placed'")->fetch_assoc()['count'];
$pending_reservations_count = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'Pending'")->fetch_assoc()['count'];
$open_tickets_count = $conn->query("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'Open'")->fetch_assoc()['count'];
$pending_reviews_count = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE is_approved = FALSE")->fetch_assoc()['count'];

// Queries for Order Summary
$on_delivery_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Dispatched'")->fetch_assoc()['count'];
$delivered_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Delivered'")->fetch_assoc()['count'];
$canceled_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Cancelled'")->fetch_assoc()['count'];

// General stats
$total_revenue = $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE status = 'Delivered'")->fetch_assoc()['sum'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500">Welcome to AdminKart Admin!</p>
    </div>
    <div class="bg-white p-2 rounded-lg border border-gray-200"><i class="fas fa-calendar-alt text-purple-600 mr-2"></i><span class="text-sm font-semibold">Filter Periode</span></div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4"><div class="bg-green-100 text-green-600 p-4 rounded-lg"><i class="fas fa-dollar-sign fa-lg"></i></div><div><p class="text-sm font-medium text-gray-500">Total Revenue</p><p class="text-2xl font-bold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($total_revenue); ?></p></div></div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4"><div class="bg-blue-100 text-blue-600 p-4 rounded-lg"><i class="fas fa-receipt fa-lg"></i></div><div><p class="text-sm font-medium text-gray-500">Total Orders</p><p class="text-2xl font-bold text-gray-800"><?php echo $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count']; ?></p></div></div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4"><div class="bg-purple-100 text-purple-600 p-4 rounded-lg"><i class="fas fa-book fa-lg"></i></div><div><p class="text-sm font-medium text-gray-500">Confirmed Bookings</p><p class="text-2xl font-bold text-gray-800"><?php echo $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status='Confirmed'")->fetch_assoc()['count']; ?></p></div></div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4"><div class="bg-indigo-100 text-indigo-600 p-4 rounded-lg"><i class="fas fa-users fa-lg"></i></div><div><p class="text-sm font-medium text-gray-500">Total Users</p><p class="text-2xl font-bold text-gray-800"><?php echo $total_users; ?></p></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-md">
        <h3 class="font-semibold text-gray-800 mb-4">Pending Tasks</h3>
        <div class="space-y-3">
            <a href="order.php" class="flex items-center justify-between bg-yellow-400 text-yellow-900 p-3 rounded-lg font-bold hover:bg-yellow-500 transition-colors"><span><i class="fas fa-box mr-2"></i> New Orders</span><span class="bg-white text-yellow-900 text-xs font-bold px-2 py-1 rounded-full"><?php echo $new_orders_count; ?></span></a>
            <a href="reservations.php" class="flex items-center justify-between bg-purple-400 text-purple-900 p-3 rounded-lg font-bold hover:bg-purple-500 transition-colors"><span><i class="fas fa-calendar-check mr-2"></i> Pending Bookings</span><span class="bg-white text-purple-900 text-xs font-bold px-2 py-1 rounded-full"><?php echo $pending_reservations_count; ?></span></a>
            <a href="support.php" class="flex items-center justify-between bg-blue-400 text-blue-900 p-3 rounded-lg font-bold hover:bg-blue-500 transition-colors"><span><i class="fas fa-life-ring mr-2"></i> Open Tickets</span><span class="bg-white text-blue-900 text-xs font-bold px-2 py-1 rounded-full"><?php echo $open_tickets_count; ?></span></a>
            <a href="reviews.php" class="flex items-center justify-between bg-pink-400 text-pink-900 p-3 rounded-lg font-bold hover:bg-pink-500 transition-colors"><span><i class="fas fa-star mr-2"></i> Reviews to Approve</span><span class="bg-white text-pink-900 text-xs font-bold px-2 py-1 rounded-full"><?php echo $pending_reviews_count; ?></span></a>
        </div>
        <hr class="my-4">
        <h3 class="font-semibold text-gray-800 mb-2">Order Summary</h3>
        <div class="flex justify-around text-center">
            <div><p class="text-2xl font-bold text-blue-600"><?php echo $on_delivery_count; ?></p><p class="text-sm text-gray-500">On Delivery</p></div>
            <div><p class="text-2xl font-bold text-green-600"><?php echo $delivered_count; ?></p><p class="text-sm text-gray-500">Delivered</p></div>
            <div><p class="text-2xl font-bold text-red-600"><?php echo $canceled_count; ?></p><p class="text-sm text-gray-500">Canceled</p></div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
        <h3 class="font-semibold text-gray-800 mb-4">Revenue (Last 7 Days)</h3>
        <!-- THE FIX: This canvas will hold our chart -->
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<?php include __DIR__ . '/common/bottom.php'; ?>

<!-- THE FIX: JavaScript to render the chart -->
<script>
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('ajax/_get_chart_data.php');
        const chartData = await response.json();

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Revenue',
                    data: chartData.data,
                    backgroundColor: 'rgba(88, 70, 232, 0.1)',
                    borderColor: '#5846E8',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    } catch (error) {
        console.error("Failed to load chart data:", error);
        document.getElementById('revenueChart').parentElement.innerHTML += '<p class="text-red-500">Could not load chart data.</p>';
    }
});
</script>