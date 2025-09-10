<?php
require_once '../../common/config.php';
check_admin_login();

header('Content-Type: application/json');

// Get sales data for the last 7 days
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $result = $conn->query("SELECT SUM(total_amount) as daily_total FROM orders WHERE status = 'Delivered' AND DATE(created_at) = '$date'");
    $row = $result->fetch_assoc();
    $sales_data['labels'][] = date('D, M j', strtotime($date));
    $sales_data['data'][] = (float)($row['daily_total'] ?? 0);
}

echo json_encode($sales_data);
exit();