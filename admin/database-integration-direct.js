// Direct Database Integration - Override after Vue is fully loaded
console.log('Direct database integration loading...');

// Function to override Vue saveContent method
function overrideSaveContent(vueInstance) {
    console.log('Overriding saveContent method for:', vueInstance);

    // Store original method as backup
    const originalSaveContent = vueInstance.saveContent;

    // Override with database save
    vueInstance.saveContent = async function() {
        console.log('Database saveContent called with content:', this.content);

        try {
            this.showNotification('Saving content to database...', 'info');

            const response = await fetch('save_content.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content: this.content
                })
            });

            console.log('Save response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('Save response data:', result);

            if (result.success) {
                this.showNotification('Content saved successfully to database! Changes are live.', 'success');

                // Update backup date
                this.lastBackupDate = new Date().toISOString();

                // Also save to localStorage as backup
                localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));

                console.log('✅ Database save successful');
            } else {
                throw new Error(result.error || 'Save failed');
            }

        } catch (error) {
            console.error('❌ Database save failed:', error);
            this.showNotification('Database save failed: ' + error.message + '. Using fallback.', 'error');

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
                localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                this.showNotification('Content saved to local storage as backup', 'warning');
            }
        }
    };

    console.log('✅ saveContent method successfully overridden');
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