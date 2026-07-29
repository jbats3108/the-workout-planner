import { expect, test } from '@playwright/test';

test.describe('dashboard', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel(/email address/i).fill('user1@test.com');
        await page.getByLabel(/^password$/i).fill('password');
        await page.getByRole('button', { name: /log in/i }).click();
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('shows seeded routines', async ({ page }) => {
        await expect(page.getByText('Barbell Strength')).toBeVisible();
    });

    test('opens routine editor', async ({ page }) => {
        await page.getByLabel('Edit routine').first().click();
        await expect(page).toHaveURL(/\/routines\/[a-z0-9-]+\/edit/);
        await expect(page.getByText('Routine', { exact: true })).toBeVisible();
    });
});
