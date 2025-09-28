// Hotfix for minor issues
console.log('Applying hotfixes...');

// Fix 1: Add image URL validation for main website
window.addEventListener('load', function() {
    setTimeout(function() {
        // Find all images and fix invalid src attributes
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (img.src && (img.src.includes('[object Object]') || img.src === '[object Object]')) {
                console.warn('Fixed invalid image src:', img.src);
                img.src = 'https://via.placeholder.com/400x300/FFD200/333?text=IMAGE';
            }
        });

        // Add mutation observer to catch dynamically added images
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const imgs = node.tagName === 'IMG' ? [node] : node.querySelectorAll ? node.querySelectorAll('img') : [];
                        imgs.forEach(img => {
                            if (img.src && (img.src.includes('[object Object]') || img.src === '[object Object]')) {
                                console.warn('Fixed dynamically added invalid image src:', img.src);
                                img.src = 'https://via.placeholder.com/400x300/FFD200/333?text=IMAGE';
                            }
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

    }, 2000);
});

console.log('Hotfixes applied');