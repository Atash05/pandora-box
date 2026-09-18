<?php

namespace App\Http\Controllers;

use App\Services\SurveyEventLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyEventController extends Controller
{
    public function store(Request $request, SurveyEventLogger $eventLogger): JsonResponse
    {
        abort_unless(
            $request->session()->get('survey.started') && ! $request->session()->has('survey.completed'),
            403,
        );

        $validated = $request->validate([
            'action' => ['required', 'string', 'max:80'],
            'screen' => ['nullable', 'string', 'max:60'],
            'details' => ['nullable', 'array'],
            'details.*' => ['nullable', 'string', 'max:160'],
        ]);

        $eventLogger->log(
            $request,
            $validated['action'],
            $validated['screen'] ?? null,
            $validated['details'] ?? [],
        );

        return response()->json(['logged' => true], 201);
    }
}
