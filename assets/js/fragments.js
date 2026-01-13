/**
 * Fragment Loader
 * Loads nav and footer fragments into pages
 * Call loadFragments() after DOM loads
 */

async function loadFragments() {
    try {
        // Determine the base path based on current location
        const isServicePage = window.location.pathname.includes('/services/');
        const basePath = isServicePage ? '../' : '';
        
        // Load navigation
        const navResponse = await fetch(`${basePath}assets/fragments/nav.html`);
        if (navResponse.ok) {
            let navContent = await navResponse.text();
            // Adjust paths for service pages
            if (isServicePage) {
                // Fix href="index.html" -> href="../index.html"
                navContent = navContent.replace(/href="index\.html"/g, 'href="../index.html"');
                // Fix src="assets/... -> src="../assets/...
                navContent = navContent.replace(/src="assets\//g, 'src="../assets/');
                // Fix other .html files that aren't in services/ (pricing.html, about.html, etc)
                navContent = navContent.replace(/href="(?!services\/)([^":#/]+\.html)"/g, 'href="../$1"');
                // Fix services/* links -> ../services/*
                navContent = navContent.replace(/href="services\/([^"]+)"/g, 'href="../services/$1"');
            }
            // Find navbar placeholder or insert before main content
            const navbar = document.querySelector('nav.navbar');
            if (navbar) {
                navbar.replaceWith(new DOMParser().parseFromString(navContent, 'text/html').body.firstChild);
                initializeNavigation();
            }
        }
        
        // Load footer
        const footerResponse = await fetch(`${basePath}assets/fragments/footer.html`);
        if (footerResponse.ok) {
            let footerContent = await footerResponse.text();
            // Adjust paths for service pages
            if (isServicePage) {
                // Fix href="services/..." -> href="../services/..."
                footerContent = footerContent.replace(/href="services\/([^"]+)"/g, 'href="../services/$1"');
                // Fix other .html files (about.html, pricing.html, etc) -> ../filename.html
                footerContent = footerContent.replace(/href="([^":#/]+\.html)"/g, 'href="../$1"');
            }
            // Find footer and replace it
            const footer = document.querySelector('footer');
            if (footer) {
                footer.replaceWith(new DOMParser().parseFromString(footerContent, 'text/html').body.firstChild);
            }
        }
    } catch (error) {
        console.error('Error loading fragments:', error);
    }
}

/**
 * Initialize navigation interactions
 */
function initializeNavigation() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    // Dropdown functionality
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const parent = toggle.closest('.dropdown');
            parent.classList.toggle('active');
        });
    });
    
    // Close dropdown when clicking outside (only on desktop)
    document.addEventListener('click', (e) => {
        const navMenu = document.querySelector('.nav-menu');
        // Only close dropdowns if clicking outside and nav menu is NOT active (desktop mode)
        if (!navMenu.classList.contains('active') && !e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown.active').forEach(d => d.classList.remove('active'));
        }
    });
    
    // Mobile hamburger menu
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
}

// Load fragments when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadFragments);
} else {
    loadFragments();
}
