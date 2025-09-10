<?php
require_once '../common/config.php';
check_login();
$user_id = $_SESSION['user_id'];
$tickets = $conn->query("SELECT * FROM support_tickets WHERE user_id = $user_id ORDER BY created_at DESC");
?>
<h2 class="text-2xl font-bold mb-1">Help & Support</h2>
<p class="text-sm text-gray-500 mb-6">Create a new ticket or view your past conversations.</p>
<form id="ajax-support-form" class="space-y-4">
    <input type="hidden" name="action" value="send_support_ticket">
    <div><label class="text-xs font-semibold text-pink-500">SUBJECT</label><input type="text" name="subject" required placeholder="e.g., Issue with my last order" class="w-full p-2 border-b-2 focus:outline-none focus:border-pink-500"></div>
    <div><label class="text-xs font-semibold text-pink-500">MESSAGE</label><textarea rows="4" name="message" required placeholder="Please describe your issue in detail..." class="w-full p-2 border-b-2 focus:outline-none focus:border-pink-500"></textarea></div>
    <div class="text-right"><button type="submit" class="bg-pink-500 text-white font-bold px-6 py-2 rounded-lg hover:bg-pink-600">Create Ticket</button></div>
</form>
<div id="ajax-message" class="mt-4 text-center text-sm"></div>
<hr class="my-8">
<h2 class="text-2xl font-bold mb-4">Your Ticket History</h2>
<div class="space-y-3 max-h-96 overflow-y-auto">
    <?php if ($tickets->num_rows > 0): while($ticket = $tickets->fetch_assoc()): ?>
        <!-- THE FIX: Each ticket is now a button that triggers a JS function -->
        <button class="view-ticket-btn w-full text-left bg-gray-50 p-4 rounded-lg border hover:border-pink-500" data-ticket-id="<?php echo $ticket['id']; ?>">
            <div class="flex justify-between items-center">
                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($ticket['subject']); ?></p>
                <span class="text-xs font-semibold rounded-full px-2 py-1 <?php echo $ticket['status'] == 'Answered' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>"><?php echo $ticket['status']; ?></span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Ticket #<?php echo $ticket['id']; ?> | Created on <?php echo date('d M, Y', strtotime($ticket['created_at'])); ?></p>
        </button>
    <?php endwhile; else: ?>
        <p class="text-gray-400 text-center py-10">You have no support tickets.</p>
    <?php endif; ?>
</div>