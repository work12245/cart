<?php
// === STEP 1: HANDLE ALL AJAX REQUESTS FIRST ===
// This ensures no HTML is sent back with JSON responses, preventing network errors.
if (isset($_POST['action'])) {
    // We only need the config file for AJAX requests.
    require_once __DIR__ . '/../common/config.php';
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action == 'add' || $action == 'edit') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'];
        $cat_id = $_POST['cat_id'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $nutrition_info = $_POST['nutrition_info'];
        $allergens = $_POST['allergens'];
        $image_path = $_POST['existing_image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../assets/images/products/";
            if (!is_dir($target_dir)) @mkdir($target_dir, 0777, true);
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "assets/images/products/" . $image_name;
            }
        }
        
        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO products (name, cat_id, description, price, stock, image, nutrition_info, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            // THE FIX: Corrected bind_param with 8 types (s, i, s, d, i, s, s, s)
            $stmt->bind_param("sisdisss", $name, $cat_id, $description, $price, $stock, $image_path, $nutrition_info, $allergens);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name = ?, cat_id = ?, description = ?, price = ?, stock = ?, image = ?, nutrition_info = ?, allergens = ? WHERE id = ?");
            // THE FIX: Corrected bind_param with 9 types (s, i, s, d, i, s, s, s, i)
            $stmt->bind_param("sisdisssi", $name, $cat_id, $description, $price, $stock, $image_path, $nutrition_info, $allergens, $id);
        }
        
        if($stmt->execute()){
            echo json_encode(['status' => 'success']);
        } else {
            // Provide a more descriptive error for debugging
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        }
    }
    
    else if ($action == 'get') {
        $id = (int)($_POST['id'] ?? 0);
        $result = $conn->query("SELECT * FROM products WHERE id = $id");
        echo json_encode($result->fetch_assoc());
    }
    
    else if ($action == 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($conn->query("DELETE FROM products WHERE id = $id")) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete product.']);
        }
    }
    
    // Stop the script after handling the AJAX request
    exit();
}

// === STEP 2: DISPLAY THE HTML PAGE ===
// This part only runs when the page is loaded normally (not via AJAX).
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';

// Fetch categories and products for display
$category_list = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($row = $cat_result->fetch_assoc()) {
    $category_list[] = $row;
}
$are_categories_available = !empty($category_list);
$products_result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id ORDER BY p.created_at DESC");
?>

<!-- HTML to display the products table -->
<div class="p-4 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Products</h1>
        <?php if ($are_categories_available): ?>
            <button onclick="prepareAddModal()" class="bg-pink-600 text-white font-bold py-2 px-5 rounded-lg shadow-lg hover:shadow-xl">
                <i class="fas fa-plus mr-2"></i> Add Product
            </button>
        <?php else: ?>
            <a href="category.php" class="bg-blue-600 text-white font-bold py-2 px-5 rounded-lg">
                <i class="fas fa-tags mr-2"></i> Add Category First
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Product</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Category</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Price</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Stock</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($prod = $products_result->fetch_assoc()): ?>
                    <tr id="prod-<?php echo $prod['id']; ?>" class="hover:bg-pink-50">
                        <td class="px-6 py-4"><div class="flex items-center"><div class="flex-shrink-0 h-14 w-14"><img class="h-14 w-14 rounded-lg object-cover" src="<?php echo SITE_URL . ($prod['image'] ?? 'assets/images/placeholder.png'); ?>"></div><div class="ml-4"><div class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($prod['name']); ?></div></div></div></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($prod['category_name'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 text-right text-base font-semibold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($prod['price']); ?></td>
                        <td class="px-6 py-4 text-center"><span class="px-3 py-1 text-xs font-bold rounded-full <?php echo $prod['stock'] > 10 ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'; ?>"><?php echo $prod['stock']; ?> IN STOCK</span></td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <button onclick="prepareEditModal(<?php echo $prod['id']; ?>)" class="text-indigo-600 hover:text-indigo-900 mr-4 font-bold">Edit</button>
                            <button onclick="deleteProduct(<?php echo $prod['id']; ?>)" class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal (Pop-up) -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-11/12 max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Product</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
        </div>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="productId">
            <input type="hidden" name="existing_image" id="existing_image">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label><input type="text" name="name" id="name" required class="w-full px-4 py-3 border rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Category</label><select name="cat_id" id="cat_id" required class="w-full px-4 py-3 border rounded-lg"><option value="" disabled selected>-- Select a Category --</option><?php foreach ($category_list as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option><?php endforeach; ?></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Price (<?php echo CURRENCY_SYMBOL; ?>)</label><input type="number" step="1" min="0" name="price" id="price" required class="w-full px-4 py-3 border rounded-lg"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><textarea name="description" id="description" rows="4" class="w-full px-4 py-3 border rounded-lg"></textarea></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label><input type="number" min="0" name="stock" id="stock" required class="w-full px-4 py-3 border rounded-lg"></div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nutritional Info (JSON Format)</label>
                    <textarea name="nutrition_info" id="nutrition_info" rows="4" class="w-full mt-1 p-2 border rounded-lg font-mono text-sm" placeholder='{"Calories": "350", "Protein": "20g", "Fat": "15g"}'></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Allergens (comma-separated)</label>
                    <input type="text" name="allergens" id="allergens" class="w-full mt-1 p-2 border rounded-lg" placeholder="e.g., Nuts, Gluten, Dairy">
                </div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label><input type="file" name="image" id="image" accept="image/*" class="w-full"><img id="imagePreview" src="" class="w-28 h-28 mt-4 object-cover rounded-lg hidden"></div>
            </div>
            <div class="mt-8 pt-6 border-t flex justify-end space-x-4">
                <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="submit" class="bg-pink-600 text-white font-bold py-3 px-7 rounded-lg hover:bg-pink-700">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
const productModal = document.getElementById('productModal');
const productForm = document.getElementById('productForm');
const modalTitle = document.getElementById('modalTitle');
const imagePreview = document.getElementById('imagePreview');
const formAction = document.getElementById('formAction');

function openModal() { productModal.classList.remove('hidden'); }
function closeModal() { productModal.classList.add('hidden'); }

function prepareAddModal() {
    productForm.reset();
    imagePreview.src = '';
    imagePreview.classList.add('hidden');
    modalTitle.textContent = 'Add New Product';
    formAction.value = 'add';
    productForm.elements.id.value = ''; // Use elements collection for safety
    openModal();
}

function prepareEditModal(id) {
    productForm.reset();
    imagePreview.src = '';
    imagePreview.classList.add('hidden');
    modalTitle.textContent = 'Edit Product';
    formAction.value = 'edit';
    productForm.elements.id.value = id;
    
    const formData = new FormData();
    formData.append('action', 'get');
    formData.append('id', id);
    
    fetch('product.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data) {
                productForm.elements.name.value = data.name;
                productForm.elements.cat_id.value = data.cat_id;
                productForm.elements.description.value = data.description;
                productForm.elements.price.value = data.price;
                productForm.elements.stock.value = data.stock;
                productForm.elements.nutrition_info.value = data.nutrition_info;
                productForm.elements.allergens.value = data.allergens;
                if(data.image) {
                    imagePreview.src = `<?php echo SITE_URL; ?>${data.image}`;
                    imagePreview.classList.remove('hidden');
                    productForm.elements.existing_image.value = data.image;
                }
                openModal();
            }
        });
}

productForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    showLoader();
    try {
        const response = await fetch('product.php', { method: 'POST', body: new FormData(productForm) });
        const result = await response.json();
        if(result.status === 'success') {
            closeModal();
            alert(formAction.value === 'add' ? 'Product Added Successfully!' : 'Product Updated Successfully!');
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

async function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        showLoader();
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            const response = await fetch('product.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                document.getElementById(`prod-${id}`).remove();
                alert('Product deleted successfully!');
            } else {
                 alert('Error deleting product: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error("Delete error:", error);
            alert('A network error occurred during deletion.');
        } finally {
            hideLoader();
        }
    }
}
</script>

<?php include __DIR__ . '/common/bottom.php'; ?>