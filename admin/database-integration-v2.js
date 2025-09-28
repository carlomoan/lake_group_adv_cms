// Enhanced Database Integration for Admin Dashboard
console.log('Enhanced Database integration script loading...');

// Wait for Vue to be fully mounted
function waitForVue() {
    return new Promise((resolve, reject) => {
        let attempts = 0;
        const maxAttempts = 30;

        function checkVue() {
            attempts++;
            console.log('Vue detection attempt #' + attempts);

            // Multiple strategies to find the Vue app
            let vueInstance = null;

            // Strategy 1: Check global Vue apps
            if (window.Vue && window.Vue._instance) {
                console.log('Found Vue via window.Vue._instance');
                vueInstance = window.Vue._instance;
            }

            // Strategy 2: Check app element
            const appElement = document.getElementById('app');
            if (appElement) {
                console.log('App element found, checking for Vue properties...');

                if (appElement.__vue_app__ && appElement.__vue_app__._instance) {
                    console.log('Found Vue via appElement.__vue_app__._instance');
                    vueInstance = appElement.__vue_app__._instance;
                } else if (appElement.__vue__) {
                    console.log('Found Vue via appElement.__vue__');
                    vueInstance = appElement.__vue__;
                } else if (appElement._vnode && appElement._vnode.component) {
                    console.log('Found Vue via appElement._vnode.component');
                    vueInstance = appElement._vnode.component;
                }
            }

            // Strategy 3: Look for Vue components anywhere in DOM
            if (!vueInstance) {
                const allElements = document.querySelectorAll('*');
                for (let el of allElements) {
                    if (el.__vue__ && el.__vue__.saveContent) {
                        console.log('Found Vue instance with saveContent method');
                        vueInstance = el.__vue__;
                        break;
                    }
                    if (el.__vueParentComponent) {
                        console.log('Found Vue parent component');
                        vueInstance = el.__vueParentComponent;
                        break;
                    }
                }
            }

            // Strategy 4: Look for createApp result in window
            if (!vueInstance) {
                for (let prop in window) {
                    if (window[prop] && window[prop].saveContent && typeof window[prop].saveContent === 'function') {
                        console.log('Found Vue-like object in window.' + prop);
                        vueInstance = window[prop];
                        break;
                    }
                }
            }

            // Strategy 5: Direct approach - look for mounted Vue 3 apps
            if (!vueInstance && window.__VUE_DEVTOOLS_GLOBAL_HOOK__) {
                const hook = window.__VUE_DEVTOOLS_GLOBAL_HOOK__;
                if (hook.apps && hook.apps.length > 0) {
                    console.log('Found Vue app via devtools hook');
                    vueInstance = hook.apps[0]._instance;
                }
            }

            if (vueInstance) {
                console.log('Vue instance found:', vueInstance);
                resolve(vueInstance);
            } else if (attempts >= maxAttempts) {
                console.error('Failed to find Vue instance after ' + maxAttempts + ' attempts');
                reject(new Error('Vue instance not found'));
            } else {
                setTimeout(checkVue, 1000);
            }
        }

        // Start checking after DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => setTimeout(checkVue, 500));
        } else {
            setTimeout(checkVue, 500);
        }
    });
}

// Override Vue methods when found
async function setupDatabaseIntegration() {
    try {
        const vueInstance = await waitForVue();
        console.log('Setting up database integration for Vue instance');

        // Override saveContent method
        const originalSaveContent = vueInstance.saveContent;
        vueInstance.saveContent = async function() {
            console.log('Database saveContent called');
            try {
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

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }

                const result = await response.json();
                console.log('Save response:', result);

                if (result.success) {
                    if (this.showNotification) {
                        this.showNotification('Content saved successfully to database!', 'success');
                    }
                    console.log('Content saved successfully to database');

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
                console.error('Database save error:', error);

                if (this.showNotification) {
                    this.showNotification('Database save failed: ' + error.message + '. Falling back to localStorage.', 'warning');
                }

                // Fallback to original method or localStorage
                try {
                    if (originalSaveContent && typeof originalSaveContent === 'function') {
                        await originalSaveContent.call(this);
                    } else {
                        localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                        if (this.showNotification) {
                            this.showNotification('Content saved to local storage as backup', 'warning');
                        }
                    }
                } catch (fallbackError) {
                    console.error('Fallback save also failed:', fallbackError);
                    if (this.showNotification) {
                        this.showNotification('All save methods failed: ' + fallbackError.message, 'error');
                    }
                }
            }
        };

        console.log('Database integration successfully set up!');

        // Test the integration
        if (vueInstance.showNotification) {
            vueInstance.showNotification('Database integration active! Changes will be saved to database.', 'success');
        }

    } catch (error) {
        console.error('Failed to set up database integration:', error);
        console.log('Setting up fallback window function...');

        // Fallback: create global save function
        window.saveContentToDatabase = async function(content) {
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
                if (result.success) {
                    alert('Content saved successfully to database!');
                } else {
                    alert('Failed to save: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Save error: ' + error.message);
            }
        };
    }
}

// Start the integration
setupDatabaseIntegration();

console.log('Enhanced database integration script loaded');