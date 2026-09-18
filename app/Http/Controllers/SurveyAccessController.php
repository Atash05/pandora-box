<?php

namespace App\Http\Controllers;

use App\Services\SurveyEventLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyAccessController extends Controller
{
    public function start(Request $request, SurveyEventLogger $eventLogger): RedirectResponse
    {
        if ($request->session()->has('survey.completed')) {
            return to_route('home');
        }

        $eventLogger->log($request, 'access_code_submitted', 'access');

        $validated = $request->validate([
            'start_code' => ['required', 'string'],
        ]);

        if (! hash_equals((string) config('survey.start_code'), $validated['start_code'])) {
            $eventLogger->log($request, 'access_code_rejected', 'access');

            return back()->withErrors(['start_code' => 'That code is not correct.']);
        }

        $request->session()->put('survey.started', true);
        $eventLogger->log($request, 'access_granted', 'pandora_box');

        return to_route('home');
    }

    public function decline(Request $request, SurveyEventLogger $eventLogger): JsonResponse
    {
        abort_unless(
            $request->session()->get('survey.started') && ! $request->session()->has('survey.completed'),
            403,
        );

        $eventLogger->log($request, 'survey_declined', 'declined_result');
        $request->session()->put('survey.completed', 'declined');
        $request->session()->forget('survey.started');

        return response()->json(['completed' => true]);
    }
}
