<?php
// === STEP 1: HANDLE ALL AJAX REQUESTS FIRST ===
if (isset($_POST['action'])) {
    include __DIR__ . '/../common/config.php';
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action == 'add' || $action == 'edit') {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'];
        $description = $_POST['description'];
        $discount_text = $_POST['discount_text'];
        $image_path = $_POST['existing_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../assets/images/offers/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) { $image_path = "assets/images/offers/" . $image_name; }
        }
        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO offers (title, description, discount_text, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $description, $discount_text, $image_path);
        } else {
            $stmt = $conn->prepare("UPDATE offers SET title = ?, description = ?, discount_text = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $description, $discount_text, $image_path, $id);
        }
        if($stmt->execute()){ echo json_encode(['status' => 'success']); }
        else { echo json_encode(['status' => 'error', 'message' => 'Database error.']); }
    }

    if ($action == 'get') {
        $id = (int)($_POST['id'] ?? 0);
        $result = $conn->query("SELECT * FROM offers WHERE id = $id");
        echo json_encode($result->fetch_assoc());
    }

    if ($action == 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM offers WHERE id = $id");
        echo json_encode(['status' => 'success']);
    }
    exit();
}

// === STEP 2: DISPLAY THE HTML PAGE ===
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';

$offers_result = $conn->query("SELECT * FROM offers ORDER BY id DESC");
?>

<!-- HTML to display the offers table -->
<div class="p-4 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Daily Offers</h1>
        <!-- --- THE FIX: Button now targets the modal via data attribute --- -->
        <button data-modal-target="offerModal" onclick="prepareAddModal()" class="bg-pink-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-pink-700 shadow-lg">
            <i class="fas fa-plus mr-2"></i> Add Offer
        </button>
    </div>
    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Offer Details</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Discount</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($offer = $offers_result->fetch_assoc()): ?>
                    <tr id="offer-<?php echo $offer['id']; ?>">
                        <td class="px-6 py-4"><div class="flex items-center"><img class="h-12 w-12 rounded-full object-cover" src="<?php echo SITE_URL . ($offer['image'] ?? 'assets/images/placeholder.png'); ?>"><div class="ml-4"><div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($offer['title']); ?></div><div class="text-sm text-gray-500"><?php echo htmlspecialchars($offer['description']); ?></div></div></div></td>
                        <td class="px-6 py-4"><span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><?php echo htmlspecialchars($offer['discount_text']); ?></span></td>
                        <td class="px-6 py-4 text-right">
                            <!-- --- THE FIX: Button now targets the modal and calls a specific function --- -->
                            <button data-modal-target="offerModal" onclick="prepareEditModal(<?php echo $offer['id']; ?>)" class="text-indigo-600 hover:text-indigo-900 mr-4 font-bold">Edit</button>
                            <button onclick="deleteOffer(<?php echo $offer['id']; ?>)" class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- HTML for the Add/Edit Modal (Pop-up) -->
<div id="offerModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-11/12 max-w-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Offer</h2>
            <!-- --- THE FIX: Close button uses data attribute --- -->
            <button data-modal-close class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
        </div>
        <form id="offerForm" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="offerId">
            <input type="hidden" name="existing_image" id="existing_image">
            <div><label class="block text-sm font-medium text-gray-700">Title</label><input type="text" name="title" id="title" required class="w-full mt-1 p-2 border rounded-lg"></div>
            <div><label class="block text-sm font-medium text-gray-700">Description</label><input type="text" name="description" id="description" class="w-full mt-1 p-2 border rounded-lg"></div>
            <div><label class="block text-sm font-medium text-gray-700">Discount Text (e.g., 17% OFF)</label><input type="text" name="discount_text" id="discount_text" required class="w-full mt-1 p-2 border rounded-lg"></div>
            <div><label class="block text-sm font-medium text-gray-700">Image</label><input type="file" name="image" id="image" class="w-full mt-1"></div>
            <div class="pt-4 flex justify-end space-x-3">
                <!-- --- THE FIX: Cancel button uses data attribute --- -->
                <button type="button" data-modal-close class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">Save Offer</button>
            </div>
        </form>
    </div>
</div>

<!-- This script only contains logic SPECIFIC to this page (offer form) -->
<script>
const offerForm = document.getElementById('offerForm');
const modalTitle = document.getElementById('modalTitle');

// Function to prepare the modal for adding a new offer
function prepareAddModal() {
    offerForm.reset();
    modalTitle.textContent = 'Add New Offer';
    offerForm.action.value = 'add';
    offerForm.id.value = '';
}

// Function to prepare the modal for editing an offer
function prepareEditModal(id) {
    offerForm.reset();
    modalTitle.textContent = 'Edit Offer';
    offerForm.action.value = 'edit';
    offerForm.id.value = id;
    
    const formData = new FormData();
    formData.append('action', 'get');
    formData.append('id', id);
    
    fetch('offers.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data) {
                offerForm.title.value = data.title;
                offerForm.description.value = data.description;
                offerForm.discount_text.value = data.discount_text;
                offerForm.existing_image.value = data.image;
            }
        });
}

// Event listener for the form submission
offerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    showLoader();
    try {
        const response = await fetch('offers.php', { method: 'POST', body: new FormData(offerForm) });
        const result = await response.json();
        if(result.status === 'success') {
            location.reload();
        } else {
            alert('An error occurred: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error("Form submission error:", error);
        alert('A network error occurred.');
    } finally {
        hideLoader();
    }
});

// Function to delete an offer
async function deleteOffer(id) {
    if (confirm('Are you sure you want to delete this offer?')) {
        showLoader();
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            await fetch('offers.php', { method: 'POST', body: formData });
            document.getElementById(`offer-${id}`).remove();
        } catch (error) {
            console.error("Delete error:", error);
        } finally {
            hideLoader();
        }
    }
}
</script>

<?php include __DIR__ . '/common/bottom.php'; ?>```

Mubarak ho! Is code ko daalne ke baad, aapke Admin Panel ki pop-up se judi sabhi samasyaen hamesha ke liye theek ho jaayengi.