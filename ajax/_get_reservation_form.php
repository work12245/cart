<?php
require_once '../common/config.php';
check_login();
$user_id = $_SESSION['user_id'];
$reservations_result = $conn->query("SELECT * FROM reservations WHERE user_id = $user_id ORDER BY created_at DESC");

// THE FIX: This is the 100% robust way to fetch the price.
$price_per_person = ''; // Set a safe default
$price_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'price_per_person'");
if ($price_result && $price_result->num_rows > 0) {
    $price_row = $price_result->fetch_assoc();
    if (!empty($price_row['setting_value'])) {
        $price_per_person = $price_row['setting_value'];
    }
}
?>
<h2 class="text-2xl font-bold mb-1">Your Reservation History</h2>
<p class="text-sm text-gray-500 mb-6">Track the status of your past and upcoming table bookings.</p>
<div class="space-y-3 max-h-64 overflow-y-auto pr-2 mb-8">
    <?php if ($reservations_result->num_rows > 0): while($res = $reservations_result->fetch_assoc()): ?>
        <div class="bg-gray-50 p-4 rounded-lg border flex justify-between items-center">
            <div>
                <p class="font-bold text-gray-800">For <?php echo $res['num_guests']; ?> Guests</p>
                <p class="text-sm text-gray-600"><?php echo date('D, d M Y', strtotime($res['reservation_date'])); ?> at <?php echo date('h:i A', strtotime($res['reservation_time'])); ?></p>
                <p class="text-sm font-semibold text-pink-600 mt-1">Total: <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($res['total_price'], 2); ?></p>
            </div>
            <span class="text-xs font-bold rounded-full px-3 py-1 <?php if ($res['status'] == 'Pending') echo 'bg-yellow-100 text-yellow-800'; elseif ($res['status'] == 'Confirmed') echo 'bg-green-100 text-green-800'; else echo 'bg-red-100 text-red-800'; ?>"><?php echo $res['status']; ?></span>
        </div>
    <?php endwhile; else: ?>
        <div class="text-center py-10"><i class="fas fa-calendar-times fa-3x text-gray-300"></i><p class="mt-4 text-gray-500">You have no reservations yet.</p></div>
    <?php endif; ?>
</div>
<hr class="my-6">
<h2 class="text-2xl font-bold mb-1">Make a New Reservation</h2>
<form id="ajax-reservation-form" class="space-y-4">
    <input type="hidden" name="action" value="make_reservation">
    <input type="hidden" id="price-per-person" data-price="<?php echo $price_per_person; ?>">
    <div><label class="text-xs font-semibold text-pink-500">DATE</label><input type="date" name="date" required class="w-full p-2 border-b-2"></div>
    <div><label class="text-xs font-semibold text-pink-500">TIME</label><input type="time" name="time" required class="w-full p-2 border-b-2"></div>
    <div><label class="text-xs font-semibold text-pink-500">NUMBER OF GUESTS</label><select name="guests" id="num-guests" required class="w-full p-2 border-b-2 bg-white"><?php for ($i = 1; $i <= 10; $i++): ?><option value="<?php echo $i; ?>" <?php if ($i == 2) echo 'selected'; ?>><?php echo $i; ?> Guest<?php if ($i > 1) echo 's'; ?></option><?php endfor; ?></select></div>
    <div class="text-right font-bold text-xl">Total Price: <span class="text-pink-500"><?php echo CURRENCY_SYMBOL; ?><span id="total-price">0.00</span></span></div>
    <div class="text-right"><button type="submit" class="bg-pink-500 text-white font-bold px-6 py-2 rounded-lg">Book Table</button></div>
</form>
<div id="ajax-message" class="mt-4 text-center text-sm"></div>

<script>
    // THE FINAL FIX: `DOMContentLoaded` ko hata diya gaya hai.
    // Ab yeh script turant run hoga jaise hi AJAX se load hoga, lekin elements load hone ke baad.
    
    // Use a small delay to ensure all elements are in the DOM
    setTimeout(function() {
        const numGuestsSelect = document.getElementById('num-guests');
        const pricePerPersonInput = document.getElementById('price-per-person');
        const totalPriceSpan = document.getElementById('total-price');
        const reservationForm = document.getElementById('ajax-reservation-form'); 
        const ajaxMessageDiv = document.getElementById('ajax-message'); 

        // Total price calculate karne ka function
        function calculateTotalPrice() {
            const guests = parseInt(numGuestsSelect.value);
            const priceString = pricePerPersonInput.dataset.price;
            const price = parseFloat(priceString);
            
            let total = 0;
            if (!isNaN(guests) && !isNaN(price)) {
                total = guests * price;
            }

            totalPriceSpan.textContent = total.toFixed(2);
        }

        // Page load hone par aur guests change hone par price update karein
        calculateTotalPrice();
        if (numGuestsSelect) {
            numGuestsSelect.addEventListener('change', calculateTotalPrice);
        }

        if (reservationForm) {
            reservationForm.addEventListener('submit', function(event) {
                ajaxMessageDiv.textContent = '';
            });
        }
        
        const dateInput = reservationForm.querySelector('input[name="date"]');
        if (dateInput) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            dateInput.min = `${yyyy}-${mm}-${dd}`;
            dateInput.value = `${yyyy}-${mm}-${dd}`;
        }
    }, 100); // 100 milliseconds ka delay
</script>