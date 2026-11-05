import './qic/workspace.js';
function initQicSidebar() {
    const sidebar = document.getElementById('qic-sidebar');
    const menuButton = document.querySelector('[data-menu]');

    if (!sidebar || !menuButton) return;

    // Hindari pemasangan event berulang.
    if (menuButton.dataset.sidebarReady === 'true') return;
    menuButton.dataset.sidebarReady = 'true';

    const mobileScreen = window.matchMedia('(max-width: 760px)');

    function setOpen(open) {
        sidebar.classList.toggle('is-open', open);
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.textContent = open ? 'Tutup menu' : 'Menu';
    }

    // Mulai dengan sidebar mobile tertutup.
    setOpen(false);

    menuButton.addEventListener('click', () => {
        setOpen(!sidebar.classList.contains('is-open'));
    });

    // Tutup dengan tombol Escape dan kembalikan fokus.
    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape' &&
            mobileScreen.matches &&
            sidebar.classList.contains('is-open')
        ) {
            setOpen(false);
            menuButton.focus();
        }
    });

    // Reset status saat ukuran layar melewati breakpoint.
    mobileScreen.addEventListener('change', () => {
        setOpen(false);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQicSidebar);
} else {
    initQicSidebar();
}