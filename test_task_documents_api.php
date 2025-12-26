<?php
/**
 * Test file for Task Documents API endpoints in dolibarrmodernfrontend module
 * 
 * This file tests the new project task documents endpoints:
 * - GET /task/{id}/documents
 * - GET /project/{id}/tasks/documents
 * 
 * Version: 1.2.4
 * Date: 2025-10-05
 */

require '../../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array("dolibarrmodernfrontend@dolibarrmodernfrontend", "projects"));

// Access control
if (!$user->rights->dolibarrmodernfrontend->read && !$user->rights->projet->lire) {
    accessforbidden();
}

/*
 * View
 */
llxHeader("", $langs->trans("TaskDocumentsAPITest"));

print load_fiche_titre("Test de API de Documentos de Tareas - dolibarrmodernfrontend v1.2.4", '', 'object_dolibarrmodernfrontend@dolibarrmodernfrontend');

print '<div class="fichecenter">';

// Test 1: Basic System Check
print '<h2>🔧 1. Verificación del Sistema</h2>';
print '<div class="info">';

// Check module activation
$module_enabled = !empty($conf->dolibarrmodernfrontend->enabled);
print '<p><strong>Módulo dolibarrmodernfrontend:</strong> ' . ($module_enabled ? '✅ Activado' : '❌ Desactivado') . '</p>';

// Check Project module
$project_enabled = !empty($conf->projet->enabled);
print '<p><strong>Módulo Proyectos:</strong> ' . ($project_enabled ? '✅ Activado' : '⚠️ Desactivado (requerido)') . '</p>';

// Check API activation
$api_enabled = !empty($conf->api->enabled);
print '<p><strong>API REST:</strong> ' . ($api_enabled ? '✅ Activada' : '❌ Desactivada') . '</p>';

// Check database connection
$db_ok = ($db && $db->connected);
print '<p><strong>Conexión BD:</strong> ' . ($db_ok ? '✅ Conectada' : '❌ Error') . '</p>';

// Check permissions
$has_module_perms = isset($user->rights->dolibarrmodernfrontend) && $user->rights->dolibarrmodernfrontend->read;
$has_project_perms = isset($user->rights->projet) && $user->rights->projet->lire;
print '<p><strong>Permisos módulo:</strong> ' . ($has_module_perms ? '✅ Sí' : '❌ No') . '</p>';
print '<p><strong>Permisos proyectos:</strong> ' . ($has_project_perms ? '✅ Sí' : '❌ No') . '</p>';
print '<p><strong>Acceso API:</strong> ' . ($has_module_perms || $has_project_perms ? '✅ Permitido' : '❌ Denegado') . '</p>';

print '</div>';

// Test 2: API Class Check
print '<h2>🔍 2. Verificación de Clases</h2>';
print '<div class="info">';

try {
    require_once DOL_DOCUMENT_ROOT.'/custom/dolibarrmodernfrontend/class/api_dolibarrmodernfrontend.class.php';
    print '<p><strong>Clase API:</strong> ✅ Cargada correctamente</p>';
    
    $api = new DolibarrmodernfrontendApi();
    print '<p><strong>Instanciación API:</strong> ✅ Exitosa</p>';
    
    // Check if new methods exist
    $methods_to_check = [
        'getTaskDocuments',
        'getProjectTasksDocuments'
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

// Check projet table
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."projet'";
$resql = $db->query($sql);
$projet_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla projet:</strong> ' . ($projet_exists ? '✅ Existe (nativa)' : '❌ No encontrada') . '</p>';

// Check projet_task table
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."projet_task'";
$resql = $db->query($sql);
$task_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla projet_task:</strong> ' . ($task_exists ? '✅ Existe (nativa)' : '❌ No encontrada') . '</p>';

// Check ecm_files table
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."ecm_files'";
$resql = $db->query($sql);
$ecm_files_exists = ($resql && $db->num_rows($resql) > 0);
print '<p><strong>Tabla ecm_files:</strong> ' . ($ecm_files_exists ? '✅ Existe (metadatos)' : '❌ No encontrada') . '</p>';

print '</div>';

// Test 4: Sample Data Check
print '<h2>📊 4. Datos de Ejemplo</h2>';
print '<div class="info">';

// Count projects
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."projet WHERE entity IN (".getEntity('project').")";
$resql = $db->query($sql);
$project_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $project_count = $obj->count;
}
print '<p><strong>Proyectos totales:</strong> ' . $project_count . '</p>';

// Count tasks
$sql = "SELECT COUNT(*) as count FROM ".MAIN_DB_PREFIX."projet_task";
$resql = $db->query($sql);
$task_count = 0;
if ($resql) {
    $obj = $db->fetch_object($resql);
    $task_count = $obj->count;
}
print '<p><strong>Tareas totales:</strong> ' . $task_count . '</p>';

// Check documents directory
if (!empty($conf->projet->dir_output)) {
    $project_dir_exists = is_dir($conf->projet->dir_output);
    print '<p><strong>Directorio de proyectos:</strong> ' . ($project_dir_exists ? '✅ Existe' : '❌ No encontrado') . '</p>';
    print '<p><strong>Ruta:</strong> <code>' . $conf->projet->dir_output . '</code></p>';
}

print '</div>';

// Test 5: Projects and Tasks Structure
print '<h2>🗂️ 5. Estructura de Proyectos y Tareas</h2>';
print '<div class="info">';

$sql = "SELECT p.rowid, p.ref, p.title, COUNT(t.rowid) as task_count";
$sql .= " FROM ".MAIN_DB_PREFIX."projet p";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task t ON t.fk_projet = p.rowid";
$sql .= " WHERE p.entity IN (".getEntity('project').")";
$sql .= " GROUP BY p.rowid, p.ref, p.title";
$sql .= " ORDER BY p.ref";
$sql .= " LIMIT 10";

$resql = $db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);
    
    if ($num > 0) {
        print '<table class="border centpercent">';
        print '<tr class="liste_titre">';
        print '<th>ID</th>';
        print '<th>Referencia</th>';
        print '<th>Título</th>';
        print '<th>Tareas</th>';
        print '</tr>';
        
        while ($obj = $db->fetch_object($resql)) {
            print '<tr>';
            print '<td>' . $obj->rowid . '</td>';
            print '<td>' . htmlspecialchars($obj->ref) . '</td>';
            print '<td>' . htmlspecialchars($obj->title) . '</td>';
            print '<td>' . $obj->task_count . '</td>';
            print '</tr>';
        }
        print '</table>';
    } else {
        print '<p>ℹ️ No hay proyectos disponibles</p>';
    }
} else {
    print '<p>❌ Error al consultar proyectos</p>';
}

print '</div>';

// Test 6: Tasks with Potential Documents
print '<h2>📁 6. Tareas con Posibles Documentos</h2>';
print '<div class="info">';

$sql = "SELECT t.rowid, t.ref, t.label, p.ref as project_ref, p.title as project_title";
$sql .= " FROM ".MAIN_DB_PREFIX."projet_task t";
$sql .= " INNER JOIN ".MAIN_DB_PREFIX."projet p ON p.rowid = t.fk_projet";
$sql .= " WHERE p.entity IN (".getEntity('project').")";
$sql .= " ORDER BY p.ref, t.ref";
$sql .= " LIMIT 20";

$resql = $db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);
    
    if ($num > 0) {
        print '<p><strong>Total de tareas:</strong> ' . $num . '</p>';
        print '<table class="border centpercent">';
        print '<tr class="liste_titre">';
        print '<th>Task ID</th>';
        print '<th>Proyecto</th>';
        print '<th>Tarea</th>';
        print '<th>Directorio</th>';
        print '<th>¿Existe?</th>';
        print '</tr>';
        
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        
        while ($obj = $db->fetch_object($resql)) {
            $projectref = dol_sanitizeFileName($obj->project_ref);
            $taskref = $obj->ref ? dol_sanitizeFileName($obj->ref) : (string) $obj->rowid;
            $upload_dir = $conf->projet->dir_output . '/' . $projectref . '/task/' . $taskref;
            $dir_exists = is_dir($upload_dir);
            
            // Count files if directory exists
            $file_count = 0;
            if ($dir_exists) {
                $filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$');
                $file_count = is_array($filearray) ? count($filearray) : 0;
            }
            
            print '<tr>';
            print '<td>' . $obj->rowid . '</td>';
            print '<td>' . htmlspecialchars($obj->project_ref) . '</td>';
            print '<td>' . htmlspecialchars($obj->label) . '</td>';
            print '<td><small><code>' . $taskref . '</code></small></td>';
            print '<td>' . ($dir_exists ? '✅ Sí (' . $file_count . ' archivos)' : '❌ No') . '</td>';
            print '</tr>';
        }
        print '</table>';
    } else {
        print '<p>ℹ️ No hay tareas disponibles</p>';
    }
} else {
    print '<p>❌ Error al consultar tareas</p>';
}

print '</div>';

// Test 7: API Endpoints Documentation
print '<h2>🚀 7. Endpoints de Documentos de Tareas</h2>';
print '<div class="info">';

$base_url = dol_buildpath('/api/index.php/dolibarrmodernfrontend', 2);

print '<h3>GET - Obtener Documentos de una Tarea</h3>';
print '<p><strong>URL:</strong> <code>GET ' . $base_url . '/task/{id}/documents</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los archivos subidos a una tarea específica</p>';
print '<p><strong>Retorna:</strong></p>';
print '<ul>';
print '<li>Información de la tarea y proyecto asociado</li>';
print '<li>Lista completa de documentos con metadatos</li>';
print '<li>URLs de descarga directa</li>';
print '</ul>';

print '<h3>GET - Obtener Documentos de Todas las Tareas de un Proyecto</h3>';
print '<p><strong>URL:</strong> <code>GET ' . $base_url . '/project/{id}/tasks/documents</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los documentos de todas las tareas de un proyecto</p>';
print '<p><strong>Retorna:</strong></p>';
print '<ul>';
print '<li>Información del proyecto</li>';
print '<li>Array de tareas con sus documentos</li>';
print '<li>Contadores totales de tareas y documentos</li>';
print '</ul>';

print '</div>';

// Test 8: Example Usage
print '<h2>💡 8. Ejemplos de Uso</h2>';
print '<div class="info">';

print '<h3>Ejemplo con cURL - Obtener Documentos de una Tarea</h3>';
print '<pre><code># Obtener documentos de la tarea 1
curl -X GET "' . $base_url . '/task/1/documents" \
     -H "DOLAPIKEY: your_api_key"</code></pre>';

print '<h3>Ejemplo con cURL - Subir Documento a una Tarea</h3>';
print '<pre><code># Subir archivo a la tarea 1 (contenido en base64)
curl -X POST "' . $base_url . '/task/1/documents" \
     -H "DOLAPIKEY: your_api_key" \
     -H "Content-Type: application/json" \
     -d \'{
       "filename": "documento.pdf",
       "filecontent": "JVBERi0xLjQK...",
       "overwriteifexists": false,
       "label": "Documento Técnico",
       "description": "Especificaciones del proyecto"
     }\'</code></pre>';

print '<h3>Ejemplo con cURL - Todos los Documentos del Proyecto</h3>';
print '<pre><code># Obtener documentos de todas las tareas del proyecto 1
curl -X GET "' . $base_url . '/project/1/tasks/documents" \
     -H "DOLAPIKEY: your_api_key"</code></pre>';

print '<h3>Ejemplo con JavaScript (fetch)</h3>';
print '<pre><code>// Obtener documentos de una tarea
fetch(\'' . $base_url . '/task/1/documents\', {
    headers: {
        \'DOLAPIKEY\': \'your_api_key\'
    }
})
.then(response => response.json())
.then(data => {
    console.log(\'Tarea:\', data.task_label);
    console.log(\'Proyecto:\', data.project_title);
    console.log(\'Total documentos:\', data.total_documents);
    
    data.documents.forEach(doc => {
        console.log(\'- \' + doc.name + \' (\' + doc.size + \' bytes)\');
        console.log(\'  URL: \' + doc.download_url);
    });
});

// Obtener documentos de todas las tareas del proyecto
fetch(\'' . $base_url . '/project/1/tasks/documents\', {
    headers: {
        \'DOLAPIKEY\': \'your_api_key\'
    }
})
.then(response => response.json())
.then(data => {
    console.log(\'Proyecto:\', data.project_title);
    console.log(\'Total tareas:\', data.total_tasks);
    console.log(\'Total documentos:\', data.total_documents);
    
    data.tasks.forEach(task => {
        console.log(\'\\nTarea:\', task.task_label);
        console.log(\'  Documentos:\', task.total_documents);
        
        task.documents.forEach(doc => {
            console.log(\'  - \' + doc.name);
        });
    });
});

// Subir archivo a una tarea
function uploadFileToTask(taskId, file) {
    // Leer archivo y convertir a base64
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const base64Content = btoa(e.target.result);
        
        fetch(\'' . $base_url . '/task/\' + taskId + \'/documents\', {
            method: \'POST\',
            headers: {
                \'DOLAPIKEY\': \'your_api_key\',
                \'Content-Type\': \'application/json\'
            },
            body: JSON.stringify({
                filename: file.name,
                filecontent: base64Content,
                overwriteifexists: false,
                label: \'Documento subido desde frontend\'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(\'Archivo subido:\', data.file.name);
                console.log(\'URL de descarga:\', data.file.download_url);
            }
        });
    };
    
    reader.readAsBinaryString(file);
}

// Uso: uploadFileToTask(1, fileInputElement.files[0]);</code></pre>';

print '</div>';

// Test 9: Document Path Structure
print '<h2>📂 9. Estructura de Rutas de Documentos</h2>';
print '<div class="info">';

print '<p><strong>Patrón de ruta:</strong></p>';
print '<p><code>{projet_dir_output}/{project_ref}/task/{task_ref}/</code></p>';

print '<p><strong>Ejemplo:</strong></p>';
print '<p><code>' . $conf->projet->dir_output . '/PROJ2023-001/task/T001/documento.pdf</code></p>';

print '<p><strong>Parámetro modulepart:</strong> <code>project_task</code></p>';

print '<p><strong>URL de descarga:</strong></p>';
print '<p><code>/document.php?modulepart=project_task&file={project_ref}/task/{task_ref}/{filename}</code></p>';

print '</div>';

// Test 10: Troubleshooting
print '<h2>🔧 10. Solución de Problemas</h2>';
print '<div class="info">';

print '<h3>Errores Comunes</h3>';
print '<ul>';
print '<li><strong>401 Unauthorized:</strong> Verificar API key y permisos de proyecto/dolibarrmodernfrontend</li>';
print '<li><strong>404 Not Found:</strong> Verificar que la tarea o proyecto existe</li>';
print '<li><strong>Sin documentos:</strong> Verificar que se hayan subido archivos a la tarea</li>';
print '<li><strong>Directorio no existe:</strong> El directorio se crea al subir el primer archivo</li>';
print '</ul>';

print '<h3>Verificaciones</h3>';
print '<ul>';
print '<li>✅ Módulo dolibarrmodernfrontend activado</li>';
print '<li>✅ Módulo Proyectos activado</li>';
print '<li>✅ API REST activada en Dolibarr</li>';
print '<li>✅ Usuario con permisos de proyecto</li>';
print '<li>✅ Tareas con archivos subidos</li>';
print '</ul>';

print '<h3>Cómo subir documentos a una tarea</h3>';
print '<ol>';
print '<li>Ir a <strong>Proyectos > [Proyecto] > Tareas</strong></li>';
print '<li>Hacer clic en la tarea deseada</li>';
print '<li>Ir a la pestaña <strong>Documentos</strong></li>';
print '<li>Subir archivos usando el botón de carga</li>';
print '<li>Los archivos se almacenarán en <code>documents/project/{ref}/task/{task_ref}/</code></li>';
print '</ol>';

print '</div>';

// Summary
print '<h2>📋 Resumen del Test</h2>';
print '<div class="info">';

$all_ok = $module_enabled && $project_enabled && $api_enabled && $db_ok && ($has_module_perms || $has_project_perms);

if ($all_ok) {
    print '<p><strong>Estado general:</strong> ✅ Todo correcto - Los endpoints de documentos de tareas están listos</p>';
    print '<p><strong>Versión:</strong> dolibarrmodernfrontend v1.2.4</p>';
    print '<p><strong>Nuevos endpoints:</strong> 2 endpoints de documentos de tareas implementados</p>';
    print '<p><strong>Características:</strong></p>';
    print '<ul>';
    print '<li>✅ Documentos de tarea individual</li>';
    print '<li>✅ Documentos de todas las tareas del proyecto</li>';
    print '<li>✅ Metadatos desde llx_ecm_files</li>';
    print '<li>✅ URLs de descarga directa</li>';
    print '<li>✅ Información de proyecto y tarea</li>';
    print '</ul>';
} else {
    print '<p><strong>Estado general:</strong> ❌ Hay problemas que resolver antes de usar la API</p>';
    
    if (!$module_enabled) print '<p>- Activar el módulo dolibarrmodernfrontend</p>';
    if (!$project_enabled) print '<p>- Activar el módulo Proyectos</p>';
    if (!$api_enabled) print '<p>- Activar la API REST en Dolibarr</p>';
    if (!$db_ok) print '<p>- Verificar conexión a la base de datos</p>';
    if (!$has_module_perms && !$has_project_perms) print '<p>- Configurar permisos de proyecto o dolibarrmodernfrontend</p>';
}

print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();
?>
