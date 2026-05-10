<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateEnvironment extends Command
{
    protected $signature   = 'env:validate';
    protected $description = 'Validate all required environment variables are set';

    private array $required = [
        'APP_KEY'       => 'Generate with: php artisan key:generate',
        'APP_URL'       => 'Your Railway app URL e.g. https://xxx.up.railway.app',
        'DB_HOST'       => 'PostgreSQL host from Railway PostgreSQL service',
        'DB_DATABASE'   => 'PostgreSQL database from Railway PostgreSQL service',
        'DB_USERNAME'   => 'PostgreSQL username from Railway PostgreSQL service',
        'DB_PASSWORD'   => 'PostgreSQL password from Railway PostgreSQL service',
        'REDIS_HOST'    => 'Redis host from Railway Redis service',
    ];

    public function handle(): int
    {
        $missing = [];

        foreach ($this->required as $key => $hint) {
            if (empty(env($key))) {
                $missing[$key] = $hint;
            }
        }

        if (empty($missing)) {
            $this->info('✅ All required environment variables are set.');
            return 0;
        }

        $this->error('❌ Missing required environment variables:');
        foreach ($missing as $key => $hint) {
            $this->line("  <fg=red>{$key}</> — {$hint}");
        }

        return 1;
    }
}
