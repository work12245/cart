document.addEventListener('DOMContentLoaded', function() {
    // Disable text selection
    document.body.classList.add('select-none');

    // Disable right-click context menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // Disable zoom
    document.addEventListener('keydown', function(event) {
        if ((event.ctrlKey === true || event.metaKey === true) && (event.which === 61 || event.which === 107 || event.which === 173 || event.which === 109 || event.which === 187 || event.which === 189)) {
            event.preventDefault();
        }
    });
    document.addEventListener('wheel', function(event) {
        if (event.ctrlKey === true) {
            event.preventDefault();
        }
    }, { passive: false });

});

// Simple reusable modal
function showModal(title, message) {
    const modalHTML = `
        <div id="reusableModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-sm text-center">
                <h3 class="text-lg font-bold mb-2">${title}</h3>
                <p class="text-gray-600">${message}</p>
                <button onclick="closeModal()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Close</button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function closeModal() {
    const modal = document.getElementById('reusableModal');
    if (modal) {
        modal.remove();
    }
}

// Simple loading indicator
function showLoader() {
    const loaderHTML = `
        <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
            <div class="bg-white p-5 rounded-full flex items-center justify-center shadow-lg">
                <div class="w-4 h-4 rounded-full bg-red-600 animate-bounce" style="animation-delay: -0.3s;"></div>
                <div class="w-4 h-4 rounded-full bg-red-600 animate-bounce mx-2" style="animation-delay: -0.15s;"></div>
                <div class="w-4 h-4 rounded-full bg-red-600 animate-bounce"></div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', loaderHTML);
}

function hideLoader() {
    const loader = document.getElementById('loadingModal');
    if (loader) {
        loader.remove();
    }
}