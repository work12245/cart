<?php
require_once '../common/config.php';
check_login();

$user_id = $_SESSION['user_id'];
$addresses = $conn->query("SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY is_default DESC, id DESC");
?>

<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">My Addresses</h2>
        <button onclick="prepareAddAddress()" class="bg-pink-500 text-white font-bold px-4 py-2 text-sm rounded-lg hover:bg-pink-600">
            <i class="fas fa-plus mr-2"></i>Add New
        </button>
    </div>
    <p class="text-sm text-gray-500 -mt-2">Manage your saved addresses for faster checkout.</p>
    
    <div id="address-list-container" class="space-y-3">
        <?php if ($addresses->num_rows > 0): while($addr = $addresses->fetch_assoc()): ?>
            <div id="address-<?php echo $addr['id']; ?>" class="bg-gray-50 p-4 rounded-lg border flex justify-between items-start">
                <div>
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($addr['full_name']); ?> (<?php echo htmlspecialchars($addr['address_type']); ?>)</p>
                    <p class="text-sm text-gray-600 mt-1"><?php echo nl2br(htmlspecialchars($addr['address_line'])); ?></p>
                    <p class="text-sm text-gray-600">Pincode: <?php echo htmlspecialchars($addr['pincode']); ?></p>
                    <p class="text-sm text-gray-600">Phone: <?php echo htmlspecialchars($addr['phone']); ?></p>
                </div>
                <div class="flex items-center flex-shrink-0 ml-4">
                    <?php if($addr['is_default']): ?>
                        <span class="text-xs bg-green-100 text-green-700 font-bold px-2 py-1 rounded-full mr-3">Default</span>
                    <?php endif; ?>
                    <div class="relative">
                        <button onclick="toggleAddressMenu(<?php echo $addr['id']; ?>)" class="p-2 text-gray-500 hover:text-gray-800 rounded-full hover:bg-gray-200">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div id="address-menu-<?php echo $addr['id']; ?>" class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg z-20 hidden animate-fade-in-sm">
                            <a href="javascript:void(0)" onclick="prepareEditAddress(<?php echo $addr['id']; ?>)" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-edit w-4 mr-2"></i>Edit
                            </a>
                            <a href="javascript:void(0)" onclick="deleteAddress(<?php echo $addr['id']; ?>)" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-trash-alt w-4 mr-2"></i>Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; endif; ?>
        
        <?php if ($addresses->num_rows == 0): ?>
            <p id="no-address-message" class="text-gray-400 text-center py-8">No addresses saved yet. Click 'Add New' to get started.</p>
        <?php else: ?>
            <p id="no-address-message" class="text-gray-400 text-center py-8 hidden">No addresses saved yet.</p>
        <?php endif; ?>
    </div>

    <!-- This form is hidden by default and will be shown in a modal -->
    <div id="address-form-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center">
        <div id="address-form-container" class="bg-white p-6 rounded-lg shadow-xl w-11/12 max-w-lg animate-fade-in-up">
            <h3 id="address-form-title" class="text-xl font-bold mb-4">Add a New Address</h3>
            <form id="ajax-address-form" class="space-y-4">
                <input type="hidden" name="action" id="address_action_field" value="add_address">
                <input type="hidden" name="address_id" id="address_id_field">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">FULL NAME</label>
                        <input type="text" name="full_name" placeholder="Your Name" required class="w-full p-2 border-b-2 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">PHONE NUMBER</label>
                        <input type="tel" name="phone" placeholder="10-digit Mobile Number" required class="w-full p-2 border-b-2 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">FULL ADDRESS (House No, Street, Area, City)</label>
                    <textarea name="address_line" placeholder="e.g., House No. 123, Sunshine Apartments" required rows="3" class="w-full p-2 border-b-2 focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">PINCODE</label>
                    <div class="flex items-center">
                        <input type="text" name="pincode" id="pincode-input" placeholder="6-digit Pincode" required class="w-full p-2 border-b-2 focus:outline-none">
                        <button type="button" id="check-pincode-btn" class="ml-2 bg-gray-200 text-sm font-bold px-4 py-2 rounded-lg">Check</button>
                    </div>
                    <div id="pincode-message" class="text-xs mt-1 h-4"></div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">ADDRESS TYPE</label>
                    <select name="address_type" class="w-full p-2 border-b-2 bg-white focus:outline-none">
                        <option>Home</option>
                        <option>Work</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_default" id="is_default_checkbox" class="h-4 w-4">
                    <label for="is_default_checkbox" class="ml-2 text-sm text-gray-700">Set as default address</label>
                </div>
                <div class="text-right pt-4 space-x-3">
                    <button type="button" id="cancel-address-btn" class="bg-gray-200 text-gray-800 font-bold px-6 py-2 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" id="save-address-btn" class="bg-pink-500 text-white font-bold px-6 py-2 rounded-lg hover:bg-pink-600 disabled:bg-gray-400">Save Address</button>
                </div>
            </form>
            <div id="ajax-reply-message" class="mt-4 text-center text-sm"></div>
        </div>
    </div>
</div>