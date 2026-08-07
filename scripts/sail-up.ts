/**
 * Inject host LAN IP into .env for Sail Vite/HMR, then `sail up`.
 *
 * Phone + PC both work after this — Vite advertises the LAN IP; Laravel
 * uses the request Host for URLs (see UseRequestRootUrl).
 *
 * Usage (host):
 *   node --experimental-strip-types scripts/sail-up.ts
 *   node --experimental-strip-types scripts/sail-up.ts -- --build
 */
import { spawnSync } from 'node:child_process';
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { detectLanIpv4 } from '../vite/detectLanHost.ts';

const root = resolve(import.meta.dirname, '..');
const envPath = resolve(root, '.env');
const sailArgs = process.argv.includes('--') ? process.argv.slice(process.argv.indexOf('--') + 1) : process.argv.slice(2).filter((a) => a !== '--');

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

function readAppPort(env: string): string {
    const match = env.match(/^APP_PORT=(.*)$/m);

    return (match?.[1] ?? '8000').trim() || '8000';
}

if (!existsSync(envPath)) {
    console.error('Missing .env — copy .env.example first.');
    process.exit(1);
}

let env = readFileSync(envPath, 'utf8');
const appPort = readAppPort(env);
const lanHost = process.env.VITE_DEV_HOST?.trim() || readEnvValue(env, 'VITE_DEV_HOST') || detectLanIpv4();

if (lanHost) {
    env = upsertEnv(env, 'VITE_DEV_HOST', lanHost);
    writeFileSync(envPath, env);
    console.log(`Sail: phone → http://${lanHost}:${appPort} · PC → http://localhost:${appPort}`);
} else {
    console.warn('No LAN IPv4 found — phone access may fail. Set VITE_DEV_HOST=192.168.x.x');
}

const sail = resolve(root, 'vendor/bin/sail');
if (!existsSync(sail)) {
    console.error('Missing vendor/bin/sail — run composer install.');
    process.exit(1);
}

const upArgs = ['up', '-d', ...sailArgs];
const result = spawnSync(sail, upArgs, { cwd: root, stdio: 'inherit', env: process.env });
process.exit(result.status ?? 1);
