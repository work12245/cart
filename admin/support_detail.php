<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($ticket_id == 0) { echo "<p>Invalid Ticket ID.</p>"; include __DIR__ . '/common/bottom.php'; exit(); }

// Handle both Status Update AND Reply Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'send_reply' && !empty($_POST['reply_message'])) {
        $reply_message = $_POST['reply_message'];
        // 1. Insert the admin's reply into the new messages table
        $stmt = $conn->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_admin_reply) VALUES (?, ?, ?, 1)");
        // We need the user_id associated with the ticket
        $user_id_result = $conn->query("SELECT user_id FROM support_tickets WHERE id = $ticket_id")->fetch_assoc();
        $user_id = $user_id_result['user_id'];
        $stmt->bind_param("iis", $ticket_id, $user_id, $reply_message);
        $stmt->execute();
        
        // 2. Update the ticket status to "Answered"
        $conn->query("UPDATE support_tickets SET status = 'Answered' WHERE id = $ticket_id");

    } elseif ($_POST['action'] == 'update_status') {
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $ticket_id);
        $stmt->execute();
    }
    redirect("support_detail.php?id=$ticket_id&success=true");
}

// Fetch ticket details
$ticket_stmt = $conn->prepare("SELECT st.*, u.name as user_name, u.email as user_email FROM support_tickets st JOIN users u ON st.user_id = u.id WHERE st.id = ?");
$ticket_stmt->bind_param("i", $ticket_id);
$ticket_stmt->execute();
$ticket = $ticket_stmt->get_result()->fetch_assoc();

// Fetch all messages for this ticket
$messages_stmt = $conn->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC");
$messages_stmt->bind_param("i", $ticket_id);
$messages_stmt->execute();
$messages = $messages_stmt->get_result();
?>

<?php if(isset($_GET['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p>Ticket has been updated successfully!</p></div>
<?php endif; ?>

<a href="support.php" class="text-indigo-600 hover:underline mb-6 inline-block">&larr; Back to All Tickets</a>
<h1 class="text-3xl font-bold text-gray-800">Support Ticket #<?php echo $ticket['id']; ?></h1>
<p class="text-gray-600">Subject: <span class="font-semibold"><?php echo htmlspecialchars($ticket['subject']); ?></span></p>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Conversation Thread -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-lg font-semibold border-b pb-2 mb-4">Conversation</h2>
        <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
            <!-- Initial user message -->
            <div class="flex justify-start">
                <div class="bg-gray-100 p-4 rounded-lg max-w-lg">
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($ticket['user_name']); ?></p>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($ticket['message']); ?></p>
                    <p class="text-xs text-gray-400 mt-2 text-right"><?php echo date('d M, Y, h:i A', strtotime($ticket['created_at'])); ?></p>
                </div>
            </div>
            <!-- Replies -->
            <?php while($msg = $messages->fetch_assoc()): ?>
                <div class="flex <?php echo $msg['is_admin_reply'] ? 'justify-end' : 'justify-start'; ?>">
                    <div class="<?php echo $msg['is_admin_reply'] ? 'bg-indigo-500 text-white' : 'bg-gray-100'; ?> p-4 rounded-lg max-w-lg">
                        <p class="font-bold"><?php echo $msg['is_admin_reply'] ? 'Admin' : htmlspecialchars($ticket['user_name']); ?></p>
                        <p class="text-sm whitespace-pre-wrap"><?php echo htmlspecialchars($msg['message']); ?></p>
                        <p class="text-xs <?php echo $msg['is_admin_reply'] ? 'text-indigo-200' : 'text-gray-400'; ?> mt-2 text-right"><?php echo date('d M, Y, h:i A', strtotime($msg['created_at'])); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <!-- Reply Form -->
        <form method="POST" class="mt-6 border-t pt-4">
            <input type="hidden" name="action" value="send_reply">
            <h3 class="font-semibold mb-2">Send a Reply</h3>
            <textarea name="reply_message" rows="4" placeholder="Type your reply here..." required class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
            <button type="submit" class="w-full mt-2 bg-indigo-600 text-white font-semibold py-2 rounded-md hover:bg-indigo-700">Send Reply</button>
        </form>
    </div>

    <!-- User & Status -->
    <div class="bg-white p-6 rounded-xl shadow-md self-start">
        <h2 class="text-lg font-semibold border-b pb-2 mb-4">User Details</h2>
        <div class="space-y-2 text-sm text-gray-700">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($ticket['user_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($ticket['user_email']); ?></p>
        </div>
        <h2 class="text-lg font-semibold border-b pb-2 mb-4 mt-6">Update Status</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <select name="status" class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="Open" <?php if($ticket['status'] == 'Open') echo 'selected'; ?>>Open</option>
                <option value="Answered" <?php if($ticket['status'] == 'Answered') echo 'selected'; ?>>Answered</option>
                <option value="Closed" <?php if($ticket['status'] == 'Closed') echo 'selected'; ?>>Closed</option>
            </select>
            <button type="submit" class="w-full mt-4 bg-purple-600 text-white font-semibold py-2 rounded-md hover:bg-purple-700">Update</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>