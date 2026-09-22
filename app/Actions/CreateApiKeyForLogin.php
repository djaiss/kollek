<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EmailType;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Jobs\SendEmail;
use App\Mail\NewLoginDetected;
use App\Models\User;

/**
 * Create an API token for a successful login. The token is named after the
 * device it came from, and a security notification goes out about the sign-in.
 */
class CreateApiKeyForLogin
{
    public function __construct(
        private readonly User $user,
        private readonly ?string $deviceName = null,
    ) {}

    public function execute(): string
    {
        $device = $this->deviceLabel();

        $token = $this->user->createToken('Login from '.$device)->plainTextToken;
        $this->log();
        $this->sendEmail($device);

        return $token;
    }

    private function deviceLabel(): string
    {
        $deviceName = TextSanitizer::plainText((string) $this->deviceName);

        if ($deviceName === '') {
            return 'an unknown device';
        }

        return $deviceName;
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ApiKeyCreation,
        )->onQueue('low');
    }

    private function sendEmail(string $device): void
    {
        SendEmail::dispatch(
            mailable: new NewLoginDetected(
                device: $device,
            ),
            user: $this->user,
            emailType: EmailType::NewLogin,
        )->onQueue('high');
    }
}
