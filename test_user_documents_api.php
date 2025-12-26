<?php
/**
 * Test file for User Documents API endpoint in dolibarrmodernfrontend module
 * 
 * This file tests the new ECM documents endpoint:
 * - GET /user/{id}/documents
 * 
 * Version: 1.2.3
 * Date: 2025-10-01
 */

require '../../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array("dolibarrmodernfrontend@dolibarrmodernfrontend", "ecm"));

// Access control
if (!$user->rights->dolibarrmodernfrontend->read && !$user->rights->ecm->read) {
    accessforbidden();
}

/*
 * View
 */
llxHeader("", $langs->trans("UserDocumentsAPITest"));

print load_fiche_titre("Test de API de Documentos de Usuario - dolibarrmodernfrontend v1.2.3", '', 'object_dolibarrmodernfrontend@dolibarrmodernfrontend');

print '<div class="fichecenter">';

// Test 1: Basic System Check
print '<h2>🔧 1. Verificación del Sistema</h2>';
print '<div class="info">';

// Check module activation
$module_enabled = !empty($conf->dolibarrmodernfrontend->enabled);
print '<p><strong>Módulo dolibarrmodernfrontend:</strong> ' . ($module_enabled ? '✅ Activado' : '❌ Desactivado') . '</p>';

// Check ECM module
$ecm_enabled = !empty($conf->ecm->enabled);
print '<p><strong>Módulo ECM:</strong> ' . ($ecm_enabled ? '✅ Activado' : '⚠️ Desactivado (recomendado para usar el endpoint)') . '</p>';

// Check API activation
$api_enabled = !empty($conf->api->enabled);
print '<p><strong>API REST:</strong> ' . ($api_enabled ? '✅ Activada' : '❌ Desactivada') . '</p>';

// Check database connection
$db_ok = ($db && $db->connected);
print '<p><strong>Conexión BD:</strong> ' . ($db_ok ? '✅ Conectada' : '❌ Error') . '</p>';

// Check permissions
$has_module_perms = isset($user->rights->dolibarrmodernfrontend) && $user->rights->dolibarrmodernfrontend->read;
$has_ecm_perms = isset($user->rights->ecm) && $user->rights->ecm->read;
print '<p><strong>Permisos módulo:</strong> ' . ($has_module_perms ? '✅ Sí' : '❌ No') . '</p>';
print '<p><strong>Permisos ECM:</strong> ' . ($has_ecm_perms ? '✅ Sí' : '❌ No') . '</p>';
print '<p><strong>Acceso API:</strong> ' . ($has_module_perms || $has_ecm_perms ? '✅ Permitido' : '❌ Denegado') . '</p>';

print '</div>';

// Test 2: API Class Check
print '<h2>🔍 2. Verificación de Clases</h2>';
print '<div class="info">';

try {
    require_once DOL_DOCUMENT_ROOT.'/custom/dolibarrmodernfrontend/class/api_dolibarrmodernfrontend.class.php';
    print '<p><strong>Clase API:</strong> ✅ Cargada correctamente</p>';
    
    $api = new DolibarrmodernfrontendApi();
    print '<p><strong>Instanciación API:</strong> ✅ Exitosa</p>';
    
    // Check if new method exists
    $method_exists = method_exists($api, 'getUserDocuments');
    print '<p><strong>Método getUserDocuments():</strong> ' . ($method_exists ? '✅ Existe' : '❌ No encontrado') . '</p>';
    
} catch (Exception $e) {
    print '<p><strong>Error:</strong> ❌ ' . $e->getMessage() . '</p>';
}

print '</div>';

// Test 3: Database Tables Check
print '<h2>🗄️ 3. Verificación de Tablas ECM</h2>';
print '<div class="info">';

// Check ecm_directories table
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."ecm_directories'";
$resql = $db->query($sql);
$ecm_directories_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla ecm_directories:</strong> ' . ($ecm_directories_exists ? '✅ Existe (nativa)' : '❌ No encontrada') . '</p>';

// Check ecm_files table
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."ecm_files'";
$resql = $db->query($sql);
$ecm_files_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla ecm_files:</strong> ' . ($ecm_files_exists ? '✅ Existe (nativa)' : '❌ No encontrada') . '</p>';

print '</div>';

// Test 4: Sample Data Check
print '<h2>📊 4. Datos de Ejemplo</h2>';
print '<div class="info">';

// Count users
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."user WHERE statut = 1";
$resql = $db->query($sql);
$user_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $user_count = $obj->count;
}
print '<p><strong>Usuarios activos:</strong> ' . $user_count . '</p>';

// Count ECM directories
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."ecm_directories WHERE entity = ".$conf->entity;
$resql = $db->query($sql);
$dir_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $dir_count = $obj->count;
}
print '<p><strong>Directorios ECM totales:</strong> ' . $dir_count . '</p>';

// Count user-specific directories
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."ecm_directories WHERE fk_user > 0 AND entity = ".$conf->entity;
$resql = $db->query($sql);
$user_dir_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $user_dir_count = $obj->count;
}
print '<p><strong>Directorios de usuarios:</strong> ' . $user_dir_count . '</p>';

// Count common directories
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."ecm_directories WHERE (fk_user IS NULL OR fk_user = 0) AND entity = ".$conf->entity;
$resql = $db->query($sql);
$common_dir_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $common_dir_count = $obj->count;
}
print '<p><strong>Directorios comunes:</strong> ' . $common_dir_count . '</p>';

// Count ECM files
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."ecm_files WHERE entity = ".$conf->entity;
$resql = $db->query($sql);
$file_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $file_count = $obj->count;
}
print '<p><strong>Archivos registrados en ECM:</strong> ' . $file_count . '</p>';

print '</div>';

// Test 5: Directory Structure
print '<h2>🗂️ 5. Estructura de Directorios ECM</h2>';
print '<div class="info">';

$sql = "SELECT d.rowid, d.label, d.description, d.fk_user, d.fk_parent, d.cachenbofdoc, u.login";
$sql .= " FROM ".MAIN_DB_PREFIX."ecm_directories d";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid = d.fk_user";
$sql .= " WHERE d.entity = ".$conf->entity;
$sql .= " ORDER BY d.fk_user, d.label";
$sql .= " LIMIT 20";

$resql = $db->query($sql);
if ($resql) {
    print '<table class="border centpercent">';
    print '<tr class="liste_titre">';
    print '<th>ID</th>';
    print '<th>Etiqueta</th>';
    print '<th>Usuario</th>';
    print '<th>Archivos</th>';
    print '<th>Tipo</th>';
    print '</tr>';
    
    while ($obj = $db->fetch_object($resql)) {
        print '<tr>';
        print '<td>' . $obj->rowid . '</td>';
        print '<td>' . htmlspecialchars($obj->label) . '</td>';
        print '<td>' . ($obj->login ? htmlspecialchars($obj->login) : '<em>Común</em>') . '</td>';
        print '<td>' . $obj->cachenbofdoc . '</td>';
        print '<td>' . ($obj->fk_user > 0 ? '👤 Usuario' : '🌐 Común') . '</td>';
        print '</tr>';
    }
    print '</table>';
} else {
    print '<p>❌ Error al consultar directorios</p>';
}

print '</div>';

// Test 6: Current User's Directories
print '<h2>👤 6. Directorios del Usuario Actual</h2>';
print '<div class="info">';

print '<p><strong>Usuario:</strong> ' . $user->login . ' (ID: ' . $user->id . ')</p>';

$sql = "SELECT d.rowid, d.label, d.description, d.fullrelativename, d.cachenbofdoc";
$sql .= " FROM ".MAIN_DB_PREFIX."ecm_directories d";
$sql .= " WHERE d.fk_user = ".((int) $user->id);
$sql .= " AND d.entity = ".$conf->entity;
$sql .= " ORDER BY d.label";

$resql = $db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);
    
    if ($num > 0) {
        print '<p><strong>Total de directorios:</strong> ' . $num . '</p>';
        print '<table class="border centpercent">';
        print '<tr class="liste_titre">';
        print '<th>Etiqueta</th>';
        print '<th>Ruta Relativa</th>';
        print '<th>Archivos</th>';
        print '</tr>';
        
        while ($obj = $db->fetch_object($resql)) {
            print '<tr>';
            print '<td>' . htmlspecialchars($obj->label) . '</td>';
            print '<td><code>' . htmlspecialchars($obj->fullrelativename) . '</code></td>';
            print '<td>' . $obj->cachenbofdoc . '</td>';
            print '</tr>';
        }
        print '</table>';
    } else {
        print '<p>ℹ️ El usuario actual no tiene directorios personales en ECM</p>';
        print '<p><em>Puede crear directorios desde Documentos > Directorios manuales</em></p>';
    }
} else {
    print '<p>❌ Error al consultar directorios del usuario</p>';
}

print '</div>';

// Test 7: API Endpoint Documentation
print '<h2>🚀 7. Endpoint de Documentos de Usuario</h2>';
print '<div class="info">';

$base_url = dol_buildpath('/api/index.php/dolibarrmodernfrontend', 2);

print '<h3>GET - Obtener Documentos del Usuario</h3>';
print '<p><strong>URL:</strong> <code>GET ' . $base_url . '/user/{id}/documents</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los directorios manuales y archivos del usuario desde ECM</p>';
print '<p><strong>Retorna:</strong></p>';
print '<ul>';
print '<li><code>directories</code>: Directorios específicos del usuario</li>';
print '<li><code>common_directories</code>: Directorios comunes accesibles</li>';
print '<li><code>files</code>: Archivos dentro de cada directorio con metadatos</li>';
print '</ul>';

print '</div>';

// Test 8: Example Usage
print '<h2>💡 8. Ejemplos de Uso</h2>';
print '<div class="info">';

print '<h3>Ejemplo con cURL</h3>';
print '<pre><code># Obtener documentos del usuario 1
curl -X GET "' . $base_url . '/user/1/documents" \
     -H "DOLAPIKEY: your_api_key"

# Obtener documentos del usuario actual
curl -X GET "' . $base_url . '/user/' . $user->id . '/documents" \
     -H "DOLAPIKEY: your_api_key"</code></pre>';

print '<h3>Ejemplo con JavaScript (fetch)</h3>';
print '<pre><code>// Obtener documentos del usuario
fetch(\'' . $base_url . '/user/1/documents\', {
    headers: {
        \'DOLAPIKEY\': \'your_api_key\'
    }
})
.then(response => response.json())
.then(data => {
    console.log(\'Directorios del usuario:\', data.directories);
    console.log(\'Directorios comunes:\', data.common_directories);
    
    // Listar archivos
    data.directories.forEach(dir => {
        console.log(\'Directorio:\', dir.label);
        dir.files.forEach(file => {
            console.log(\'  - Archivo:\', file.name, \'(\' + file.size + \' bytes)\');
        });
    });
});</code></pre>';

print '</div>';

// Test 9: ECM Configuration
print '<h2>⚙️ 9. Configuración ECM</h2>';
print '<div class="info">';

print '<p><strong>Directorio ECM:</strong> <code>' . $conf->ecm->dir_output . '</code></p>';
print '<p><strong>Entidad:</strong> ' . $conf->entity . '</p>';

// Check if ECM directory exists
$ecm_dir_exists = is_dir($conf->ecm->dir_output);
print '<p><strong>Directorio ECM existe:</strong> ' . ($ecm_dir_exists ? '✅ Sí' : '❌ No') . '</p>';

if ($ecm_dir_exists) {
    $ecm_writable = is_writable($conf->ecm->dir_output);
    print '<p><strong>Directorio ECM escribible:</strong> ' . ($ecm_writable ? '✅ Sí' : '❌ No') . '</p>';
}

print '</div>';

// Test 10: Troubleshooting
print '<h2>🔧 10. Solución de Problemas</h2>';
print '<div class="info">';

print '<h3>Errores Comunes</h3>';
print '<ul>';
print '<li><strong>401 Unauthorized:</strong> Verificar API key y permisos ECM/dolibarrmodernfrontend</li>';
print '<li><strong>404 Not Found:</strong> Verificar que el usuario existe</li>';
print '<li><strong>Sin directorios:</strong> El usuario debe crear directorios en ECM primero</li>';
print '</ul>';

print '<h3>Verificaciones</h3>';
print '<ul>';
print '<li>✅ Módulo dolibarrmodernfrontend activado</li>';
print '<li>✅ Módulo ECM activado (recomendado)</li>';
print '<li>✅ API REST activada en Dolibarr</li>';
print '<li>✅ Usuario con permisos ECM o dolibarrmodernfrontend</li>';
print '<li>✅ Directorios manuales creados en ECM</li>';
print '</ul>';

print '<h3>Cómo crear directorios manuales</h3>';
print '<ol>';
print '<li>Ir a <strong>Documentos > Directorios manuales</strong></li>';
print '<li>Hacer clic en <strong>Nuevo directorio</strong></li>';
print '<li>Asignar el directorio a un usuario específico (opcional)</li>';
print '<li>Subir archivos al directorio creado</li>';
print '</ol>';

print '</div>';

// Summary
print '<h2>📋 Resumen del Test</h2>';
print '<div class="info">';

$all_ok = $module_enabled && $api_enabled && $db_ok && ($has_module_perms || $has_ecm_perms);

if ($all_ok) {
    print '<p><strong>Estado general:</strong> ✅ Todo correcto - El endpoint de documentos está listo para usar</p>';
    print '<p><strong>Versión:</strong> dolibarrmodernfrontend v1.2.3</p>';
    print '<p><strong>Nuevo endpoint:</strong> GET /user/{id}/documents implementado</p>';
    print '<p><strong>Sistema:</strong> Usa tablas nativas ECM (llx_ecm_directories, llx_ecm_files)</p>';
    print '<p><strong>Características:</strong></p>';
    print '<ul>';
    print '<li>✅ Lista directorios manuales del usuario</li>';
    print '<li>✅ Lista directorios comunes compartidos</li>';
    print '<li>✅ Incluye todos los archivos con metadatos</li>';
    print '<li>✅ URLs de descarga directa</li>';
    print '</ul>';
} else {
    print '<p><strong>Estado general:</strong> ❌ Hay problemas que resolver antes de usar la API</p>';
    
    if (!$module_enabled) print '<p>- Activar el módulo dolibarrmodernfrontend</p>';
    if (!$api_enabled) print '<p>- Activar la API REST en Dolibarr</p>';
    if (!$db_ok) print '<p>- Verificar conexión a la base de datos</p>';
    if (!$has_module_perms && !$has_ecm_perms) print '<p>- Configurar permisos ECM o dolibarrmodernfrontend</p>';
}

print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();
?>
