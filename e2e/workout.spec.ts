import { expect, test, type Page } from '@playwright/test';

async function loginAsUser(page: Page): Promise<void> {
    await page.goto('/login');
    await page.getByLabel(/email address/i).fill('user1@test.com');
    await page.getByLabel(/^password$/i).fill('password');
    await page.getByRole('button', { name: /log in/i }).click();
    await expect(page).toHaveURL(/\/dashboard/);
}

async function clearInProgressWorkout(page: Page): Promise<void> {
    const abandon = page.getByRole('button', { name: 'Abandon' });
    if (await abandon.isVisible()) {
        page.once('dialog', (dialog) => dialog.accept());
        await abandon.click();
        await expect(abandon).not.toBeVisible();
    }
}

async function startBarbellStrength(page: Page): Promise<void> {
    const card = page
        .locator('div.rounded-xl.border')
        .filter({ has: page.getByRole('heading', { name: 'Barbell Strength', level: 3 }) });
    await card.getByRole('button', { name: 'Start' }).click();
    await expect(page).toHaveURL(/\/workouts\/\d+/);
}

test.describe('workout player', () => {
    test.describe.configure({ mode: 'serial' });

    test.beforeEach(async ({ page }) => {
        await loginAsUser(page);
        await clearInProgressWorkout(page);
    });

    test('starts routine and opens player', async ({ page }) => {
        await startBarbellStrength(page);
        await expect(page.getByRole('heading', { name: 'Barbell Strength' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Finish', exact: true })).toBeVisible();
    });

    test('completes a warm-up set', async ({ page }) => {
        await startBarbellStrength(page);
        await expect(page.getByText(/warm-up/i)).toBeVisible();
        await page.getByRole('button', { name: 'Complete set' }).click();
        await expect(page.locator('header .font-mono')).toHaveText(/1\/\d+/, { timeout: 15_000 });
    });
});
