<?php
/**
 * Test script for ID Professional Validator URL endpoint
 * 
 * Este script prueba el endpoint GET /idprofvalidatorurl
 * que devuelve las URLs de validación de IDs profesionales por país
 */

// Configuración
$dolibarr_url = 'http://localhost:8080'; // Ajusta según tu instalación
$api_key = 'TU_API_KEY_AQUI'; // Reemplaza con tu API key de Dolibarr

echo "=== TEST: ID Professional Validator URL Endpoint ===\n\n";

// Test 1: Verificar que el endpoint responde (país de la empresa por defecto)
echo "Test 1: GET /idprofvalidatorurl (país de la empresa)\n";
echo "-----------------------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dolibarr_url . '/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $api_key,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";

if ($http_code == 200) {
    echo "✅ Endpoint responde correctamente\n\n";
    
    $data = json_decode($response, true);
    
    if ($data && isset($data['validator_urls'])) {
        echo "Modo de filtro: " . $data['filter_mode'] . "\n";
        if (isset($data['company_country_code'])) {
            echo "País de la empresa: {$data['company_country_name']} ({$data['company_country_code']})\n";
        }
        if (isset($data['note'])) {
            echo "Nota: {$data['note']}\n";
        }
        echo "Países disponibles: " . $data['countries_count'] . "\n\n";
        
        // Mostrar información de cada país
        foreach ($data['validator_urls'] as $country_code => $country_data) {
            echo "País: {$country_data['country_name']} ({$country_code})\n";
            
            if (isset($country_data['idprof1'])) {
                $idprof = $country_data['idprof1'];
                echo "  - Tipo ID: {$idprof['name']}\n";
                echo "  - URL Template: {$idprof['url_template']}\n";
                echo "  - Descripción: {$idprof['description']}\n";
                
                // Ejemplo de uso
                $example_id = '';
                switch ($country_code) {
                    case 'FR':
                        $example_id = '123456789';
                        break;
                    case 'ES':
                        $example_id = 'B12345678';
                        break;
                    case 'GB':
                    case 'UK':
                        $example_id = '12345678';
                        break;
                    case 'PT':
                        $example_id = '123456789';
                        break;
                    case 'IN':
                        $example_id = '12345678901';
                        break;
                    case 'DZ':
                        $example_id = '123456789012345';
                        break;
                }
                
                if ($example_id) {
                    $example_url = str_replace('{IDPROF}', $example_id, $idprof['url_template']);
                    echo "  - Ejemplo: $example_url\n";
                }
            }
            echo "\n";
        }
        
        // Mostrar instrucciones de uso
        if (isset($data['usage'])) {
            echo "Instrucciones de uso:\n";
            echo "  {$data['usage']['description']}\n";
            echo "  Ejemplo: {$data['usage']['example']}\n\n";
        }
        
    } else {
        echo "❌ Respuesta no contiene validator_urls\n";
        echo "Respuesta: " . print_r($data, true) . "\n";
    }
    
} else {
    echo "❌ Error en la petición\n";
    echo "Respuesta: $response\n\n";
}

// Test 2: Obtener todos los países
echo "\n\nTest 2: GET /idprofvalidatorurl?all=1 (todos los países)\n";
echo "-----------------------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dolibarr_url . '/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?all=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $api_key,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";

if ($http_code == 200) {
    $data = json_decode($response, true);
    echo "✅ Todos los países obtenidos\n";
    echo "Total de países: " . $data['countries_count'] . "\n";
    echo "Países: " . implode(', ', array_keys($data['validator_urls'])) . "\n";
} else {
    echo "❌ Error: $response\n";
}

// Test 3: Obtener país específico (España)
echo "\n\nTest 3: GET /idprofvalidatorurl?country=ES (España)\n";
echo "-----------------------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dolibarr_url . '/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?country=ES');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $api_key,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";

if ($http_code == 200) {
    $data = json_decode($response, true);
    echo "✅ País específico obtenido\n";
    echo "Modo: " . $data['filter_mode'] . "\n";
    echo "País solicitado: " . $data['requested_country'] . "\n";
    
    if (isset($data['validator_urls']['ES'])) {
        $es = $data['validator_urls']['ES'];
        echo "Nombre: {$es['country_name']}\n";
        echo "ID: {$es['idprof1']['name']}\n";
        echo "URL: {$es['idprof1']['url_template']}\n";
    }
} else {
    echo "❌ Error: $response\n";
}

// Test 4: Intentar obtener país no disponible
echo "\n\nTest 4: GET /idprofvalidatorurl?country=US (país no disponible)\n";
echo "-----------------------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dolibarr_url . '/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?country=US');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'DOLAPIKEY: ' . $api_key,
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";

if ($http_code == 404) {
    echo "✅ Error esperado: País no disponible\n";
    $data = json_decode($response, true);
    echo "Mensaje: " . $data['error']['message'] . "\n";
} else {
    echo "❌ Respuesta inesperada: $response\n";
}

// Ejemplo de uso en JavaScript
echo "\n\n=== Ejemplo de uso en JavaScript ===\n\n";
echo <<<'JAVASCRIPT'
// 1. Obtener URLs del país de la empresa (por defecto)
fetch('http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl', {
    headers: {
        'DOLAPIKEY': 'TU_API_KEY_AQUI'
    }
})
.then(response => response.json())
.then(data => {
    console.log('Modo:', data.filter_mode); // 'company'
    console.log('País de la empresa:', data.company_country_code);
    console.log('Países disponibles:', data.countries_count);
    
    // Obtener la URL del primer país disponible
    const firstCountry = Object.keys(data.validator_urls)[0];
    const countryData = data.validator_urls[firstCountry];
    const idProf = '123456789'; // ID profesional sin espacios
    const validationUrl = countryData.idprof1.url_template.replace('{IDPROF}', idProf);
    
    console.log('URL de validación:', validationUrl);
    window.open(validationUrl, '_blank');
})
.catch(error => console.error('Error:', error));

// 2. Obtener todos los países
fetch('http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?all=1', {
    headers: {
        'DOLAPIKEY': 'TU_API_KEY_AQUI'
    }
})
.then(response => response.json())
.then(data => {
    console.log('Total países:', data.countries_count);
    console.log('Países:', Object.keys(data.validator_urls));
    // ['FR', 'GB', 'UK', 'ES', 'IN', 'DZ', 'PT']
});

// 3. Obtener país específico
fetch('http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?country=ES', {
    headers: {
        'DOLAPIKEY': 'TU_API_KEY_AQUI'
    }
})
.then(response => response.json())
.then(data => {
    console.log('Modo:', data.filter_mode); // 'specific'
    console.log('País solicitado:', data.requested_country); // 'ES'
    
    const esData = data.validator_urls.ES;
    const nif = 'B12345678';
    const url = esData.idprof1.url_template.replace('{IDPROF}', nif);
    console.log('URL:', url);
});

// 4. Función genérica para obtener URL de validación
async function getValidatorUrl(countryCode, idProf) {
    // Si no se especifica país, usa el de la empresa
    const url = countryCode 
        ? `http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?country=${countryCode}`
        : 'http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl';
    
    const response = await fetch(url, {
        headers: { 'DOLAPIKEY': 'TU_API_KEY_AQUI' }
    });
    
    const data = await response.json();
    
    // Obtener el primer país disponible
    const firstCountry = Object.keys(data.validator_urls)[0];
    const countryData = data.validator_urls[firstCountry];
    
    if (countryData && countryData.idprof1) {
        const cleanId = idProf.replace(/\s/g, '');
        return countryData.idprof1.url_template.replace('{IDPROF}', cleanId);
    }
    return null;
}

// Uso de la función
getValidatorUrl('ES', 'B12345678').then(url => {
    console.log('URL de validación:', url);
    // http://www.e-informa.es/servlet/app/portal/ENTP/screen/SProducto/prod/ETIQUETA_EMPRESA/nif/B12345678
});

// Uso sin especificar país (usa el de la empresa)
getValidatorUrl(null, '123456789').then(url => {
    console.log('URL de validación (país empresa):', url);
});
JAVASCRIPT;

echo "\n\n=== Ejemplo de uso en cURL ===\n\n";
echo <<<'CURL'
# 1. Obtener URLs del país de la empresa (por defecto)
curl -X GET \
  "http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl" \
  -H "DOLAPIKEY: TU_API_KEY_AQUI" \
  -H "Content-Type: application/json"

# 2. Obtener todos los países
curl -X GET \
  "http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?all=1" \
  -H "DOLAPIKEY: TU_API_KEY_AQUI"

# 3. Obtener país específico (España)
curl -X GET \
  "http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?country=ES" \
  -H "DOLAPIKEY: TU_API_KEY_AQUI"

# 4. Procesar respuesta con jq (opcional)
curl -X GET \
  "http://localhost:8080/api/index.php/dolibarrmodernfrontend/idprofvalidatorurl?country=FR" \
  -H "DOLAPIKEY: TU_API_KEY_AQUI" \
  | jq '.validator_urls.FR.idprof1.url_template'

CURL;

echo "\n\n=== Países soportados ===\n\n";
echo "FR - France (SIREN)\n";
echo "GB/UK - United Kingdom (Company Number)\n";
echo "ES - Spain (NIF/CIF)\n";
echo "IN - India (TIN)\n";
echo "DZ - Algeria (NIF)\n";
echo "PT - Portugal (NIF)\n";

echo "\n=== Fin del test ===\n";
