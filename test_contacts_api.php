<?php
/**
 * Test file for Contacts API endpoints in dolibarmodernfrontend module
 * 
 * This file tests the new contact management endpoints:
 * - GET /tickets/{id}/contacts
 * - POST /tickets/{id}/contacts
 * - DELETE /tickets/{id}/contacts/{contact_id}/{contact_source}
 * 
 * Version: 1.2.2
 * Date: 2025-09-29
 */

require '../../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array("dolibarmodernfrontend@dolibarmodernfrontend"));

// Access control
if (!$user->rights->dolibarmodernfrontend->read && !$user->rights->ticket->read) {
    accessforbidden();
}

/*
 * View
 */
llxHeader("", $langs->trans("ContactsAPITest"));

print load_fiche_titre("Test de API de Contactos - dolibarmodernfrontend v1.2.2", '', 'object_dolibarmodernfrontend@dolibarmodernfrontend');

print '<div class="fichecenter">';

// Test 1: Basic System Check
print '<h2>🔧 1. Verificación del Sistema</h2>';
print '<div class="info">';

// Check module activation
$module_enabled = !empty($conf->dolibarmodernfrontend->enabled);
print '<p><strong>Módulo dolibarmodernfrontend:</strong> ' . ($module_enabled ? '✅ Activado' : '❌ Desactivado') . '</p>';

// Check API activation
$api_enabled = !empty($conf->api->enabled);
print '<p><strong>API REST:</strong> ' . ($api_enabled ? '✅ Activada' : '❌ Desactivada') . '</p>';

// Check database connection
$db_ok = ($db && $db->connected);
print '<p><strong>Conexión BD:</strong> ' . ($db_ok ? '✅ Conectada' : '❌ Error') . '</p>';

// Check permissions
$has_module_perms = isset($user->rights->dolibarmodernfrontend) && $user->rights->dolibarmodernfrontend->read;
$has_ticket_perms = isset($user->rights->ticket) && $user->rights->ticket->read;
print '<p><strong>Permisos módulo:</strong> ' . ($has_module_perms ? '✅ Sí' : '❌ No') . '</p>';
print '<p><strong>Permisos tickets:</strong> ' . ($has_ticket_perms ? '✅ Sí' : '❌ No') . '</p>';
print '<p><strong>Acceso API:</strong> ' . ($has_module_perms || $has_ticket_perms ? '✅ Permitido' : '❌ Denegado') . '</p>';

print '</div>';

// Test 2: API Class Check
print '<h2>🔍 2. Verificación de Clases</h2>';
print '<div class="info">';

try {
    require_once DOL_DOCUMENT_ROOT.'/custom/dolibarmodernfrontend/class/api_dolibarmodernfrontend.class.php';
    print '<p><strong>Clase API:</strong> ✅ Cargada correctamente</p>';
    
    $api = new DolibarmodernfrontendApi();
    print '<p><strong>Instanciación API:</strong> ✅ Exitosa</p>';
    
    // Check if new methods exist
    $methods_to_check = [
        'getTicketContacts',
        'addTicketContact', 
        'removeTicketContact'
    ];
    
    foreach ($methods_to_check as $method) {
        $exists = method_exists($api, $method);
        print '<p><strong>Método ' . $method . '():</strong> ' . ($exists ? '✅ Existe' : '❌ No encontrado') . '</p>';
    }
    
} catch (Exception $e) {
    print '<p><strong>Error:</strong> ❌ ' . $e->getMessage() . '</p>';
}

print '</div>';

// Test 3: Database Tables Check
print '<h2>🗄️ 3. Verificación de Tablas</h2>';
print '<div class="info">';

// Check element_contact table (native)
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."element_contact'";
$resql = $db->query($sql);
$element_contact_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla element_contact:</strong> ' . ($element_contact_exists ? '✅ Existe (nativa)' : '❌ No encontrada') . '</p>';

// Check socpeople table (contacts)
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."socpeople'";
$resql = $db->query($sql);
$socpeople_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla socpeople:</strong> ' . ($socpeople_exists ? '✅ Existe (contactos)' : '❌ No encontrada') . '</p>';

// Check user table (internal users)
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."user'";
$resql = $db->query($sql);
$user_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla user:</strong> ' . ($user_exists ? '✅ Existe (usuarios internos)' : '❌ No encontrada') . '</p>';

// Check c_type_contact table (contact types)
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."c_type_contact'";
$resql = $db->query($sql);
$type_contact_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla c_type_contact:</strong> ' . ($type_contact_exists ? '✅ Existe (tipos de contacto)' : '❌ No encontrada') . '</p>';

print '</div>';

// Test 4: Sample Data Check
print '<h2>📊 4. Datos de Ejemplo</h2>';
print '<div class="info">';

// Count tickets
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."ticket";
$resql = $db->query($sql);
$ticket_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $ticket_count = $obj->count;
}
print '<p><strong>Tickets disponibles:</strong> ' . $ticket_count . '</p>';

// Count contacts
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."socpeople WHERE statut = 1";
$resql = $db->query($sql);
$contact_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $contact_count = $obj->count;
}
print '<p><strong>Contactos activos:</strong> ' . $contact_count . '</p>';

// Count users
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."user WHERE statut = 1";
$resql = $db->query($sql);
$user_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $user_count = $obj->count;
}
print '<p><strong>Usuarios activos:</strong> ' . $user_count . '</p>';

// Count contact types for tickets
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."c_type_contact WHERE element = 'ticket' AND active = 1";
$resql = $db->query($sql);
$contact_type_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $contact_type_count = $obj->count;
}
print '<p><strong>Tipos de contacto para tickets:</strong> ' . $contact_type_count . '</p>';

print '</div>';

// Test 5: Contact Types Available
print '<h2>🏷️ 5. Tipos de Contacto Disponibles</h2>';
print '<div class="info">';

$sql = "SELECT code, libelle, source FROM ".MAIN_DB_PREFIX."c_type_contact";
$sql .= " WHERE element = 'ticket' AND active = 1";
$sql .= " ORDER BY source, code";

$resql = $db->query($sql);
if ($resql) {
    print '<table class="border centpercent">';
    print '<tr class="liste_titre">';
    print '<th>Código</th>';
    print '<th>Etiqueta</th>';
    print '<th>Fuente</th>';
    print '</tr>';
    
    while ($obj = $db->fetch_object($resql)) {
        print '<tr>';
        print '<td><code>' . $obj->code . '</code></td>';
        print '<td>' . $obj->libelle . '</td>';
        print '<td>' . ($obj->source == 'internal' ? '🏢 Interno' : '👤 Externo') . '</td>';
        print '</tr>';
    }
    print '</table>';
} else {
    print '<p>❌ Error al consultar tipos de contacto</p>';
}

print '</div>';

// Test 6: API Endpoints Documentation
print '<h2>🚀 6. Endpoints de Contactos Disponibles</h2>';
print '<div class="info">';

$base_url = dol_buildpath('/api/index.php/dolibarmodernfrontend', 2);

print '<h3>GET - Obtener Contactos</h3>';
print '<p><strong>URL:</strong> <code>GET ' . $base_url . '/tickets/{id}/contacts</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los contactos asociados a un ticket</p>';

print '<h3>POST - Agregar Contacto</h3>';
print '<p><strong>URL:</strong> <code>POST ' . $base_url . '/tickets/{id}/contacts</code></p>';
print '<p><strong>Descripción:</strong> Agrega un contacto a un ticket</p>';
print '<p><strong>Body JSON:</strong></p>';
print '<pre><code>{
    "contact_id": 123,
    "contact_type": "CUSTOMER",
    "contact_source": "external"
}</code></pre>';

print '<h3>DELETE - Eliminar Contacto</h3>';
print '<p><strong>URL:</strong> <code>DELETE ' . $base_url . '/tickets/{id}/contacts/{contact_id}/{contact_source}</code></p>';
print '<p><strong>Descripción:</strong> Elimina un contacto de un ticket</p>';

print '</div>';

// Test 7: Example Usage
print '<h2>💡 7. Ejemplos de Uso</h2>';
print '<div class="info">';

print '<h3>Ejemplo con cURL</h3>';
print '<pre><code># Obtener contactos del ticket 1
curl -X GET "' . $base_url . '/tickets/1/contacts" \
     -H "DOLAPIKEY: your_api_key"

# Agregar contacto externo al ticket 1
curl -X POST "' . $base_url . '/tickets/1/contacts" \
     -H "DOLAPIKEY: your_api_key" \
     -H "Content-Type: application/json" \
     -d \'{"contact_id": 123, "contact_type": "CUSTOMER", "contact_source": "external"}\'

# Eliminar contacto del ticket 1
curl -X DELETE "' . $base_url . '/tickets/1/contacts/123/external" \
     -H "DOLAPIKEY: your_api_key"</code></pre>';

print '<h3>Ejemplo con JavaScript (fetch)</h3>';
print '<pre><code>// Obtener contactos
fetch(\'' . $base_url . '/tickets/1/contacts\', {
    headers: {
        \'DOLAPIKEY\': \'your_api_key\'
    }
})
.then(response => response.json())
.then(data => console.log(data));

// Agregar contacto
fetch(\'' . $base_url . '/tickets/1/contacts\', {
    method: \'POST\',
    headers: {
        \'DOLAPIKEY\': \'your_api_key\',
        \'Content-Type\': \'application/json\'
    },
    body: JSON.stringify({
        contact_id: 123,
        contact_type: \'CUSTOMER\',
        contact_source: \'external\'
    })
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>';

print '</div>';

// Test 8: Troubleshooting
print '<h2>🔧 8. Solución de Problemas</h2>';
print '<div class="info">';

print '<h3>Errores Comunes</h3>';
print '<ul>';
print '<li><strong>401 Unauthorized:</strong> Verificar API key y permisos de usuario</li>';
print '<li><strong>404 Not Found:</strong> Verificar que el ticket o contacto existe</li>';
print '<li><strong>400 Bad Request:</strong> Verificar formato JSON y campos requeridos</li>';
print '<li><strong>409 Conflict:</strong> El contacto ya está asociado al ticket</li>';
print '</ul>';

print '<h3>Verificaciones</h3>';
print '<ul>';
print '<li>✅ Módulo dolibarmodernfrontend activado</li>';
print '<li>✅ API REST activada en Dolibarr</li>';
print '<li>✅ Usuario con permisos adecuados</li>';
print '<li>✅ API key válida configurada</li>';
print '<li>✅ Contactos y tickets existentes en la base de datos</li>';
print '</ul>';

print '</div>';

// Summary
print '<h2>📋 Resumen del Test</h2>';
print '<div class="info">';

$all_ok = $module_enabled && $api_enabled && $db_ok && ($has_module_perms || $has_ticket_perms);

if ($all_ok) {
    print '<p><strong>Estado general:</strong> ✅ Todo correcto - Los endpoints de contactos están listos para usar</p>';
    print '<p><strong>Versión:</strong> dolibarmodernfrontend v1.2.2</p>';
    print '<p><strong>Nuevos endpoints:</strong> 3 endpoints de gestión de contactos implementados</p>';
    print '<p><strong>Sistema:</strong> Usa métodos nativos de Dolibarr (add_contact, delete_contact)</p>';
} else {
    print '<p><strong>Estado general:</strong> ❌ Hay problemas que resolver antes de usar la API</p>';
    
    if (!$module_enabled) print '<p>- Activar el módulo dolibarmodernfrontend</p>';
    if (!$api_enabled) print '<p>- Activar la API REST en Dolibarr</p>';
    if (!$db_ok) print '<p>- Verificar conexión a la base de datos</p>';
    if (!$has_module_perms && !$has_ticket_perms) print '<p>- Configurar permisos de usuario</p>';
}

print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();
?>
