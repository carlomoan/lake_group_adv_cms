// Fixed Database Integration for Admin Dashboard
console.log('Database integration script loading...');

// Wait for page to be fully loaded
window.addEventListener('load', function() {
    console.log('Page loaded, attempting to override Vue methods...');

    // Multiple strategies to find and override the Vue app
    let overrideAttempts = 0;
    const maxAttempts = 20;

    function attemptOverride() {
        overrideAttempts++;
        console.log('Override attempt #' + overrideAttempts);

        // Strategy 1: Look for Vue app instance
        const appElement = document.getElementById('app');
        if (appElement && appElement.__vue_app__ && appElement.__vue_app__._instance) {
            console.log('Found Vue app via __vue_app__ property');
            overrideVueMethods(appElement.__vue_app__._instance);
            return true;
        }

        // Strategy 2: Look for Vue instance directly
        if (appElement && appElement.__vue__) {
            console.log('Found Vue app via __vue__ property');
            overrideVueMethods(appElement.__vue__);
            return true;
        }

        // Strategy 3: Look for global Vue instance
        if (window.Vue && window.Vue._instance) {
            console.log('Found Vue app via global Vue._instance');
            overrideVueMethods(window.Vue._instance);
            return true;
        }

        // Strategy 4: Look for any mounted Vue components
        const vueComponents = document.querySelectorAll('[data-v-]');
        if (vueComponents.length > 0) {
            for (let component of vueComponents) {
                if (component.__vue__) {
                    console.log('Found Vue component, trying to override...');
                    overrideVueMethods(component.__vue__);
                    return true;
                }
            }
        }

        console.log('Vue app not found yet, retrying in 1 second...');
        if (overrideAttempts < maxAttempts) {
            setTimeout(attemptOverride, 1000);
        } else {
            console.error('Failed to find Vue app after ' + maxAttempts + ' attempts');
            // Fallback: override window-level save functions
            setupWindowFallback();
        }
        return false;
    }

    function overrideVueMethods(vueInstance) {
        console.log('Overriding Vue methods for instance:', vueInstance);

        // Override saveContent method
        vueInstance.saveContent = async function() {
            console.log('Database saveContent called');
            try {
                // Show notification if method exists
                if (this.showNotification) {
                    this.showNotification('Saving content to database...', 'info');
                }

                const response = await fetch('save_content.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content: this.content || {}
                    })
                });

                const result = await response.json();
                console.log('Save response:', result);

                if (result.success) {
                    if (this.showNotification) {
                        this.showNotification('Content saved successfully to database!', 'success');
                    }
                    console.log('Content saved successfully');

                    // Also save to localStorage as backup
                    try {
                        localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                    } catch (e) {
                        console.warn('Could not save to localStorage:', e);
                    }
                } else {
                    throw new Error(result.error || 'Failed to save content');
                }

            } catch (error) {
                console.error('Save error:', error);
                if (this.showNotification) {
                    this.showNotification('Failed to save content: ' + error.message, 'error');
                }

                // Fallback to localStorage
                try {
                    localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                    if (this.showNotification) {
                        this.showNotification('Content saved to local storage as backup', 'warning');
                    }
                } catch (localError) {
                    console.error('Local storage error:', localError);
                }
            }
        };

        console.log('Vue methods overridden successfully');
    }

    function setupWindowFallback() {
        console.log('Setting up window-level fallback save function');

        // Create a global save function as fallback
        window.saveContentToDatabase = async function(content) {
            console.log('Window fallback save called');
            try {
                const response = await fetch('save_content.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content: content || {}
                    })
                });

                const result = await response.json();
                console.log('Fallback save response:', result);

                if (result.success) {
                    alert('Content saved successfully to database!');
                } else {
                    alert('Failed to save: ' + (result.error || 'Unknown error'));
                }

            } catch (error) {
                console.error('Fallback save error:', error);
                alert('Save error: ' + error.message);
            }
        };
    }

    // Start the override process
    setTimeout(attemptOverride, 500);
});

console.log('Database integration script loaded');