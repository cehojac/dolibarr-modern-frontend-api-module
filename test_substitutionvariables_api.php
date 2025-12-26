<?php
/**
 * Test script for Substitution Variables API endpoint
 * 
 * This script tests the new /substitutionvariables endpoint of the dolibarrmodernfrontend module
 * 
 * Usage:
 * 1. Configure your API credentials below
 * 2. Run from browser: http://localhost/custom/dolibarrmodernfrontend/test_substitutionvariables_api.php
 * 3. Or run from command line: php test_substitutionvariables_api.php
 */

// Configuration
$DOLIBARR_URL = 'http://localhost';  // Change to your Dolibarr URL
$API_KEY = 'YOUR_API_KEY_HERE';      // Replace with your actual API key

// API endpoint
$endpoint = $DOLIBARR_URL . '/api/index.php/dolibarrmodernfrontend/substitutionvariables';

echo "<h1>Test: Substitution Variables API Endpoint</h1>\n";
echo "<hr>\n";

// Test 1: Get all substitution variables
echo "<h2>Test 1: Get All Substitution Variables</h2>\n";
echo "<p><strong>Endpoint:</strong> GET $endpoint</p>\n";

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

echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
echo "<pre>";
if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "Message: " . $data['message'] . "\n";
        echo "Total Variables: " . $data['total_variables'] . "\n";
        echo "Context Filter: " . $data['context_filter'] . "\n\n";
        
        if (!empty($data['available_contexts'])) {
            echo "Available Contexts:\n";
            foreach ($data['available_contexts'] as $context) {
                echo "  - $context\n";
            }
            echo "\n";
        }
        
        if (!empty($data['variables_grouped'])) {
            echo "Variables by Category:\n";
            foreach ($data['variables_grouped'] as $category => $vars) {
                echo "\n[$category] (" . count($vars) . " variables):\n";
                $count = 0;
                foreach ($vars as $key => $info) {
                    if ($count < 3) { // Show only first 3 of each category
                        echo "  $key = " . $info['value'] . "\n";
                        echo "    Description: " . $info['description'] . "\n";
                        $count++;
                    }
                }
                if (count($vars) > 3) {
                    echo "  ... and " . (count($vars) - 3) . " more\n";
                }
            }
        }
    } else {
        echo "Error decoding JSON response\n";
        echo $response;
    }
} else {
    echo "No response received\n";
}
echo "</pre>\n";
echo "<hr>\n";

// Test 2: Get user variables only
echo "<h2>Test 2: Get User Variables Only</h2>\n";
$test_url = $endpoint . '?context=user';
echo "<p><strong>Endpoint:</strong> GET $test_url</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $API_KEY,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
echo "<pre>";
if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['variables'])) {
        echo "Total User Variables: " . count($data['variables']) . "\n\n";
        foreach ($data['variables'] as $key => $info) {
            echo "$key = " . $info['value'] . "\n";
            echo "  Description: " . $info['description'] . "\n";
        }
    } else {
        echo $response;
    }
} else {
    echo "No response received\n";
}
echo "</pre>\n";
echo "<hr>\n";

// Test 3: Get company variables only
echo "<h2>Test 3: Get Company Variables Only</h2>\n";
$test_url = $endpoint . '?context=mycompany';
echo "<p><strong>Endpoint:</strong> GET $test_url</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $API_KEY,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
echo "<pre>";
if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['variables'])) {
        echo "Total Company Variables: " . count($data['variables']) . "\n\n";
        foreach ($data['variables'] as $key => $info) {
            echo "$key = " . $info['value'] . "\n";
            echo "  Description: " . $info['description'] . "\n";
        }
    } else {
        echo $response;
    }
} else {
    echo "No response received\n";
}
echo "</pre>\n";
echo "<hr>\n";

// Test 4: Get ticket variables only
echo "<h2>Test 4: Get Ticket Variables Only</h2>\n";
$test_url = $endpoint . '?context=ticket';
echo "<p><strong>Endpoint:</strong> GET $test_url</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $API_KEY,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
echo "<pre>";
if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['variables'])) {
        echo "Total Ticket Variables: " . count($data['variables']) . "\n\n";
        foreach ($data['variables'] as $key => $info) {
            echo "$key = " . $info['value'] . "\n";
            echo "  Description: " . $info['description'] . "\n";
        }
    } else {
        echo $response;
    }
} else {
    echo "No response received\n";
}
echo "</pre>\n";
echo "<hr>\n";

// Test 5: Get datetime variables only
echo "<h2>Test 5: Get Date/Time Variables Only</h2>\n";
$test_url = $endpoint . '?context=datetime';
echo "<p><strong>Endpoint:</strong> GET $test_url</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $API_KEY,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
echo "<pre>";
if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['variables'])) {
        echo "Total Date/Time Variables: " . count($data['variables']) . "\n\n";
        foreach ($data['variables'] as $key => $info) {
            echo "$key = " . $info['value'] . "\n";
            echo "  Description: " . $info['description'] . "\n";
        }
    } else {
        echo $response;
    }
} else {
    echo "No response received\n";
}
echo "</pre>\n";
echo "<hr>\n";

echo "<h2>Summary</h2>\n";
echo "<p>All tests completed. Check the results above.</p>\n";
echo "<p><strong>Note:</strong> Make sure to configure your API_KEY in this file before running the tests.</p>\n";
?>
