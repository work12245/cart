<?php
// === STEP 1: HANDLE ALL AJAX REQUESTS FIRST ===
if (isset($_POST['action'])) {
    include __DIR__ . '/../common/config.php';
    header('Content-Type: application/json');
    $action = $_POST['action'];
    
    if ($action == 'add' || $action == 'edit') {
        $name = $_POST['name'] ?? '';
        $id = $_POST['id'] ?? 0;
        $image_path = $_POST['existing_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../assets/images/categories/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "assets/images/categories/" . $image_name;
            }
        }
        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $image_path);
        } else {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $image_path, $id);
        }
        if($stmt->execute()){ echo json_encode(['status' => 'success']); } 
        else { echo json_encode(['status' => 'error', 'message' => 'Database operation failed.']); }
    }
    
    if ($action == 'get') {
        $id = (int)($_POST['id'] ?? 0);
        $result = $conn->query("SELECT * FROM categories WHERE id = $id");
        echo json_encode($result->fetch_assoc());
    }
    
    if ($action == 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM categories WHERE id = $id");
        echo json_encode(['status' => 'success']);
    }
    
    exit();
}

// === STEP 2: DISPLAY THE HTML PAGE ===
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!-- HTML to display the categories table -->
<div class="p-4 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Categories</h1>
        <!-- --- THE FIX: Button now targets the modal via data attribute --- -->
        <button data-modal-target="categoryModal" onclick="prepareAddModal()" class="bg-pink-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-pink-700">
            <i class="fas fa-plus mr-2"></i> Add Category
        </button>
    </div>
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="w-full table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Image</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Name</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                <tr id="cat-<?php echo $cat['id']; ?>">
                    <td class="px-4 py-2"><img src="<?php echo SITE_URL . ($cat['image'] ?? ''); ?>" class="w-12 h-12 rounded-full object-cover"></td>
                    <td class="px-4 py-2 font-medium"><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td class="px-4 py-2 text-right">
                        <!-- --- THE FIX: Button now targets the modal and calls a specific function --- -->
                        <button data-modal-target="categoryModal" onclick="prepareEditModal(<?php echo $cat['id']; ?>)" class="text-indigo-600 hover:text-indigo-800 p-1 mr-2" title="Edit"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteCategory(<?php echo $cat['id']; ?>)" class="text-red-600 hover:text-red-800 p-1" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- HTML for the Add/Edit Modal (Pop-up) -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h2 id="modalTitle" class="text-xl font-bold">Add Category</h2>
            <!-- --- THE FIX: Close button uses data attribute --- -->
            <button data-modal-close class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
        </div>
        <form id="categoryForm" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="categoryId">
            <input type="hidden" name="existing_image" id="existing_image">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium">Name</label>
                    <input type="text" id="name" name="name" required class="w-full mt-1 px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium">Image</label>
                    <input type="file" id="image" name="image" accept="image/*" class="w-full mt-1">
                    <img id="imagePreview" src="" class="w-24 h-24 mt-2 object-cover rounded-full hidden border-2 border-gray-200">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <!-- --- THE FIX: Cancel button uses data attribute --- -->
                <button type="button" data-modal-close class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">Cancel</button>
                <button type="submit" class="bg-pink-600 text-white font-bold px-4 py-2 rounded-md hover:bg-pink-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- This script only contains logic SPECIFIC to this page (category form) -->
<script>
const categoryForm = document.getElementById('categoryForm');
const modalTitle = document.getElementById('modalTitle');
const imagePreview = document.getElementById('imagePreview');

function prepareAddModal() {
    categoryForm.reset();
    imagePreview.src = '';
    imagePreview.classList.add('hidden');
    modalTitle.textContent = 'Add Category';
    categoryForm.action.value = 'add';
    categoryForm.id.value = '';
}

function prepareEditModal(id) {
    categoryForm.reset();
    imagePreview.src = '';
    imagePreview.classList.add('hidden');
    
    modalTitle.textContent = 'Edit Category';
    categoryForm.action.value = 'edit';
    categoryForm.id.value = id;
    
    const formData = new FormData();
    formData.append('action', 'get');
    formData.append('id', id);
    
    fetch('category.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data) {
                categoryForm.name.value = data.name;
                if(data.image) {
                    imagePreview.src = `<?php echo SITE_URL; ?>${data.image}`;
                    imagePreview.classList.remove('hidden');
                    categoryForm.existing_image.value = data.image;
                }
            }
        });
}

categoryForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    showLoader();
    try {
        const response = await fetch('category.php', { method: 'POST', body: new FormData(categoryForm) });
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

async function deleteCategory(id) {
    if (confirm('Are you sure? This may affect products in this category.')) {
        showLoader();
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            await fetch('category.php', { method: 'POST', body: formData });
            document.getElementById(`cat-${id}`).remove();
        } catch (error) {
            console.error("Delete error:", error);
        } finally {
            hideLoader();
        }
    }
}
</script>

<?php include __DIR__ . '/common/bottom.php'; ?>