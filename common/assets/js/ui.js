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

    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
    function currentTheme() {
        return document.documentElement.dataset.theme || (systemTheme.matches ? 'dark' : 'light');
    }
    function updateThemeControls() {
        const dark = currentTheme() === 'dark';
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.setAttribute('aria-pressed', String(dark));
            button.setAttribute('aria-label', dark ? 'Ativar tema claro' : 'Ativar tema escuro');
            button.title = dark ? 'Tema claro' : 'Tema escuro';
        });
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
        if (target.closest('[data-theme-toggle]')) {
            event.preventDefault();
            const theme = currentTheme() === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = theme;
            try {
                localStorage.setItem('cinelis-theme', theme);
            } catch (_) {
                /* Private browsing may disallow storage. */
            }
            updateThemeControls();
        }
    });
    document.addEventListener('submit', (event) => {
        if (event.target.matches('[data-demo-form]')) {
            event.preventDefault();
            showToast();
        }
    });
    systemTheme.addEventListener('change', updateThemeControls);
    window.CineLisUI = Object.freeze({ toast: showToast });
    updateThemeControls();
})();
