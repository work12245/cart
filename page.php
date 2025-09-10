<?php
include 'common/header.php';
$slug = $_GET['slug'] ?? '';
if (empty($slug)) { echo "<p>Page not found.</p>"; include 'common/bottom.php'; exit(); }

$stmt = $conn->prepare("SELECT * FROM pages WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) { echo "<p>Page not found.</p>"; include 'common/bottom.php'; exit(); }
$page = $result->fetch_assoc();
?>
<div class="py-12 max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold text-center"><?php echo htmlspecialchars($page['title']); ?></h1>
    <div class="mt-8 bg-white p-8 rounded-lg shadow-md prose max-w-none">
        <?php echo $page['content']; // No htmlspecialchars here to allow admin to use HTML formatting ?>
    </div>
</div>
<?php include 'common/bottom.php'; ?>