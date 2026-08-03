<?php

namespace App\Auth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Override;

class GenerateRegistrationInviteSecretCommand extends Command
{
    #[Override]
    protected $signature = 'registration:invite-secret
                            {--write : Write REGISTRATION_INVITE into .env}';

    #[Override]
    protected $description = 'Generate a REGISTRATION_INVITE secret for bootstrap / master invite links';

    public function handle(): int
    {
        $secret = Str::random(40);

        if ($this->option('write')) {
            $path = base_path('.env');
            if (! is_file($path)) {
                $this->error('.env not found.');

                return self::FAILURE;
            }

            $env = file_get_contents($path);
            if ($env === false) {
                $this->error('Could not read .env.');

                return self::FAILURE;
            }

            if (preg_match('/^REGISTRATION_INVITE=/m', $env) === 1) {
                $env = preg_replace(
                    '/^REGISTRATION_INVITE=.*/m',
                    'REGISTRATION_INVITE='.$secret,
                    $env,
                    1,
                );
            } else {
                $env = rtrim($env)."\n\nREGISTRATION_INVITE=".$secret."\n";
            }

            if (file_put_contents($path, $env) === false) {
                $this->error('Could not write .env.');

                return self::FAILURE;
            }

            $this->info('Wrote REGISTRATION_INVITE to .env');
            $this->line('Run: php artisan config:clear');
        }

        $this->line($secret);
        $this->comment('Master URL (only if env secret is set): '.url('/register?invite='.urlencode($secret)));
        $this->comment('Local: leave REGISTRATION_INVITE empty. Prod: Admin → Invites for one-time links.');

        return self::SUCCESS;
    }
}
