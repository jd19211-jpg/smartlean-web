<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['error' => 'Fatal PHP error', 'detail' => $error]);
    }
});

header('Content-Type: application/json; charset=utf-8');

function client_ip() {
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded !== '') {
        $parts = explode(',', $forwarded);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function rate_limit_ok($ip, $limit, $windowSeconds) {
    $file = __DIR__ . '/data/ratelimit.json';
    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        return true;
    }
    flock($fp, LOCK_EX);
    $contents = stream_get_contents($fp);
    $data = json_decode((string) $contents, true);
    if (!is_array($data)) {
        $data = [];
    }
    $now = time();
    $timestamps = $data[$ip] ?? [];
    $timestamps = array_values(array_filter($timestamps, function ($t) use ($now, $windowSeconds) {
        return ($now - $t) < $windowSeconds;
    }));
    $allowed = count($timestamps) < $limit;
    if ($allowed) {
        $timestamps[] = $now;
    }
    $data[$ip] = $timestamps;
    if (count($data) > 500) {
        $data = array_slice($data, -200, null, true);
    }
    rewind($fp);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $allowed;
}

function daily_email_cap_ok($limit) {
    $file = __DIR__ . '/data/emailcount.json';
    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        return true;
    }
    flock($fp, LOCK_EX);
    $contents = stream_get_contents($fp);
    $data = json_decode((string) $contents, true);
    $today = gmdate('Y-m-d');
    if (!is_array($data) || ($data['date'] ?? '') !== $today) {
        $data = ['date' => $today, 'count' => 0];
    }
    $allowed = $data['count'] < $limit;
    if ($allowed) {
        $data['count']++;
    }
    rewind($fp);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $allowed;
}

function save_lead_to_db($email, $lang, $transcriptText, $meeting = null) {
    if (!defined('DB_HOST') || DB_HOST === '' || !defined('DB_NAME') || DB_NAME === '') {
        return false;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $stmt = $pdo->prepare('INSERT INTO leads (email, lang, transcript, meeting_booked, meeting_date, meeting_start, meeting_end, calendar_event_id)
            VALUES (:email, :lang, :transcript, :meeting_booked, :meeting_date, :meeting_start, :meeting_end, :calendar_event_id)
            ON DUPLICATE KEY UPDATE
                lang = VALUES(lang),
                transcript = VALUES(transcript),
                meeting_booked = GREATEST(meeting_booked, VALUES(meeting_booked)),
                meeting_date = COALESCE(VALUES(meeting_date), meeting_date),
                meeting_start = COALESCE(VALUES(meeting_start), meeting_start),
                meeting_end = COALESCE(VALUES(meeting_end), meeting_end),
                calendar_event_id = COALESCE(VALUES(calendar_event_id), calendar_event_id)');
        $stmt->execute([
            'email' => $email,
            'lang' => $lang,
            'transcript' => $transcriptText,
            'meeting_booked' => $meeting !== null ? 1 : 0,
            'meeting_date' => $meeting['date'] ?? null,
            'meeting_start' => $meeting['startTime'] ?? null,
            'meeting_end' => $meeting['endTime'] ?? null,
            'calendar_event_id' => $meeting['eventId'] ?? null
        ]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function save_chat_log($conversationId, $lang, $transcriptText, $email, $meetingBooked) {
    if (!defined('DB_HOST') || DB_HOST === '' || !defined('DB_NAME') || DB_NAME === '') {
        return false;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $stmt = $pdo->prepare('INSERT INTO chat_logs (conversation_id, lang, transcript, email, meeting_booked)
            VALUES (:conversation_id, :lang, :transcript, :email, :meeting_booked)
            ON DUPLICATE KEY UPDATE
                lang = VALUES(lang),
                transcript = VALUES(transcript),
                email = COALESCE(VALUES(email), email),
                meeting_booked = GREATEST(meeting_booked, VALUES(meeting_booked))');
        $stmt->execute([
            'conversation_id' => $conversationId,
            'lang' => $lang,
            'transcript' => $transcriptText,
            'email' => $email,
            'meeting_booked' => $meetingBooked ? 1 : 0
        ]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function gemini_generate_content($systemPrompt, $contents, $tools) {
    $payload = [
        'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
        'contents' => $contents
    ];
    if ($tools !== null) {
        $payload['tools'] = $tools;
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . GEMINI_API_KEY;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return ['response' => $response, 'httpCode' => $httpCode, 'curlError' => $curlError];
}

try {

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/calendar.php';

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
    echo json_encode(['error' => 'Server not configured: missing GEMINI_API_KEY']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$messages = $input['messages'] ?? null;
$lang = ($input['lang'] ?? 'sk') === 'en' ? 'en' : 'sk';
$fromEmail = defined('FROM_EMAIL') && FROM_EMAIL !== '' ? FROM_EMAIL : 'noreply@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
$conversationId = trim((string) ($input['conversationId'] ?? ''));
if ($conversationId === '' || strlen($conversationId) > 64) {
    $conversationId = uniqid('conv-', true);
}

if (!is_array($messages) || count($messages) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'messages array required']);
    exit;
}

if (!rate_limit_ok(client_ip(), 10, 600)) {
    $limitReply = $lang === 'sk'
        ? 'Poslali ste príliš veľa správ naraz. Skúste to prosím o pár minút znova.'
        : "You've sent too many messages at once. Please try again in a few minutes.";
    echo json_encode(['reply' => $limitReply, 'leadCaptured' => false]);
    exit;
}

$systemPromptDefaults = [
    'sk' => "Si AI asistent na webe Igora, konzultanta pre Management, Lean, Six Sigma a Automatizáciu (UiPath, n8n, AI agenti).\nTvoja úloha je stručne a vecne odpovedať na otázky návštevníkov o týchto oblastiach a o tom, ako môže Igor pomôcť ich firme.\nAk návštevník prejaví reálny záujem o spoluprácu, zdvorilo ho požiadaj o email, aby sa mu Igor mohol ozvať.\nAk návštevník navrhne konkrétny deň a čas stretnutia, použi nástroj check_calendar_availability na overenie, či je Igor v tom čase voľný, a podľa výsledku mu odpovedz (potvrď termín, alebo ho popros o iný čas, ak je obsadený).\nOdpovedaj v slovenčine, stručne (max 3-4 vety), profesionálne a priateľsky. Nevymýšľaj si konkrétne ceny, termíny ani referencie, ktoré nepoznáš.",
    'en' => "You are the AI assistant on Igor's website, a consultant for Management, Lean, Six Sigma, and Automation (UiPath, n8n, AI agents).\nYour job is to answer visitor questions about these areas concisely and helpfully, and explain how Igor can help their company.\nIf a visitor shows genuine interest in working together, politely ask for their email so Igor can follow up.\nIf a visitor proposes a specific meeting date and time, use the check_calendar_availability tool to check whether Igor is free then, and reply accordingly (confirm the slot, or ask for another time if it's busy).\nReply in English, concisely (max 3-4 sentences), professional and friendly. Do not invent specific prices, availability, or references you don't know."
];

$promptFile = __DIR__ . '/../prompts/system-' . $lang . '.txt';
$systemPrompt = $systemPromptDefaults[$lang];
if (is_readable($promptFile)) {
    $fileContent = trim((string) file_get_contents($promptFile));
    if ($fileContent !== '') {
        $systemPrompt = $fileContent;
    }
}

$todayLine = ($lang === 'sk' ? 'Dnešný dátum je' : "Today's date is") . ' ' . gmdate('Y-m-d') . ' (' . gmdate('l') . ').';
$systemPrompt .= "\n\n" . $todayLine;

$contents = [];
$transcriptText = '';
foreach ($messages as $m) {
    $role = ($m['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
    $text = mb_substr((string) ($m['text'] ?? ''), 0, 2000);
    $contents[] = ['role' => $role, 'parts' => [['text' => $text]]];
    $who = $role === 'user' ? 'Návštevník' : 'AI';
    $transcriptText .= $who . ': ' . $text . "\n";
}

$tools = [[
    'functionDeclarations' => [
        [
            'name' => 'check_calendar_availability',
            'description' => "Check whether Igor is free or busy in his calendar for a specific date and time range. Always use this before confirming a meeting time a visitor proposes.",
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'date' => ['type' => 'string', 'description' => 'Date in YYYY-MM-DD format'],
                    'startTime' => ['type' => 'string', 'description' => 'Start time in 24-hour HH:MM format'],
                    'endTime' => ['type' => 'string', 'description' => 'End time in 24-hour HH:MM format (typically 30-60 minutes after startTime)']
                ],
                'required' => ['date', 'startTime', 'endTime']
            ]
        ],
        [
            'name' => 'schedule_meeting',
            'description' => "Book the meeting: creates the event in Igor's calendar and sends the visitor a calendar invite by email. Only call this after (1) check_calendar_availability confirmed the slot is free, (2) you have the visitor's email address, AND (3) the visitor has explicitly confirmed they want to book that slot (do not call this right after just checking availability -- ask the visitor first and wait for a clear yes).",
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'date' => ['type' => 'string', 'description' => 'Date in YYYY-MM-DD format'],
                    'startTime' => ['type' => 'string', 'description' => 'Start time in 24-hour HH:MM format'],
                    'endTime' => ['type' => 'string', 'description' => 'End time in 24-hour HH:MM format'],
                    'email' => ['type' => 'string', 'description' => "Visitor's email address"],
                    'topic' => ['type' => 'string', 'description' => 'Short summary of what the visitor wants to discuss, in a few words']
                ],
                'required' => ['date', 'startTime', 'endTime', 'email']
            ]
        ]
    ]
]];

function schedule_meeting($args, $lang, $fromEmail, $transcriptText) {
    $date = $args['date'] ?? '';
    $startTime = $args['startTime'] ?? '';
    $endTime = $args['endTime'] ?? '';
    $email = trim((string) ($args['email'] ?? ''));
    $topic = trim((string) ($args['topic'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'reason' => 'invalid_email'];
    }

    $summary = $topic !== '' ? ('Konzultácia: ' . $topic) : 'Konzultácia s Igorom';
    $icsDescription = $topic !== ''
        ? ('Stretnutie dohodnuté cez web chat. Téma: ' . $topic . '. Kontakt: ' . $email)
        : ('Stretnutie dohodnuté cez web chat. Kontakt: ' . $email);
    $eventDescription = $icsDescription . "\n\n--- Prepis konverzácie z chatu ---\n" . trim($transcriptText);

    $event = create_calendar_event($date, $startTime, $endTime, $summary, $eventDescription);
    if (!($event['ok'] ?? false)) {
        return $event;
    }

    $leadEmail = defined('LEAD_EMAIL') && LEAD_EMAIL !== '' ? LEAD_EMAIL : $fromEmail;
    $ics = build_ics_invite($date, $startTime, $endTime, $summary, $icsDescription, $leadEmail, $email);

    $visitorSubjects = ['sk' => 'Pozvánka: ' . $summary, 'en' => 'Invitation: ' . $summary];
    $visitorBodies = [
        'sk' => "Dobrý deň,\n\npotvrdzujeme stretnutie s Igorom: {$date} {$startTime}–{$endTime}.\nV prílohe nájdete kalendárovú pozvánku.\n\nS pozdravom,\nIgor",
        'en' => "Hello,\n\nthis confirms the meeting with Igor: {$date} {$startTime}–{$endTime}.\nA calendar invite is attached.\n\nBest regards,\nIgor"
    ];

    $boundary = 'smartlean-' . uniqid();
    $visitorHeaders = "From: Igor <$fromEmail>\r\nContent-Type: multipart/mixed; boundary=\"$boundary\"";
    $visitorBody = "--$boundary\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
        . $visitorBodies[$lang] . "\r\n"
        . "--$boundary\r\n"
        . "Content-Type: text/calendar; charset=UTF-8; method=REQUEST\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $ics . "\r\n"
        . "--$boundary--";
    @mail($email, mb_encode_mimeheader($visitorSubjects[$lang], 'UTF-8'), $visitorBody, $visitorHeaders);

    if (defined('LEAD_EMAIL') && LEAD_EMAIL !== '') {
        $ownerSubject = mb_encode_mimeheader('Nová schôdzka dohodnutá cez chat — ' . $email, 'UTF-8');
        $ownerBody = "Termín: {$date} {$startTime}–{$endTime}\nKontakt: $email\nTéma: " . ($topic !== '' ? $topic : '(neuvedená)') . "\n\nUdalosť bola automaticky vytvorená vo vašom kalendári.";
        $ownerHeaders = "From: Web chat <$fromEmail>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";
        @mail(LEAD_EMAIL, $ownerSubject, $ownerBody, $ownerHeaders);
    }

    return ['ok' => true, 'booked' => true, 'eventId' => $event['eventId'] ?? null];
}

$fallbackReply = $lang === 'sk'
    ? 'Prepáčte, momentálne neviem odpovedať. Skúste to prosím znova.'
    : "Sorry, I couldn't generate a reply. Please try again.";

$reply = null;
$bookedMeeting = null;
$maxToolCalls = 4;

for ($toolRound = 0; $toolRound < $maxToolCalls; $toolRound++) {
    $result = gemini_generate_content($systemPrompt, $contents, $tools);

    if ($result['response'] === false) {
        echo json_encode(['error' => 'Gemini request failed', 'detail' => $result['curlError']]);
        exit;
    }

    $geminiData = json_decode($result['response'], true);

    if ($result['httpCode'] < 200 || $result['httpCode'] >= 300) {
        echo json_encode(['error' => 'Gemini API error', 'detail' => $geminiData]);
        exit;
    }

    $parts = $geminiData['candidates'][0]['content']['parts'] ?? [];
    $functionCall = null;
    $functionCallPart = null;
    foreach ($parts as $part) {
        if (isset($part['functionCall'])) {
            $functionCall = $part['functionCall'];
            $functionCallPart = $part;
        }
        if (isset($part['text'])) {
            $reply = $part['text'];
        }
    }

    if ($functionCall === null) {
        break;
    }

    $fnName = $functionCall['name'] ?? '';
    $args = $functionCall['args'] ?? [];

    if ($fnName === 'check_calendar_availability') {
        $toolResult = check_calendar_availability($args['date'] ?? '', $args['startTime'] ?? '', $args['endTime'] ?? '');
    } elseif ($fnName === 'schedule_meeting') {
        $toolResult = schedule_meeting($args, $lang, $fromEmail, $transcriptText);
        if ($toolResult['ok'] ?? false) {
            $bookedMeeting = [
                'date' => $args['date'] ?? null,
                'startTime' => $args['startTime'] ?? null,
                'endTime' => $args['endTime'] ?? null,
                'eventId' => $toolResult['eventId'] ?? null
            ];
        }
    } else {
        $toolResult = ['ok' => false, 'reason' => 'unknown_function'];
    }

    $contents[] = ['role' => 'model', 'parts' => [$functionCallPart]];
    $contents[] = ['role' => 'user', 'parts' => [['functionResponse' => [
        'name' => $fnName,
        'response' => $toolResult
    ]]]];

    $reply = null;
}

if ($reply === null) {
    $reply = $fallbackReply;
}

$leadCaptured = false;
$visitorEmail = null;
foreach ($messages as $m) {
    if (($m['role'] ?? '') === 'user' && preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/', (string) ($m['text'] ?? ''), $match)) {
        $visitorEmail = $match[0];
        break;
    }
}

// Log every conversation (even without an email), one row per conversation, kept up to date each turn.
save_chat_log($conversationId, $lang, $transcriptText, $visitorEmail, $bookedMeeting !== null);

if ($visitorEmail !== null) {
    // Keep the DB row for this email up to date every turn (full transcript + latest meeting status).
    save_lead_to_db($visitorEmail, $lang, $transcriptText, $bookedMeeting);

    // Only send the "thanks for reaching out" notification the first time this email appears in the conversation.
    $emailMentionedBefore = false;
    foreach (array_slice($messages, 0, -1) as $m) {
        if (($m['role'] ?? '') === 'user' && stripos((string) ($m['text'] ?? ''), $visitorEmail) !== false) {
            $emailMentionedBefore = true;
            break;
        }
    }

    if (!$emailMentionedBefore && daily_email_cap_ok(50)) {
        if (defined('LEAD_EMAIL') && LEAD_EMAIL !== '') {
            $ownerSubject = mb_encode_mimeheader('Nový kontakt z web chatu — ' . $visitorEmail, 'UTF-8');
            $ownerBody = "Email: $visitorEmail\nJazyk: $lang\nČas: " . gmdate('c') . "\n\nPrepis konverzácie:\n$transcriptText";
            $ownerHeaders = "From: Web chat <$fromEmail>\r\nReply-To: $visitorEmail\r\nContent-Type: text/plain; charset=UTF-8";
            $leadCaptured = @mail(LEAD_EMAIL, $ownerSubject, $ownerBody, $ownerHeaders);
        }

        $confirmSubjects = ['sk' => 'Ďakujeme za vašu správu', 'en' => 'Thank you for your message'];
        $confirmBodies = [
            'sk' => "Dobrý deň,\n\nďakujeme za vašu správu cez web chat. Igor sa vám čo najskôr ozve.\n\nS pozdravom,\nIgor",
            'en' => "Hello,\n\nthank you for reaching out via the website chat. Igor will get back to you as soon as possible.\n\nBest regards,\nIgor"
        ];
        $visitorSubject = mb_encode_mimeheader($confirmSubjects[$lang], 'UTF-8');
        $visitorHeaders = "From: Igor <$fromEmail>\r\nContent-Type: text/plain; charset=UTF-8";
        @mail($visitorEmail, $visitorSubject, $confirmBodies[$lang], $visitorHeaders);
    }
}

echo json_encode(['reply' => $reply, 'leadCaptured' => $leadCaptured]);

} catch (Throwable $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'error' => 'Exception',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
