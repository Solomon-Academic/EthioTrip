/**
 * Mobile navigation: hamburger toggle for all .navbar elements
 */
(function () {
    function initNavbar(navbar) {
        if (navbar.dataset.navReady === '1') return;
        navbar.dataset.navReady = '1';
        navbar.classList.add('site-navbar');

        let inner = navbar.querySelector('.navbar-inner');
        const navPanel = navbar.querySelector('.nav, .nav-links');
        const logo = navbar.querySelector('.logo');

        if (!inner) {
            inner = document.createElement('div');
            inner.className = 'navbar-inner';
            if (logo) inner.appendChild(logo);
            const toggle = createToggle();
            inner.appendChild(toggle);
            if (navPanel) {
                navbar.insertBefore(inner, navPanel);
            } else {
                navbar.prepend(inner);
            }
        } else if (!inner.querySelector('.nav-toggle')) {
            inner.appendChild(createToggle());
        }

        const toggle = navbar.querySelector('.nav-toggle');
        if (!toggle) return;

        if (navPanel) {
            navPanel.classList.add('site-nav-panel');
            if (navPanel.id === '') {
                navPanel.id = 'siteNav-' + Math.random().toString(36).slice(2, 8);
            }
            toggle.setAttribute('aria-controls', navPanel.id);
        }

        toggle.setAttribute('aria-expanded', 'false');
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = navbar.classList.toggle('is-menu-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        navPanel?.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navbar.classList.remove('is-menu-open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', function (e) {
            if (!navbar.contains(e.target)) {
                navbar.classList.remove('is-menu-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 992) {
                navbar.classList.remove('is-menu-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function createToggle() {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'nav-toggle';
        btn.setAttribute('aria-label', 'Open menu');
        btn.innerHTML = '<span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span>';
        return btn;
    }

    function initAll() {
        document.querySelectorAll('.navbar').forEach(initNavbar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
