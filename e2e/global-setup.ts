import { execSync } from 'node:child_process';

export default async function globalSetup(): Promise<void> {
    execSync('php artisan migrate:fresh --seed --seeder=E2eSeeder --force', { stdio: 'inherit' });
    execSync('php artisan ziggy:generate', { stdio: 'inherit' });
}
