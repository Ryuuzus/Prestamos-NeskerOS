import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { test } from 'node:test';
import puppeteer from 'puppeteer';

test('bounded paste, native catalog validation and upload checks in Chromium', { timeout: 120_000 }, async () => {
    const executablePath = process.env.BROWSER_PATH || [
        'C:/Program Files/Google/Chrome/Application/chrome.exe',
        'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
        puppeteer.executablePath(),
    ].find((path) => existsSync(path));
    const browser = await puppeteer.launch({ executablePath, headless: true });
    try {
        const page = await browser.newPage();
        const errors = [];
        page.on('pageerror', (error) => errors.push(error.message));
        await page.setContent([
            '<form id="form">',
            '<div><input id="search" type="search" maxlength="100"></div>',
            '<div><input id="name" type="text" required maxlength="100" data-catalog-name',
            ' pattern="(?=.*[\\p{L}\\p{N}])[\\p{L}\\p{M}\\p{N} .\\-]+"',
            ' title="Usa letras, números, espacios, puntos y guiones."></div>',
            '<input id="floors" type="number" min="1" max="100" step="1" required>',
            '<input id="file" type="file" data-max-bytes="2097152" accept=".xlsx,.xls,.csv">',
            '<p data-file-feedback></p><button>Enviar</button></form>',
        ].join(''));
        await page.addScriptTag({ content: readFileSync(new URL('../../resources/js/form-validation.js', import.meta.url), 'utf8') });
        const client = await page.createCDPSession();
        await client.send('HeapProfiler.collectGarbage');
        const before = await page.metrics();
        const pasted = await page.evaluate(() => {
            const field = document.querySelector('#search');
            for (let i = 0; i < 100; i++) {
                const clipboardData = new DataTransfer();
                clipboardData.setData('text/plain', 'mensaje largo '.repeat(100_000));
                field.dispatchEvent(new ClipboardEvent('paste', { bubbles: true, cancelable: true, clipboardData }));
            }
            return { length: field.value.length, hints: document.querySelectorAll('[data-input-limit-message]').length };
        });
        assert.deepEqual(pasted, { length: 100, hints: 1 });
        await client.send('HeapProfiler.collectGarbage');
        const after = await page.metrics();
        assert.ok(after.JSHeapUsedSize - before.JSHeapUsedSize < 5 * 1024 * 1024, 'Large paste contents must not remain retained in JS memory');

        const replacement = await page.evaluate(() => {
            const field = document.querySelector('#search');
            field.value = 'ABCDE';
            field.setSelectionRange(1, 4);
            const clipboardData = new DataTransfer();
            clipboardData.setData('text/plain', 'xy');
            field.dispatchEvent(new ClipboardEvent('paste', { bubbles: true, cancelable: true, clipboardData }));
            return field.value;
        });
        assert.equal(replacement, 'AxyE');

        const validity = await page.evaluate(() => {
            const name = document.querySelector('#name');
            return ['Edificio José-2.', 'Aula 101', '<script>', '!!!', '   '].map((value) => {
                name.value = value;
                name.dispatchEvent(new Event('input', { bubbles: true }));
                return name.checkValidity();
            });
        });
        assert.deepEqual(validity, [true, true, false, false, false]);

        const fileValidity = await page.evaluate(() => {
            const input = document.querySelector('#file');
            return [
                new File(['name,floors\nCentral,2'], 'edificios.csv'),
                new File(['invalid'], 'archivo.exe'),
                new File([new Uint8Array(2097153)], 'grande.xlsx'),
                new File([], 'vacio.csv'),
                new File(['name,floors\nNorte,3'], 'correcto.csv'),
            ].map((file) => {
                const data = new DataTransfer();
                data.items.add(file);
                input.files = data.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                return input.checkValidity();
            });
        });
        assert.deepEqual(fileValidity, [true, false, false, false, true]);
        const prevented = await page.evaluate(() => {
            const form = document.querySelector('#form');
            document.querySelector('#name').value = 'Aula Norte';
            document.querySelector('#name').dispatchEvent(new Event('input', { bubbles: true }));
            document.querySelector('#floors').value = '1000000';
            let submitted = false;
            form.addEventListener('submit', (event) => { event.preventDefault(); submitted = true; });
            form.requestSubmit();
            return !submitted;
        });
        assert.equal(prevented, true, 'Invalid forms must be stopped before sending a request');
        assert.deepEqual(errors, []);
    } finally {
        await browser.close();
    }
});
