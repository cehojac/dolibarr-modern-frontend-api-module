<?php
/**
 * Archivo de prueba para verificar el funcionamiento del módulo dolibarmodernfrontend
 * Este archivo debe ejecutarse desde el navegador para probar la funcionalidad básica
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/dolibarmodernfrontend/class/ticketinterventionlink.class.php';

// Load translation files required by the page
$langs->loadLangs(array("dolibarmodernfrontend@dolibarmodernfrontend"));

// Access control
if (!$user->rights->dolibarmodernfrontend->read) {
    accessforbidden();
}

/*
 * View
 */
llxHeader("", "Test API - ".$langs->trans("ModuleDolibarmodernfrontendName"));

print load_fiche_titre("Test del Módulo dolibarmodernfrontend", '', 'object_dolibarmodernfrontend@dolibarmodernfrontend');

print '<div class="fichecenter">';

print '<h3>Pruebas de Funcionalidad</h3>';

$object = new TicketInterventionLink($db);

// Test 1: Verificar que la clase se instancia correctamente
print '<div class="info">';
print '<h4>✅ Test 1: Instanciación de la clase</h4>';
if ($object) {
    print '<p><strong>ÉXITO:</strong> La clase TicketInterventionLink se instanció correctamente.</p>';
} else {
    print '<p><strong>ERROR:</strong> No se pudo instanciar la clase TicketInterventionLink.</p>';
}
print '</div>';

// Test 2: Verificar conexión a la base de datos
print '<div class="info">';
print '<h4>✅ Test 2: Conexión a la base de datos</h4>';
if ($object->db && $object->db->connected) {
    print '<p><strong>ÉXITO:</strong> Conexión a la base de datos establecida.</p>';
} else {
    print '<p><strong>ERROR:</strong> No hay conexión a la base de datos.</p>';
}
print '</div>';

// Test 3: Verificar que la tabla llx_element_element existe
print '<div class="info">';
print '<h4>✅ Test 3: Verificación de tabla nativa llx_element_element</h4>';
$sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."element_element'";
$resql = $db->query($sql);
if ($resql && $db->num_rows($resql) > 0) {
    print '<p><strong>ÉXITO:</strong> La tabla llx_element_element existe en la base de datos.</p>';
} else {
    print '<p><strong>ERROR:</strong> La tabla llx_element_element no existe.</p>';
}
print '</div>';

// Test 4: Obtener todas las vinculaciones existentes
print '<div class="info">';
print '<h4>✅ Test 4: Consulta de vinculaciones existentes</h4>';
$links = $object->getAllLinks();
if (is_array($links)) {
    print '<p><strong>ÉXITO:</strong> Se pudieron consultar las vinculaciones. Total encontradas: '.count($links).'</p>';
    if (count($links) > 0) {
        print '<p>Primeras vinculaciones encontradas:</p>';
        print '<ul>';
        foreach (array_slice($links, 0, 3) as $link) {
            print '<li>Ticket: '.$link['ticket_ref'].' → Intervención: '.$link['intervention_ref'].'</li>';
        }
        print '</ul>';
    }
} else {
    print '<p><strong>ERROR:</strong> No se pudieron consultar las vinculaciones. Error: '.$object->error.'</p>';
}
print '</div>';

// Test 5: Verificar permisos del usuario
print '<div class="info">';
print '<h4>✅ Test 5: Verificación de permisos</h4>';
$permisos = array();
if ($user->rights->dolibarmodernfrontend->read) $permisos[] = 'Leer';
if ($user->rights->dolibarmodernfrontend->write) $permisos[] = 'Escribir';
if ($user->rights->dolibarmodernfrontend->delete) $permisos[] = 'Eliminar';
if ($user->rights->dolibarmodernfrontend->admin) $permisos[] = 'Administrar';

if (count($permisos) > 0) {
    print '<p><strong>ÉXITO:</strong> El usuario tiene los siguientes permisos: '.implode(', ', $permisos).'</p>';
} else {
    print '<p><strong>ADVERTENCIA:</strong> El usuario no tiene permisos específicos para este módulo.</p>';
}
print '</div>';

// Información del sistema
print '<h3>Información del Sistema</h3>';
print '<div class="info">';
print '<ul>';
print '<li><strong>Módulo:</strong> dolibarmodernfrontend v1.0.0</li>';
print '<li><strong>Sistema de vinculaciones:</strong> Nativo de Dolibarr (llx_element_element)</li>';
print '<li><strong>Número de módulo:</strong> 105003</li>';
print '<li><strong>Usuario actual:</strong> '.$user->login.'</li>';
print '<li><strong>Fecha de prueba:</strong> '.dol_print_date(dol_now(), 'dayhour').'</li>';
print '</ul>';
print '</div>';

print '<h3>Endpoints de la API</h3>';
print '<div class="info">';
print '<p>Los siguientes endpoints están disponibles:</p>';
print '<ul>';
print '<li><strong>POST</strong> /api/index.php/dolibarmodernfrontend/link/{ticket_id}/{intervention_id}</li>';
print '<li><strong>DELETE</strong> /api/index.php/dolibarmodernfrontend/unlink/{ticket_id}/{intervention_id}</li>';
print '<li><strong>GET</strong> /api/index.php/dolibarmodernfrontend/ticket/{ticket_id}/interventions</li>';
print '<li><strong>GET</strong> /api/index.php/dolibarmodernfrontend/intervention/{intervention_id}/tickets</li>';
print '<li><strong>POST</strong> /api/index.php/dolibarmodernfrontend/tickets/{ticket_id}/sendemail</li>';
print '</ul>';
print '</div>';

// Test del nuevo endpoint de email
print '<h3>Test del Endpoint de Email</h3>';
print '<div class="info">';
print '<h4>✅ Test 6: Verificación de clases para envío de email</h4>';

// Verificar que las clases necesarias existen
$email_classes = array(
    'CMailFile' => DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php',
    'ActionComm' => DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php',
    'Ticket' => DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php'
);

$all_classes_ok = true;
foreach ($email_classes as $class_name => $class_path) {
    if (file_exists($class_path)) {
        print '<p>✅ <strong>'.$class_name.':</strong> Disponible</p>';
    } else {
        print '<p>❌ <strong>'.$class_name.':</strong> No encontrada en '.$class_path.'</p>';
        $all_classes_ok = false;
    }
}

if ($all_classes_ok) {
    print '<p><strong>ÉXITO:</strong> Todas las clases necesarias para el envío de email están disponibles.</p>';
} else {
    print '<p><strong>ERROR:</strong> Faltan algunas clases necesarias para el envío de email.</p>';
}

// Verificar configuración de email
print '<h4>✅ Test 7: Verificación de configuración de email</h4>';
$email_config_ok = true;

if (!empty($conf->global->MAIN_MAIL_EMAIL_FROM)) {
    print '<p>✅ <strong>Email remitente:</strong> '.$conf->global->MAIN_MAIL_EMAIL_FROM.'</p>';
} else {
    print '<p>⚠️ <strong>Email remitente:</strong> No configurado (se usará email del usuario)</p>';
}

if (!empty($conf->global->MAIN_MAIL_SMTP_SERVER)) {
    print '<p>✅ <strong>Servidor SMTP:</strong> Configurado</p>';
} else {
    print '<p>⚠️ <strong>Servidor SMTP:</strong> No configurado (se usará mail() de PHP)</p>';
}

print '<p><strong>INFO:</strong> Los endpoints de email están listos para usar. Ejemplos de uso:</p>';

print '<h5>Método Nativo (sin archivos adjuntos):</h5>';
print '<pre><code>POST /api/index.php/dolibarmodernfrontend/tickets/123/sendemail
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "subject": "Actualización del ticket",
    "message": "Estimado cliente, su ticket ha sido actualizado...",
    "send_to_internal": false
}</code></pre>';

print '<h5>Método Personalizado (con archivos adjuntos):</h5>';
print '<pre><code>POST /api/index.php/dolibarmodernfrontend/tickets/123/sendemail
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "subject": "Re: Ticket #123",
    "message": "&lt;p&gt;Contenido HTML&lt;/p&gt;",
    "recipients": ["cliente@empresa.com", "soporte@empresa.com"],
    "attachments": [
        {
            "name": "archivo1.pdf",
            "size": 1024000,
            "type": "application/pdf",
            "content": "base64_content_here"
        }
    ]
}</code></pre>';

print '<p><strong>Características del nuevo endpoint:</strong></p>';
print '<ul>';
print '<li>✅ Soporte completo para archivos adjuntos en base64</li>';
print '<li>✅ Destinatarios personalizables</li>';
print '<li>✅ Contenido HTML en mensajes</li>';
print '<li>✅ Validación de archivos (máximo 10MB por archivo)</li>';
print '<li>✅ Limpieza automática de archivos temporales</li>';
print '<li>✅ Registro en historial del ticket</li>';
print '</ul>';

print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();
?>
