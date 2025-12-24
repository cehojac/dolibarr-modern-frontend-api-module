<?php
/**
 * Test script for Email Templates API endpoint
 * 
 * This script tests the new /emailtemplates endpoint of the dolibarmodernfrontend module
 * 
 * Usage:
 * 1. Configure your API credentials below
 * 2. Run from browser: http://localhost/custom/dolibarmodernfrontend/test_emailtemplates_api.php
 * 3. Or run from command line: php test_emailtemplates_api.php
 */

// Configuration
$DOLIBARR_URL = 'http://localhost';  // Change to your Dolibarr URL
$API_KEY = 'YOUR_API_KEY_HERE';      // Replace with your actual API key

// API endpoint
$endpoint = $DOLIBARR_URL . '/api/index.php/dolibarmodernfrontend/emailtemplates';

echo "<h1>Test: Email Templates API Endpoint</h1>\n";
echo "<hr>\n";

// Test 1: Get all email templates
echo "<h2>Test 1: Get All Email Templates</h2>\n";
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
        echo "Total Templates: " . $data['total_count'] . "\n\n";
        
        if (!empty($data['available_types'])) {
            echo "Available Template Types:\n";
            foreach ($data['available_types'] as $type) {
                echo "  - $type\n";
            }
            echo "\n";
        }
        
        if (!empty($data['available_langs'])) {
            echo "Available Languages:\n";
            foreach ($data['available_langs'] as $lang) {
                echo "  - $lang\n";
            }
            echo "\n";
        }
        
        if (!empty($data['templates'])) {
            echo "First 3 Templates:\n";
            $count = 0;
            foreach ($data['templates'] as $template) {
                if ($count >= 3) break;
                echo "\n  Template #" . ($count + 1) . ":\n";
                echo "    ID: " . $template['id'] . "\n";
                echo "    Label: " . $template['label'] . "\n";
                echo "    Type: " . $template['type_template'] . "\n";
                echo "    Language: " . $template['lang'] . "\n";
                echo "    Subject: " . $template['subject'] . "\n";
                echo "    Private: " . ($template['private'] ? 'Yes' : 'No') . "\n";
                echo "    Enabled: " . ($template['is_enabled'] ? 'Yes' : 'No') . "\n";
                if (!empty($template['variables'])) {
                    echo "    Variables: " . implode(', ', $template['variables']) . "\n";
                }
                $count++;
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

// Test 2: Filter by type (ticket)
echo "<h2>Test 2: Filter by Type (ticket)</h2>\n";
$endpoint_filtered = $endpoint . '?type_template=ticket';
echo "<p><strong>Endpoint:</strong> GET $endpoint_filtered</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint_filtered);
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
        echo "Total Templates (type=ticket): " . $data['total_count'] . "\n\n";
        
        if (!empty($data['templates'])) {
            echo "Ticket Templates:\n";
            foreach ($data['templates'] as $template) {
                echo "\n  - " . $template['label'] . " (ID: " . $template['id'] . ")\n";
                echo "    Subject: " . $template['subject'] . "\n";
                echo "    Language: " . $template['lang'] . "\n";
            }
        } else {
            echo "No ticket templates found\n";
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

// Test 3: Filter by language (es_ES)
echo "<h2>Test 3: Filter by Language (es_ES)</h2>\n";
$endpoint_lang = $endpoint . '?lang=es_ES';
echo "<p><strong>Endpoint:</strong> GET $endpoint_lang</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint_lang);
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
        echo "Total Templates (lang=es_ES): " . $data['total_count'] . "\n\n";
        
        if (!empty($data['templates'])) {
            echo "Spanish Templates:\n";
            foreach ($data['templates'] as $template) {
                echo "\n  - " . $template['label'] . " (Type: " . $template['type_template'] . ")\n";
            }
        } else {
            echo "No Spanish templates found\n";
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

// Test 4: Filter enabled only
echo "<h2>Test 4: Filter Enabled Templates Only</h2>\n";
$endpoint_enabled = $endpoint . '?enabled=1';
echo "<p><strong>Endpoint:</strong> GET $endpoint_enabled</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint_enabled);
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
        echo "Total Enabled Templates: " . $data['total_count'] . "\n";
    } else {
        echo "Error decoding JSON response\n";
        echo $response;
    }
} else {
    echo "No response received\n";
}
echo "</pre>\n";
echo "<hr>\n";

// Test 5: Combined filters
echo "<h2>Test 5: Combined Filters (type=ticket, lang=es_ES, enabled=1)</h2>\n";
$endpoint_combined = $endpoint . '?type_template=ticket&lang=es_ES&enabled=1';
echo "<p><strong>Endpoint:</strong> GET $endpoint_combined</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint_combined);
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
        echo "Filters Applied:\n";
        foreach ($data['filters_applied'] as $key => $value) {
            echo "  - $key: $value\n";
        }
        echo "\nTotal Templates: " . $data['total_count'] . "\n\n";
        
        if (!empty($data['templates'])) {
            echo "Matching Templates:\n";
            foreach ($data['templates'] as $template) {
                echo "\n  Template: " . $template['label'] . "\n";
                echo "    ID: " . $template['id'] . "\n";
                echo "    Subject: " . $template['subject'] . "\n";
                echo "    Content Preview: " . substr($template['content'], 0, 100) . "...\n";
                if (!empty($template['variables'])) {
                    echo "    Variables: " . implode(', ', $template['variables']) . "\n";
                }
            }
        } else {
            echo "No templates match the combined filters\n";
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

echo "<h2>Test Summary</h2>\n";
echo "<p>All tests completed. Check the results above.</p>\n";
echo "<p><strong>Note:</strong> Make sure to configure your API_KEY at the top of this file.</p>\n";
echo "<p><strong>API Documentation:</strong> The endpoint supports the following parameters:</p>\n";
echo "<ul>\n";
echo "  <li><code>type_template</code> - Filter by template type (e.g., 'ticket', 'invoice', 'order')</li>\n";
echo "  <li><code>lang</code> - Filter by language code (e.g., 'es_ES', 'en_US', 'fr_FR')</li>\n";
echo "  <li><code>enabled</code> - Filter by enabled status (0 or 1)</li>\n";
echo "  <li><code>private</code> - Filter by privacy (0=public, 1=private)</li>\n";
echo "</ul>\n";
?>
