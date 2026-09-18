<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#fff5fa">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('pandora-box.svg') }}">
        <title>Pandora’s Box</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="page-shell {{ session('survey.completed') === 'success' ? 'mood-success' : (session('survey.completed') === 'declined' ? 'mood-defeat' : (! session('survey.started') ? 'mood-access' : 'mood-box')) }}" data-survey-active="{{ session('survey.started') ? 'true' : 'false' }}">
            <div class="orb orb--one"></div>
            <div class="orb orb--two"></div>
            <div class="sparkles" aria-hidden="true">
                <span>✦</span><span>·</span><span>✦</span><span>·</span><span>✦</span>
            </div>

            <section class="card" aria-live="polite">
                @if (session('survey.completed') === 'success')
                    <div class="result result--success">
                        <h1>Thank you 😊</h1>
                    </div>
                @elseif (session('survey.completed') === 'declined')
                    <div class="result result--declined">
                        <h1>Thanks for being honest.</h1>
                        <p>No number — no messages. 😢</p>
                    </div>
                @elseif (! session('survey.started'))
                    <div class="access-screen">
                        <p class="eyebrow">private question</p>
                        <h1>Enter the code to begin</h1>
                        <p class="intro">This little page is waiting for the right person.</p>

                        <form class="access-form" method="POST" action="{{ route('survey.start') }}">
                            @csrf
                            <label for="start-code">Access code</label>
                            <input id="start-code" name="start_code" type="password" autocomplete="one-time-code" required autofocus>
                            @error('start_code')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <button class="button button--primary button--wide" type="submit">Begin <span>→</span></button>
                        </form>
                    </div>
                @else
                    <div id="box-screen" class="screen screen--active screen--box">
                        <p class="eyebrow">one last step</p>
                        <h1>Pandora’s Box</h1>
                        <p class="intro">Tap the box to open the question inside.</p>

                        <button id="pandora-box" class="pandora-box" type="button" aria-label="Open Pandora’s Box">
                            <span class="pandora-box__glow"></span>
                            <span class="pandora-box__lid"><span></span></span>
                            <span class="pandora-box__base"><span></span></span>
                        </button>
                        <p class="box-prompt">Tap to open</p>
                    </div>

                    <div id="main-question" class="screen screen--intro" hidden>
                        <p class="eyebrow">a small question</p>
                        <h1>Will you give me your number?</h1>
                        <p class="intro">I’d like to send you a nice message sometimes — only if you’re okay with it.</p>

                        <div class="actions">
                            <button id="yes-button" class="button button--primary" type="button">Yes, of course</button>
                            <button id="no-button" class="button button--quiet" type="button">No, I won’t</button>
                        </div>
                        <p id="tiny-note" class="tiny-note" hidden></p>
                    </div>

                    <div id="phone-screen" class="screen" hidden>
                        <div class="emoji-bubble" aria-hidden="true">✿</div>
                        <p class="eyebrow">thank you</p>
                        <h1>Then leave your number</h1>
                        <form class="phone-form" method="POST" action="{{ route('phone-requests.store') }}">
                            @csrf
                            <label for="phone">Your phone number</label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+993 6_ ______" value="{{ old('phone') }}" required autofocus>
                            @error('phone')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <button class="button button--primary button--wide" type="submit">Send my number <span>→</span></button>
                        </form>
                        <button class="text-button" type="button" data-back-to-main>Go back</button>
                    </div>

                    <div id="no-screen" class="screen" hidden>
                        <div class="emoji-bubble emoji-bubble--soft" aria-hidden="true">☁</div>
                        <p id="no-step" class="eyebrow">no pressure</p>
                        <h1 id="no-title">Are you sure?</h1>
                        <p id="no-final-copy" class="intro" hidden></p>
                        <div id="no-answers" class="answer-stack"></div>
                    </div>
                @endif
            </section>
            <div id="escape-layer"></div>
        </main>
    </body>
</html>
