import { expect, test } from '@playwright/test';

test.describe('workout player', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel(/email address/i).fill('user1@test.com');
        await page.getByLabel(/^password$/i).fill('password');
        await page.getByRole('button', { name: /log in/i }).click();
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('starts routine and opens player', async ({ page }) => {
        const card = page
            .locator('div.rounded-xl.border')
            .filter({ has: page.getByRole('heading', { name: 'Barbell Strength', level: 3 }) });
        await card.getByRole('button', { name: 'Start' }).click();
        await expect(page).toHaveURL(/\/workouts\/\d+/);
        await expect(page.getByRole('heading', { name: 'Barbell Strength' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Finish', exact: true })).toBeVisible();
    });
});
