        </main>
    </div> <!-- End main-container -->

    <!-- === Quick View Modal (Initially Hidden) === -->
    <div id="quick-view-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
        <div id="modal-content" class="bg-white rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] flex flex-col md:flex-row transform transition-all duration-300 scale-95 overflow-hidden">
            <!-- Close Button -->
            <button id="modal-close-btn" class="absolute top-4 right-4 w-10 h-10 bg-gray-200 text-gray-700 rounded-full text-lg z-10 hover:bg-brand-primary hover:text-white transition-colors">&times;</button>
            
            <!-- Image Section -->
            <div class="w-full md:w-1/2 p-6 flex items-center justify-center bg-gray-100">
                <img id="modal-image" src="" class="max-w-full max-h-96 object-contain drop-shadow-xl" alt="Product Image">
            </div>
            
            <!-- Details Section -->
            <div class="w-full md:w-1/2 p-8 flex flex-col">
                <span id="modal-category" class="text-sm font-semibold text-brand-primary">Category</span>
                <h2 id="modal-title" class="text-3xl font-bold text-gray-800 mt-2">Product Name</h2>
                
                <div class="mt-4 flex items-center space-x-4">
                    <div class="text-yellow-500"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> <span class="ml-2 text-sm text-gray-400">(150 Reviews)</span></div>
                    <span id="modal-stock" class="text-sm font-semibold bg-green-100 text-green-700 px-3 py-1 rounded-full">In Stock</span>
                </div>

                <p id="modal-price" class="text-5xl font-extrabold text-gray-900 my-6">$0.00</p>
                
                <p id="modal-description" class="text-gray-600 text-sm overflow-y-auto max-h-24">Product description goes here...</p>
                
                <form id="modal-cart-form" class="mt-auto pt-6 space-y-4">
                    <input type="hidden" id="modal-product-id" name="product_id">
                    <div class="flex items-center space-x-4">
                
                <!-- Quantity Counter -->
                        <div class="flex items-center border border-gray-300 rounded-lg">
                            <button type="button" class="quantity-btn px-4 py-3 text-lg font-bold text-gray-700" data-change="-1">-</button>
                            <input type="number" id="modal-quantity" name="quantity" value="1" min="1" class="w-16 text-center border-l border-r py-2 focus:outline-none">
                            <button type="button" class="quantity-btn px-4 py-3 text-lg font-bold text-gray-700" data-change="1">+</button>
                        </div>
                        <!-- Add to Cart Button -->
                        <button type="submit" id="modal-add-to-cart-btn" class="flex-1 bg-brand-primary text-white font-bold py-3 rounded-lg shadow-md hover:bg-red-600 transition-colors"><i class="fas fa-shopping-cart mr-2"></i>Add to Cart</button>
                    </div>
                    <!-- Buy Now Button -->
                    <button type="button" id="modal-buy-now-btn" class="w-full bg-gray-800 text-white font-bold py-3 rounded-lg shadow-md hover:bg-gray-900 transition-colors">Buy Now</button>
                </form>
                
                <!-- Share Icons -->
                <div class="mt-6 pt-4 border-t flex items-center space-x-4">
                    <span class="text-sm font-semibold text-gray-600">Share:</span>
                    <a href="#" class="text-gray-400 hover:text-blue-600"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-400 hover:text-sky-500"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-600"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    
    <footer class="bg-gray-800 text-white pt-20 pb-10 mt-24">
        <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            <div>
                <h3 class="text-3xl font-bold text-white-800">Quick<span class="text-brand-primary">Kart</span></h3>
                <p class="mt-4 text-sm text-white-600">Continue QuickKart 2024 all rights reserved.</p>
                <h4 class="mt-8 font-bold text-lg text-white-800">Follow Us On</h4>
                <div class="flex space-x-4 mt-3">
                    <a href="#" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-md text-gray-600 hover:text-brand-primary"><i class="fab fa-pinterest fa-lg"></i></a>
                    <a href="#" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-md text-gray-600 hover:text-brand-primary"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-md text-gray-600 hover:text-brand-primary"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-md text-gray-600 hover:text-brand-primary"><i class="fab fa-facebook-f fa-lg"></i></a>
                </div>
            </div>
            <div>
                <h4 class="font-bold text-xl text-white-800 mb-4">Menu</h4>
                <ul class="space-y-3 text-white-600">
                    <li><a href="index.php" class="hover:text-brand-primary hover:underline">Home</a></li>
                    <li><a href="blog.php" class="hover:text-brand-primary hover:underline">Blogs</a></li>
                    <li><a href="product.php" class="hover:text-brand-primary hover:underline">Menu</a></li>
                    <li><a href="page.php?slug=about-us" class="hover:text-brand-primary hover:underline">About Us</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-xl text-white-800 mb-4">Information</h4>
                <ul class="space-y-3 text-white-600">
                    <li><a href="page.php?slug=terms-conditions" class="hover:text-brand-primary hover:underline">Terms & Conditions</a></li>
                    <li><a href="page.php?slug=privacy-policy" class="hover:text-brand-primary hover:underline">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-brand-primary hover:underline">Fast Delivery</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-xl text-white-800 mb-4">Contact</h4>
                <ul class="space-y-3 text-white-600">
                    <li><a href="#" class="hover:text-brand-primary hover:underline">+123 456 789</a></li>
                    <li><a href="#" class="hover:text-brand-primary hover:underline">info@quickkart.com</a></li>
                    <li><a href="#" class="hover:text-brand-primary hover:underline">123, New York, USA</a></li>
                </ul>
            </div>
        </div>
    </footer>


    <?php if (!isset($is_homepage) || !$is_homepage): ?>
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-t-2xl z-40 lg:hidden">
        <div class="flex justify-around max-w-lg mx-auto">
            <a href="<?php echo SITE_URL; ?>index.php" class="flex-1 text-center py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'text-brand-primary' : 'text-gray-600'; ?> transition-colors">
                <i class="fas fa-home text-xl"></i><span class="block text-xs font-semibold">Home</span>
            </a>
            <a href="<?php echo SITE_URL; ?>cart.php" class="flex-1 text-center py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'text-brand-primary' : 'text-gray-600'; ?> hover:text-brand-primary transition-colors relative">
                <i class="fas fa-shopping-cart text-xl"></i><span class="block text-xs font-semibold">Cart</span>
                 <?php $cart_count_bottom = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; if ($cart_count_bottom > 0) { echo "<span class='absolute top-1 right-[calc(50%-22px)] bg-brand-primary text-white text-xs rounded-full h-4 w-4 flex items-center justify-center'>$cart_count_bottom</span>"; } ?>
            </a>
            <a href="<?php echo SITE_URL; ?>profile.php" class="flex-1 text-center py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'text-brand-primary' : 'text-gray-600'; ?> hover:text-brand-primary transition-colors">
                <i class="fas fa-user text-xl"></i><span class="block text-xs font-semibold">Profile</span>
            </a>
        </div>
    </nav>
    <div class="pb-20 lg:pb-0"></div>
    <script> document.getElementById('main-container').classList.add('pb-20'); </script>
    <?php endif; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Current page detection
        const currentPage = '<?php echo basename($_SERVER['PHP_SELF']); ?>';

        // --- Header Scroll Effect ---
        const header = document.getElementById('main-header');
        if (header) { 
            window.addEventListener('scroll', () => { 
                window.scrollY > 20 ? header.classList.add('bg-white/80', 'backdrop-blur-lg', 'shadow-md') : header.classList.remove('bg-white/80', 'backdrop-blur-lg', 'shadow-md'); 
            }); 
        }
        
        // --- Dynamic Menu Filtering (Common to index.php and product.php if they both have it) ---
        const productGrid = document.getElementById('product-grid');
        const categoryFilters = document.querySelectorAll('.category-filter');
        if (productGrid && categoryFilters.length > 0) {
            categoryFilters.forEach(filter => {
                filter.addEventListener('click', async function(e) {
                    e.preventDefault();
                    categoryFilters.forEach(f => { f.classList.remove('bg-brand-primary', 'text-white', 'border-brand-primary'); f.classList.add('bg-white', 'text-gray-700', 'border-gray-200'); });
                    this.classList.add('bg-brand-primary', 'text-white', 'border-brand-primary'); this.classList.remove('bg-white', 'text-gray-700', 'border-gray-200');
                    const catId = this.dataset.catid;
                    productGrid.style.opacity = '0';
                    try {
                        // Dynamically fetch products based on category, adjusting action as needed for current page
                        const response = await fetch(`${currentPage}?action=get_products&cat_id=${catId}`); // Use currentPage to ensure correct endpoint
                        const result = await response.json();
                        setTimeout(() => { productGrid.innerHTML = result.html; productGrid.style.opacity = '1'; }, 300);
                    } catch (error) { 
                        productGrid.innerHTML = '<p class="col-span-full text-center">Failed to load products.</p>'; 
                        productGrid.style.opacity = '1'; 
                    }
                });
            });
        }
        
        // --- Offer Slider ---
        const sliderTrack = document.getElementById('offer-slider-track');
        if(sliderTrack){ 
            const slides = Array.from(sliderTrack.children); 
            if(slides.length > 3) { 
                const slideCount = slides.length / 2; // Assuming slides are duplicated for infinite loop
                let currentIndex = 0; 
                setInterval(() => { 
                    currentIndex = (currentIndex + 1) % slideCount; 
                    sliderTrack.style.transform = `translateX(-${currentIndex * (100 / 3)}%)`; 
                }, 4000); 
            }
        }
        
        // --- Quick View Modal Logic Functions (Defined once, used conditionally) ---
        const quickViewModal = document.getElementById('quick-view-modal'); 
        const modalContent = document.getElementById('modal-content'); 
        const modalCloseBtn = document.getElementById('modal-close-btn');

        // Helper function for showing/hiding loader (assuming it exists globally)
        function showLoader() { /* Implement your loader logic or remove */ console.log("Showing loader"); }
        function hideLoader() { /* Implement your loader logic or remove */ console.log("Hiding loader"); }
        function showModal(title, message) { /* Implement your generic modal logic or remove */ console.log(`${title}: ${message}`); alert(`${title}\n${message}`);}

        async function openQuickViewModal(productId) {
            showLoader();
            try {
                const response = await fetch(`index.php?action=get_product_detail&id=${productId}`); // Always fetch from index.php for quick view
                const result = await response.json();
                if (result.status === 'success') {
                    const product = result.data;
                    document.getElementById('modal-image').src = product.image_url; 
                    document.getElementById('modal-title').textContent = product.name; 
                    document.getElementById('modal-category').textContent = product.category_name || 'Category'; // Assuming category name might come
                    document.getElementById('modal-price').innerHTML = `<?php echo CURRENCY_SYMBOL; ?>${parseFloat(product.price).toFixed(2)}`; 
                    document.getElementById('modal-stock').textContent = product.stock > 0 ? `${product.stock} In Stock` : 'Out of Stock'; 
                    document.getElementById('modal-description').textContent = product.description; 
                    document.getElementById('modal-product-id').value = product.id; 
                    document.getElementById('modal-quantity').value = 1;

                    quickViewModal.classList.remove('hidden'); 
                    setTimeout(() => { 
                        quickViewModal.classList.remove('opacity-0'); 
                        modalContent.classList.remove('scale-95'); 
                    }, 50);
                } else { 
                    showModal('Error', result.message); 
                }
            } catch (error) { 
                console.error('Error fetching product details for quick view:', error);
                showModal('Error', 'Could not fetch product details.'); 
            } finally { 
                hideLoader(); 
            }
        }

        function closeQuickViewModal() { 
            modalContent.classList.add('scale-95'); 
            quickViewModal.classList.add('opacity-0'); 
            setTimeout(() => quickViewModal.classList.add('hidden'), 300); 
        }

        // --- CONDITIONAL PRODUCT CARD CLICK LOGIC ---
        if (currentPage === 'index.php') {
            // Logic for Homepage Product Cards (Open Quick View Modal)
            document.body.addEventListener('click', function(e) { 
                const card = e.target.closest('.product-card-clickable'); 
                if (card) { 
                    e.preventDefault(); // Prevent any default link behavior on the card
                    openQuickViewModal(card.dataset.productid); 
                } 
            });

            // Quick View Modal specific event listeners
            modalCloseBtn.addEventListener('click', closeQuickViewModal);
            quickViewModal.addEventListener('click', (e) => { 
                if(e.target === quickViewModal) closeQuickViewModal(); 
            });

            modalContent.querySelectorAll('.quantity-btn').forEach(btn => { 
                btn.addEventListener('click', function() { 
                    const change = parseInt(this.dataset.change); 
                    const qtyInput = document.getElementById('modal-quantity'); 
                    let currentVal = parseInt(qtyInput.value); 
                    if (currentVal + change > 0) { 
                        qtyInput.value = currentVal + change; 
                    } 
                }); 
            });

            document.getElementById('modal-cart-form').addEventListener('submit', async function(e) { 
                e.preventDefault(); 
                showLoader(); 
                const formData = new FormData(this); 
                formData.append("action","add_to_cart")
                // ... (previous code in bottom.php, specifically the add to cart AJAX handler)

        try {
            const response = await fetch('index.php?action=add_to_cart',
             {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            // *** STEP 2.1: Replace showModal with custom toast notification ***
            showToastNotification(result.status, result.message);

            if (result.status === 'success') {
                closeQuickViewModal(); // If a quick view modal is open, close it

                // *** STEP 2.2: Remove the page reload ***
                // setTimeout(() => location.reload(), 1500); // <-- REMOVE THIS LINE COMPLETELY

                // *** STEP 2.3: Update cart count dynamically ***
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    // Update the text content with the new cart count from the server response
                    cartCountElement.textContent = result.cart_count;
                    
                    // If cart was empty, it might have been hidden. Show it now.
                    if (result.cart_count > 0) {
                        cartCountElement.classList.remove('hidden');
                    } else {
                        cartCountElement.classList.add('hidden'); // Hide if cart becomes 0
                    }
                }
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            showToastNotification('error', 'An error occurred while adding to cart.'); // Use toast for errors too
        } finally {
            hideLoader();
        }
// ... (rest of the code in bottom.php)
            });
            
            // --- Buy Now Logic (for Quick View Modal) ---
            document.getElementById('modal-buy-now-btn').addEventListener('click', async function(e) {
                e.preventDefault();
                showLoader();
                const formData = new FormData(document.getElementById('modal-cart-form'));
                formData.append("action","buy_now")
                try {
                    const response = await fetch(`index.php?action=buy_now`, { method: 'POST', body: formData });
                    const result = await response.json();
                    
                    if(result.status === 'success') {
                        window.location.href = 'checkout.php?mode=buy_now';
                    } else {
                        showModal('Error', result.message);
                    }
                } catch (error) {
                    console.error('Error with buy now:', error);
                    showModal('Error', 'An error occurred.');
                } finally {
                    hideLoader();
                }
            });

        } else if (currentPage === 'product.php') {
            // Logic for Product Page Cards (Go to Product Details Page)
            document.body.addEventListener('click', function(e) {
                const card = e.target.closest('.product-card-link'); // Use a specific class for product page cards
                if (card) {
                    e.preventDefault(); // Prevent default if card itself is not an <a> tag
                    const productId = card.dataset.productid;
                    if (productId) {
                        window.location.href = `product_details.php?id=${productId}`; // Redirect to product details page
                    } else {
                        console.error("Product ID not found for product page card.");
                    }
                }
            });
            // On product.php, the Quick View Modal's functions will be defined but not actively used by card clicks.
            // Its HTML is still present, but effectively dormant for product.php.
        }

// Example: In common/config.php (or a dedicated JS file included early)
// Make sure this is outside any DOMContentLoaded listener if it needs to be globally accessible immediately.

function showToastNotification(type, message) {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        // Create toast container if it doesn't exist
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        // Tailwind classes for positioning: fixed top-right, z-index high, spacing for multiple toasts
        toastContainer.className = 'fixed top-4 right-4 z-[9999] space-y-2 max-w-xs';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    // Initial hidden state and transition classes
    toast.className = `p-4 rounded-lg shadow-lg text-white font-semibold transition-all duration-500 transform translate-x-full opacity-0 flex items-center gap-2`;
    
    let icon = '';
    if (type === 'success') {
        toast.classList.add('bg-green-500');
        icon = '<i class="fas fa-check-circle"></i>';
    } else if (type === 'error') {
        toast.classList.add('bg-red-500');
        icon = '<i class="fas fa-times-circle"></i>';
    } else { // Default or info
        toast.classList.add('bg-gray-700');
        icon = '<i class="fas fa-info-circle"></i>';
    }

    toast.innerHTML = `${icon} <span>${message}</span>`;
    document.getElementById('toast-container').appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
        toast.classList.add('translate-x-0', 'opacity-100');
    }, 100); // Small delay for CSS transition to work

    // Animate out and remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-full', 'opacity-0');
        // Remove from DOM after transition completes
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3000); // 3 seconds visible
}

// Ensure your showModal is either removed or modified if it's the one showing the browser alert
// If your existing showModal is a custom HTML modal, then it needs to handle its own dismissal
// If it's still alert(), then find and remove it or change it:
/*
function showModal(title, message) {
    // alert(title + '\n' + message); // REMOVE THIS LINE IF IT'S CAUSING THE ISSUE
    // OR if you have a custom modal, ensure it has a proper auto-dismiss or close button functionality.
}
*/


        // --- Booking Modal Logic (Common, assuming it's used elsewhere or on homepage for example) ---
        const bookingModal = document.getElementById('booking-modal'); 
        const bookingModalContent = document.getElementById('booking-modal-content'); 
        const bookingForm = document.getElementById('booking-form');

        window.openBookingModal = function() { 
            if (bookingModal) { 
                bookingModal.classList.remove('hidden'); 
                setTimeout(() => { 
                    bookingModal.classList.remove('opacity-0'); 
                    bookingModalContent.classList.remove('scale-95'); 
                }, 50); 
            } 
        }
        window.closeBookingModal = function() { 
            if (bookingModal) { 
                bookingModalContent.classList.add('scale-95'); 
                bookingModal.classList.add('opacity-0'); 
                setTimeout(() => bookingModal.classList.add('hidden'), 300); 
            } 
        }
        if (bookingModal) { 
            bookingModal.addEventListener('click', (e) => { 
                if (e.target === bookingModal) { closeBookingModal(); } 
            }); 
        }
        if (bookingForm) { 
            bookingForm.addEventListener('submit', async function(e) { 
                e.preventDefault(); 
                showLoader(); 
                const formData = new FormData(this); 
                try { 
                    const response = await fetch(`index.php?action=book_table`, { method: 'POST', body: formData }); 
                    const result = await response.json(); 
                    if(result.status === 'success') { 
                        closeBookingModal(); 
                        setTimeout(() => { showModal('Success!', result.message); }, 350); 
                        this.reset(); 
                    } else { 
                        showModal('Error', result.message); 
                    } 
                } catch (error) { 
                    console.error('Error booking table:', error);
                    showModal('Error', 'An unexpected error occurred.'); 
                } finally { 
                    hideLoader(); 
                } 
            }); 
        }

    });
    </script>
</body>
</html>