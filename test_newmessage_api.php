<?php
/**
 * Test script for POST /tickets/newmessage endpoint
 * 
 * This script tests the new endpoint that allows creating messages in tickets
 * with a custom user attribution.
 * 
 * USAGE:
 * 1. Configure your API credentials below
 * 2. Set a valid ticket_id and user_id
 * 3. Run: php test_newmessage_api.php
 * 
 * Or access via browser:
 * http://localhost/dolibarr/custom/dolibarrmodernfrontend/test_newmessage_api.php
 */

// ============================================================================
// CONFIGURATION - MODIFY THESE VALUES
// ============================================================================

// Your Dolibarr API configuration
$DOLIBARR_URL = 'http://localhost/dolibarr';  // Your Dolibarr URL (without trailing slash)
$API_KEY = 'YOUR_API_KEY_HERE';                // Your API key (DOLAPIKEY)

// Test parameters
$TICKET_ID = 1;        // ID of the ticket to add message to
$CONTACT_ID = 115;     // ID of the contact who creates the message (0 = API user)
$MESSAGE = 'Este es un mensaje de prueba creado vía API con contacto personalizado';
$PRIVATE = 0;          // 0 = public message, 1 = private message
$SEND_EMAIL = 0;       // 0 = don't send email, 1 = send email notification

// ============================================================================
// TEST EXECUTION
// ============================================================================

echo "=======================================================\n";
echo "TEST: POST /tickets/{ticket_id}/newmessage - Add message to ticket\n";
echo "=======================================================\n\n";

// Build the API endpoint URL
$endpoint = $DOLIBARR_URL . '/api/index.php/dolibarrmodernfrontend/tickets/' . $TICKET_ID . '/newmessage';

// Prepare POST data (ticket_id is now in the URL, not in POST body)
// Note: subject is not needed, it will use the ticket's subject automatically
$postData = array(
    'message' => $MESSAGE,
    'contact_id' => $CONTACT_ID,
    'private' => $PRIVATE,
    'send_email' => $SEND_EMAIL
);

echo "📋 Request Details:\n";
echo "-------------------\n";
echo "Endpoint: $endpoint\n";
echo "Method: POST\n";
echo "Parameters:\n";
echo "  - ticket_id: $TICKET_ID (in URL)\n";
echo "  - contact_id: $CONTACT_ID " . ($CONTACT_ID > 0 ? "(custom contact)" : "(API user)") . "\n";
echo "  - message: " . substr($MESSAGE, 0, 50) . "...\n";
echo "  - private: $PRIVATE " . ($PRIVATE ? "(private)" : "(public)") . "\n";
echo "  - send_email: $SEND_EMAIL " . ($SEND_EMAIL ? "(yes)" : "(no)") . "\n";
echo "  - subject: (uses ticket subject automatically)\n";
echo "\n";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $API_KEY,
    'Content-Type: application/x-www-form-urlencoded'
));

// Execute request
echo "🚀 Sending request...\n\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

// Display results
echo "📊 Response:\n";
echo "------------\n";
echo "HTTP Code: $httpCode\n";

if ($curlError) {
    echo "❌ cURL Error: $curlError\n";
} else {
    echo "\n";
    
    // Try to decode JSON response
    $jsonResponse = json_decode($response, true);
    
    if ($jsonResponse !== null) {
        echo "✅ JSON Response:\n";
        echo json_encode($jsonResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // Display summary
        echo "\n";
        echo "📝 Summary:\n";
        echo "-----------\n";
        
        if (isset($jsonResponse['success']) && $jsonResponse['success']) {
            echo "✅ SUCCESS: Message created successfully!\n";
            echo "   - Message ID: " . ($jsonResponse['message_id'] ?? 'N/A') . "\n";
            echo "   - Ticket ID: " . ($jsonResponse['ticket_id'] ?? 'N/A') . "\n";
            echo "   - Ticket Ref: " . ($jsonResponse['ticket_ref'] ?? 'N/A') . "\n";
            echo "   - Created by: " . ($jsonResponse['created_by_user_name'] ?? 'N/A') . "\n";
            echo "   - User ID: " . ($jsonResponse['created_by_user_id'] ?? 'N/A') . "\n";
            echo "   - User Login: " . ($jsonResponse['created_by_user_login'] ?? 'N/A') . "\n";
            echo "   - Private: " . ($jsonResponse['private'] ? 'Yes' : 'No') . "\n";
            echo "   - Email sent: " . ($jsonResponse['send_email'] ? 'Yes' : 'No') . "\n";
            echo "   - Timestamp: " . ($jsonResponse['timestamp'] ?? 'N/A') . "\n";
        } else {
            echo "❌ FAILED: " . ($jsonResponse['error']['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "⚠️  Raw Response (not JSON):\n";
        echo $response . "\n";
    }
}

echo "\n";
echo "=======================================================\n";
echo "TEST COMPLETED\n";
echo "=======================================================\n";

// ============================================================================
// ADDITIONAL TEST SCENARIOS
// ============================================================================

echo "\n\n";
echo "💡 Additional Test Scenarios:\n";
echo "==============================\n\n";

echo "1️⃣  Test with API user (contact_id = 0):\n";
echo "   Change: \$CONTACT_ID = 0;\n\n";

echo "2️⃣  Test with private message:\n";
echo "   Change: \$PRIVATE = 1;\n\n";

echo "3️⃣  Test with email notification:\n";
echo "   Change: \$SEND_EMAIL = 1;\n\n";


echo "4️⃣  Test with different contact:\n";
echo "   Change: \$CONTACT_ID = 5; // or any valid contact ID\n\n";

echo "5️⃣  Test error handling (invalid ticket):\n";
echo "   Change: \$TICKET_ID = 99999;\n\n";

echo "6️⃣  Test error handling (invalid contact):\n";
echo "   Change: \$CONTACT_ID = 99999;\n\n";

echo "7️⃣  Test with HTML message:\n";
echo "   Change: \$MESSAGE = '<p>This is <strong>HTML</strong> message</p>';\n\n";

// ============================================================================
// CURL COMMAND EXAMPLE
// ============================================================================

echo "\n";
echo "🔧 Equivalent cURL command:\n";
echo "===========================\n";
echo "curl -X POST \\\n";
echo "  '$endpoint' \\\n";
echo "  -H 'DOLAPIKEY: $API_KEY' \\\n";
echo "  -H 'Content-Type: application/x-www-form-urlencoded' \\\n";
echo "  -d 'message=" . urlencode($MESSAGE) . "' \\\n";
echo "  -d 'contact_id=$CONTACT_ID' \\\n";
echo "  -d 'private=$PRIVATE' \\\n";
echo "  -d 'send_email=$SEND_EMAIL'\n";
echo "\n";

// ============================================================================
// JAVASCRIPT FETCH EXAMPLE
// ============================================================================

echo "\n";
echo "📱 JavaScript Fetch Example:\n";
echo "============================\n";
echo "const ticketId = $TICKET_ID;\n";
echo "const response = await fetch(`\${dolibarrUrl}/api/index.php/dolibarrmodernfrontend/tickets/\${ticketId}/newmessage`, {\n";
echo "  method: 'POST',\n";
echo "  headers: {\n";
echo "    'DOLAPIKEY': '$API_KEY',\n";
echo "    'Content-Type': 'application/x-www-form-urlencoded'\n";
echo "  },\n";
echo "  body: new URLSearchParams({\n";
echo "    'message': '" . addslashes($MESSAGE) . "',\n";
echo "    'contact_id': '$CONTACT_ID',\n";
echo "    'private': '$PRIVATE',\n";
echo "    'send_email': '$SEND_EMAIL'\n";
echo "    // Note: subject is not needed, uses ticket subject automatically\n";
echo "  })\n";
echo "});\n";
echo "const data = await response.json();\n";
echo "console.log(data);\n";
echo "\n";
