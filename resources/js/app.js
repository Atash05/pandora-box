const mainQuestion = document.querySelector('#main-question');
const boxScreen = document.querySelector('#box-screen');
const phoneScreen = document.querySelector('#phone-screen');
const noScreen = document.querySelector('#no-screen');
const pandoraBox = document.querySelector('#pandora-box');
const yesButton = document.querySelector('#yes-button');
const noButton = document.querySelector('#no-button');
const tinyNote = document.querySelector('#tiny-note');
const pageShell = document.querySelector('.page-shell');
const escapeLayer = document.querySelector('#escape-layer');
const isSurveyActive = pageShell?.dataset.surveyActive === 'true';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const questions = [
    {
        eyebrow: 'no pressure',
        title: 'Are you sure?',
        answers: ["I don't want to leave my number", "Okay, here's my number"],
    },
    {
        eyebrow: '',
        title: 'Would you think about it once more?',
        answers: ["I've thought it through, and I don't want to leave my number", 'Maybe I can share it'],
    },
    {
        eyebrow: 'one last question',
        title: "Are you sure you won't change your mind?",
        answers: ['Definitely not', "Okay, here's my number"],
    },
];

let dodgeCount = 0;
let questionIndex = 0;
let isFinalTransition = false;
let noButtonAnchor;

function track(action, screen, details = {}) {
    if (!isSurveyActive || !csrfToken) return;

    fetch('/survey/events', {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
        },
        body: JSON.stringify({ action, screen, details }),
    }).catch(() => {});
}

function setMood(mood) {
    if (!pageShell) return;

    pageShell.classList.remove('mood-access', 'mood-box', 'mood-invite', 'mood-hope', 'mood-suspense', 'mood-defeat', 'mood-success');
    pageShell.classList.add(`mood-${mood}`);
}

function markSurveyDeclined() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrfToken) return;

    fetch('/survey/decline', {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
        },
    });
}

function show(screen) {
    [boxScreen, mainQuestion, phoneScreen, noScreen].forEach((element) => {
        if (!element) return;
        element.hidden = element !== screen;
        element.classList.toggle('screen--active', element === screen);
    });
}

function openPandoraBox() {
    if (!pandoraBox || pandoraBox.classList.contains('is-opening')) return;

    track('pandora_box_opened', 'pandora_box');
    pandoraBox.classList.add('is-opening');

    window.setTimeout(() => {
        boxScreen.classList.add('is-leaving');
        pageShell.classList.add('is-opening-question');
    }, 1050);

    window.setTimeout(() => {
        pageShell.classList.add('is-light-revealing');
    }, 1400);

    window.setTimeout(() => {
        setMood('invite');
        show(mainQuestion);
        track('screen_viewed', 'main_question');
        mainQuestion.classList.add('is-arriving');

        window.requestAnimationFrame(() => {
            mainQuestion.classList.remove('is-arriving');
        });
    }, 5550);

    window.setTimeout(() => {
        pageShell.classList.remove('is-opening-question', 'is-light-revealing');
    }, 6200);
}

function openPhoneForm(source) {
    setMood('hope');
    show(phoneScreen);
    track('phone_form_opened', 'phone_form', { source });
    window.setTimeout(() => document.querySelector('#phone')?.focus(), 150);
}

function moveNoButton() {
    if (!pageShell || !escapeLayer) return;

    const currentPosition = noButton.getBoundingClientRect();
    const card = document.querySelector('.card');
    const cardPosition = card.getBoundingClientRect();
    const shellPosition = pageShell.getBoundingClientRect();
    const viewportPadding = 16;
    const cardGap = 18;
    const positions = [];
    const isFirstMove = !noButton.classList.contains('is-escaping');

    const addPositionArea = (minX, maxX, minY, maxY) => {
        if (maxX < minX || maxY < minY) return;

        for (let index = 0; index < 12; index += 1) {
            positions.push({
                x: minX + Math.random() * (maxX - minX),
                y: minY + Math.random() * (maxY - minY),
            });
        }
    };

    addPositionArea(
        shellPosition.left + viewportPadding,
        cardPosition.left - cardGap - currentPosition.width,
        shellPosition.top + viewportPadding,
        shellPosition.bottom - currentPosition.height - viewportPadding,
    );
    addPositionArea(
        cardPosition.right + cardGap,
        shellPosition.right - currentPosition.width - viewportPadding,
        shellPosition.top + viewportPadding,
        shellPosition.bottom - currentPosition.height - viewportPadding,
    );
    addPositionArea(
        shellPosition.left + viewportPadding,
        shellPosition.right - currentPosition.width - viewportPadding,
        shellPosition.top + viewportPadding,
        cardPosition.top - cardGap - currentPosition.height,
    );
    addPositionArea(
        shellPosition.left + viewportPadding,
        shellPosition.right - currentPosition.width - viewportPadding,
        cardPosition.bottom + cardGap,
        shellPosition.bottom - currentPosition.height - viewportPadding,
    );

    const destination = positions.reduce((furthest, candidate) => {
        const candidateDistance = Math.hypot(candidate.x - currentPosition.left, candidate.y - currentPosition.top);
        const furthestDistance = Math.hypot(furthest.x - currentPosition.left, furthest.y - currentPosition.top);

        return candidateDistance > furthestDistance ? candidate : furthest;
    }, positions[0] ?? {
        x: currentPosition.left,
        y: currentPosition.top,
    });

    if (isFirstMove) {
        noButtonAnchor = document.createComment('no-button-anchor');
        noButton.before(noButtonAnchor);
        escapeLayer.append(noButton);
        noButton.classList.add('is-escaping');
        noButton.style.position = 'absolute';
        noButton.style.zIndex = '10';
        noButton.style.width = `${currentPosition.width}px`;
        noButton.style.height = `${currentPosition.height}px`;
        noButton.style.left = `${currentPosition.left - shellPosition.left}px`;
        noButton.style.top = `${currentPosition.top - shellPosition.top}px`;
        noButton.style.transition = 'none';
        noButton.getBoundingClientRect();
    }

    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
            noButton.style.transition = '';
            noButton.style.left = `${destination.x - shellPosition.left}px`;
            noButton.style.top = `${destination.y - shellPosition.top}px`;
        });
    });
}

function restoreNoButton() {
    if (noButtonAnchor) {
        noButtonAnchor.replaceWith(noButton);
        noButtonAnchor = undefined;
    }

    noButton.removeAttribute('style');
    noButton.classList.remove('is-escaping');
}

function renderQuestion() {
    setMood('suspense');
    const current = questions[questionIndex];
    const noStep = document.querySelector('#no-step');
    const noFinalCopy = document.querySelector('#no-final-copy');
    const noEmoji = document.querySelector('#no-screen .emoji-bubble');
    noEmoji.hidden = false;
    noEmoji.textContent = '☁';
    noStep.hidden = !current.eyebrow;
    noStep.textContent = current.eyebrow;
    noFinalCopy.hidden = true;
    document.querySelector('#no-title').textContent = current.title;
    track('screen_viewed', 'decline_question', { question: String(questionIndex + 1) });
    const answers = document.querySelector('#no-answers');
    answers.replaceChildren();

    current.answers.forEach((answer, index) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'answer-button';
        button.textContent = answer;
        button.addEventListener('click', () => {
            track('decline_question_answered', 'decline_question', {
                question: String(questionIndex + 1),
                answer,
            });

            if (index === current.answers.length - 1) {
                openPhoneForm(`question_${questionIndex + 1}_number_choice`);
                return;
            }

            if (questionIndex < questions.length - 1) {
                questionIndex += 1;
                renderQuestion();
                return;
            }

            if (isFinalTransition) return;

            isFinalTransition = true;
            noScreen.classList.add('is-leaving');
            pageShell.classList.add('is-mood-transition');

            window.setTimeout(() => {
                document.querySelector('#no-screen .emoji-bubble').hidden = true;
                document.querySelector('#no-step').hidden = true;
                document.querySelector('#no-title').textContent = 'Thanks for being honest.';
                document.querySelector('#no-final-copy').textContent = 'No number — no messages. 😢';
                document.querySelector('#no-final-copy').hidden = false;
                answers.replaceChildren();
                setMood('defeat');
                markSurveyDeclined();

                noScreen.classList.remove('is-leaving');
                noScreen.classList.add('is-arriving');

                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => {
                        noScreen.classList.remove('is-arriving');
                        pageShell.classList.remove('is-mood-transition');
                    });
                });
            }, 380);
        });
        answers.append(button);
    });
}

yesButton?.addEventListener('click', () => {
    track('initial_question_answered', 'main_question', { answer: 'Yes, of course' });
    openPhoneForm('initial_yes');
});
pandoraBox?.addEventListener('click', openPandoraBox);
document.querySelector('[data-back-to-main]')?.addEventListener('click', () => {
    track('phone_form_back_clicked', 'phone_form');
    setMood('invite');
    show(mainQuestion);
    track('screen_viewed', 'main_question');
});

document.querySelector('.phone-form')?.addEventListener('submit', () => {
    track('phone_form_submitted', 'phone_form');
});

noButton?.addEventListener('click', (event) => {
    if (dodgeCount < 4) {
        event.preventDefault();
        dodgeCount += 1;
        track('initial_no_button_clicked', 'main_question', { attempt: String(dodgeCount) });
        const remaining = 4 - dodgeCount;

        if (remaining > 0) {
            if (tinyNote) {
                tinyNote.hidden = false;
                tinyNote.textContent = ['This button is a little shy…', 'It is trying very hard not to be sad…', 'One more try and I’ll give up.'][dodgeCount - 1];
            }
        } else {
            if (tinyNote) {
                tinyNote.hidden = false;
                tinyNote.textContent = 'Okay, you win. It will not run away anymore.';
            }
        }

        moveNoButton();
        return;
    }

    restoreNoButton();
    track('initial_no_confirmed', 'main_question', { answer: 'No, I won’t' });
    questionIndex = 0;
    renderQuestion();
    show(noScreen);
});

if (isSurveyActive && boxScreen) {
    track('screen_viewed', 'pandora_box');
}
