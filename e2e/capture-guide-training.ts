/**
 * One-shot: refresh tutorial Training/Preferences guide screenshots.
 * Usage (Sail up, DB seeded): npx playwright test e2e/capture-guide-training.ts --config=playwright.guide.config.ts
 */
import { expect, test } from '@playwright/test';
import path from 'node:path';

const guideDir = path.join(process.cwd(), 'public/images/guide');

test('capture training guide screenshots', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel(/email address/i).fill('user1@test.com');
    await page.getByLabel(/^password$/i).fill('password');
    await page.getByRole('button', { name: /log in/i }).click();
    await expect(page).toHaveURL(/\/dashboard/);

    await page.goto('/settings/training');
    await expect(page.getByRole('heading', { name: 'Progression' })).toBeVisible();
    await expect(page.getByText(/Defaults for new routines/i).first()).toBeVisible();

    await page.setViewportSize({ width: 1280, height: 900 });
    await page.getByRole('heading', { name: 'Progression' }).scrollIntoViewIfNeeded();
    await page.screenshot({ path: path.join(guideDir, 'training-desktop.png'), fullPage: false });

    await page.setViewportSize({ width: 390, height: 844 });
    await page.getByRole('heading', { name: 'Progression' }).scrollIntoViewIfNeeded();
    await page.screenshot({ path: path.join(guideDir, 'training-mobile.png'), fullPage: false });
});
