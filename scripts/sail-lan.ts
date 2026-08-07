/**
 * Point Sail Vite at a phone-reachable LAN IP (or clear it for PC-only HMR).
 * Does not rewrite APP_URL — UseRequestRootUrl follows the browser Host.
 *
 * Usage (host, not inside Sail):
 *   node --experimental-strip-types scripts/sail-lan.ts
 *   node --experimental-strip-types scripts/sail-lan.ts --localhost
 */
import { spawnSync } from 'node:child_process';
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { detectLanIpv4 } from '../vite/detectLanHost.ts';

const root = resolve(import.meta.dirname, '..');
const envPath = resolve(root, '.env');
const toLocalhost = process.argv.includes('--localhost');
const dryRun = process.argv.includes('--dry-run');

function readAppPort(env: string): string {
    const match = env.match(/^APP_PORT=(.*)$/m);

    return (match?.[1] ?? '8000').trim() || '8000';
}

function readEnvValue(env: string, key: string): string | null {
    const match = env.match(new RegExp(`^${key}=(.*)$`, 'm'));
    const value = match?.[1]?.trim();

    return value ? value : null;
}

function upsertEnv(content: string, key: string, value: string): string {
    const line = `${key}=${value}`;
    const pattern = new RegExp(`^#?\\s*${key}=.*$`, 'm');

    if (pattern.test(content)) {
        return content.replace(pattern, line);
    }

    return `${content.trimEnd()}\n${line}\n`;
}

function commentEnv(content: string, key: string): string {
    const pattern = new RegExp(`^#?\\s*${key}=.*$`, 'm');

    if (pattern.test(content)) {
        return content.replace(pattern, `# ${key}=`);
    }

    return content;
}

function restartSailServices(): void {
    if (dryRun) {
        return;
    }

    const sail = existsSync(resolve(root, 'vendor/bin/sail')) ? resolve(root, 'vendor/bin/sail') : null;

    if (!sail) {
        return;
    }

    const result = spawnSync(sail, ['restart', 'vite', 'laravel.test'], {
        cwd: root,
        stdio: 'inherit',
        env: process.env,
    });

    if (result.status !== 0) {
        console.error('Sail restart skipped or failed — run: npm run sail:up');
    }
}

if (!existsSync(envPath)) {
    console.error('Missing .env — copy .env.example first.');
    process.exit(1);
}

let env = readFileSync(envPath, 'utf8');
const appPort = readAppPort(env);

if (toLocalhost) {
    env = commentEnv(env, 'VITE_DEV_HOST');
    if (!dryRun) {
        writeFileSync(envPath, env);
    }
    console.log(`Sail Vite → localhost HMR (PC-only). App still at http://localhost:${appPort}`);
    restartSailServices();
    process.exit(0);
}

const lanHost = process.env.VITE_DEV_HOST?.trim() || readEnvValue(env, 'VITE_DEV_HOST') || detectLanIpv4();

if (!lanHost) {
    console.error('No LAN IPv4 found. Set VITE_DEV_HOST=192.168.x.x and re-run.');
    process.exit(1);
}

env = upsertEnv(env, 'VITE_DEV_HOST', lanHost);
if (!dryRun) {
    writeFileSync(envPath, env);
}

console.log(`Sail: phone → http://${lanHost}:${appPort} · PC → http://localhost:${appPort}`);
restartSailServices();
