(() => {
    'use strict';
    const message = 'Action not implemented yet';
    let toastTimer;

    function showToast() {
        const toast = document.querySelector('[data-ui-toast]');
        if (!toast) return;
        clearTimeout(toastTimer);
        const label = toast.querySelector('[data-toast-message]');
        label.textContent = '';
        toast.hidden = false;
        requestAnimationFrame(() => {
            label.textContent = message;
        });
        toastTimer = setTimeout(() => {
            toast.hidden = true;
        }, 7000);
    }

    document.querySelectorAll('form[data-demo-form]').forEach((form) => {
        form.noValidate = true;
    });
    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) return;
        const action = target.closest('[data-demo-action]');
        const submit = target.closest('button, input[type="submit"], input[type="image"]');
        if (action || (submit?.type === 'submit' && submit.form?.matches('[data-demo-form]'))) {
            event.preventDefault();
            showToast();
            return;
        }
        if (target.closest('[data-toast-close]')) {
            clearTimeout(toastTimer);
            document.querySelector('[data-ui-toast]').hidden = true;
        }
    });
    document.addEventListener('submit', (event) => {
        if (event.target.matches('[data-demo-form]')) {
            event.preventDefault();
            showToast();
        }
    });
})();
