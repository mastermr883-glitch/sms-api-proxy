<?php
ob_start(); // এই লাইনটি হেডার এরর দূর করবে
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$user_key = isset($_GET['api_key']) ? $_GET['api_key'] : (isset($input['api_key']) ? $input['api_key'] : '');

$allowed_key = getenv('SUB_API_KEY');
$main_api_url = getenv('MAIN_API_URL');
$main_api_key = getenv('MAIN_API_KEY');

if (!$allowed_key || !$main_api_url || !$main_api_key) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Environment variables are not configured in Vercel."]);
    exit();
}

if ($user_key !== $allowed_key) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid API Key"]);
    exit();
}

$query_params = $_GET;
$query_params['api_key'] = $main_api_key;
$target_url = rtrim($main_api_url, '/') . "/liveaccess?" . http_build_query($query_params);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
http_response_code($http_code);
echo $response;
curl_close($ch);
?>
