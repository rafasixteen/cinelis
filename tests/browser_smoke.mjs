import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.CINELIS_BASE_URL || 'http://cinelis.localhost';
const output = new URL('../tmp/mockup-implementation/screenshots/', import.meta.url);
await fs.mkdir(output, { recursive: true });
const routes = [
    '/',
    '/movies',
    '/movies/a-ultima-luz',
    '/movies/entre-mares',
    '/movies/orbita',
    '/movies/dias-de-verao',
    '/movies/o-outro-lado',
    '/movies?q=zzzz-no-match',
    '/sessions',
    '/sessions?movie=orbita',
    '/sessions/a-ultima-luz-20261027-2030/seats',
    '/checkout',
    '/checkout?seats=',
    '/checkout/confirmation',
    '/tickets',
    '/tickets?period=past',
    '/tickets/CIN-DEMO-1',
    '/account',
    '/account/login',
    '/account/register',
    '/account/recover',
    '/account/history',
    '/contact',
    '/help',
    '/privacy',
    '/admin/',
    '/admin/login',
    '/admin/movies',
    '/admin/movies?q=zzzz-no-match',
    '/admin/movies/create',
    '/admin/movies/1/edit',
    '/admin/rooms',
    '/admin/rooms/create',
    '/admin/rooms/1/layout',
    '/admin/sessions',
    '/admin/sessions/create',
    '/admin/sessions/1/edit',
    '/admin/sessions/3/tickets',
    '/admin/sessions/3/tickets?page=2',
    '/admin/tickets',
    '/admin/orders',
    '/admin/users',
    '/admin/users/create',
    '/admin/users/1/edit',
    '/admin/settings',
];
const capture = new Set([
    '/',
    '/movies/a-ultima-luz',
    '/sessions/a-ultima-luz-20261027-2030/seats',
    '/checkout',
    '/account/login',
    '/tickets/CIN-DEMO-1',
    '/admin/',
    '/admin/movies',
    '/admin/rooms/1/layout',
    '/admin/sessions/create',
    '/admin/users',
]);
const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium',
    headless: true,
    args: ['--no-sandbox'],
});
const context = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
    colorScheme: 'light',
});
const page = await context.newPage();
const errors = [],
    badAssets = [],
    links = new Set(),
    results = [];
page.on('pageerror', (error) => errors.push(error.message));
page.on('response', (response) => {
    if (response.status() >= 400 && !response.request().isNavigationRequest())
        badAssets.push([response.status(), response.url()]);
});
let actions = 0;
for (const width of [1440, 390]) {
    await page.setViewportSize({ width, height: width === 390 ? 844 : 1000 });
    for (const route of routes) {
        const response = await page.goto(base + route, { waitUntil: 'networkidle' });
        assert.equal(response.status(), 200, route + ' must render');
        assert.equal(await page.locator('h1').count(), 1, route + ' must have one main heading');
        const metrics = await page.evaluate(() => ({
            width: innerWidth,
            content: document.documentElement.scrollWidth,
        }));
        assert.ok(
            metrics.content <= metrics.width + 1,
            `${route} overflows at ${width}px: ${metrics.content}px`,
        );
        const logo = page.locator('.ui-logo img:visible').first();
        assert.ok(
            await logo.evaluate((i) => i.complete && i.naturalWidth > 0),
            route + ' logo must load',
        );
        if (width === 1440) {
            for (const href of await page
                .locator('a[href]')
                .evaluateAll((es) => es.map((e) => e.href))) {
                const url = new URL(href);
                if (url.origin === new URL(base).origin && url.hash === '') links.add(url.href);
            }
            assert.equal(
                await page
                    .locator('form:not([data-demo-form])')
                    .evaluateAll((es) => es.filter((e) => e.method.toLowerCase() !== 'get').length),
                0,
                route + ' has an unguarded write form',
            );
            const controls = page.locator(
                '[data-demo-action], form[data-demo-form] button[type="submit"], form[data-demo-form] input[type="submit"]',
            );
            for (let i = 0; i < (await controls.count()); i++) {
                const control = controls.nth(i);
                if (!(await control.isVisible()) || !(await control.isEnabled())) continue;
                const before = page.url(),
                    requests = [];
                const listener = (r) => {
                    if (
                        r.isNavigationRequest() ||
                        r.method() !== 'GET' ||
                        ['fetch', 'xhr'].includes(r.resourceType())
                    )
                        requests.push(r.url());
                };
                page.on('request', listener);
                await control.click();
                await page.waitForFunction(
                    () =>
                        document.querySelector('[data-toast-message]')?.textContent ===
                            'Action not implemented yet' &&
                        !document.querySelector('[data-ui-toast]').hidden,
                );
                await page.waitForTimeout(30);
                page.off('request', listener);
                assert.equal(page.url(), before, route + ' write action navigated');
                assert.deepEqual(requests, [], route + ' write action requested backend');
                actions++;
                await page.locator('[data-toast-close]').click();
            }
        }
        if (capture.has(route)) {
            const name =
                route === '/' ? 'frontoffice-home' : route.replace(/^\//, '').replaceAll('/', '-');
            await page.screenshot({
                path: new URL(`${name}-${width}-light.png`, output).pathname,
                fullPage: true,
                animations: 'disabled',
            });
            await page.locator('[data-theme-toggle]').first().click();
            assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
            await page.screenshot({
                path: new URL(`${name}-${width}-dark.png`, output).pathname,
                fullPage: true,
                animations: 'disabled',
            });
            await page.locator('[data-theme-toggle]').first().click();
        }
        results.push({ route, width, status: response.status() });
    }
    console.log(`${routes.length} routes passed at ${width}px`);
}
for (const link of links) {
    const response = await context.request.get(link);
    assert.equal(response.status(), 200, 'Broken link: ' + link);
}
for (const route of [
    '/movies/not-a-film',
    '/sessions/not-a-session/seats',
    '/tickets/INVALID',
    '/admin/movies/999/edit',
    '/admin/rooms/999/layout',
    '/admin/sessions/999/edit',
    '/admin/users/999/edit',
]) {
    const response = await context.request.get(base + route);
    assert.equal(response.status(), 404, 'Missing ID must return 404: ' + route);
}
assert.deepEqual(errors, [], 'JavaScript runtime errors');
assert.deepEqual(badAssets, [], 'Missing page assets');
const report = {
    routes: routes.length,
    viewportChecks: results.length,
    links: links.size,
    writeActions: actions,
    invalidIds: 7,
    errors,
    badAssets,
};
await fs.writeFile(
    new URL('../tmp/mockup-implementation/browser-results.json', import.meta.url),
    JSON.stringify(report, null, 2),
);
console.log(JSON.stringify(report, null, 2));
await browser.close();
