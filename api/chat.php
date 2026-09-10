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

try {

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/config.php';

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
    echo json_encode(['error' => 'Server not configured: missing GEMINI_API_KEY']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$messages = $input['messages'] ?? null;
$lang = ($input['lang'] ?? 'sk') === 'en' ? 'en' : 'sk';

if (!is_array($messages) || count($messages) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'messages array required']);
    exit;
}

$systemPrompts = [
    'sk' => "Si AI asistent na webe Igora, konzultanta pre Management, Lean, Six Sigma a Automatizáciu (UiPath, n8n, AI agenti).\nTvoja úloha je stručne a vecne odpovedať na otázky návštevníkov o týchto oblastiach a o tom, ako môže Igor pomôcť ich firme.\nAk návštevník prejaví reálny záujem o spoluprácu, zdvorilo ho požiadaj o email, aby sa mu Igor mohol ozvať.\nOdpovedaj v slovenčine, stručne (max 3-4 vety), profesionálne a priateľsky. Nevymýšľaj si konkrétne ceny, termíny ani referencie, ktoré nepoznáš.",
    'en' => "You are the AI assistant on Igor's website, a consultant for Management, Lean, Six Sigma, and Automation (UiPath, n8n, AI agents).\nYour job is to answer visitor questions about these areas concisely and helpfully, and explain how Igor can help their company.\nIf a visitor shows genuine interest in working together, politely ask for their email so Igor can follow up.\nReply in English, concisely (max 3-4 sentences), professional and friendly. Do not invent specific prices, availability, or references you don't know."
];

$contents = [];
foreach ($messages as $m) {
    $role = ($m['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
    $text = mb_substr((string) ($m['text'] ?? ''), 0, 2000);
    $contents[] = ['role' => $role, 'parts' => [['text' => $text]]];
}

$payload = json_encode([
    'systemInstruction' => ['parts' => [['text' => $systemPrompts[$lang]]]],
    'contents' => $contents
]);

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 20
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['error' => 'Gemini request failed', 'detail' => $curlError]);
    exit;
}

$geminiData = json_decode($response, true);

if ($httpCode < 200 || $httpCode >= 300) {
    echo json_encode(['error' => 'Gemini API error', 'detail' => $geminiData]);
    exit;
}

$reply = $geminiData['candidates'][0]['content']['parts'][0]['text']
    ?? ($lang === 'sk'
        ? 'Prepáčte, momentálne neviem odpovedať. Skúste to prosím znova.'
        : "Sorry, I couldn't generate a reply. Please try again.");

$leadCaptured = false;
$lastUser = null;
for ($i = count($messages) - 1; $i >= 0; $i--) {
    if (($messages[$i]['role'] ?? '') === 'user') {
        $lastUser = $messages[$i];
        break;
    }
}

if ($lastUser && preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/', (string) ($lastUser['text'] ?? ''), $match)) {
    $visitorEmail = $match[0];
    $fromEmail = defined('FROM_EMAIL') && FROM_EMAIL !== '' ? FROM_EMAIL : 'noreply@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);

    if (defined('LEAD_EMAIL') && LEAD_EMAIL !== '') {
        $transcriptText = '';
        foreach ($messages as $m) {
            $who = ($m['role'] ?? 'user') === 'user' ? 'Návštevník' : 'AI';
            $transcriptText .= $who . ': ' . ($m['text'] ?? '') . "\n";
        }
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
