import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium',
    headless: true,
    args: ['--no-sandbox'],
});
const page = await browser.newPage();
const requests = [];
page.on('request', (request) => requests.push(request.url()));
await page.setContent(`<button data-demo-action><span>Guardar</span></button>
<a href="https://example.invalid/write" data-demo-action>Eliminar</a>
<form data-demo-form action="https://example.invalid/write" method="post"><label>Email <input name="email" type="email" required></label><button type="submit">Entrar</button></form>
<button type="button" data-theme-toggle>Tema</button>
<aside data-ui-toast hidden><div role="status" aria-live="polite"><span data-toast-message></span></div><button data-toast-close aria-label="Fechar">×</button></aside>`);
await page.addScriptTag({
    content: await fs.readFile(new URL('../common/assets/js/ui.js', import.meta.url), 'utf8'),
});
for (const selector of ['button[data-demo-action] span', 'a[data-demo-action]', 'form button']) {
    await page.locator(selector).click();
    await page.waitForFunction(
        () =>
            document.querySelector('[data-toast-message]').textContent ===
            'Action not implemented yet',
    );
    assert.equal(await page.locator('[data-ui-toast]').isVisible(), true);
    await page.locator('[data-toast-close]').click();
    assert.equal(await page.locator('[data-ui-toast]').isVisible(), false);
}
await page.locator('input').focus();
await page.keyboard.press('Enter');
await page.waitForFunction(
    () =>
        document.querySelector('[data-toast-message]').textContent ===
            'Action not implemented yet' && !document.querySelector('[data-ui-toast]').hidden,
);
assert.equal(
    await page.locator('input').evaluate((e) => e === document.activeElement),
    true,
    'Toast must preserve keyboard focus',
);
assert.equal(requests.length, 0, 'Writes must not issue any network requests');
await page.locator('[data-theme-toggle]').click();
assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
assert.equal(await page.locator('[data-theme-toggle]').getAttribute('aria-pressed'), 'true');
await page.locator('[data-theme-toggle]').click();
assert.equal(await page.locator('html').getAttribute('data-theme'), 'light');
console.log(
    'UI browser checks passed: click, nested target, anchor, invalid form, keyboard submit, exact toast, no requests, preserved focus, theme toggle.',
);
await browser.close();
