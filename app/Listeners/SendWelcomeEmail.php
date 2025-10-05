<?php

namespace App\Listeners;

use App\Services\EmailTemplateService;
use Illuminate\Auth\Events\Registered;

class SendWelcomeEmail
{
    public function __construct(protected EmailTemplateService $emails) {}

    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (!$user || !$user->email) return;

        $this->emails->send('welcome_user', $user->email, [
            'user'          => $user,
            'customer_name' => $user->name ?? $user->email,
        ]);
    }
}