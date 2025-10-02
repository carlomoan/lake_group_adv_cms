/**
 * Navbar and Dropdown Dynamic Handler
 * Applies database settings to navbar and dropdown elements
 */

(function() {
    'use strict';

    const NavbarDropdownHandler = {
        /**
         * Initialize navbar and dropdown settings
         */
        init(content) {
            if (!content || !content.siteSettings) return;

            this.applyNavbarSettings(content.siteSettings);
            this.applyDropdownSettings(content.siteSettings.navbar?.dropdown);
            this.initializeScrollBehavior(content.siteSettings.navbar);
            this.applyDropdownColumns();
        },

        /**
         * Apply navbar background, colors, and position
         */
        applyNavbarSettings(siteSettings) {
            const root = document.documentElement;
            const navbar = siteSettings.navbar;

            if (!navbar) return;

            // Set navbar background color
            if (navbar.backgroundColor) {
                root.style.setProperty('--navbar-bg-color', navbar.backgroundColor);
            }

            // Set navbar height
            if (navbar.height) {
                root.style.setProperty('--navbar-height', `${navbar.height}px`);
            }

            // Set navbar text colors
            if (navbar.textColor) {
                root.style.setProperty('--navbar-text-color', navbar.textColor);
            }

            if (navbar.hoverColor) {
                root.style.setProperty('--navbar-hover-color', navbar.hoverColor);
            }

            // Handle transparency for non-scrolled state
            if (navbar.transparency) {
                const alpha = (100 - navbar.transparency) / 100;
                const bgColor = navbar.backgroundColor || '#ffffff';

                // Convert hex to rgba
                const r = parseInt(bgColor.substr(1, 2), 16);
                const g = parseInt(bgColor.substr(3, 2), 16);
                const b = parseInt(bgColor.substr(5, 2), 16);

                root.style.setProperty('--navbar-bg-color-transparent', `rgba(${r}, ${g}, ${b}, ${alpha})`);
            } else {
                // No transparency, use solid color
                root.style.setProperty('--navbar-bg-color-transparent', navbar.backgroundColor || '#ffffff');
            }

            // Apply position class to header
            if (navbar.position) {
                const header = document.querySelector('.l-header');
                if (header) {
                    header.classList.remove('position-fixed', 'position-sticky', 'position-static');
                    header.classList.add(`position-${navbar.position}`);
                }
            }
        },

        /**
         * Apply dropdown menu settings
         */
        applyDropdownSettings(dropdown) {
            if (!dropdown) return;

            const root = document.documentElement;

            // Background color or gradient
            if (dropdown.backgroundType === 'color') {
                root.style.setProperty('--dropdown-bg-color', dropdown.backgroundColor || '#ffffff');
            } else if (dropdown.backgroundType === 'gradient') {
                root.style.setProperty('--dropdown-gradient-start', dropdown.gradientStart || '#ffffff');
                root.style.setProperty('--dropdown-gradient-end', dropdown.gradientEnd || '#f8f9fa');

                // Add gradient class to all dropdowns
                document.querySelectorAll('.dropdown, .sub-menu').forEach(el => {
                    el.classList.add('has-gradient');
                });
            } else if (dropdown.backgroundType === 'image' && dropdown.backgroundImage) {
                root.style.setProperty('--dropdown-bg-image', `url('${dropdown.backgroundImage}')`);
                root.style.setProperty('--dropdown-bg-position', dropdown.backgroundPosition || 'center');
                root.style.setProperty('--dropdown-bg-size', 'cover');

                // Add image class to all dropdowns
                document.querySelectorAll('.dropdown, .sub-menu').forEach(el => {
                    el.classList.add('has-bg-image');
                });
            }

            // Text colors
            if (dropdown.textColor) {
                root.style.setProperty('--dropdown-text-color', dropdown.textColor);
            }

            if (dropdown.hoverTextColor) {
                root.style.setProperty('--dropdown-hover-text', dropdown.hoverTextColor);
            }

            // Hover background
            if (dropdown.hoverBackgroundColor) {
                root.style.setProperty('--dropdown-hover-bg', dropdown.hoverBackgroundColor);
            }

            // Border radius
            if (dropdown.borderRadius !== undefined) {
                root.style.setProperty('--dropdown-border-radius', `${dropdown.borderRadius}px`);
            }

            // Shadow intensity
            if (dropdown.shadow) {
                document.querySelectorAll('.dropdown, .sub-menu').forEach(el => {
                    el.classList.remove('shadow-light', 'shadow-medium', 'shadow-heavy', 'shadow-none');
                    el.classList.add(`shadow-${dropdown.shadow}`);
                });
            }

            // Animation
            if (dropdown.animation) {
                document.querySelectorAll('.dropdown, .sub-menu').forEach(el => {
                    el.classList.add(`animation-${dropdown.animation}`);
                });
            }

            // Typography
            if (dropdown.fontSize) {
                root.style.setProperty('--dropdown-font-size', `${dropdown.fontSize}px`);
            }

            if (dropdown.lineHeight) {
                root.style.setProperty('--dropdown-line-height', dropdown.lineHeight);
            }

            if (dropdown.itemPadding) {
                root.style.setProperty('--dropdown-item-padding', `${dropdown.itemPadding}px`);
            }

            // Width
            if (dropdown.width) {
                document.querySelectorAll('.dropdown, .sub-menu').forEach(el => {
                    el.style.minWidth = `${dropdown.width}px`;
                });
            }

            // Border style
            if (dropdown.borderStyle && dropdown.borderStyle !== 'none') {
                root.style.setProperty('--dropdown-border', `1px ${dropdown.borderStyle} rgba(0, 0, 0, 0.1)`);
            }

            // Arrow style
            if (dropdown.arrowStyle) {
                document.querySelectorAll('.has-dropdown').forEach(el => {
                    el.classList.remove('arrow-chevron', 'arrow-plus', 'arrow-caret', 'arrow-none');
                    el.classList.add(`arrow-${dropdown.arrowStyle}`);
                });
            }

            // Multi-level support
            if (dropdown.enableMultiLevel) {
                document.body.classList.add('multi-level-dropdown-enabled');
            }
        },

        /**
         * Apply column layouts to dropdowns based on number of items
         */
        applyDropdownColumns() {
            document.querySelectorAll('.dropdown, .sub-menu').forEach(dropdown => {
                const itemCount = dropdown.querySelectorAll('li').length;

                // Auto-detect column count based on items
                // Or use data attribute if set: data-columns="2"
                const columns = dropdown.dataset.columns || this.calculateColumns(itemCount);

                dropdown.classList.add(`columns-${columns}`);
            });
        },

        /**
         * Calculate optimal column count based on item count
         */
        calculateColumns(itemCount) {
            if (itemCount <= 5) return 1;
            if (itemCount <= 10) return 2;
            return 3;
        },

        /**
         * Initialize scroll behavior for sticky navbar
         */
        initializeScrollBehavior(navbarSettings) {
            let lastScroll = 0;
            const header = document.querySelector('.l-header');

            if (!header) return;

            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;

                // Add/remove scrolled class
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                // Hide navbar on scroll down (optional feature)
                if (navbarSettings?.hideOnScroll) {
                    if (currentScroll > lastScroll && currentScroll > 100) {
                        header.style.transform = 'translateY(-100%)';
                    } else {
                        header.style.transform = 'translateY(0)';
                    }
                }

                lastScroll = currentScroll;
            });
        },

        /**
         * Manually set dropdown columns via data attribute
         * Usage: <ul class="dropdown" data-columns="2">
         */
        setDropdownColumns(dropdownElement, columns) {
            dropdownElement.classList.remove('columns-1', 'columns-2', 'columns-3');
            dropdownElement.classList.add(`columns-${columns}`);
        },

        /**
         * Toggle multi-level dropdown support
         */
        enableMultiLevel(enable = true) {
            if (enable) {
                document.body.classList.add('multi-level-dropdown-enabled');
            } else {
                document.body.classList.remove('multi-level-dropdown-enabled');
            }
        },

        /**
         * Debug: Force show all dropdowns (for testing)
         */
        debugShowDropdowns() {
            document.querySelectorAll('.dropdown, .sub-menu').forEach(el => {
                el.classList.add('force-visible');
            });

            console.log('🔍 Debug: All dropdowns visible');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.dropdown, .sub-menu').forEach(el => {
                    el.classList.remove('force-visible');
                });
                console.log('✅ Debug mode ended');
            }, 5000);
        }
    };

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            // Will be initialized by Vue app
        });
    }

    // Export to global scope for Vue integration
    window.NavbarDropdownHandler = NavbarDropdownHandler;

    console.log('✅ Navbar & Dropdown Handler loaded');
})();
