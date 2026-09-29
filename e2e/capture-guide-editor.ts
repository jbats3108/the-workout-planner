/**
 * One-shot: refresh tutorial editor guide screenshots from Superset Pump (shows Swap A↔B + routine settings).
 * Usage (Sail up, DB seeded): npx playwright test e2e/capture-guide-editor.ts --config=playwright.guide.config.ts
 */
import { expect, test } from '@playwright/test';
import path from 'node:path';

const guideDir = path.join(process.cwd(), 'public/images/guide');

test('capture editor guide screenshots', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel(/email address/i).fill('user1@test.com');
    await page.getByLabel(/^password$/i).fill('password');
    await page.getByRole('button', { name: /log in/i }).click();
    await expect(page).toHaveURL(/\/dashboard/);

    await page.goto('/routines/superset-pump/edit');
    await expect(page).toHaveURL(/\/routines\/superset-pump\/edit/);

    await page.setViewportSize({ width: 1280, height: 900 });
    const desktopSettings = page.locator('[data-desktop-routine-settings]');
    await expect(desktopSettings).toBeVisible();
    await expect(desktopSettings).toContainText(/Straight Sets|Progressive/i);
    await expect(page.locator('.md\\:flex [data-swap-superset]').first()).toBeVisible();
    await expect(page.getByRole('button', { name: /Loading/ })).toHaveCount(0, { timeout: 15_000 });
    await page.screenshot({ path: path.join(guideDir, 'editor-desktop.png'), fullPage: false });

    await page.setViewportSize({ width: 390, height: 844 });
    // Routine sheet first: Progression + Deload live here.
    await expect(page.locator('[data-routine-progression]')).toBeVisible();
    await page
        .locator('[data-routine-progression]')
        .getByRole('button', { name: /Progression/i })
        .click();
    await expect(page.getByText(/Straight Sets — same weight/i)).toBeVisible();
    await page.screenshot({ path: path.join(guideDir, 'editor-mobile.png'), fullPage: false });
});
