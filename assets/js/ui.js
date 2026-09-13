/* =========================================================================
   Hidrossolo — UI kit (JavaScript puro, sem frameworks)
   Substitui os comportamentos que antes vinham do Bootstrap JS:
   dropdown, modal, collapse, abas, alertas dispensáveis e navbar.
   ========================================================================= */
(function () {
    'use strict';

    /* ---------------------------------------------------------------------
     * Dropdown
     * ------------------------------------------------------------------- */
    function closeDropdowns(except) {
        document.querySelectorAll('.dropdown-menu.show').forEach(function (menu) {
            if (menu !== except) {
                menu.classList.remove('show');
            }
        });
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-bs-toggle="dropdown"]');

        if (toggle) {
            event.preventDefault();
            var container = toggle.closest('.dropdown');
            var menu = container ? container.querySelector('.dropdown-menu') : null;
            if (!menu) return;

            var willShow = !menu.classList.contains('show');
            closeDropdowns(menu);
            menu.classList.toggle('show', willShow);
            return;
        }

        if (!event.target.closest('.dropdown-menu')) {
            closeDropdowns(null);
        }
    });

    /* ---------------------------------------------------------------------
     * Modais
     * ------------------------------------------------------------------- */
    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.addEventListener('click', function (event) {
        var opener = event.target.closest('[data-bs-toggle="modal"]');
        if (opener) {
            event.preventDefault();
            openModal(document.querySelector(opener.getAttribute('data-bs-target')));
            return;
        }

        var closer = event.target.closest('[data-bs-dismiss="modal"]');
        if (closer) {
            event.preventDefault();
            closeModal(closer.closest('.modal'));
            return;
        }

        if (event.target.classList.contains('modal')) {
            closeModal(event.target);
        }
    });

    /* ---------------------------------------------------------------------
     * Alertas dispensáveis
     * ------------------------------------------------------------------- */
    document.addEventListener('click', function (event) {
        var closer = event.target.closest('[data-bs-dismiss="alert"]');
        if (!closer) return;

        event.preventDefault();
        var alert = closer.closest('.alert');
        if (!alert) return;

        alert.style.transition = 'opacity .2s ease, transform .2s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-6px)';
        window.setTimeout(function () { alert.remove(); }, 200);
    });

    /* ---------------------------------------------------------------------
     * Collapse (off-canvas / accordion / navbar)
     * ------------------------------------------------------------------- */
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-bs-toggle="collapse"]');
        if (!toggle) return;

        event.preventDefault();
        var selector = toggle.getAttribute('data-bs-target') || toggle.getAttribute('href');
        var target = selector ? document.querySelector(selector) : null;
        if (!target) return;

        var isOpen = target.classList.contains('show');
        target.classList.toggle('show', !isOpen);
        toggle.classList.toggle('collapsed', isOpen);
        toggle.setAttribute('aria-expanded', String(!isOpen));

        // Accordion: fecha os irmãos quando data-bs-parent é informado
        var parentSelector = toggle.getAttribute('data-bs-parent');
        if (parentSelector && !isOpen) {
            document.querySelectorAll(parentSelector + ' .collapse.show').forEach(function (item) {
                if (item !== target) {
                    item.classList.remove('show');
                    var siblingToggle = document.querySelector('[data-bs-target="#' + item.id + '"]');
                    if (siblingToggle) siblingToggle.classList.add('collapsed');
                }
            });
        }
    });

    /* ---------------------------------------------------------------------
     * Abas
     * ------------------------------------------------------------------- */
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-bs-toggle="tab"], [data-bs-toggle="pill"]');
        if (!toggle) return;

        event.preventDefault();
        var selector = toggle.getAttribute('data-bs-target') || toggle.getAttribute('href');
        var pane = selector ? document.querySelector(selector) : null;
        if (!pane) return;

        var container = toggle.closest('.nav, .nav-tabs');
        if (container) {
            container.querySelectorAll('.nav-link').forEach(function (link) {
                link.classList.remove('active');
            });
        }

        var tabContent = pane.parentElement;
        if (tabContent) {
            tabContent.querySelectorAll('.tab-pane').forEach(function (item) {
                item.classList.remove('active', 'show');
            });
        }

        toggle.classList.add('active');
        pane.classList.add('active', 'show');
    });

    /* ---------------------------------------------------------------------
     * Escape fecha camadas abertas
     * ------------------------------------------------------------------- */
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        closeDropdowns(null);
        document.querySelectorAll('.modal.show').forEach(closeModal);
    });

    /* ---------------------------------------------------------------------
     * Navbar: sombra ao rolar
     * ------------------------------------------------------------------- */
    var navbar = document.querySelector('.navbar.fixed-top');
    if (navbar) {
        var onScroll = function () {
            navbar.classList.toggle('navbar-scrolled', window.scrollY > 12);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }
})();
