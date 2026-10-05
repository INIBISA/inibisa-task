<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GeneratePushKeys extends Command
{
    protected $signature = 'push:keys';

    protected $description = 'Generate VAPID keys for browser push notifications';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();
        $this->line('Add these values to the server .env file, then run php artisan config:clear:');
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('VAPID_SUBJECT='.rtrim(config('app.url'), '/'));

        return self::SUCCESS;
    }
}
