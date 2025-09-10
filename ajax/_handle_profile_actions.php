<?php
require_once '../common/config.php';
check_login();

header('Content-Type: application/json');
$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Invalid action.'];

switch ($action) {
    case 'update_profile':
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        if (!empty($name) && !empty($phone)) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $phone, $user_id);
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Profile updated successfully!'];
            } else {
                $response['message'] = 'Failed to update profile.';
            }
        } else {
            $response['message'] = 'Name and phone cannot be empty.';
        }
        break;

    case 'change_password':
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $user = $conn->query("SELECT password FROM users WHERE id = $user_id")->fetch_assoc();

        if (password_verify($old_pass, $user['password'])) {
            if ($new_pass === $confirm_pass) {
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Password changed successfully!'];
                }
            } else {
                $response['message'] = 'New passwords do not match.';
            }
        } else {
            $response['message'] = 'Incorrect old password.';
        }
        break;

    case 'add_address':
    case 'edit_address':
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address_line = $_POST['address_line'] ?? '';
        $pincode = $_POST['pincode'] ?? '';
        $address_type = $_POST['address_type'] ?? 'Home';
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        $address_id = (int)($_POST['address_id'] ?? 0);

        if (!empty($full_name) && !empty($phone) && !empty($address_line) && !empty($pincode)) {
            $conn->begin_transaction();
            try {
                if ($is_default == 1) {
                    $conn->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id");
                }
                
                if ($action == 'add_address') {
                    $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, full_name, phone, address_line, pincode, address_type, is_default) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssssi", $user_id, $full_name, $phone, $address_line, $pincode, $address_type, $is_default);
                } else { // This is an edit action
                    if ($address_id > 0) {
                        $stmt = $conn->prepare("UPDATE user_addresses SET full_name=?, phone=?, address_line=?, pincode=?, address_type=?, is_default=? WHERE id=? AND user_id=?");
                        $stmt->bind_param("sssssiii", $full_name, $phone, $address_line, $pincode, $address_type, $is_default, $address_id, $user_id);
                    } else {
                        throw new Exception("Invalid Address ID for editing.");
                    }
                }
                
                $stmt->execute();
                $conn->commit();
                $response = ['status' => 'success', 'message' => 'Address saved successfully!'];

            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'An error occurred: ' . $e->getMessage();
            }
        } else { $response['message'] = 'Please fill all required fields.'; }
        break;

    case 'delete_address':
        $address_id = (int)($_POST['address_id'] ?? 0);
        if ($address_id > 0) {
            $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $address_id, $user_id);
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Address deleted successfully.'];
            } else {
                $response['message'] = 'Failed to delete address.';
            }
        }
        break;

    case 'get_address_details':
        $address_id = (int)($_POST['address_id'] ?? 0);
        if ($address_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $address_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $response = ['status' => 'success', 'data' => $result->fetch_assoc()];
            } else {
                $response['message'] = 'Address not found.';
            }
        }
        break;

    case 'check_pincode':
        $pincode_to_check = $_POST['pincode'] ?? '';
        $is_serviceable = false;
        $pincodes_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'serviceable_pincodes'");
        if ($pincodes_res->num_rows > 0) {
            $pincodes_array = json_decode($pincodes_res->fetch_assoc()['setting_value'], true);
            if (is_array($pincodes_array) && in_array($pincode_to_check, $pincodes_array)) {
                $is_serviceable = true;
            }
        }
        $response = ['serviceable' => $is_serviceable];
        break;
        
        // --- NEW FEATURE: Get all details for a specific order ---
    case 'get_order_details':
        $order_id = (int)($_POST['order_id'] ?? 0);

        if ($order_id > 0) {
            // First, get the main order details and verify it belongs to the user
            $stmt_order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt_order->bind_param("ii", $order_id, $user_id);
            $stmt_order->execute();
            $order_result = $stmt_order->get_result();

            if ($order_result->num_rows > 0) {
                $order_data = $order_result->fetch_assoc();

                // Now, get all items for that order
                $stmt_items = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $stmt_items->bind_param("i", $order_id);
                $stmt_items->execute();
                $items_result = $stmt_items->get_result();
                
                $items_data = [];
                while($item = $items_result->fetch_assoc()) {
                    $items_data[] = $item;
                }

                // Combine both results and send them back
                $response = [
                    'status' => 'success',
                    'data' => [
                        'order_info' => $order_data,
                        'order_items' => $items_data
                    ]
                ];

            } else {
                $response['message'] = 'Order not found or you do not have permission to view it.';
            }
        } else {
            $response['message'] = 'Invalid Order ID provided.';
        }
        break;
    
    // --- NEW CASE: Make a reservation ---
    case 'make_reservation':
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $num_guests = (int)($_POST['guests'] ?? 0);
        
        // Fetch price_per_person from settings
        $price_per_person = '100.00'; // Default value
        $price_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'price_per_person'");
        if ($price_result && $price_result->num_rows > 0) {
            $price_row = $price_result->fetch_assoc();
            if (!empty($price_row['setting_value'])) {
                $price_per_person = $price_row['setting_value'];
            }
        }
        $total_price = $num_guests * (float)$price_per_person;

        if (!empty($date) && !empty($time) && $num_guests > 0) {
            // Basic validation for date and time (you might want more robust validation)
            if (strtotime($date) === false || strtotime($time) === false) {
                 $response['message'] = 'Invalid date or time format.';
                 break; // Exit switch case
            }

            $stmt = $conn->prepare("INSERT INTO reservations (user_id, reservation_date, reservation_time, num_guests, total_price, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("issid", $user_id, $date, $time, $num_guests, $total_price); // 'd' for double/float
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Reservation made successfully!'];
            } else {
                $response['message'] = 'Failed to make reservation. Please try again.';
                // Optional: Log $stmt->error for debugging
            }
        } else {
            $response['message'] = 'Please fill all reservation details.';
        }
        break;
    // --- END NEW CASE ---    
        
    
    case 'add_ticket':
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, status) VALUES (?, ?, 'Open')");
        $stmt->bind_param("is", $user_id, $subject);
        if($stmt->execute()){
            $ticket_id = $stmt->insert_id;
            $stmt_reply = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
            $stmt_reply->bind_param("iis", $ticket_id, $user_id, $message);
            $stmt_reply->execute();
            $response = ['status' => 'success', 'message' => 'Ticket created successfully!'];
        }
        break;

    case 'reply_ticket':
        $ticket_id = $_POST['ticket_id'];
        $message = $_POST['message'];
        $stmt_reply = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt_reply->bind_param("iis", $ticket_id, $user_id, $message);
        if($stmt_reply->execute()){
            $conn->query("UPDATE support_tickets SET status = 'Open' WHERE id = $ticket_id");
            $response = ['status' => 'success', 'message' => 'Reply sent!'];
        }
        break;
}

echo json_encode($response);
exit();