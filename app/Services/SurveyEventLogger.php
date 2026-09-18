<?php

namespace App\Services;

use App\Models\SurveyEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyEventLogger
{
    public function __construct(private TelegramPhoneNotifier $telegramPhoneNotifier) {}

    public function log(
        Request $request,
        string $action,
        ?string $screen = null,
        array $details = [],
        ?int $phoneRequestId = null,
    ): SurveyEvent {
        $surveyId = $request->session()->get('survey.trace_id');

        if (! $surveyId) {
            $surveyId = (string) Str::uuid();
            $request->session()->put('survey.trace_id', $surveyId);
        }

        $event = SurveyEvent::query()->create([
            'survey_id' => $surveyId,
            'action' => $action,
            'screen' => $screen,
            'details' => $details ?: null,
            'phone_request_id' => $phoneRequestId,
        ]);

        if (! str_starts_with($action, 'telegram_notification_')) {
            $this->telegramPhoneNotifier->sendSurveyEvent($event);
        }

        return $event;
    }
}
