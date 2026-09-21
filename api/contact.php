<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
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

function contact_rate_limit_ok($ip, $limit, $windowSeconds) {
    $file = __DIR__ . '/data/contactrate.json';
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

try {

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

require_once __DIR__ . '/config.php';

if (!contact_rate_limit_ok(client_ip(), 5, 600)) {
    echo json_encode(['error' => 'rate_limited']);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$lang = ($_POST['lang'] ?? 'sk') === 'en' ? 'en' : 'sk';

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'invalid_input']);
    exit;
}

if (!defined('LEAD_EMAIL') || LEAD_EMAIL === '') {
    echo json_encode(['error' => 'not_configured']);
    exit;
}

$fromEmail = defined('FROM_EMAIL') && FROM_EMAIL !== '' ? FROM_EMAIL : 'noreply@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);

$subject = mb_encode_mimeheader('Nová správa z webu od ' . $name, 'UTF-8');
$body = "Meno: $name\nEmail: $email\nSpoločnosť: " . ($company !== '' ? $company : '-') . "\n\n$message";
$headers = "From: Web formulár <$fromEmail>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";

$sent = @mail(LEAD_EMAIL, $subject, $body, $headers);

if (!$sent) {
    echo json_encode(['error' => 'send_failed']);
    exit;
}

$confirmSubjects = ['sk' => 'Ďakujeme za vašu správu', 'en' => 'Thank you for your message'];
$confirmBodies = [
    'sk' => "Dobrý deň $name,\n\nďakujeme za vašu správu cez web. Igor sa vám čo najskôr ozve.\n\nS pozdravom,\nIgor",
    'en' => "Hello $name,\n\nthank you for reaching out via the website. Igor will get back to you as soon as possible.\n\nBest regards,\nIgor"
];
$visitorSubject = mb_encode_mimeheader($confirmSubjects[$lang], 'UTF-8');
$visitorHeaders = "From: Igor <$fromEmail>\r\nContent-Type: text/plain; charset=UTF-8";
@mail($email, $visitorSubject, $confirmBodies[$lang], $visitorHeaders);

echo json_encode(['ok' => true]);

} catch (Throwable $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
}
