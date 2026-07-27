import { expect, test } from '@playwright/test';

test.describe('auth', () => {
    test('login page renders', async ({ page }) => {
        await page.goto('/login');
        await expect(page.getByRole('heading', { name: /log in to your account/i })).toBeVisible();
        await expect(page.getByLabel(/email address/i)).toBeVisible();
        await expect(page.getByLabel(/^password$/i)).toBeVisible();
    });

    test('user can sign in and reach dashboard', async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel(/email address/i).fill('user1@test.com');
        await page.getByLabel(/^password$/i).fill('password');
        await page.getByRole('button', { name: /log in/i }).click();
        await expect(page).toHaveURL(/\/dashboard/);
        await expect(page.getByRole('heading', { name: /my routines/i })).toBeVisible();
    });
});
