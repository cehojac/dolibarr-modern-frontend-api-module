<?php
/**
 * Simple test to check email templates API response structure
 */

// Configuration
$DOLIBARR_URL = 'http://localhost';
$API_KEY = 'YOUR_API_KEY_HERE';

$endpoint = $DOLIBARR_URL . '/api/index.php/dolibarrmodernfrontend/emailtemplates';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $API_KEY,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');
echo json_encode(array(
    'http_code' => $http_code,
    'response' => json_decode($response, true),
    'raw_response' => $response
), JSON_PRETTY_PRINT);
?>
