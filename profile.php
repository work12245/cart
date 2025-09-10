<?php 
$is_profile_page = true; 
include 'common/header.php'; 
check_login();
// Step 1: Variable ko pehle se banao (taaki warning na aaye)
$successMessage = null;
// Step 2: Agar URL mein success message hai, to variable ki value set karo
if (isset($_GET['success']) && $_GET['success'] == 'true' && isset($_GET['order_id'])) {
    $order_id = htmlspecialchars($_GET['order_id']);
    $successMessage = "Thank you! Your Order #" . $order_id . " has been placed successfully.";
}
?>
<style>
    body { background-color: #F9FAFB; } main.container { padding: 0; max-width: 1200px; }
    .profile-container { display: flex; background: white; border-radius: 1rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); overflow: hidden; }
    .profile-sidebar { flex-shrink: 0; } .profile-content { flex-grow: 1; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .content-fade-in { animation: fadeIn 0.4s ease-out; }
    #mobile-profile-modal { transition: opacity 0.3s ease-in-out; } #mobile-modal-content { transition: transform 0.3s ease-in-out; }
</style>

<!-- --- THE FIX: Display the success message if it exists --- -->
<?php if ($successMessage): ?>
<div id="success-alert" class="max-w-5xl mx-auto mt-8 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-md" role="alert">
  <div class="flex">
    <div class="py-1"><svg class="fill-current h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zM10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5.41l-2.59-2.59L5 11.41l3 3 6-6L12.59 7 9 10.59z"/></svg></div>
    <div>
      <p class="font-bold">Order Placed!</p>
      <p class="text-sm"><?php echo $successMessage; ?></p>
    </div>
    <button onclick="document.getElementById('success-alert').style.display='none'" class="ml-auto text-green-700">&times;</button>
  </div>
</div>
<?php endif; ?>

<div class="bg-pink-500 text-white rounded-t-xl max-w-5xl mx-auto mt-8 p-4 font-bold text-lg shadow-lg">QuickKart Profile</div>
<div class="profile-container max-w-5xl mx-auto mb-8">
    <?php include 'common/profile_sidebar.php'; ?>
    <div id="profile-content" class="hidden lg:block p-8 w-full"></div>
    <div class="lg:hidden w-full"><?php include 'common/profile_sidebar.php'; ?></div>
</div>
<div id="mobile-profile-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden opacity-0 flex items-center justify-center">
    <div id="mobile-modal-content" class="bg-white rounded-lg shadow-xl w-11/12 max-w-lg max-h-[90vh] flex flex-col transform scale-95">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 id="modal-title" class="text-xl font-bold"></h2>
            <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
        </div>
        <div id="modal-body" class="p-6 overflow-y-auto"></div>
    </div>
</div>
<div id="order-details-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center p-4">
    <div id="order-details-content" class="bg-gray-50 rounded-2xl shadow-xl w-full max-w-2xl max-h-[95vh] flex flex-col transform scale-95 transition-transform duration-300">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 border-b bg-white rounded-t-2xl">
            <h2 id="order-modal-title" class="text-xl font-bold text-gray-800">Order Details</h2>
            <button id="close-order-modal-btn" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
        </div>
        <div id="order-modal-body" class="p-6 overflow-y-auto">
            <!-- Loader -->
            <div class="text-center p-10">Loading details...</div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // === All required elements ===
    const contentArea = document.getElementById('profile-content');
    const mobileSidebarContainer = document.querySelector('.lg\\:hidden');
    const mobileModal = document.getElementById('mobile-profile-modal');
    const mobileModalContent = document.getElementById('mobile-modal-content');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');
    const closeModalBtn = document.getElementById('close-modal-btn');
    // === NEW: Order Details Modal Elements ===
    const orderModal = document.getElementById('order-details-modal');
    const orderModalContent = document.getElementById('order-details-content');
    const orderModalBody = document.getElementById('order-modal-body');
    const closeOrderModalBtn = document.getElementById('close-order-modal-btn');

    // === Main content loading function ===
    async function loadContent(targetUrl, title, isModal) {
        showLoader();
        try {
            const response = await fetch(targetUrl);
            const html = await response.text();
            
            const container = isModal ? modalBody : contentArea;
            if (isModal) modalTitle.textContent = title;

            container.innerHTML = html;
            
            if (targetUrl.includes('_get_address_form.php')) {
                initializeAddressFormScripts();
            }

            if (isModal) {
                mobileModal.classList.remove('hidden');
                setTimeout(() => { mobileModal.classList.remove('opacity-0'); mobileModalContent.classList.remove('scale-95'); }, 10);
            } else {
                contentArea.classList.remove('hidden');
                if (window.innerWidth < 1024 && mobileSidebarContainer) {
                    mobileSidebarContainer.style.display = 'none';
                }
                container.classList.remove('content-fade-in');
                void container.offsetWidth;
                container.classList.add('content-fade-in');
            }
        } catch (error) { 
            console.error('Failed to load content:', error);
            const container = isModal ? modalBody : contentArea;
            container.innerHTML = '<p class="text-red-500 text-center p-8">Sorry, content could not be loaded. Please try again.</p>';
        } finally { 
            hideLoader(); 
        }
    }
    
    // === Function to activate address form buttons ===
    function initializeAddressFormScripts() {
        const addressFormModal = document.getElementById('address-form-modal');
        const addressFormContainer = document.getElementById('address-form-container');
        const addressForm = document.getElementById('ajax-address-form');
        if (!addressForm) return; 

        const pincodeInput = document.getElementById('pincode-input');
        const checkBtn = document.getElementById('check-pincode-btn');
        const msgDiv = document.getElementById('pincode-message');
        const saveBtn = document.getElementById('save-address-btn');
        const replyDiv = document.getElementById('ajax-reply-message');
        const cancelBtn = document.getElementById('cancel-address-btn');

        // Function to show/hide the address form modal
        const showAddressModal = () => addressFormModal.classList.remove('hidden');
        const hideAddressModal = () => addressFormModal.classList.add('hidden');
        cancelBtn.addEventListener('click', hideAddressModal);
        addressFormModal.addEventListener('click', (e) => {
            if(e.target === addressFormModal) hideAddressModal();
        });

        // --- NEW FUNCTIONS FOR EDIT/DELETE ---
        window.toggleAddressMenu = function(id) {
            document.querySelectorAll('[id^="address-menu-"]').forEach(menu => {
                if (menu.id !== `address-menu-${id}`) {
                    menu.classList.add('hidden');
                }
            });
            document.getElementById(`address-menu-${id}`).classList.toggle('hidden');
        }

        window.prepareAddAddress = function() {
            addressForm.reset();
            replyDiv.textContent = '';
            document.getElementById('address-form-title').textContent = 'Add a New Address';
            addressForm.querySelector('[name="action"]').value = 'add_address';
            addressForm.querySelector('[name="address_id"]').value = '';
            saveBtn.disabled = true;
            msgDiv.textContent = '';
            showAddressModal();
        }

        window.prepareEditAddress = function(id) {
            addressForm.reset();
            replyDiv.textContent = '';
            const formData = new FormData();
            formData.append('action', 'get_address_details');
            formData.append('address_id', id);

            fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success' && result.data) {
                        const data = result.data;
                        document.getElementById('address-form-title').textContent = 'Edit Address';
                        addressForm.querySelector('[name="action"]').value = 'edit_address';
                        addressForm.querySelector('[name="address_id"]').value = data.id;
                        addressForm.querySelector('[name="full_name"]').value = data.full_name;
                        addressForm.querySelector('[name="phone"]').value = data.phone;
                        addressForm.querySelector('[name="address_line"]').value = data.address_line;
                        addressForm.querySelector('[name="pincode"]').value = data.pincode;
                        addressForm.querySelector('[name="address_type"]').value = data.address_type;
                        addressForm.querySelector('[name="is_default"]').checked = (data.is_default == 1);
                        saveBtn.disabled = false;
                        msgDiv.textContent = '';
                        showAddressModal();
                    } else {
                        alert(result.message || 'Could not fetch address details.');
                    }
                });
        }

        window.deleteAddress = async function(id) {
            if (confirm('Are you sure you want to delete this address?')) {
                const formData = new FormData();
                formData.append('action', 'delete_address');
                formData.append('address_id', id);
                
                const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.status === 'success') {
                    document.getElementById(`address-${id}`).remove();
                    const container = document.getElementById('address-list-container');
                    const noMsg = document.getElementById('no-address-message');
                    if (container && noMsg && container.querySelectorAll('[id^="address-"]').length === 0) {
                        noMsg.classList.remove('hidden');
                    }
                } else {
                    alert(result.message || 'Failed to delete address.');
                }
            }
        }
        
        document.body.addEventListener('click', function(event) {
            if (!event.target.closest('.relative')) {
                document.querySelectorAll('[id^="address-menu-"]').forEach(menu => menu.classList.add('hidden'));
            }
        }, true);

        const checkPincode = async () => {
             const pincode = pincodeInput.value;
            if (pincode.length !== 6 || !/^\d+$/.test(pincode)) {
                msgDiv.textContent = 'Please enter a valid 6-digit pincode.';
                msgDiv.className = 'text-xs mt-1 h-4 text-red-500';
                saveBtn.disabled = true;
                return;
            }
            const formData = new FormData();
            formData.append('action', 'check_pincode');
            formData.append('pincode', pincode);
            try {
                const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.serviceable) {
                    msgDiv.textContent = 'Great! We deliver to this area.';
                    msgDiv.className = 'text-xs mt-1 h-4 text-green-600';
                    saveBtn.disabled = false;
                } else {
                    msgDiv.textContent = 'Sorry, we do not deliver to this pincode yet.';
                    msgDiv.className = 'text-xs mt-1 h-4 text-red-500';
                    saveBtn.disabled = true;
                }
            } catch (e) { saveBtn.disabled = true; }
        };
        if(checkBtn) checkBtn.addEventListener('click', checkPincode);
        if(pincodeInput) {
            pincodeInput.addEventListener('input', () => {
                if (pincodeInput.value.length === 6) { checkPincode(); } 
                else { saveBtn.disabled = true; msgDiv.textContent = ''; }
            });
        }
        
        addressForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            replyDiv.textContent = 'Saving...';
            const formData = new FormData(addressForm);
            const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            replyDiv.className = 'mt-4 text-center text-sm ' + (result.status === 'success' ? 'text-green-600' : 'text-red-500');
            replyDiv.textContent = result.message;
            if (result.status === 'success') {
                setTimeout(() => {
                    hideAddressModal();
                    loadContent('ajax/_get_address_form.php', 'Address', window.innerWidth < 1024);
                }, 1500);
            }
        });
    }
    
    
    // === NEW: Function to open and fetch order details ===
    async function showOrderDetails(orderId) {
        orderModal.classList.remove('hidden');
        setTimeout(() => orderModalContent.classList.remove('scale-95'), 10);
        orderModalBody.innerHTML = '<div class="text-center p-10">Loading details...</div>';

        const formData = new FormData();
        formData.append('action', 'get_order_details');
        formData.append('order_id', orderId);

        try {
            const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                const { order_info, order_items } = result.data;
                let itemsHtml = '';
                order_items.forEach(item => {
                    itemsHtml += `
                        <div class="flex items-center space-x-4 py-2 border-b">
                            <img src="${item.image}" class="w-16 h-16 rounded-lg object-cover">
                            <div class="flex-grow">
                                <p class="font-semibold">${item.name}</p>
                                <p class="text-sm text-gray-500">Qty: ${item.quantity} × <?php echo CURRENCY_SYMBOL; ?>${parseFloat(item.price).toFixed(2)}</p>
                            </div>
                            <p class="font-bold"><?php echo CURRENCY_SYMBOL; ?>${(item.quantity * item.price).toFixed(2)}</p>
                        </div>
                    `;
                });
                
                let reviewHtml = '';
                if (order_info.status === 'Delivered' || order_info.status === 'Completed') {
                    reviewHtml = `
                        <div class="bg-white p-4 rounded-lg mt-4 border">
                            <h3 class="font-bold text-md mb-2">Leave a Review</h3>
                            <textarea class="w-full border rounded-md p-2" rows="3" placeholder="How was your experience?"></textarea>
                            <button class="mt-2 w-full bg-pink-600 text-white font-bold py-2 rounded-lg hover:bg-pink-700">Submit Review</button>
                        </div>
                    `;
                }

                orderModalBody.innerHTML = `
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-bold text-md mb-2">Items in Order #${order_info.id}</h3>
                            <div class="space-y-2">${itemsHtml}</div>
                            <div class="text-right mt-2 font-bold text-lg">Total: <?php echo CURRENCY_SYMBOL; ?>${parseFloat(order_info.total_amount).toFixed(2)}</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                             <h3 class="font-bold text-md mb-2">Delivery Details</h3>
                             <p class="text-sm"><strong>To:</strong> ${order_info.name}</p>
                             <p class="text-sm"><strong>Address:</strong> ${order_info.address}</p>
                             <p class="text-sm"><strong>Phone:</strong> ${order_info.phone}</p>
                        </div>
                        ${reviewHtml}
                    </div>
                `;
            } else {
                orderModalBody.innerHTML = `<p class="text-red-500 text-center">${result.message}</p>`;
            }
        } catch(e) {
            orderModalBody.innerHTML = '<p class="text-red-500 text-center">Could not load order details.</p>';
        }
    }

    // === NEW: Functions to close the order modal ===
        function closeOrderModal() {
        orderModalContent.classList.add('scale-95');
        setTimeout(() => orderModal.classList.add('hidden'), 300);
    }
    function closeModal() {
        mobileModal.classList.add('opacity-0');
        mobileModalContent.classList.add('scale-95');
        setTimeout(() => mobileModal.classList.add('hidden'), 300);
    }
    closeOrderModalBtn.addEventListener('click', closeOrderModal);
    orderModal.addEventListener('click', (e) => { if (e.target === orderModal) closeOrderModal(); });
    closeModalBtn.addEventListener('click', closeModal);
    mobileModal.addEventListener('click', (e) => { if (e.target === mobileModal) closeModal(); });

    
    // === All Event Listeners and Initial Load Logic ===
    document.body.addEventListener('click', async (event) => {
        const navLink = event.target.closest('.profile-nav-link');
        const ticketBtn = event.target.closest('.view-ticket-btn');
        // --- NEW: Check for "View Details" button click ---
        const viewOrderBtn = event.target.closest('.view-order-details-btn');
        if (navLink) {
            event.preventDefault();
            const targetUrl = navLink.dataset.target;
            const title = navLink.dataset.title;
            document.querySelectorAll('.profile-nav-link').forEach(l => l.classList.remove('bg-gray-100', 'text-pink-500'));
            document.querySelectorAll(`.profile-nav-link[data-title="${title}"]`).forEach(l => l.classList.add('bg-gray-100', 'text-pink-500'));
            loadContent(targetUrl, title, window.innerWidth < 1024);
        }
        if (ticketBtn) {
            event.preventDefault();
            const ticketId = ticketBtn.dataset.ticketId;
            loadContent(`ajax/_get_ticket_chat.php?id=${ticketId}`, `Ticket #${ticketId}`, true);
        }
        
        if (viewOrderBtn) {
            event.preventDefault();
            const orderId = viewOrderBtn.dataset.orderId;
            showOrderDetails(orderId);
        }
    });

    document.body.addEventListener('submit', async function(event) {
        if (event.target.id === 'ajax-address-form') return; 
        if (event.target.tagName !== 'FORM' || !event.target.id.startsWith('ajax-')) return;
        event.preventDefault(); 
        showLoader();
        const form = event.target; 
        const formData = new FormData(form);
        const messageDiv = form.nextElementSibling || form.querySelector('#ajax-reply-message');
        try {
            const response = await fetch('ajax/_handle_profile_actions.php', { method: 'POST', body: formData });
            const result = await response.json();
            if(messageDiv){ 
                messageDiv.textContent = result.message; 
                messageDiv.className = 'mt-4 text-center text-sm ' + (result.status === 'success' ? 'text-green-500' : 'text-red-500'); 
            }
            if (result.status === 'success') {
                if(form.id === 'ajax-reply-form') { 
                    const ticketId = formData.get('ticket_id');
                    loadContent(`ajax/_get_ticket_chat.php?id=${ticketId}`, `Ticket #${ticketId}`, true);
                } else { 
                    form.reset(); 
                }
            }
        } catch (error) { 
            if(messageDiv) messageDiv.textContent = 'A network error occurred.'; 
        } finally { 
            hideLoader(); 
        }
    });
    
 // === Logic for Initial Page Load ===
    function loadInitialSection() {
        const urlParams = new URLSearchParams(window.location.search);
        let action = urlParams.get('action') || (window.innerWidth >= 1024 ? 'profile' : null);
        if (!action) return;
        const actionToFileMap = {
            profile: '_get_profile_form.php',
            orders: '_get_my_orders.php',
            address: '_get_address_form.php',
            reservation: '_get_reservation_form.php',
            reviews: '_get_my_reviews.php',
            help: '_get_support_form.php',
            password: '_get_password_form.php'
        };
        const targetFilename = actionToFileMap[action];
        if (!targetFilename) return;
        const linksToLoad = document.querySelectorAll(`.profile-nav-link[data-target$="${targetFilename}"]`);
        if (linksToLoad.length > 0) {
            const firstLink = linksToLoad[0];
            const targetUrl = firstLink.dataset.target;
            const title = firstLink.dataset.title;
            linksToLoad.forEach(l => l.classList.add('bg-gray-100', 'text-pink-500'));
            loadContent(targetUrl, title, false);
        }
    }
    
    loadInitialSection();
});
</script>

<?php include 'common/bottom.php'; ?>