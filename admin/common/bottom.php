                </div> <!-- End container -->
            </main>
        </div> <!-- End main-content -->
    </div> <!-- End relative min-h-screen flex -->

<!-- =================================================================== -->
<!-- === THE FINAL AND UNIVERSAL SCRIPT FOR ALL ADMIN PANEL MODALS === -->
<!-- =================================================================== -->
<!-- --- START: NEW CODE FOR SUCCESS MESSAGE AND LOADER HTML --- -->
<!-- Success Message Pop-up -->
<!-- --- END: NEW CODE FOR SUCCESS MESSAGE AND LOADER HTML --- -->


<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Sidebar Logic (Your existing code, no changes needed here) ---
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const iconArrow = document.getElementById('icon-arrow');
    const iconHamburger = document.getElementById('icon-hamburger');

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth >= 1024) {
                body.classList.toggle('sidebar-mini');
                iconArrow.classList.toggle('hidden');
                iconHamburger.classList.toggle('hidden');
            } else {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }
        });
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }
    }

    // --- UNIVERSAL MODAL (POP-UP) LOGIC ---
    let activeModal = null; // Yeh track karega ki kaun sa modal khula hai

    // Function to open any modal
    function openModal(modal) {
        if (modal) {
            modal.classList.remove('hidden');
            activeModal = modal;
        }
    }

    // Function to close the currently active modal
    // This closeModal function is now available globally for product.php to call
    window.closeModal = function() { // Made global by attaching to window
        if (activeModal) {
            activeModal.classList.add('hidden');
            activeModal = null;
        }
    };

    // Event listener for all buttons that open a modal
    document.body.addEventListener('click', function(event) {
        const openBtn = event.target.closest('[data-modal-target]');
        if (openBtn) {
            const modalId = openBtn.dataset.modalTarget;
            const modal = document.getElementById(modalId);
            openModal(modal);
        }
    });

    // Event listener for all buttons that close a modal
    document.body.addEventListener('click', function(event) {
        const closeBtn = event.target.closest('[data-modal-close]');
        if (closeBtn) {
            closeModal();
        }
    });
    
    // Event listener to close modal by clicking outside
    document.body.addEventListener('click', function(event) {
        if (activeModal && event.target === activeModal) {
            closeModal();
        }
    });
    
    // Event listener to close modal with the 'Escape' key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });
});
</script>
    
</body>
</html>