/**
 * Initializes navigation functionality.
 * Called after header is dynamically loaded.
 */
function initializeNavigation() {
    console.log('Initializing navigation...');

    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mainNav = document.getElementById('main-nav');
    const navLinks = document.querySelectorAll('.nav-link');

    // Mobile Menu Toggle
    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent immediate closing
            mainNav.classList.toggle('active');
            this.classList.toggle('active');
            console.log('Mobile menu toggled');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.navbar-container')) {
                if (mainNav.classList.contains('active')) {
                    mainNav.classList.remove('active');
                    mobileMenuToggle.classList.remove('active');
                }
            }
        });

        // Close mobile menu when clicking a link
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (mainNav.classList.contains('active')) {
                    mainNav.classList.remove('active');
                    mobileMenuToggle.classList.remove('active');
                }
            });
        });
    }

    // Active Link Highlighting
    const currentPath = window.location.pathname;
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        // Handle root path
        if (currentPath === '/' || currentPath.endsWith('index.html')) {
            if (linkPath === 'index.html' || linkPath === './' || linkPath === '/') {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        } else if (currentPath.includes(linkPath) && linkPath !== 'index.html') {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Dropdown handling for mobile
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    dropdowns.forEach(dropdown => {
        const link = dropdown.querySelector('a');
        link.addEventListener('click', function (e) {
            if (window.innerWidth <= 1024) {
                // For mobile, first click opens dropdown, second click navigates (or use arrow)
                // For simplicity here, we assume the parent link is just a toggle or proper link.
                // If it has a submenu, we might want to prevent default if it's not a real page.
                // But in our case 'Services' is a real page.
                // Let's allow hover for desktop and simple click for mobile.
            }
        });
    });
}
