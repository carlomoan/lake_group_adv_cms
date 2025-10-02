/**
 * Emergency Fix: Inject DATABASE_METHODS
 * Include this file in admin/index.html if DATABASE_METHODS is not loading
 */

(function() {
    'use strict';

    console.log('🔧 Emergency DATABASE_METHODS injection starting...');

    // Check if already loaded
    if (window.DATABASE_METHODS) {
        console.log('✅ DATABASE_METHODS already exists, skipping injection');
        return;
    }

    // Inject DATABASE_METHODS
    window.DATABASE_METHODS = {
        // Deep merge helper
        deepMerge(target, source) {
            const output = { ...target };
            if (this.isObject(target) && this.isObject(source)) {
                Object.keys(source).forEach(key => {
                    if (this.isObject(source[key])) {
                        if (!(key in target)) {
                            Object.assign(output, { [key]: source[key] });
                        } else {
                            output[key] = this.deepMerge(target[key], source[key]);
                        }
                    } else {
                        Object.assign(output, { [key]: source[key] });
                    }
                });
            }
            return output;
        },

        isObject(item) {
            return item && typeof item === 'object' && !Array.isArray(item);
        },

        // Save content to database
        async saveContent() {
            try {
                this.showNotification('Saving content to database...', 'info');

                const response = await fetch('/admin/save_content.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content: this.content
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();

                if (result.success) {
                    let message = 'Content saved successfully to database!';
                    if (result.website_regenerated) {
                        message += ' Your website has been updated automatically.';
                    }
                    this.showNotification(message, 'success');
                    this.lastBackupDate = new Date().toISOString();

                    // Also save to localStorage as backup
                    localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                } else {
                    console.error('Save failed - Server response:', result);
                    const errorMsg = result.error || 'Failed to save content';
                    throw new Error(errorMsg);
                }

                console.log('✅ Content saved to database:', this.content);
            } catch (error) {
                this.showNotification('Failed to save content: ' + error.message, 'error');
                console.error('❌ Save error:', error);

                // Fallback to localStorage if database save fails
                try {
                    localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                    this.showNotification('Content saved to local storage as backup', 'warning');
                } catch (localError) {
                    console.error('Local storage error:', localError);
                }
            }
        },

        // Load content from database
        async loadContent() {
            try {
                // Try to load from database first
                const response = await fetch('/admin/save_content.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.content) {
                        console.log('✅ Loaded content from database:', result.content);
                        // Deep merge database content with existing structure
                        this.content = this.deepMerge(this.content, result.content);
                        this.showNotification('Content loaded from database!', 'success');
                        return;
                    }
                }

                // Fallback to localStorage if database load fails
                const savedContent = localStorage.getItem('petroleumGasContent');
                if (savedContent) {
                    const parsedContent = JSON.parse(savedContent);
                    this.content = this.deepMerge(this.content, parsedContent);
                    this.showNotification('Content loaded from local storage', 'info');
                } else {
                    this.showNotification('Using default content structure', 'info');
                }
            } catch (error) {
                this.showNotification('Failed to load content: ' + error.message, 'error');
                console.error('❌ Load error:', error);

                // Try localStorage as final fallback
                try {
                    const savedContent = localStorage.getItem('petroleumGasContent');
                    if (savedContent) {
                        const parsedContent = JSON.parse(savedContent);
                        this.content = this.deepMerge(this.content, parsedContent);
                        this.showNotification('Loaded backup from local storage', 'warning');
                    }
                } catch (localError) {
                    console.error('Local storage error:', localError);
                }
            }
        }
    };

    console.log('✅ DATABASE_METHODS injected successfully!');
    console.log('Available methods:', Object.keys(window.DATABASE_METHODS));

})();
