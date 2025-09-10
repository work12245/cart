<?php
require_once 'common/config.php';

// --- AJAX REQUEST HANDLER for filtering ---
if (isset($_GET['action']) && $_GET['action'] == 'filter_products') {
    $cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';
    
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id WHERE 1";
    if ($cat_id > 0) { $sql .= " AND p.cat_id = $cat_id"; }
    
    $sort_options = ['new' => 'ORDER BY p.created_at DESC', 'price_asc' => 'ORDER BY p.price ASC', 'price_desc' => 'ORDER BY p.price DESC'];
    $sql .= " " . ($sort_options[$sort] ?? $sort_options['new']);
    
    $products_result = $conn->query($sql);
    $output = '';
    if ($products_result->num_rows > 0) {
        while ($product = $products_result->fetch_assoc()) {
            $old_price = number_format($product['price'] * 1.3, 2);
            // THE FIX: The entire card is wrapped in an <a> tag and DOES NOT have the 'product-card-clickable' class.
            $output .= '
            <a href="product_detail.php?id=' . $product['id'] . '" class="block group">
                <div class="bg-white rounded-2xl p-6 product-card">
                    <div class="relative mb-4">
                        <div class="bg-gray-100 rounded-full w-48 h-48 mx-auto overflow-hidden">
                            <img src="' . (file_exists($product['image']) ? SITE_URL.$product['image'] : 'https://i.ibb.co/fH7Bw3t/product-pizza.png') . '" class="w-full h-full object-cover">
                        </div>
                        <span class="absolute top-2 left-2 bg-brand-primary text-white text-xs font-semibold px-3 py-1 rounded-full">' . htmlspecialchars($product['category_name'] ?? 'Food') . '</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="text-yellow-500 text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div>
                        <h3 class="mt-2 text-xl font-bold text-gray-800 text-center truncate w-full">' . htmlspecialchars($product['name']) . '</h3>
                        <p class="mt-2 font-semibold text-gray-800">' . CURRENCY_SYMBOL . number_format($product['price'], 2) . ' <s class="text-gray-400 font-normal">' . CURRENCY_SYMBOL . $old_price . '</s></p>
                    </div>
                    <div class="mt-4 flex justify-center space-x-2">
                        <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-shopping-cart"></i></div>
                        <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-heart"></i></div>
                        <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-eye"></i></div>
                    </div>
                </div>
            </a>';
        }
    } else { $output = '<p class="col-span-full text-center text-gray-500 py-16"><i class="fas fa-search fa-3x text-gray-300 mb-4"></i><br>No products found in this category.</p>'; }
    
    echo $output;
    exit(); 
}

// --- REGULAR PAGE LOAD ---
include 'common/header.php';

$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id WHERE 1";
$category_name = "All Products";
if ($cat_id > 0) {
    $sql .= " AND p.cat_id = $cat_id";
    $cat_res = $conn->query("SELECT name FROM categories WHERE id = $cat_id");
    if ($cat_res->num_rows > 0) { $category_name = $cat_res->fetch_assoc()['name']; }
}
$sort_options = ['new' => 'ORDER BY p.created_at DESC', 'price_asc' => 'ORDER BY p.price ASC', 'price_desc' => 'ORDER BY p.price DESC'];
$sql .= " " . ($sort_options[$sort] ?? $sort_options['new']);
$products_result = $conn->query($sql);
?>

<div class="pt-8 pb-16">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-10">
        <div class="text-center sm:text-left">
            <p class="text-brand-primary font-semibold">Our Menu</p>
            <h1 id="page-title" class="text-4xl md:text-5xl font-extrabold text-gray-800 tracking-tighter"><?php echo htmlspecialchars($category_name); ?></h1>
        </div>
        <div class="relative mt-4 sm:mt-0">
            <select id="sort-filter" class="appearance-none bg-white border-2 border-gray-200 rounded-full py-2 pl-4 pr-10 text-sm font-semibold text-gray-700 focus:outline-none focus:border-brand-primary focus:ring-0">
                <option value="new" <?php echo ($sort == 'new') ? 'selected' : ''; ?>>Sort by: Newest</option>
                <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
        </div>
    </div>

    <div class="mb-10 flex justify-center items-center flex-wrap gap-3">
        <a href="#" class="category-filter py-2 px-6 rounded-full font-semibold border-2 transition-colors <?php echo ($cat_id == 0) ? 'bg-brand-primary text-white border-brand-primary' : 'bg-white text-gray-700 border-gray-200 hover:border-brand-primary'; ?>" data-catid="0" data-catname="All Products">All</a>
        <?php 
            $filter_categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
            while ($cat = $filter_categories_result->fetch_assoc()): 
        ?>
        <a href="#" class="category-filter py-2 px-6 rounded-full font-semibold border-2 transition-colors <?php echo ($cat_id == $cat['id']) ? 'bg-brand-primary text-white border-brand-primary' : 'bg-white text-gray-700 border-gray-200 hover:border-brand-primary'; ?>" data-catid="<?php echo $cat['id']; ?>" data-catname="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
        <?php endwhile; ?>
    </div>

    <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php if ($products_result->num_rows > 0): ?>
            <?php while ($product = $products_result->fetch_assoc()): $old_price = number_format($product['price'] * 1.3, 2); ?>
                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="block group">
                    <div class="bg-white rounded-2xl p-6 product-card">
                        <div class="relative mb-4"><div class="bg-gray-100 rounded-full w-48 h-48 mx-auto overflow-hidden"><img src="<?php echo file_exists($product['image']) ? SITE_URL.$product['image'] : 'https://i.ibb.co/fH7Bw3t/product-pizza.png'; ?>" class="w-full h-full object-cover"></div><span class="absolute top-2 left-2 bg-brand-primary text-white text-xs font-semibold px-3 py-1 rounded-full"><?php echo htmlspecialchars($product['category_name'] ?? 'Food'); ?></span></div>
                        <div class="flex flex-col items-center"><div class="text-yellow-500 text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div><h3 class="mt-2 text-xl font-bold text-gray-800 text-center truncate w-full"><?php echo htmlspecialchars($product['name']); ?></h3><p class="mt-2 font-semibold text-gray-800"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 2); ?> <s class="text-gray-400 font-normal"><?php echo CURRENCY_SYMBOL; ?><?php echo $old_price; ?></s></p></div>
                        <div class="mt-4 flex justify-center space-x-2">
                            <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-shopping-cart"></i></div>
                            <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-heart"></i></div>
                            <div class="w-10 h-10 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center"><i class="fas fa-eye"></i></div>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="col-span-full text-center text-gray-500 py-16"><i class="fas fa-search fa-3x text-gray-300 mb-4"></i><br>No products found in this category.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productGrid = document.getElementById('product-grid');
    const categoryFilters = document.querySelectorAll('.category-filter');
    const sortFilter = document.getElementById('sort-filter');
    const pageTitle = document.getElementById('page-title');
    let currentCatId = new URLSearchParams(window.location.search).get('cat_id') || 0;
    async function updateProducts() {
        productGrid.style.opacity = '0.5';
        const sortValue = sortFilter.value;
        const fetchUrl = `product.php?action=filter_products&cat_id=${currentCatId}&sort=${sortValue}`;
        try {
            const response = await fetch(fetchUrl);
            const newHtml = await response.text();
            setTimeout(() => { productGrid.innerHTML = newHtml; productGrid.style.opacity = '1'; }, 300);
            const newBrowserUrl = `product.php?cat_id=${currentCatId}&sort=${sortValue}`;
            history.pushState(null, '', currentCatId == 0 ? `product.php?sort=${sortValue}` : newBrowserUrl);
        } catch (error) {
            productGrid.innerHTML = '<p class="col-span-full text-center text-red-500">Error loading products.</p>';
            productGrid.style.opacity = '1';
        }
    }
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function(e) {
            e.preventDefault();
            categoryFilters.forEach(f => { f.classList.remove('bg-brand-primary', 'text-white', 'border-brand-primary'); f.classList.add('bg-white', 'text-gray-700', 'border-gray-200'); });
            this.classList.add('bg-brand-primary', 'text-white', 'border-brand-primary');
            currentCatId = this.dataset.catid;
            pageTitle.textContent = this.dataset.catname;
            updateProducts();
        });
    });
    sortFilter.addEventListener('change', updateProducts);
});
</script>

<?php
include 'common/bottom.php';
?>