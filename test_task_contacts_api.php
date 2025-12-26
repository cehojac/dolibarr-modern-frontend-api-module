<?php
/**
 * Test script for Task Contacts API endpoints
 * 
 * Tests the new endpoints:
 * - GET /task/{id}/contacts - Get contacts assigned to a task
 * - POST /task/{id}/assign - Assign a user to a task with a role
 * - DELETE /task/{id}/contacts/{contact_id}/{contact_source} - Remove contact from task
 * 
 * Usage: Access via browser: http://localhost/custom/dolibarrmodernfrontend/test_task_contacts_api.php
 */

// Configuration
define('DOLAPIKEY', 'YOUR_API_KEY_HERE'); // Replace with your actual API key
define('DOLAPIURL', 'http://localhost/api/index.php/dolibarrmodernfrontend');

// Test data - MODIFY THESE VALUES
$test_task_id = 1;        // ID of an existing task
$test_user_id = 1;        // ID of a user to assign
$test_role = 'TASKEXECUTIVE'; // Role to assign (TASKEXECUTIVE, TASKMANAGER, etc.)

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Task Contacts API - Dolibarr Modern Frontend</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            border-left: 4px solid #2196F3;
            padding-left: 10px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        pre {
            background-color: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .button-blue {
            background-color: #2196F3;
        }
        .button-blue:hover {
            background-color: #0b7dda;
        }
        .button-red {
            background-color: #f44336;
        }
        .button-red:hover {
            background-color: #da190b;
        }
        .config-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #4CAF50;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Task Contacts API - Dolibarr Modern Frontend v1.2.5</h1>
        
        <?php if (DOLAPIKEY === 'YOUR_API_KEY_HERE'): ?>
        <div class="config-warning">
            <strong>⚠️ Configuration Required!</strong><br>
            Please edit this file and set your API key and test data:
            <ul>
                <li><code>DOLAPIKEY</code> - Your Dolibarr API key</li>
                <li><code>$test_task_id</code> - ID of an existing task</li>
                <li><code>$test_user_id</code> - ID of a user to assign</li>
                <li><code>$test_role</code> - Role to assign (TASKEXECUTIVE, TASKMANAGER)</li>
            </ul>
        </div>
        <?php else: ?>
        
        <div class="info">
            <strong>📋 Test Configuration:</strong><br>
            <strong>API URL:</strong> <?php echo DOLAPIURL; ?><br>
            <strong>Task ID:</strong> <?php echo $test_task_id; ?><br>
            <strong>User ID:</strong> <?php echo $test_user_id; ?><br>
            <strong>Role:</strong> <?php echo $test_role; ?>
        </div>

        <?php
        // Helper function to make API calls
        function callAPI($method, $endpoint, $data = null) {
            $url = DOLAPIURL . $endpoint;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'DOLAPIKEY: ' . DOLAPIKEY,
                'Content-Type: application/json'
            ));
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            } elseif ($method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            return array(
                'response' => $response,
                'http_code' => $http_code,
                'error' => $error,
                'url' => $url,
                'method' => $method
            );
        }

        // Test 1: Get task contacts
        echo '<h2>Test 1: GET /task/{id}/contacts</h2>';
        echo '<div class="test-section">';
        echo '<p><strong>Endpoint:</strong> GET /task/' . $test_task_id . '/contacts</p>';
        echo '<p><strong>Description:</strong> Get all contacts assigned to the task</p>';
        
        $result = callAPI('GET', '/task/' . $test_task_id . '/contacts');
        
        if ($result['error']) {
            echo '<div class="error">❌ cURL Error: ' . htmlspecialchars($result['error']) . '</div>';
        } else {
            echo '<p><strong>HTTP Status:</strong> ' . $result['http_code'] . '</p>';
            
            if ($result['http_code'] == 200) {
                echo '<div class="success">✅ Success! Task contacts retrieved</div>';
                $data = json_decode($result['response'], true);
                
                if (isset($data['contacts']) && count($data['contacts']) > 0) {
                    echo '<p><strong>Found ' . count($data['contacts']) . ' contact(s):</strong></p>';
                    echo '<table>';
                    echo '<tr><th>User ID</th><th>Name</th><th>Email</th><th>Role</th><th>Source</th></tr>';
                    foreach ($data['contacts'] as $contact) {
                        echo '<tr>';
                        echo '<td>' . ($contact['user_id'] ?? $contact['contact_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($contact['fullname'] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($contact['email'] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($contact['contact_type_code'] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($contact['contact_source'] ?? '') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="info">ℹ️ No contacts assigned to this task yet</div>';
                }
            } else {
                echo '<div class="error">❌ Error: HTTP ' . $result['http_code'] . '</div>';
            }
            
            echo '<p><strong>Response:</strong></p>';
            echo '<pre>' . htmlspecialchars(json_encode(json_decode($result['response']), JSON_PRETTY_PRINT)) . '</pre>';
        }
        echo '</div>';

        // Test 2: Assign user to task
        echo '<h2>Test 2: POST /task/{id}/assign</h2>';
        echo '<div class="test-section">';
        echo '<p><strong>Endpoint:</strong> POST /task/' . $test_task_id . '/assign</p>';
        echo '<p><strong>Description:</strong> Assign a user to the task with a specific role</p>';
        
        $assign_data = array(
            'user_id' => $test_user_id,
            'role' => $test_role
        );
        
        echo '<p><strong>Request Data:</strong></p>';
        echo '<pre>' . htmlspecialchars(json_encode($assign_data, JSON_PRETTY_PRINT)) . '</pre>';
        
        $result = callAPI('POST', '/task/' . $test_task_id . '/assign', $assign_data);
        
        if ($result['error']) {
            echo '<div class="error">❌ cURL Error: ' . htmlspecialchars($result['error']) . '</div>';
        } else {
            echo '<p><strong>HTTP Status:</strong> ' . $result['http_code'] . '</p>';
            
            if ($result['http_code'] == 200) {
                echo '<div class="success">✅ Success! User assigned to task</div>';
                $data = json_decode($result['response'], true);
                
                if (isset($data['user_info'])) {
                    echo '<p><strong>Assigned User:</strong></p>';
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Value</th></tr>';
                    echo '<tr><td>User ID</td><td>' . ($data['user_info']['user_id'] ?? '') . '</td></tr>';
                    echo '<tr><td>Name</td><td>' . htmlspecialchars($data['user_info']['fullname'] ?? '') . '</td></tr>';
                    echo '<tr><td>Email</td><td>' . htmlspecialchars($data['user_info']['email'] ?? '') . '</td></tr>';
                    echo '<tr><td>Role</td><td>' . htmlspecialchars($data['user_info']['role'] ?? '') . '</td></tr>';
                    echo '<tr><td>Element Contact ID</td><td>' . ($data['element_contact_id'] ?? '') . '</td></tr>';
                    echo '</table>';
                }
            } elseif ($result['http_code'] == 409) {
                echo '<div class="info">ℹ️ User already assigned to this task with this role</div>';
            } else {
                echo '<div class="error">❌ Error: HTTP ' . $result['http_code'] . '</div>';
            }
            
            echo '<p><strong>Response:</strong></p>';
            echo '<pre>' . htmlspecialchars(json_encode(json_decode($result['response']), JSON_PRETTY_PRINT)) . '</pre>';
        }
        echo '</div>';

        // Test 3: Get task contacts again (to verify assignment)
        echo '<h2>Test 3: GET /task/{id}/contacts (Verification)</h2>';
        echo '<div class="test-section">';
        echo '<p><strong>Endpoint:</strong> GET /task/' . $test_task_id . '/contacts</p>';
        echo '<p><strong>Description:</strong> Verify that the user was assigned successfully</p>';
        
        $result = callAPI('GET', '/task/' . $test_task_id . '/contacts');
        
        if ($result['error']) {
            echo '<div class="error">❌ cURL Error: ' . htmlspecialchars($result['error']) . '</div>';
        } else {
            echo '<p><strong>HTTP Status:</strong> ' . $result['http_code'] . '</p>';
            
            if ($result['http_code'] == 200) {
                echo '<div class="success">✅ Success! Task contacts retrieved</div>';
                $data = json_decode($result['response'], true);
                
                if (isset($data['contacts']) && count($data['contacts']) > 0) {
                    echo '<p><strong>Found ' . count($data['contacts']) . ' contact(s):</strong></p>';
                    echo '<table>';
                    echo '<tr><th>User ID</th><th>Name</th><th>Email</th><th>Role</th><th>Source</th><th>Element Contact ID</th></tr>';
                    foreach ($data['contacts'] as $contact) {
                        echo '<tr>';
                        echo '<td>' . ($contact['user_id'] ?? $contact['contact_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($contact['fullname'] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($contact['email'] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($contact['contact_type_code'] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($contact['contact_source'] ?? '') . '</td>';
                        echo '<td>' . ($contact['element_contact_id'] ?? '') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="info">ℹ️ No contacts assigned to this task</div>';
                }
            }
            
            echo '<p><strong>Response:</strong></p>';
            echo '<pre>' . htmlspecialchars(json_encode(json_decode($result['response']), JSON_PRETTY_PRINT)) . '</pre>';
        }
        echo '</div>';

        // Test 4: Remove contact from task
        echo '<h2>Test 4: DELETE /task/{id}/contacts/{contact_id}/{contact_source}</h2>';
        echo '<div class="test-section">';
        echo '<p><strong>Endpoint:</strong> DELETE /task/' . $test_task_id . '/contacts/' . $test_user_id . '/internal</p>';
        echo '<p><strong>Description:</strong> Remove the assigned user from the task</p>';
        echo '<p><strong>Note:</strong> This will remove the user we just assigned in Test 2</p>';
        
        $result = callAPI('DELETE', '/task/' . $test_task_id . '/contacts/' . $test_user_id . '/internal');
        
        if ($result['error']) {
            echo '<div class="error">❌ cURL Error: ' . htmlspecialchars($result['error']) . '</div>';
        } else {
            echo '<p><strong>HTTP Status:</strong> ' . $result['http_code'] . '</p>';
            
            if ($result['http_code'] == 200) {
                echo '<div class="success">✅ Success! Contact removed from task</div>';
            } elseif ($result['http_code'] == 404) {
                echo '<div class="info">ℹ️ Contact not found in this task (may have been removed already)</div>';
            } else {
                echo '<div class="error">❌ Error: HTTP ' . $result['http_code'] . '</div>';
            }
            
            echo '<p><strong>Response:</strong></p>';
            echo '<pre>' . htmlspecialchars(json_encode(json_decode($result['response']), JSON_PRETTY_PRINT)) . '</pre>';
        }
        echo '</div>';

        // Summary
        echo '<h2>📊 Test Summary</h2>';
        echo '<div class="test-section">';
        echo '<p><strong>New Endpoints Available:</strong></p>';
        echo '<ul>';
        echo '<li><code>GET /task/{id}/contacts</code> - Get all contacts assigned to a task</li>';
        echo '<li><code>POST /task/{id}/assign</code> - Assign a user to a task with a role</li>';
        echo '<li><code>DELETE /task/{id}/contacts/{contact_id}/{contact_source}</code> - Remove a contact from a task</li>';
        echo '</ul>';
        
        echo '<p><strong>Valid Roles for Tasks:</strong></p>';
        echo '<ul>';
        echo '<li><code>TASKEXECUTIVE</code> - Task executor/worker</li>';
        echo '<li><code>TASKMANAGER</code> - Task manager</li>';
        echo '</ul>';
        
        echo '<p><strong>Notes:</strong></p>';
        echo '<ul>';
        echo '<li>All endpoints use the native Dolibarr system (llx_element_contact table)</li>';
        echo '<li>Supports both internal users and external contacts</li>';
        echo '<li>Prevents duplicate assignments</li>';
        echo '<li>Compatible with Dolibarr\'s native task contact management</li>';
        echo '</ul>';
        echo '</div>';
        ?>
        
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #e8f5e9; border-radius: 5px;">
            <strong>✅ Module Version:</strong> dolibarrmodernfrontend v1.2.5<br>
            <strong>📅 Date:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
            <strong>🔗 Documentation:</strong> <a href="api_doc.php">API Documentation</a>
        </div>
    </div>
</body>
</html>
