import { execSync } from 'node:child_process';

export default async function globalSetup(): Promise<void> {
    if (!process.env.PLAYWRIGHT_SKIP_DB_SETUP) {
        execSync('php artisan migrate:fresh --seed --seeder=E2eSeeder --force', { stdio: 'inherit' });
    }

    execSync('php artisan ziggy:generate', { stdio: 'inherit' });
}
