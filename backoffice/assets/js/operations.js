(() => {
    'use strict';
    document.addEventListener('click', (event) => {
        document.querySelectorAll('.profile-menu[open]').forEach((menu) => {
            if (!menu.contains(event.target)) menu.open = false;
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.profile-menu[open]').forEach((menu) => {
                menu.open = false;
                menu.querySelector('summary').focus();
            });
        }
    });
})();
