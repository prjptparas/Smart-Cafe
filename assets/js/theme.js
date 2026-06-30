/**
 * Smart Cafe - Theme Toggle (Dark/Light Mode)
 * 
 * Manages theme switching and persists preference in localStorage.
 * Apply this script early to prevent flash of wrong theme.
 */

(function() {
    'use strict';

    const THEME_KEY = 'smartcafe_theme';

    /**
     * Get stored theme or default to 'light'
     */
    function getStoredTheme() {
        return localStorage.getItem(THEME_KEY) || 'light';
    }

    /**
     * Apply theme to the document
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(THEME_KEY, theme);
        
        // Update toggle button icon if it exists
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }
        }
    }

    /**
     * Toggle between light and dark themes
     */
    function toggleTheme() {
        const currentTheme = getStoredTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
    }

    // Apply theme immediately (before DOM is ready) to prevent flash
    applyTheme(getStoredTheme());

    // Attach click handler once DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleTheme);
            // Set initial icon
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = getStoredTheme() === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }
        }
    });

    // Expose globally for admin pages
    window.SmartCafeTheme = { toggle: toggleTheme, apply: applyTheme, get: getStoredTheme };
})();
