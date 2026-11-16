(() => {
    'use strict';
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

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) {
            return;
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

    function localizedDateValue(input) {
        if (!input.value) {
            return input.type === 'datetime-local' ? 'dd/mm/yyyy, --:--' : 'dd/mm/yyyy';
        }

        const [date, time] = input.value.split('T');
        const [year, month, day] = date.split('-');
        const formattedDate = `${day}/${month}/${year}`;

        return input.type === 'datetime-local'
            ? `${formattedDate}, ${(time || '--:--').slice(0, 5)}`
            : formattedDate;
    }

    function initLocalizedDate(input) {
        const wrapper = document.createElement('span');
        const display = document.createElement('span');

        wrapper.className = 'localized-date-input';
        display.className = 'localized-date-input__display';
        display.setAttribute('aria-hidden', 'true');
        input.parentNode.insertBefore(wrapper, input);
        wrapper.append(display, input);

        const update = () => {
            display.textContent = localizedDateValue(input);
        };

        input.addEventListener('input', update);
        input.addEventListener('change', update);
        update();
    }

    document.querySelectorAll('input[data-localized-date]').forEach(initLocalizedDate);

    function initCombobox(combobox) {
        const input = combobox.querySelector('[data-combobox-input]');
        const panel = combobox.querySelector('[data-combobox-panel]');
        const control = combobox.querySelector('[data-combobox-control]');
        const selected = combobox.querySelector('[data-combobox-selected]');
        const empty = combobox.querySelector('[data-combobox-empty]');
        const options = [...combobox.querySelectorAll('[data-combobox-option]')];

        if (!input || !panel || !control || !selected || !empty || options.length === 0) {
            return;
        }

        const updateSelected = () => {
            selected.replaceChildren();
            const checked = options.filter((option) => option.querySelector('input')?.checked);

            checked.forEach((option) => {
                const tag = document.createElement('span');
                tag.className = 'ui-combobox__tag';
                tag.dataset.comboboxTag = option.querySelector('input').value;
                tag.textContent = option.querySelector('span').textContent;
                selected.append(tag);
            });
        };

        const filter = () => {
            const query = input.value.trim().toLocaleLowerCase();
            let visible = 0;

            options.forEach((option) => {
                const matches = !query || option.dataset.label.includes(query);
                option.hidden = !matches;

                if (matches) {
                    visible += 1;
                }
            });

            empty.hidden = visible !== 0;
        };

        const open = () => {
            panel.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            filter();
        };

        const close = () => {
            panel.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.value = '';
            filter();
        };

        input.addEventListener('focus', open);
        input.addEventListener('input', open);

        control.addEventListener('click', () => {
            input.focus();
            open();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
                input.blur();
            }
        });

        options.forEach((option) => {
            option.querySelector('input').addEventListener('change', () => {
                option.setAttribute('aria-selected', String(option.querySelector('input').checked));
                updateSelected();
            });
        });

        document.addEventListener('click', (event) => {
            if (!combobox.contains(event.target)) {
                close();
            }
        });

        updateSelected();
        filter();
    }

    document.querySelectorAll('[data-ui-combobox]').forEach(initCombobox);
    systemTheme.addEventListener('change', updateThemeControls);
    updateThemeControls();
})();
