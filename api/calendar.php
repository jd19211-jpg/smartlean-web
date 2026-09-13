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
        'scope' => 'https://www.googleapis.com/auth/calendar.events',
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

/**
 * Creates an event on Igor's calendar. Returns ['ok' => true, 'eventId' => string, 'htmlLink' => string]
 * on success, ['ok' => false, 'reason' => string] on failure.
 */
function create_calendar_event($date, $startTime, $endTime, $summary, $description) {
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
        $startDt = new DateTime($date . 'T' . $startTime . ':00', $tz);
        $endDt = new DateTime($date . 'T' . $endTime . ':00', $tz);
    } catch (Exception $e) {
        return ['ok' => false, 'reason' => 'invalid_input'];
    }

    $payload = json_encode([
        'summary' => $summary,
        'description' => $description,
        'start' => ['dateTime' => $startDt->format(DateTime::RFC3339), 'timeZone' => $timeZone],
        'end' => ['dateTime' => $endDt->format(DateTime::RFC3339), 'timeZone' => $timeZone]
    ]);

    $ch = curl_init('https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode(GOOGLE_CALENDAR_ID) . '/events');
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
        return ['ok' => false, 'reason' => 'event_insert_failed'];
    }

    $data = json_decode($response, true);
    if (empty($data['id'])) {
        return ['ok' => false, 'reason' => 'event_insert_failed'];
    }

    return ['ok' => true, 'eventId' => $data['id'], 'htmlLink' => $data['htmlLink'] ?? null];
}

/**
 * Builds a minimal iCalendar (.ics) REQUEST body for a meeting invite.
 */
function build_ics_invite($date, $startTime, $endTime, $summary, $description, $organizerEmail, $attendeeEmail) {
    $timeZone = defined('CALENDAR_TIMEZONE') && CALENDAR_TIMEZONE !== '' ? CALENDAR_TIMEZONE : 'Europe/Bratislava';
    $tz = new DateTimeZone($timeZone);
    $utc = new DateTimeZone('UTC');

    $startDt = (new DateTime($date . 'T' . $startTime . ':00', $tz))->setTimezone($utc);
    $endDt = (new DateTime($date . 'T' . $endTime . ':00', $tz))->setTimezone($utc);
    $stamp = (new DateTime('now', $utc));

    $esc = function ($text) {
        return str_replace(["\\", "\n", ",", ";"], ["\\\\", "\\n", "\\,", "\\;"], (string) $text);
    };

    $uid = uniqid('smartlean-', true) . '@baterierychle.cz';

    $lines = [
        'BEGIN:VCALENDAR',
        'PRODID:-//SmartLean//Chat Booking//EN',
        'VERSION:2.0',
        'CALSCALE:GREGORIAN',
        'METHOD:REQUEST',
        'BEGIN:VEVENT',
        'UID:' . $uid,
        'DTSTAMP:' . $stamp->format('Ymd\THis\Z'),
        'DTSTART:' . $startDt->format('Ymd\THis\Z'),
        'DTEND:' . $endDt->format('Ymd\THis\Z'),
        'SUMMARY:' . $esc($summary),
        'DESCRIPTION:' . $esc($description),
        'ORGANIZER;CN=Igor:mailto:' . $organizerEmail,
        'ATTENDEE;CN=' . $esc($attendeeEmail) . ';RSVP=TRUE:mailto:' . $attendeeEmail,
        'STATUS:CONFIRMED',
        'SEQUENCE:0',
        'END:VEVENT',
        'END:VCALENDAR'
    ];

    return implode("\r\n", $lines) . "\r\n";
}
