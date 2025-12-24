<?php
/**
 * Test script for email API endpoint
 * 
 * This script tests the sendTicketEmail endpoint to verify it works correctly
 * after fixing the 401 permission issue.
 */

// Configuration
$dolibarr_url = 'https://gestion.carlos-herrera.consulting';
$api_key = 'YOUR_API_KEY_HERE'; // Replace with actual API key
$ticket_id = 456; // Replace with actual ticket ID

// Test data
$email_data = array(
    'subject' => 'Re: Ticket #456',
    'message' => '<p>Hola, he revisado el ticket y necesito más información.</p>',
    'recipients' => array('cliente@empresa.com'),
    'attachments' => array()
);

// API endpoint
$endpoint = $dolibarr_url . '/api/index.php/dolibarmodernfrontendapi/tickets/' . $ticket_id . '/sendemail';

// Headers
$headers = array(
    'Content-Type: application/json',
    'Accept: application/json',
    'DOLAPIKEY: ' . $api_key
);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute request
echo "Testing email API endpoint...\n";
echo "URL: " . $endpoint . "\n";
echo "Data: " . json_encode($email_data, JSON_PRETTY_PRINT) . "\n\n";

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

curl_close($ch);

// Display results
echo "HTTP Code: " . $http_code . "\n";

if ($curl_error) {
    echo "cURL Error: " . $curl_error . "\n";
} else {
    echo "Response:\n";
    $response_data = json_decode($response, true);
    if ($response_data) {
        echo json_encode($response_data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
}

echo "\n=== DIAGNOSIS ===\n";

if ($http_code == 401) {
    echo "❌ Still getting 401 Unauthorized\n";
    echo "Possible causes:\n";
    echo "1. API key is invalid or expired\n";
    echo "2. User doesn't have ticket write permissions\n";
    echo "3. Module permissions still being checked incorrectly\n";
    echo "\nSolutions:\n";
    echo "1. Verify API key is correct\n";
    echo "2. Check user has 'Tickets' module write permissions\n";
    echo "3. Assign dolibarmodernfrontend module permissions to user\n";
} elseif ($http_code == 404) {
    echo "❌ Ticket not found\n";
    echo "Solution: Use a valid ticket ID that exists in the system\n";
} elseif ($http_code == 200 || $http_code == 201) {
    echo "✅ Success! Email API is working correctly\n";
} else {
    echo "⚠️  Unexpected response code: " . $http_code . "\n";
    echo "Check the response details above for more information\n";
}

echo "\n=== INSTRUCTIONS ===\n";
echo "1. Replace 'YOUR_API_KEY_HERE' with your actual Dolibarr API key\n";
echo "2. Replace ticket_id (456) with a valid ticket ID from your system\n";
echo "3. Replace recipient email with a valid test email address\n";
echo "4. Run this script: php test_email_api.php\n";
?>
