<?php
require_once '../common/config.php';
check_login();

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if ($ticket_id == 0) { exit('<p>Invalid Ticket ID.</p>'); }

// Fetch ticket, ensuring it belongs to the logged-in user
$ticket_stmt = $conn->prepare("SELECT * FROM support_tickets WHERE id = ? AND user_id = ?");
$ticket_stmt->bind_param("ii", $ticket_id, $user_id);
$ticket_stmt->execute();
$ticket_result = $ticket_stmt->get_result();
if ($ticket_result->num_rows == 0) { exit('<p>Ticket not found.</p>'); }
$ticket = $ticket_result->fetch_assoc();

// Fetch all messages for this ticket
$messages_stmt = $conn->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC");
$messages_stmt->bind_param("i", $ticket_id);
$messages_stmt->execute();
$messages = $messages_stmt->get_result();
?>

<!-- Chat History -->
<div class="space-y-4 mb-6">
    <!-- Initial user message -->
    <div class="flex justify-end">
        <div class="bg-gradient-to-br from-red-500 to-pink-500 text-white p-3 rounded-lg rounded-br-none max-w-sm">
            <p class="text-sm"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
            <p class="text-xs text-red-100 mt-2 text-right"><?php echo date('d M, Y, h:i A', strtotime($ticket['created_at'])); ?></p>
        </div>
    </div>

    <!-- Replies -->
    <?php while($msg = $messages->fetch_assoc()): ?>
        <div class="flex <?php echo $msg['is_admin_reply'] ? 'justify-start' : 'justify-end'; ?>">
            <div class="<?php echo $msg['is_admin_reply'] ? 'bg-gray-200 text-gray-800 rounded-bl-none' : 'bg-gradient-to-br from-red-500 to-pink-500 text-white rounded-br-none'; ?> p-3 rounded-lg max-w-sm">
                <p class="font-bold text-xs mb-1"><?php echo $msg['is_admin_reply'] ? 'Admin' : 'You'; ?></p>
                <p class="text-sm"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                <p class="text-xs <?php echo $msg['is_admin_reply'] ? 'text-gray-500' : 'text-red-100'; ?> mt-2 text-right"><?php echo date('d M, Y, h:i A', strtotime($msg['created_at'])); ?></p>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Reply Form -->
<form id="ajax-reply-form" class="sticky bottom-0 bg-white pt-2">
    <input type="hidden" name="action" value="send_ticket_reply">
    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
    <div class="relative">
        <input name="reply_message" placeholder="Type your reply here..." required class="w-full pl-4 pr-20 py-3 bg-gray-100 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-pink-500" autocomplete="off">
        <button type="submit" class="absolute inset-y-0 right-0 flex items-center justify-center bg-pink-500 text-white font-bold w-14 h-full rounded-full hover:bg-pink-600">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</form>
<div id="ajax-reply-message" class="mt-2 text-center text-sm"></div>