<?php
header('Content-Type: application/json; charset=utf-8');

$out = [];
$out['php_version'] = phpversion();
$out['curl_loaded'] = extension_loaded('curl');
$out['mbstring_loaded'] = extension_loaded('mbstring');
$out['allow_url_fopen'] = ini_get('allow_url_fopen');
$out['max_execution_time'] = ini_get('max_execution_time');
$out['config_exists'] = file_exists(__DIR__ . '/config.php');

if ($out['config_exists']) {
    require_once __DIR__ . '/config.php';
    $out['gemini_key_set'] = defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '';
    $out['gemini_key_length'] = defined('GEMINI_API_KEY') ? strlen(GEMINI_API_KEY) : 0;
}

if (extension_loaded('curl')) {
    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $resp = curl_exec($ch);
    $out['curl_test_http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $out['curl_test_error'] = curl_error($ch);
    $out['curl_test_response_snippet'] = $resp !== false ? substr($resp, 0, 300) : null;
    curl_close($ch);
}

if (extension_loaded('curl') && $out['gemini_key_set']) {
    $start = microtime(true);
    $url2 = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . GEMINI_API_KEY;
    $payload2 = json_encode([
        'contents' => [['role' => 'user', 'parts' => [['text' => 'Say hi in one word.']]]]
    ]);
    $ch2 = curl_init($url2);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $payload2,
        CURLOPT_TIMEOUT => 25
    ]);
    $resp2 = curl_exec($ch2);
    $out['generate_test_seconds'] = round(microtime(true) - $start, 2);
    $out['generate_test_http_code'] = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $out['generate_test_curl_errno'] = curl_errno($ch2);
    $out['generate_test_curl_error'] = curl_error($ch2);
    $out['generate_test_response_snippet'] = $resp2 !== false ? substr($resp2, 0, 500) : null;
    curl_close($ch2);
}

echo json_encode($out, JSON_PRETTY_PRINT);
