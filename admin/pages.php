<?php
include __DIR__ . '/../common/config.php';
include __DIR__ . '/common/header.php';
// ... baaki ka code
// Handle content update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slug'])) {
    $slug = $_POST['slug'];
    $content = $_POST['content'];
    $stmt = $conn->prepare("UPDATE pages SET content = ? WHERE slug = ?");
    $stmt->bind_param("ss", $content, $slug);
    $stmt->execute();
    echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>Page content updated successfully!</div>";
}

$pages = $conn->query("SELECT * FROM pages");
?>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Pages Content</h1>
<div class="bg-white p-6 rounded-xl shadow-md">
    <form method="POST">
        <div class="mb-4">
            <label for="slug" class="block text-sm font-semibold mb-2">Select Page to Edit:</label>
            <select name="slug" id="page-selector" class="w-full p-2 border rounded-md">
                <?php while($page = $pages->fetch_assoc()) {
                    echo "<option value='{$page['slug']}' data-content='" . htmlspecialchars($page['content']) . "'>{$page['title']}</option>";
                } ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="content" class="block text-sm font-semibold mb-2">Page Content:</label>
            <textarea name="content" id="content-editor" rows="10" class="w-full p-2 border rounded-md"></textarea>
        </div>
        <button type="submit" class="bg-purple-600 text-white font-bold py-2 px-6 rounded-lg">Save Content</button>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('page-selector');
    const editor = document.getElementById('content-editor');
    // Function to update editor content
    const updateEditor = () => {
        const selectedOption = selector.options[selector.selectedIndex];
        editor.value = selectedOption.dataset.content;
    };
    // Update on change
    selector.addEventListener('change', updateEditor);
    // Initial content load
    updateEditor();
});
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>