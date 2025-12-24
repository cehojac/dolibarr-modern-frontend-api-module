<?php
/**
 * Script de prueba para subir documentos a tareas via API
 * 
 * IMPORTANTE: Configurar las variables antes de ejecutar
 */

// ============================================
// CONFIGURACIÓN - EDITAR ESTOS VALORES
// ============================================
$API_URL = 'http://localhost:8080/api/index.php';
$DOLAPIKEY = 'tu_api_key_aqui';
$TASK_REF = 'T001'; // Cambiar por una referencia de tarea válida

// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Realiza una petición API
 */
function apiRequest($url, $method, $apiKey, $data = null) {
    $ch = curl_init();
    
    $headers = [
        'DOLAPIKEY: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

/**
 * Muestra resultado de forma bonita
 */
function showResult($testName, $result) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "TEST: {$testName}\n";
    echo str_repeat('=', 60) . "\n";
    echo "HTTP Status: {$result['code']}\n";
    echo "Response:\n";
    echo json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if ($result['code'] >= 200 && $result['code'] < 300) {
        echo "✓ SUCCESS\n";
    } else {
        echo "✗ FAILED\n";
    }
}

// ============================================
// TESTS
// ============================================

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST DE SUBIDA DE DOCUMENTOS A TAREAS VIA API            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

// ============================================
// TEST 1: Subir archivo de texto plano
// ============================================
$textFileData = [
    'filename' => 'notas_tarea_' . date('YmdHis') . '.txt',
    'modulepart' => 'project_task',
    'ref' => $TASK_REF,
    'subdir' => '',
    'filecontent' => "Este es un archivo de prueba creado el " . date('Y-m-d H:i:s') . "\n\n" .
                     "Contenido:\n" .
                     "- Línea 1\n" .
                     "- Línea 2\n" .
                     "- Línea 3\n\n" .
                     "Fin del documento.",
    'fileencoding' => '',
    'overwriteifexists' => 0,
    'createdirifnotexists' => 1
];

$result1 = apiRequest(
    $API_URL . '/documents/upload',
    'POST',
    $DOLAPIKEY,
    $textFileData
);

showResult('1. Subir archivo de texto plano', $result1);

// ============================================
// TEST 2: Subir archivo CSV
// ============================================
$csvContent = "Nombre,Apellido,Email\n" .
              "Juan,Pérez,juan@example.com\n" .
              "María,García,maria@example.com\n" .
              "Pedro,López,pedro@example.com";

$csvFileData = [
    'filename' => 'datos_' . date('YmdHis') . '.csv',
    'modulepart' => 'project_task',
    'ref' => $TASK_REF,
    'subdir' => '',
    'filecontent' => $csvContent,
    'fileencoding' => '',
    'overwriteifexists' => 0,
    'createdirifnotexists' => 1
];

$result2 = apiRequest(
    $API_URL . '/documents/upload',
    'POST',
    $DOLAPIKEY,
    $csvFileData
);

showResult('2. Subir archivo CSV', $result2);

// ============================================
// TEST 3: Subir archivo con contenido base64
// ============================================
// Crear un pequeño PDF de ejemplo
$pdfContent = "%PDF-1.4\n" .
              "%âãÏÓ\n" .
              "1 0 obj\n<</Type /Catalog /Pages 2 0 R>>\nendobj\n" .
              "2 0 obj\n<</Type /Pages /Kids [3 0 R] /Count 1>>\nendobj\n" .
              "3 0 obj\n<</Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources <</Font <</F1 5 0 R>>>>>>\nendobj\n" .
              "4 0 obj\n<</Length 44>>\nstream\nBT\n/F1 24 Tf\n100 700 Td\n(Documento de prueba) Tj\nET\nendstream\nendobj\n" .
              "5 0 obj\n<</Type /Font /Subtype /Type1 /BaseFont /Helvetica>>\nendobj\n" .
              "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000262 00000 n \n0000000355 00000 n \n" .
              "trailer\n<</Size 6 /Root 1 0 R>>\nstartxref\n439\n%%EOF";

$pdfBase64 = base64_encode($pdfContent);

$pdfFileData = [
    'filename' => 'documento_' . date('YmdHis') . '.pdf',
    'modulepart' => 'project_task',
    'ref' => $TASK_REF,
    'subdir' => '',
    'filecontent' => $pdfBase64,
    'fileencoding' => 'base64',
    'overwriteifexists' => 0,
    'createdirifnotexists' => 1
];

$result3 = apiRequest(
    $API_URL . '/documents/upload',
    'POST',
    $DOLAPIKEY,
    $pdfFileData
);

showResult('3. Subir archivo PDF (base64)', $result3);

// ============================================
// RESUMEN
// ============================================
echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN DE TESTS                                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

$tests = [
    'Texto plano' => $result1['code'],
    'CSV' => $result2['code'],
    'PDF (base64)' => $result3['code']
];

foreach ($tests as $test => $code) {
    $status = ($code >= 200 && $code < 300) ? '✓ PASS' : '✗ FAIL';
    printf("%-20s : %s (HTTP %d)\n", $test, $status, $code);
}

echo "\n";
