<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$API_BASE = 'https://www.telematicsadvance.com/api/v1';
$API_KEY  = '46647d88fc386c40f85f34eda7dd6f9dcf496094';

$allowed = [
    'unit/list'               => 'GET',
    'unit_data/can_period'    => 'GET',
    'unit_data/can_point'     => 'GET',
    'unit_data/history_point' => 'GET'
];

$action = $_GET['action'] ?? null;
if (!$action || !isset($allowed[$action])) {
    http_response_code(400);
    echo json_encode(['error' => 'Acción no permitida']);
    exit;
}

$params = $_GET;
unset($params['action']);
$params['key'] = $API_KEY;

$url = $API_BASE . '/' . $action . '.json?' . http_build_query($params);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => ['Accept: application/json']
]);

$resp = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$err = curl_error($ch);
curl_close($ch);

http_response_code($status ?: 502);
echo $err ? json_encode(['error' => $err]) : $resp;
