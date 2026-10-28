(() => {
    'use strict';
    const picker = document.querySelector('[data-seat-picker]');
    if (picker) {
        const seats = [...picker.querySelectorAll('[data-seat]')];
        const summary = picker.querySelector('[data-seat-summary]');
        const total = picker.querySelector('[data-seat-total]');
        const continuation = picker.querySelector('[data-seat-continue]');
        const money = (value) =>
            new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(value);
        const update = () => {
            const selected = seats
                .filter((seat) => seat.getAttribute('aria-pressed') === 'true')
                .map((seat) => seat.dataset.seat);
            summary.textContent = selected.length
                ? `Lugares ${selected.join(', ')} · ${selected.length} ${selected.length === 1 ? 'bilhete' : 'bilhetes'} × ${money(Number(picker.dataset.price))}`
                : 'Selecione pelo menos um lugar.';
            total.textContent = money(selected.length * Number(picker.dataset.price));
            const url = new URL(picker.dataset.checkoutUrl, window.location.origin);
            url.searchParams.set('seats', selected.join(','));
            continuation.href = url.href;
            continuation.setAttribute('aria-disabled', String(selected.length === 0));
        };
        seats.forEach((seat) =>
            seat.addEventListener('click', () => {
                const selected = seat.getAttribute('aria-pressed') !== 'true';
                seat.setAttribute('aria-pressed', String(selected));
                seat.textContent = selected ? '✓' : seat.dataset.seat.slice(1);
                update();
            }),
        );
        continuation.addEventListener('click', (event) => {
            if (continuation.getAttribute('aria-disabled') === 'true') {
                event.preventDefault();
                seats.find((seat) => !seat.disabled)?.focus();
            }
        });
        const map = picker.querySelector('.seat-map');
        const initialSize = parseFloat(getComputedStyle(map).getPropertyValue('--seat-size'));
        let zoom = initialSize;
        picker.querySelectorAll('[data-seat-zoom]').forEach((button) =>
            button.addEventListener('click', () => {
                zoom = Math.max(
                    initialSize,
                    Math.min(54, zoom + (button.dataset.seatZoom === 'in' ? 6 : -6)),
                );
                map.style.setProperty('--seat-size', `${zoom}px`);
            }),
        );
        update();
    }
    const payment = document.querySelector('[data-payment-method]');
    payment?.addEventListener('change', () => {
        document.querySelector('[data-payment-phone]').hidden = payment.value !== 'mbway';
        document.querySelector('[data-payment-card]').hidden = payment.value !== 'card';
    });
})();
