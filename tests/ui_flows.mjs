import assert from 'node:assert/strict';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.CINELIS_BASE_URL || 'http://cinelis.localhost';
const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium',
    headless: true,
    args: ['--no-sandbox'],
});
const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
try {
    await page.goto(base + '/movies');
    await page.locator('input[name="q"]').fill('Entre marés');
    await page.getByRole('button', { name: 'Filtrar', exact: true }).click();
    assert.equal(await page.locator('.movie-card').count(), 1);
    assert.match(await page.locator('.movie-card').innerText(), /Entre marés/);

    await page.goto(base + '/sessions/a-ultima-luz-20261027-2030/seats');
    assert.equal(await page.locator('[data-seat="B3"]').isDisabled(), true);
    await page.locator('[data-seat="A4"]').click();
    assert.match(await page.locator('[data-seat-total]').innerText(), /22,50/);
    await page.locator('[data-seat-continue]').click();
    assert.equal(new URL(page.url()).searchParams.get('seats'), 'A2,A3,A4');
    await page.locator('[data-payment-method]').selectOption('card');
    assert.equal(await page.locator('[data-payment-card]').isVisible(), true);
    assert.equal(await page.locator('[data-payment-phone]').isVisible(), false);
    await page.getByRole('link', { name: 'Alterar lugares' }).click();
    assert.deepEqual(
        await page
            .locator('[data-seat][aria-pressed="true"]')
            .evaluateAll((es) => es.map((e) => e.dataset.seat)),
        ['A2', 'A3', 'A4'],
    );
    for (const seat of ['A2', 'A3', 'A4']) await page.locator(`[data-seat="${seat}"]`).click();
    assert.equal(await page.locator('[data-seat-continue]').getAttribute('aria-disabled'), 'true');
    const seatUrl = page.url();
    await page.locator('[data-seat-continue]').evaluate((e) => e.click());
    assert.equal(page.url(), seatUrl);

    await page.goto(base + '/admin/movies');
    await page.getByLabel('Pesquisar', { exact: true }).fill('Entre marés');
    await page.getByRole('button', { name: 'Filtrar', exact: true }).click();
    assert.equal(await page.locator('tbody tr').count(), 1);
    assert.match(await page.locator('tbody').innerText(), /Entre marés/);
    await page.goto(base + '/admin/sessions/3/tickets');
    const firstPage = await page.locator('tbody').innerText();
    await page.locator('.pagination a').filter({ hasText: /^2$/ }).click();
    assert.equal(new URL(page.url()).searchParams.get('page'), '2');
    assert.notEqual(await page.locator('tbody').innerText(), firstPage);
    await page.locator('.profile-menu summary').click();
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('.profile-menu').evaluate((e) => e.open), false);
    assert.equal(
        await page.locator('.profile-menu summary').evaluate((e) => e === document.activeElement),
        true,
    );

    for (const route of ['/account/login', '/admin/login']) {
        await page.goto(base + route);
        const input = page.locator('input[type="email"]:visible').first();
        await input.fill('demo@example.test');
        await input.focus();
        const requests = [];
        const listener = (r) => requests.push(r.url());
        page.on('request', listener);
        await page.keyboard.press('Enter');
        await page.waitForFunction(
            () =>
                document.querySelector('[data-toast-message]')?.textContent ===
                    'Action not implemented yet' &&
                !document.querySelector('[data-ui-toast]').hidden,
        );
        page.off('request', listener);
        assert.deepEqual(requests, [], route + ' keyboard submit must not request backend');
        assert.equal(await input.evaluate((e) => e === document.activeElement), true);
    }
    console.log(
        'UI flows passed: filters, pagination, seat totals/round trip/empty selection, payment switch, profile keyboard focus, request-free keyboard login.',
    );
} finally {
    await browser.close();
}
