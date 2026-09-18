<?php

namespace App\Http\Controllers;

use App\Models\PhoneRequest;
use App\Services\SurveyEventLogger;
use App\Services\TelegramPhoneNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhoneRequestController extends Controller
{
    public function store(
        Request $request,
        SurveyEventLogger $eventLogger,
        TelegramPhoneNotifier $telegramPhoneNotifier,
    ): RedirectResponse {
        $eventLogger->log($request, 'phone_submission_started', 'phone_form');

        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:7', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
        ], [
            'phone.required' => 'Please enter your phone number.',
            'phone.min' => 'Please check your phone number and try again.',
            'phone.regex' => 'A phone number may only contain digits, +, parentheses, spaces, and hyphens.',
        ]);

        $phoneRequest = PhoneRequest::query()->create([
            'phone' => $validated['phone'],
        ]);

        $eventLogger->log($request, 'phone_saved', 'success_result', [], $phoneRequest->id);

        $telegramSent = $telegramPhoneNotifier->sendPhoneNumber($phoneRequest->phone);
        $eventLogger->log(
            $request,
            $telegramSent ? 'telegram_notification_sent' : 'telegram_notification_failed',
            'success_result',
            [],
            $phoneRequest->id,
        );

        $request->session()->put('survey.completed', 'success');
        $request->session()->forget('survey.started');

        return to_route('home');
    }
}
