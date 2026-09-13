<?php

function google_service_account_credentials() {
    $file = __DIR__ . '/google-service-account.json';
    if (!is_readable($file)) {
        return null;
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data) || empty($data['client_email']) || empty($data['private_key'])) {
        return null;
    }
    return $data;
}

function google_calendar_access_token() {
    $creds = google_service_account_credentials();
    if ($creds === null) {
        return null;
    }

    $now = time();
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claims = [
        'iss' => $creds['client_email'],
        'scope' => 'https://www.googleapis.com/auth/calendar.readonly',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ];

    $base64url = function ($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    };

    $segments = $base64url(json_encode($header)) . '.' . $base64url(json_encode($claims));

    $signature = '';
    $signed = openssl_sign($segments, $signature, $creds['private_key'], 'sha256WithRSAEncryption');
    if (!$signed) {
        return null;
    }

    $jwt = $segments . '.' . $base64url($signature);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]),
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

/**
 * Returns ['ok' => true, 'available' => bool] on success, ['ok' => false, 'reason' => string] on failure.
 */
function check_calendar_availability($date, $startTime, $endTime) {
    if (!defined('GOOGLE_CALENDAR_ID') || GOOGLE_CALENDAR_ID === '') {
        return ['ok' => false, 'reason' => 'not_configured'];
    }

    $accessToken = google_calendar_access_token();
    if ($accessToken === null) {
        return ['ok' => false, 'reason' => 'auth_failed'];
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)
        || !preg_match('/^\d{2}:\d{2}$/', (string) $startTime)
        || !preg_match('/^\d{2}:\d{2}$/', (string) $endTime)) {
        return ['ok' => false, 'reason' => 'invalid_input'];
    }

    $timeZone = defined('CALENDAR_TIMEZONE') && CALENDAR_TIMEZONE !== '' ? CALENDAR_TIMEZONE : 'Europe/Bratislava';

    try {
        $tz = new DateTimeZone($timeZone);
        $minDt = new DateTime($date . 'T' . $startTime . ':00', $tz);
        $maxDt = new DateTime($date . 'T' . $endTime . ':00', $tz);
    } catch (Exception $e) {
        return ['ok' => false, 'reason' => 'invalid_input'];
    }

    $timeMin = $minDt->format(DateTime::RFC3339);
    $timeMax = $maxDt->format(DateTime::RFC3339);

    $payload = json_encode([
        'timeMin' => $timeMin,
        'timeMax' => $timeMax,
        'timeZone' => $timeZone,
        'items' => [['id' => GOOGLE_CALENDAR_ID]]
    ]);

    $ch = curl_init('https://www.googleapis.com/calendar/v3/freeBusy');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return ['ok' => false, 'reason' => 'freebusy_request_failed'];
    }

    $data = json_decode($response, true);
    $busy = $data['calendars'][GOOGLE_CALENDAR_ID]['busy'] ?? null;

    if ($busy === null) {
        return ['ok' => false, 'reason' => 'calendar_not_shared_or_not_found'];
    }

    return ['ok' => true, 'available' => count($busy) === 0];
}
