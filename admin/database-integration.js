// Database Integration for Admin Dashboard
// Include this file to enable database saving functionality

// Override the default save and load methods
window.addEventListener('DOMContentLoaded', function() {
    // Wait for Vue to mount, then override methods
    setTimeout(function() {
        // Find the Vue app instance in the mounted DOM
        const appElement = document.getElementById('app');
        if (appElement && appElement.__vue_app__) {
            const app = appElement.__vue_app__._instance;

            // Override saveContent method
            app.saveContent = async function() {
                try {
                    this.showNotification('Saving content to database...', 'info');

                    const response = await fetch('./save_content.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            content: this.content
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showNotification('Content saved successfully to database! Changes are live on your website.', 'success');
                        this.lastBackupDate = new Date().toISOString();

                        // Also save to localStorage as backup
                        localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                    } else {
                        console.error('Save failed - Server response:', result);
                        const errorMsg = result.error || 'Failed to save content';

                        // Show detailed error if available
                        if (result.details) {
                            console.error('Error details:', result.details);
                        }
                        if (result.trace) {
                            console.error('Error trace:', result.trace);
                        }

                        throw new Error(errorMsg);
                    }

                    console.log('Content saved to database:', this.content);
                } catch (error) {
                    this.showNotification('Failed to save content: ' + error.message, 'error');
                    console.error('Save error:', error);

                    // Fallback to localStorage if database save fails
                    try {
                        localStorage.setItem('petroleumGasContent', JSON.stringify(this.content));
                        this.showNotification('Content saved to local storage as backup', 'warning');
                    } catch (localError) {
                        console.error('Local storage error:', localError);
                    }
                }
            };

            // Override loadContent method
            app.loadContent = async function() {
                try {
                    // Try to load from database first
                    const response = await fetch('./save_content.php', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success && result.content) {
                            // Merge database content with existing structure
                            this.content = { ...this.content, ...result.content };
                            this.showNotification('Content loaded from database!', 'success');
                            return;
                        }
                    }

                    // Fallback to localStorage if database load fails
                    const savedContent = localStorage.getItem('petroleumGasContent');
                    if (savedContent) {
                        const parsedContent = JSON.parse(savedContent);
                        this.content = { ...this.content, ...parsedContent };
                        this.showNotification('Content loaded from local storage', 'info');
                    } else {
                        this.showNotification('Using default content structure', 'info');
                    }
                } catch (error) {
                    this.showNotification('Failed to load content: ' + error.message, 'error');
                    console.error('Load error:', error);

                    // Try localStorage as final fallback
                    try {
                        const savedContent = localStorage.getItem('petroleumGasContent');
                        if (savedContent) {
                            const parsedContent = JSON.parse(savedContent);
                            this.content = { ...this.content, ...parsedContent };
                            this.showNotification('Loaded backup from local storage', 'warning');
                        }
                    } catch (localError) {
                        console.error('Local storage error:', localError);
                    }
                }
            };

            console.log('Database integration methods overridden successfully');
        }
    }, 1000);
});