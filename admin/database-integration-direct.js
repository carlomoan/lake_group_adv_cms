// Direct Database Integration - Override after Vue is fully loaded
console.log('Direct database integration loading...');

// Function to override Vue saveContent method
function overrideSaveContent(vueInstance) {
    console.log('Overriding saveContent method for:', vueInstance);

    // Store original method as backup
    const originalSaveContent = vueInstance.saveContent;

    // Override with database save
    vueInstance.saveContent = async function() {
        console.log('Database saveContent called');
        console.log('this context:', this);
        console.log('Content available:', !!this.content);
        console.log('showNotification available:', typeof this.showNotification);

        // Get content from multiple possible sources
        let contentToSave = this.content || this.$data?.content || vueInstance.content || {};
        console.log('Content to save:', contentToSave);

        try {
            // Try to show notification if available
            if (typeof this.showNotification === 'function') {
                this.showNotification('Saving content to database...', 'info');
            } else if (typeof vueInstance.showNotification === 'function') {
                vueInstance.showNotification('Saving content to database...', 'info');
            } else {
                console.log('Saving content to database...');
            }

            const response = await fetch('save_content.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content: contentToSave
                })
            });

            console.log('Save response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('Save response data:', result);

            if (result.success) {
                // Try to show success notification
                if (typeof this.showNotification === 'function') {
                    this.showNotification('Content saved successfully to database! Changes are live.', 'success');
                } else if (typeof vueInstance.showNotification === 'function') {
                    vueInstance.showNotification('Content saved successfully to database! Changes are live.', 'success');
                } else {
                    console.log('✅ Content saved successfully to database! Changes are live.');
                    // Fallback: show browser notification
                    if (typeof alert === 'function') {
                        alert('Content saved successfully to database!');
                    }
                }

                // Update backup date if possible
                if (this.lastBackupDate !== undefined) {
                    this.lastBackupDate = new Date().toISOString();
                } else if (vueInstance.lastBackupDate !== undefined) {
                    vueInstance.lastBackupDate = new Date().toISOString();
                }

                // Also save to localStorage as backup
                localStorage.setItem('petroleumGasContent', JSON.stringify(contentToSave));

                console.log('✅ Database save successful');
            } else {
                throw new Error(result.error || 'Save failed');
            }

        } catch (error) {
            console.error('❌ Database save failed:', error);

            // Try to show error notification
            if (typeof this.showNotification === 'function') {
                this.showNotification('Database save failed: ' + error.message + '. Using fallback.', 'error');
            } else if (typeof vueInstance.showNotification === 'function') {
                vueInstance.showNotification('Database save failed: ' + error.message + '. Using fallback.', 'error');
            } else {
                console.log('❌ Database save failed, using fallback');
            }

            // Fallback to original method if it exists
            if (originalSaveContent && typeof originalSaveContent === 'function') {
                console.log('Trying original save method...');
                try {
                    await originalSaveContent.call(this);
                } catch (fallbackError) {
                    console.error('Original save also failed:', fallbackError);
                }
            } else {
                // Fallback to localStorage
                console.log('Using localStorage fallback...');
                localStorage.setItem('petroleumGasContent', JSON.stringify(contentToSave));

                if (typeof this.showNotification === 'function') {
                    this.showNotification('Content saved to local storage as backup', 'warning');
                } else if (typeof vueInstance.showNotification === 'function') {
                    vueInstance.showNotification('Content saved to local storage as backup', 'warning');
                } else {
                    console.log('Content saved to local storage as backup');
                }
            }
        }
    };

    // Also override loadContent method for database loading
    const originalLoadContent = vueInstance.loadContent;

    vueInstance.loadContent = async function() {
        console.log('Database loadContent called');

        try {
            console.log('Loading content from database...');

            const response = await fetch('save_content.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            console.log('Load response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('Load response data:', result);

            if (result.success && result.content) {
                // Merge database content with existing content
                const loadedContent = result.content;
                console.log('Merging loaded content:', loadedContent);

                // Try to update content in multiple ways
                if (this.content && typeof this.content === 'object') {
                    this.content = { ...this.content, ...loadedContent };
                } else if (vueInstance.content && typeof vueInstance.content === 'object') {
                    vueInstance.content = { ...vueInstance.content, ...loadedContent };
                }

                // Show success notification
                if (typeof this.showNotification === 'function') {
                    this.showNotification('Content loaded from database successfully!', 'success');
                } else if (typeof vueInstance.showNotification === 'function') {
                    vueInstance.showNotification('Content loaded from database successfully!', 'success');
                } else {
                    console.log('✅ Content loaded from database successfully!');
                }

                console.log('✅ Database load successful');
                return loadedContent;

            } else {
                console.log('No content in database response, trying fallback...');
                throw new Error('No content returned from database');
            }

        } catch (error) {
            console.error('❌ Database load failed:', error);

            // Try to show error notification
            if (typeof this.showNotification === 'function') {
                this.showNotification('Database load failed: ' + error.message + '. Using fallback.', 'warning');
            } else if (typeof vueInstance.showNotification === 'function') {
                vueInstance.showNotification('Database load failed: ' + error.message + '. Using fallback.', 'warning');
            } else {
                console.log('❌ Database load failed, using fallback');
            }

            // Fallback to original method if it exists
            if (originalLoadContent && typeof originalLoadContent === 'function') {
                console.log('Trying original load method...');
                try {
                    return await originalLoadContent.call(this);
                } catch (fallbackError) {
                    console.error('Original load also failed:', fallbackError);
                }
            } else {
                // Fallback to localStorage
                console.log('Using localStorage fallback...');
                try {
                    const savedContent = localStorage.getItem('petroleumGasContent');
                    if (savedContent) {
                        const parsedContent = JSON.parse(savedContent);

                        // Try to update content
                        if (this.content && typeof this.content === 'object') {
                            this.content = { ...this.content, ...parsedContent };
                        } else if (vueInstance.content && typeof vueInstance.content === 'object') {
                            vueInstance.content = { ...vueInstance.content, ...parsedContent };
                        }

                        if (typeof this.showNotification === 'function') {
                            this.showNotification('Content loaded from local storage', 'info');
                        } else if (typeof vueInstance.showNotification === 'function') {
                            vueInstance.showNotification('Content loaded from local storage', 'info');
                        } else {
                            console.log('Content loaded from local storage');
                        }

                        return parsedContent;
                    }
                } catch (localError) {
                    console.error('Local storage load also failed:', localError);
                }
            }
        }
    };

    console.log('✅ saveContent and loadContent methods successfully overridden');
    return true;
}

// Multiple strategies to find and override the Vue instance
function findAndOverrideVue() {
    console.log('Searching for Vue instance...');

    // Strategy 1: Direct window object search
    for (let prop in window) {
        if (window[prop] &&
            typeof window[prop] === 'object' &&
            window[prop].saveContent &&
            typeof window[prop].saveContent === 'function' &&
            window[prop].showNotification &&
            typeof window[prop].showNotification === 'function') {

            console.log('Found Vue-like instance in window.' + prop);
            return overrideSaveContent(window[prop]);
        }
    }

    // Strategy 2: Check app element
    const appElement = document.getElementById('app');
    if (appElement) {
        // Check various Vue 3 mounting patterns
        const vuePatterns = [
            () => appElement.__vue_app__?._instance,
            () => appElement.__vue_app__?._instance?.proxy,
            () => appElement.__vue__,
            () => appElement._vnode?.component?.proxy,
            () => appElement._vnode?.component
        ];

        for (let getVue of vuePatterns) {
            try {
                const vue = getVue();
                if (vue && vue.saveContent && typeof vue.saveContent === 'function') {
                    console.log('Found Vue instance via app element');
                    return overrideSaveContent(vue);
                }
            } catch (e) {
                // Continue trying other patterns
            }
        }
    }

    // Strategy 3: DOM traversal for Vue components
    const elements = document.querySelectorAll('*');
    for (let element of elements) {
        if (element.__vue__ && element.__vue__.saveContent) {
            console.log('Found Vue instance via DOM traversal');
            return overrideSaveContent(element.__vue__);
        }
    }

    console.log('❌ Vue instance not found');
    return false;
}

// Try multiple times to find Vue instance
let attempts = 0;
const maxAttempts = 15;

function attemptOverride() {
    attempts++;
    console.log(`Attempt ${attempts}/${maxAttempts} to find Vue instance`);

    if (findAndOverrideVue()) {
        console.log('🎉 Database integration setup complete!');

        // Automatically load content from database
        setTimeout(async () => {
            console.log('Auto-loading content from database...');

            // Find the Vue instance again to call loadContent
            const appElement = document.getElementById('app');
            let vueToLoad = null;

            // Try multiple ways to find the Vue instance
            if (appElement && appElement.__vue_app__ && appElement.__vue_app__._instance) {
                vueToLoad = appElement.__vue_app__._instance;
            } else {
                // Search window properties
                for (let prop in window) {
                    if (window[prop] &&
                        typeof window[prop] === 'object' &&
                        window[prop].loadContent &&
                        typeof window[prop].loadContent === 'function') {
                        vueToLoad = window[prop];
                        break;
                    }
                }
            }

            if (vueToLoad && typeof vueToLoad.loadContent === 'function') {
                try {
                    await vueToLoad.loadContent();
                    console.log('✅ Auto-load completed successfully');
                } catch (error) {
                    console.error('❌ Auto-load failed:', error);
                }
            } else {
                console.log('Could not find Vue instance for auto-load');
            }
        }, 2000); // Wait 2 seconds for Vue to fully initialize

        return;
    }

    if (attempts < maxAttempts) {
        setTimeout(attemptOverride, 1000);
    } else {
        console.error('❌ Failed to find Vue instance after all attempts');
        console.log('Setting up manual override button...');

        // Create a manual override button as last resort
        const button = document.createElement('button');
        button.textContent = 'Enable Database Save';
        button.style.position = 'fixed';
        button.style.top = '10px';
        button.style.right = '10px';
        button.style.zIndex = '9999';
        button.style.background = '#28a745';
        button.style.color = 'white';
        button.style.border = 'none';
        button.style.padding = '10px 15px';
        button.style.borderRadius = '5px';
        button.style.cursor = 'pointer';

        button.onclick = function() {
            if (findAndOverrideVue()) {
                button.textContent = '✅ Database Save Active';
                button.style.background = '#6c757d';
                button.disabled = true;
            } else {
                alert('Still cannot find Vue instance. Check console for details.');
            }
        };

        document.body.appendChild(button);
    }
}

// Start the process when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(attemptOverride, 1000);
    });
} else {
    setTimeout(attemptOverride, 1000);
}

console.log('Direct database integration script loaded');