// Database Integration for Admin Dashboard
// Include this file to enable database saving functionality

// Store the methods to be injected into Vue app
window.DATABASE_METHODS = {
    // Enhanced saveContent method
    async saveContent() {
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
    },

    // Enhanced loadContent method
    async loadContent() {
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
    }
};

console.log('Database methods loaded and ready for injection');