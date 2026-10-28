(() => {
    'use strict';
    document.addEventListener('click', (event) => {
        const seat = event.target.closest('[data-seat]');
        if (seat) {
            const selected = seat.getAttribute('aria-pressed') !== 'true';
            document
                .querySelectorAll('[data-seat]')
                .forEach((item) => item.setAttribute('aria-pressed', 'false'));
            seat.setAttribute('aria-pressed', String(selected));
            const output = document.querySelector('[data-seat-selection]');
            if (output)
                output.textContent = selected
                    ? `Lugar ${seat.dataset.seat} selecionado`
                    : 'Nenhum lugar selecionado';
        }
        if (event.target.closest('[data-seat-select-tool]')) {
            const firstSeat = document.querySelector('[data-seat]');
            if (firstSeat) firstSeat.focus();
        }
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
