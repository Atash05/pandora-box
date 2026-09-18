<?php

namespace App\Services;

use App\Models\SurveyEvent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramPhoneNotifier
{
    public function sendPhoneNumber(string $phone): bool
    {
        return $this->sendMessage("New phone number:\n{$phone}");
    }

    public function sendSurveyEvent(SurveyEvent $event): bool
    {
        if ($event->action === 'survey_declined') {
            return $this->sendMessage('She chose not to share her phone number.');
        }

        $lines = [
            'Survey activity',
            Str::headline($event->action),
        ];

        if ($event->screen) {
            $lines[] = 'Screen: '.Str::headline($event->screen);
        }

        foreach ($event->details ?? [] as $key => $value) {
            $lines[] = Str::headline($key).": {$value}";
        }

        return $this->sendMessage(implode("\n", $lines));
    }

    private function sendMessage(string $text): bool
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (! filled($botToken) || ! filled($chatId)) {
            Log::warning('Telegram notification skipped: Telegram settings are incomplete.');

            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Telegram notification failed to connect.', [
                'exception' => $exception::class,
            ]);

            return false;
        }

        if (! $response->successful() || ! $response->json('ok')) {
            Log::warning('Telegram notification was rejected.', [
                'status' => $response->status(),
            ]);

            return false;
        }

        return true;
    }
}
