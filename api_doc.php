<?php
require '../../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array("dolibarrmodernfrontend@dolibarrmodernfrontend"));

// Access control
if (!$user->rights->dolibarrmodernfrontend->read) {
    accessforbidden();
}

/*
 * View
 */
llxHeader("", $langs->trans("APIDocumentation"));

print load_fiche_titre($langs->trans("APIDocumentation"), '', 'object_dolibarrmodernfrontend@dolibarrmodernfrontend');

print '<div class="fichecenter">';

print '<h2>API Endpoints para Dolibarr Modern Frontend</h2>';

print '<div class="info">';
print '<p><strong>Base URL:</strong> /api/index.php/dolibarrmodernfrontend</p>';
print '<p><strong>Autenticación:</strong> API Key requerida en header DOLAPIKEY</p>';
print '<p><strong>Sistema:</strong> Usa la tabla nativa llx_element_element de Dolibarr</p>';
print '</div>';

print '<h3>Endpoints Disponibles</h3>';

// Link Ticket with Intervention
print '<div class="api-endpoint">';
print '<h4>1. Vincular Ticket con Intervención</h4>';
print '<p><strong>POST</strong> <code>/link/{ticket_id}/{intervention_id}</code></p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>ticket_id</code> (int): ID del ticket</li>';
print '<li><code>intervention_id</code> (int): ID de la intervención</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>POST /api/index.php/dolibarrmodernfrontend/link/123/456
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Nota:</strong> Usa el sistema nativo de Dolibarr (llx_element_element)</p>';
print '</div>';

// Unlink Ticket from Intervention
print '<div class="api-endpoint">';
print '<h4>2. Desvincular Ticket de Intervención</h4>';
print '<p><strong>DELETE</strong> <code>/unlink/{ticket_id}/{intervention_id}</code></p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>ticket_id</code> (int): ID del ticket</li>';
print '<li><code>intervention_id</code> (int): ID de la intervención</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>DELETE /api/index.php/dolibarrmodernfrontend/unlink/123/456
DOLAPIKEY: your_api_key</code></pre>';
print '</div>';

// Get Interventions by Ticket
print '<div class="api-endpoint">';
print '<h4>3. Obtener Intervenciones por Ticket</h4>';
print '<p><strong>GET</strong> <code>/ticket/{ticket_id}/interventions</code></p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>ticket_id</code> (int): ID del ticket</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>GET /api/index.php/dolibarrmodernfrontend/ticket/123/interventions
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta:</strong></p>';
print '<pre><code>{
    "ticket_id": 123,
    "ticket_ref": "TIC2023-001",
    "interventions_count": 2,
    "interventions": [
        {
            "link_id": 1,
            "intervention_id": 456,
            "intervention_ref": "INT2023-001",
            "intervention_label": "Reparación servidor",
            "link_type": "manual",
            "link_description": "Vinculación manual",
            "client_name": "Cliente ABC"
        }
    ]
}</code></pre>';
print '</div>';

// Get Tickets by Intervention
print '<div class="api-endpoint">';
print '<h4>4. Obtener Tickets por Intervención</h4>';
print '<p><strong>GET</strong> <code>/intervention/{intervention_id}/tickets</code></p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>intervention_id</code> (int): ID de la intervención</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>GET /api/index.php/dolibarrmodernfrontend/intervention/456/tickets
DOLAPIKEY: your_api_key</code></pre>';
print '</div>';

// Send Email to Ticket Contacts (Legacy)
print '<div class="api-endpoint">';
print '<h4>5. Enviar Email a Contactos del Ticket (Método Nativo)</h4>';
print '<p><strong>POST</strong> <code>/tickets/{ticket_id}/sendemail</code></p>';
print '<p><strong>Descripción:</strong> Envía un email a todos los contactos externos relacionados con el ticket usando el método nativo de Dolibarr.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>ticket_id</code> (int): ID del ticket</li>';
print '</ul>';
print '<p><strong>Cuerpo de la solicitud (JSON):</strong></p>';
print '<pre><code>{
    "subject": "Asunto del email",
    "message": "Contenido del mensaje",
    "private": false,
    "send_to_internal": false
}</code></pre>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>POST /api/index.php/dolibarrmodernfrontend/tickets/123/sendemail
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "subject": "Actualización del ticket",
    "message": "Estimado cliente,\n\nLe informamos que su ticket ha sido actualizado..."
}</code></pre>';
print '</div>';

// Send Email with Custom Format and Attachments
print '<div class="api-endpoint">';
print '<h4>6. Enviar Email con Formato Personalizado y Archivos Adjuntos</h4>';
print '<p><strong>POST</strong> <code>/tickets/{ticket_id}/sendemail</code></p>';
print '<p><strong>Descripción:</strong> Envía emails con formato personalizado, destinatarios específicos y soporte completo para archivos adjuntos en base64.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>ticket_id</code> (int): ID del ticket</li>';
print '</ul>';
print '<p><strong>Cuerpo de la solicitud (JSON):</strong></p>';
print '<pre><code>{
    "subject": "Re: Ticket #123",
    "message": "&lt;p&gt;Contenido HTML&lt;/p&gt;",
    "recipients": ["email1@example.com", "email2@example.com"],
    "attachments": [
        {
            "name": "archivo1.pdf",
            "size": 1024000,
            "type": "application/pdf",
            "content": "base64_content_here"
        },
        {
            "name": "imagen.jpg",
            "size": 512000,
            "type": "image/jpeg",
            "content": "base64_content_here"
        }
    ]
}</code></pre>';
print '<p><strong>Campos:</strong></p>';
print '<ul>';
print '<li><code>subject</code> (string, requerido): Asunto del email</li>';
print '<li><code>message</code> (string, requerido): Contenido del mensaje (HTML soportado)</li>';
print '<li><code>recipients</code> (array, opcional): Lista de emails destinatarios. Si está vacío, usa contactos del ticket</li>';
print '<li><code>attachments</code> (array, opcional): Lista de archivos adjuntos</li>';
print '</ul>';
print '<p><strong>Campos de Attachments:</strong></p>';
print '<ul>';
print '<li><code>name</code> (string, requerido): Nombre del archivo</li>';
print '<li><code>content</code> (string, requerido): Contenido del archivo codificado en base64</li>';
print '<li><code>size</code> (int, opcional): Tamaño del archivo en bytes</li>';
print '<li><code>type</code> (string, opcional): Tipo MIME del archivo</li>';
print '</ul>';
print '<p><strong>Ejemplo completo:</strong></p>';
print '<pre><code>POST /api/index.php/dolibarrmodernfrontend/tickets/123/sendemail
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "subject": "Re: Ticket #123 - Documentación adjunta",
    "message": "&lt;h2&gt;Estimado cliente&lt;/h2&gt;&lt;p&gt;Adjuntamos la documentación solicitada.&lt;/p&gt;",
    "recipients": ["cliente@empresa.com", "soporte@empresa.com"],
    "attachments": [
        {
            "name": "manual_usuario.pdf",
            "size": 2048000,
            "type": "application/pdf",
            "content": "JVBERi0xLjQKJcOkw7zDtsO..."
        }
    ]
}</code></pre>';
print '<p><strong>Respuesta exitosa:</strong></p>';
print '<pre><code>{
    "success": true,
    "ticket_id": 123,
    "ticket_ref": "TIC2023-001",
    "subject": "Re: Ticket #123 - Documentación adjunta",
    "message": "&lt;h2&gt;Estimado cliente&lt;/h2&gt;&lt;p&gt;Adjuntamos la documentación solicitada.&lt;/p&gt;",
    "recipients_total": 2,
    "attachments_total": 1,
    "emails_sent": 2,
    "emails_failed": 0,
    "sent_to": [
        {
            "email": "cliente@empresa.com",
            "sent_at": "2023-12-01 14:30:00",
            "attachments_count": 1
        },
        {
            "email": "soporte@empresa.com",
            "sent_at": "2023-12-01 14:30:00",
            "attachments_count": 1
        }
    ],
    "failed": [],
    "attachments_processed": [
        {
            "name": "manual_usuario.pdf",
            "size": 2048000,
            "type": "application/pdf"
        }
    ],
    "timestamp": "2023-12-01 14:30:00",
    "method": "custom_format_with_attachments"
}</code></pre>';
print '<p><strong>Características Avanzadas:</strong></p>';
print '<ul>';
print '<li>Soporte completo para archivos adjuntos en base64</li>';
print '<li>Validación de contenido base64 y tipos MIME</li>';
print '<li>Límite de 10MB por archivo adjunto</li>';
print '<li>Destinatarios personalizables o automáticos desde contactos del ticket</li>';
print '<li>Contenido HTML soportado en el mensaje</li>';
print '<li>Limpieza automática de archivos temporales</li>';
print '<li>Registro en historial del ticket con archivos adjuntos</li>';
print '<li>Manejo individual de errores por destinatario</li>';
print '<li>Usa CMailFile nativo de Dolibarr para máxima compatibilidad</li>';
print '<li>Sanitización de nombres de archivos</li>';
print '</ul>';
print '<p><strong>Limitaciones:</strong></p>';
print '<ul>';
print '<li>Máximo 10MB por archivo adjunto</li>';
print '<li>Archivos se procesan en memoria (considerar límites de PHP)</li>';
print '<li>Requiere configuración SMTP válida en Dolibarr</li>';
print '</ul>';
print '</div>';

// Get Ticket Contacts
print '<div class="api-endpoint">';
print '<h4>7. Obtener Contactos de un Ticket</h4>';
print '<p><strong>GET</strong> <code>/tickets/{id}/contacts</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los contactos (internos y externos) asociados a un ticket.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID del ticket</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>GET /api/index.php/dolibarrmodernfrontend/tickets/123/contacts
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta:</strong></p>';
print '<pre><code>{
    "ticket_id": 123,
    "ticket_ref": "TIC2023-001",
    "ticket_subject": "Problema con servidor",
    "contacts": [
        {
            "contact_id": 456,
            "element_contact_id": 789,
            "user_id": null,
            "lastname": "García",
            "firstname": "Juan",
            "fullname": "Juan García",
            "email": "juan.garcia@empresa.com",
            "phone": "+34 123 456 789",
            "phone_perso": "",
            "phone_mobile": "+34 987 654 321",
            "company_id": 10,
            "company_name": "Empresa ABC S.L.",
            "contact_type_code": "CUSTOMER",
            "contact_type_label": "Cliente",
            "contact_source": "external",
            "status": 1
        },
        {
            "contact_id": 15,
            "element_contact_id": 790,
            "user_id": 15,
            "lastname": "López",
            "firstname": "María",
            "fullname": "María López",
            "email": "maria.lopez@miempresa.com",
            "phone": "+34 111 222 333",
            "phone_perso": "",
            "phone_mobile": "+34 444 555 666",
            "company_id": null,
            "company_name": "Internal User",
            "contact_type_code": "SUPPORTTEC",
            "contact_type_label": "Soporte técnico",
            "contact_source": "internal",
            "status": 1
        }
    ],
    "count": 2
}</code></pre>';
print '</div>';

// Add Contact to Ticket
print '<div class="api-endpoint">';
print '<h4>8. Agregar Contacto a un Ticket</h4>';
print '<p><strong>POST</strong> <code>/tickets/{id}/contacts</code></p>';
print '<p><strong>Descripción:</strong> Agrega un contacto (interno o externo) a un ticket usando el sistema nativo de Dolibarr.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID del ticket</li>';
print '</ul>';
print '<p><strong>Cuerpo de la solicitud (JSON):</strong></p>';
print '<pre><code>{
    "contact_id": 456,
    "contact_type": "CUSTOMER",
    "contact_source": "external"
}</code></pre>';
print '<p><strong>Campos:</strong></p>';
print '<ul>';
print '<li><code>contact_id</code> (int, requerido): ID del contacto o usuario</li>';
print '<li><code>contact_type</code> (string, requerido): Código del tipo de contacto (ej: CUSTOMER, SUPPORTTEC)</li>';
print '<li><code>contact_source</code> (string, opcional): "external" para contactos externos o "internal" para usuarios internos. Por defecto: "external"</li>';
print '</ul>';
print '<p><strong>Ejemplo para contacto externo:</strong></p>';
print '<pre><code>POST /api/index.php/dolibarrmodernfrontend/tickets/123/contacts
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "contact_id": 456,
    "contact_type": "CUSTOMER",
    "contact_source": "external"
}</code></pre>';
print '<p><strong>Ejemplo para usuario interno:</strong></p>';
print '<pre><code>POST /api/index.php/dolibarrmodernfrontend/tickets/123/contacts
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "contact_id": 15,
    "contact_type": "SUPPORTTEC",
    "contact_source": "internal"
}</code></pre>';
print '<p><strong>Respuesta exitosa:</strong></p>';
print '<pre><code>{
    "success": true,
    "message": "Contact added successfully to ticket",
    "ticket_id": 123,
    "ticket_ref": "TIC2023-001",
    "contact_type": "CUSTOMER",
    "contact_source": "external",
    "contact_info": {
        "contact_id": 456,
        "lastname": "García",
        "firstname": "Juan",
        "fullname": "Juan García",
        "email": "juan.garcia@empresa.com",
        "phone": "+34 123 456 789",
        "company_id": 10,
        "contact_source": "external"
    },
    "element_contact_id": 791,
    "timestamp": "2023-12-01 15:30:00"
}</code></pre>';
print '</div>';

// Remove Contact from Ticket
print '<div class="api-endpoint">';
print '<h4>9. Eliminar Contacto de un Ticket</h4>';
print '<p><strong>DELETE</strong> <code>/tickets/{id}/contacts/{contact_id}/{contact_source}</code></p>';
print '<p><strong>Descripción:</strong> Elimina un contacto de un ticket usando el sistema nativo de Dolibarr.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID del ticket</li>';
print '<li><code>contact_id</code> (int): ID del contacto o usuario a eliminar</li>';
print '<li><code>contact_source</code> (string): "external" para contactos externos o "internal" para usuarios internos</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>DELETE /api/index.php/dolibarrmodernfrontend/tickets/123/contacts/456/external
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta exitosa:</strong></p>';
print '<pre><code>{
    "success": true,
    "message": "Contact removed successfully from ticket",
    "ticket_id": 123,
    "ticket_ref": "TIC2023-001",
    "contact_id": 456,
    "contact_source": "external",
    "element_contact_id": 791,
    "timestamp": "2023-12-01 15:35:00"
}</code></pre>';
print '</div>';

// Get User Documents (ECM Manual Directories)
print '<div class="api-endpoint">';
print '<h4>10. Obtener Documentos del Usuario (Directorios Manuales ECM)</h4>';
print '<p><strong>GET</strong> <code>/user/{id}/documents</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los directorios manuales y archivos del usuario desde el módulo ECM (Gestión Electrónica de Documentos).</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID del usuario</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>GET /api/index.php/dolibarrmodernfrontend/user/1/documents
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta:</strong></p>';
print '<pre><code>{
    "user_id": 1,
    "user_login": "admin",
    "user_fullname": "Administrador Sistema",
    "directories": [
        {
            "directory_id": 15,
            "label": "Base de conocimientos",
            "description": "Documentación técnica y manuales",
            "parent_id": null,
            "relativepath": "Base de conocimientos",
            "date_created": "2023-01-15 10:30:00",
            "date_modified": "2023-12-01 14:20:00",
            "files_count": 1,
            "files": [
                {
                    "name": "manual_usuario.pdf",
                    "size": 2048000,
                    "date": "2023-11-15 12:00:00",
                    "type": "application/pdf",
                    "relativepath": "Base de conocimientos/manual_usuario.pdf",
                    "download_url": "/document.php?modulepart=ecm&file=Base%20de%20conocimientos%2Fmanual_usuario.pdf",
                    "file_info": {
                        "file_id": 123,
                        "label": "Manual de Usuario v2.0",
                        "gen_or_uploaded": "uploaded",
                        "date_c": "2023-11-15 12:00:00",
                        "date_m": "2023-11-15 12:00:00"
                    }
                }
            ]
        },
        {
            "directory_id": 16,
            "label": "CONTRATOS",
            "description": "",
            "parent_id": null,
            "relativepath": "CONTRATOS",
            "date_created": "2023-02-10 09:00:00",
            "date_modified": null,
            "files_count": 0,
            "files": []
        }
    ],
    "common_directories": [
        {
            "directory_id": 1,
            "label": "Branding",
            "description": "Material corporativo",
            "parent_id": null,
            "relativepath": "Branding",
            "date_created": "2022-12-01 10:00:00",
            "date_modified": "2023-10-05 11:30:00",
            "files_count": 3,
            "files": [
                {
                    "name": "logo.png",
                    "size": 45678,
                    "date": "2023-10-05 11:30:00",
                    "type": "image/png",
                    "relativepath": "Branding/logo.png",
                    "download_url": "/document.php?modulepart=ecm&file=Branding%2Flogo.png"
                }
            ]
        }
    ],
    "total_user_directories": 2,
    "total_common_directories": 1,
    "timestamp": "2023-12-01 16:45:00"
}</code></pre>';
print '<p><strong>Campos de Respuesta:</strong></p>';
print '<ul>';
print '<li><code>directories</code>: Directorios específicos del usuario (fk_user = user_id)</li>';
print '<li><code>common_directories</code>: Directorios comunes accesibles (sin usuario específico)</li>';
print '<li><code>files</code>: Array de archivos dentro de cada directorio</li>';
print '<li><code>file_info</code>: Información adicional del archivo desde llx_ecm_files (si existe)</li>';
print '<li><code>download_url</code>: URL para descargar el archivo</li>';
print '</ul>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Lista directorios manuales del usuario desde ECM</li>';
print '<li>✅ Lista directorios comunes (compartidos)</li>';
print '<li>✅ Incluye todos los archivos de cada directorio</li>';
print '<li>✅ Información completa de archivos (nombre, tamaño, tipo MIME, fecha)</li>';
print '<li>✅ URLs de descarga directa</li>';
print '<li>✅ Metadatos adicionales desde llx_ecm_files</li>';
print '<li>✅ Soporte para jerarquía de directorios (parent_id)</li>';
print '</ul>';
print '</div>';

// Get Task Documents
print '<div class="api-endpoint">';
print '<h4>11. Obtener Documentos de una Tarea de Proyecto</h4>';
print '<p><strong>GET</strong> <code>/task/{id}/documents</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los documentos (archivos) subidos a una tarea de proyecto específica.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID de la tarea</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>GET /api/index.php/dolibarrmodernfrontend/task/45/documents
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta:</strong></p>';
print '<pre><code>{
    "task_id": 45,
    "task_ref": "T001",
    "task_label": "Desarrollo del módulo de pagos",
    "project_id": 10,
    "project_ref": "PROJ2023-001",
    "project_title": "Sistema de Gestión Comercial",
    "upload_dir": "/var/www/dolibarr/documents/project/PROJ2023-001/task/T001",
    "dir_exists": true,
    "documents": [
        {
            "name": "especificaciones_tecnicas.pdf",
            "size": 1024000,
            "date": "2023-11-20 14:30:00",
            "type": "application/pdf",
            "relativepath": "projet/PROJ2023-001/task/T001/especificaciones_tecnicas.pdf",
            "download_url": "/document.php?modulepart=project_task&file=PROJ2023-001%2Ftask%2FT001%2Fespecificaciones_tecnicas.pdf",
            "file_info": {
                "file_id": 234,
                "label": "Especificaciones Técnicas v1.0",
                "gen_or_uploaded": "uploaded",
                "date_c": "2023-11-20 14:30:00",
                "date_m": "2023-11-20 14:30:00"
            }
        }
    ],
    "total_documents": 1,
    "timestamp": "2023-12-01 17:00:00"
}</code></pre>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Información de la tarea y proyecto asociado</li>';
print '<li>✅ Lista completa de archivos de la tarea</li>';
print '<li>✅ Metadatos desde llx_ecm_files (si existe)</li>';
print '<li>✅ URLs de descarga directa con modulepart=project_task</li>';
print '<li>✅ Ruta física del directorio de documentos</li>';
print '</ul>';
print '</div>';

// Upload Task Document
print '<div class="api-endpoint">';
print '<h4>11b. Subir Documento a una Tarea de Proyecto</h4>';
print '<p><strong>POST</strong> <code>/task/{id}/documents</code></p>';
print '<p><strong>Descripción:</strong> Sube un archivo a una tarea de proyecto específica usando codificación base64.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID de la tarea</li>';
print '</ul>';
print '<p><strong>Cuerpo de la solicitud (JSON):</strong></p>';
print '<pre><code>{
    "filename": "especificaciones.pdf",
    "filecontent": "JVBERi0xLjQKJeLjz9MKMSAwIG9iago8PAovQ...", 
    "overwriteifexists": false,
    "label": "Especificaciones Técnicas",
    "description": "Documento con las especificaciones del proyecto"
}</code></pre>';
print '<p><strong>Campos:</strong></p>';
print '<ul>';
print '<li><code>filename</code> (string, requerido): Nombre del archivo</li>';
print '<li><code>filecontent</code> (string, requerido): Contenido del archivo codificado en base64</li>';
print '<li><code>overwriteifexists</code> (boolean, opcional): Sobrescribir si el archivo ya existe. Por defecto: false</li>';
print '<li><code>label</code> (string, opcional): Etiqueta del archivo para llx_ecm_files</li>';
print '<li><code>description</code> (string, opcional): Descripción del archivo</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>POST /api/index.php/dolibarrmodernfrontend/task/45/documents
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "filename": "diagrama.png",
    "filecontent": "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "overwriteifexists": false,
    "label": "Diagrama de arquitectura"
}</code></pre>';
print '<p><strong>Respuesta exitosa:</strong></p>';
print '<pre><code>{
    "success": true,
    "message": "File uploaded successfully",
    "task_id": 45,
    "task_ref": "T001",
    "task_label": "Desarrollo del módulo",
    "project_id": 10,
    "project_ref": "PROJ2023-001",
    "project_title": "Sistema de Gestión",
    "file": {
        "name": "diagrama.png",
        "size": 95,
        "type": "image/png",
        "relativepath": "projet/PROJ2023-001/task/T001/diagrama.png",
        "download_url": "/document.php?modulepart=project_task&file=PROJ2023-001%2Ftask%2FT001%2Fdiagrama.png",
        "ecm_file_id": 456
    },
    "timestamp": "2023-12-01 18:00:00"
}</code></pre>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Subida de archivos mediante base64</li>';
print '<li>✅ Creación automática del directorio de la tarea si no existe</li>';
print '<li>✅ Prevención de sobrescritura accidental</li>';
print '<li>✅ Registro automático en llx_ecm_files (si ECM está activado)</li>';
print '<li>✅ Retorna URL de descarga directa del archivo</li>';
print '<li>✅ Sanitización de nombres de archivo</li>';
print '</ul>';
print '<p><strong>Códigos de Error:</strong></p>';
print '<ul>';
print '<li><strong>400:</strong> Campos requeridos faltantes o base64 inválido</li>';
print '<li><strong>401:</strong> Sin permisos de escritura</li>';
print '<li><strong>404:</strong> Tarea o proyecto no encontrado</li>';
print '<li><strong>409:</strong> El archivo ya existe (usar overwriteifexists=true)</li>';
print '<li><strong>500:</strong> Error al crear directorio o guardar archivo</li>';
print '</ul>';
print '</div>';

// Get All Project Tasks Documents
print '<div class="api-endpoint">';
print '<h4>12. Obtener Documentos de Todas las Tareas de un Proyecto</h4>';
print '<p><strong>GET</strong> <code>/project/{id}/tasks/documents</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los documentos de todas las tareas de un proyecto específico.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID del proyecto</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>GET /api/index.php/dolibarrmodernfrontend/project/10/tasks/documents
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta:</strong></p>';
print '<pre><code>{
    "project_id": 10,
    "project_ref": "PROJ2023-001",
    "project_title": "Sistema de Gestión Comercial",
    "tasks": [
        {
            "task_id": 45,
            "task_ref": "T001",
            "task_label": "Desarrollo del módulo de pagos",
            "documents": [
                {
                    "name": "especificaciones.pdf",
                    "size": 1024000,
                    "date": "2023-11-20 14:30:00",
                    "type": "application/pdf",
                    "relativepath": "projet/PROJ2023-001/task/T001/especificaciones.pdf",
                    "download_url": "/document.php?modulepart=project_task&file=..."
                }
            ],
            "total_documents": 1
        },
        {
            "task_id": 46,
            "task_ref": "T002",
            "task_label": "Testing y QA",
            "documents": [
                {
                    "name": "plan_pruebas.xlsx",
                    "size": 512000,
                    "date": "2023-11-22 09:15:00",
                    "type": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    "relativepath": "projet/PROJ2023-001/task/T002/plan_pruebas.xlsx",
                    "download_url": "/document.php?modulepart=project_task&file=..."
                }
            ],
            "total_documents": 1
        }
    ],
    "total_tasks": 2,
    "total_documents": 2,
    "timestamp": "2023-12-01 17:05:00"
}</code></pre>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Vista consolidada de todos los documentos del proyecto</li>';
print '<li>✅ Agrupa documentos por tarea</li>';
print '<li>✅ Incluye tareas sin documentos</li>';
print '<li>✅ Contador total de tareas y documentos</li>';
print '<li>✅ Ideal para obtener una visión general del proyecto</li>';
print '</ul>';
print '<p><strong>Casos de Uso:</strong></p>';
print '<ul>';
print '<li>📊 Dashboard de proyecto con estadísticas de documentos</li>';
print '<li>📁 Explorador de archivos del proyecto completo</li>';
print '<li>🔍 Búsqueda de documentos en todas las tareas</li>';
print '<li>📦 Descarga masiva de documentación del proyecto</li>';
print '</ul>';
print '</div>';

// Get Task Contacts
print '<div class="api-endpoint">';
print '<h4>13. Obtener Contactos/Recursos Asignados a una Tarea</h4>';
print '<p><strong>GET</strong> <code>/task/{id}/contacts</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todos los contactos (usuarios internos o contactos externos) asignados a una tarea de proyecto.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID de la tarea</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>GET /api/index.php/dolibarrmodernfrontend/task/45/contacts
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta:</strong></p>';
print '<pre><code>{
    "task_id": 45,
    "task_ref": "T001",
    "task_label": "Desarrollo del módulo de pagos",
    "contacts": [
        {
            "contact_id": 15,
            "element_contact_id": 234,
            "user_id": 15,
            "lastname": "López",
            "firstname": "María",
            "fullname": "María López",
            "email": "maria.lopez@empresa.com",
            "phone": "+34 111 222 333",
            "phone_mobile": "+34 444 555 666",
            "company_name": "Internal User",
            "contact_type_code": "TASKEXECUTIVE",
            "contact_type_label": "Ejecutor de tarea",
            "contact_source": "internal",
            "status": 1
        },
        {
            "contact_id": 20,
            "element_contact_id": 235,
            "user_id": 20,
            "lastname": "García",
            "firstname": "Juan",
            "fullname": "Juan García",
            "email": "juan.garcia@empresa.com",
            "phone": "+34 555 666 777",
            "phone_mobile": "+34 888 999 000",
            "company_name": "Internal User",
            "contact_type_code": "TASKMANAGER",
            "contact_type_label": "Responsable de tarea",
            "contact_source": "internal",
            "status": 1
        }
    ],
    "count": 2
}</code></pre>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Lista todos los recursos asignados a la tarea</li>';
print '<li>✅ Soporta usuarios internos y contactos externos</li>';
print '<li>✅ Incluye información completa del contacto (nombre, email, teléfono)</li>';
print '<li>✅ Muestra el rol/tipo de contacto (TASKEXECUTIVE, TASKMANAGER)</li>';
print '<li>✅ Usa el sistema nativo de Dolibarr (llx_element_contact)</li>';
print '</ul>';
print '</div>';

// Assign User to Task
print '<div class="api-endpoint">';
print '<h4>14. Asignar Usuario a una Tarea con Rol</h4>';
print '<p><strong>POST</strong> <code>/task/{id}/assign</code></p>';
print '<p><strong>Descripción:</strong> Asigna un usuario interno a una tarea de proyecto con un rol específico usando el sistema nativo de Dolibarr.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID de la tarea</li>';
print '</ul>';
print '<p><strong>Cuerpo de la solicitud (JSON):</strong></p>';
print '<pre><code>{
    "user_id": 121,
    "role": "TASKEXECUTIVE"
}</code></pre>';
print '<p><strong>Campos:</strong></p>';
print '<ul>';
print '<li><code>user_id</code> (int, requerido): ID del usuario a asignar</li>';
print '<li><code>role</code> (string, requerido): Rol del usuario en la tarea</li>';
print '</ul>';
print '<p><strong>Roles Válidos para Tareas:</strong></p>';
print '<ul>';
print '<li><code>TASKEXECUTIVE</code> - Ejecutor de la tarea (worker)</li>';
print '<li><code>TASKMANAGER</code> - Responsable/Manager de la tarea</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>POST /api/index.php/dolibarrmodernfrontend/task/45/assign
DOLAPIKEY: your_api_key
Content-Type: application/json

{
    "user_id": 121,
    "role": "TASKEXECUTIVE"
}</code></pre>';
print '<p><strong>Respuesta exitosa:</strong></p>';
print '<pre><code>{
    "success": true,
    "message": "User assigned successfully to task",
    "task_id": 45,
    "task_ref": "T001",
    "task_label": "Desarrollo del módulo de pagos",
    "role": "TASKEXECUTIVE",
    "user_info": {
        "user_id": 121,
        "contact_id": 121,
        "lastname": "Martínez",
        "firstname": "Carlos",
        "fullname": "Carlos Martínez",
        "email": "carlos.martinez@empresa.com",
        "phone": "+34 123 456 789",
        "role": "TASKEXECUTIVE",
        "contact_source": "internal"
    },
    "element_contact_id": 236,
    "timestamp": "2023-12-01 18:30:00"
}</code></pre>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Asignación de usuarios con roles específicos</li>';
print '<li>✅ Validación de usuario y rol</li>';
print '<li>✅ Prevención de asignaciones duplicadas</li>';
print '<li>✅ Usa el método nativo add_contact() de Dolibarr</li>';
print '<li>✅ Compatible con la gestión de recursos de tareas de Dolibarr</li>';
print '<li>✅ Retorna información completa del usuario asignado</li>';
print '</ul>';
print '<p><strong>Códigos de Error:</strong></p>';
print '<ul>';
print '<li><strong>400:</strong> Campos requeridos faltantes o rol inválido</li>';
print '<li><strong>401:</strong> Sin permisos de escritura en proyectos</li>';
print '<li><strong>404:</strong> Tarea o usuario no encontrado</li>';
print '<li><strong>409:</strong> Usuario ya asignado a esta tarea con este rol</li>';
print '<li><strong>500:</strong> Error al asignar usuario</li>';
print '</ul>';
print '</div>';

// Remove Contact from Task
print '<div class="api-endpoint">';
print '<h4>15. Eliminar Contacto de una Tarea</h4>';
print '<p><strong>DELETE</strong> <code>/task/{id}/contacts/{contact_id}/{contact_source}</code></p>';
print '<p><strong>Descripción:</strong> Elimina un contacto (usuario interno o contacto externo) de una tarea de proyecto.</p>';
print '<p><strong>Parámetros:</strong></p>';
print '<ul>';
print '<li><code>id</code> (int): ID de la tarea</li>';
print '<li><code>contact_id</code> (int): ID del contacto o usuario a eliminar</li>';
print '<li><code>contact_source</code> (string): "internal" para usuarios internos o "external" para contactos externos</li>';
print '</ul>';
print '<p><strong>Ejemplo:</strong></p>';
print '<pre><code>DELETE /api/index.php/dolibarrmodernfrontend/task/45/contacts/121/internal
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta exitosa:</strong></p>';
print '<pre><code>{
    "success": true,
    "message": "Contact removed successfully from task",
    "task_id": 45,
    "task_ref": "T001",
    "task_label": "Desarrollo del módulo de pagos",
    "contact_id": 121,
    "contact_source": "internal",
    "element_contact_id": 236,
    "timestamp": "2023-12-01 18:35:00"
}</code></pre>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Eliminación de asignaciones de usuarios/contactos</li>';
print '<li>✅ Soporta usuarios internos y contactos externos</li>';
print '<li>✅ Usa el método nativo delete_contact() de Dolibarr</li>';
print '<li>✅ Validación de existencia del contacto en la tarea</li>';
print '</ul>';
print '<p><strong>Códigos de Error:</strong></p>';
print '<ul>';
print '<li><strong>400:</strong> contact_source inválido</li>';
print '<li><strong>401:</strong> Sin permisos de escritura en proyectos</li>';
print '<li><strong>404:</strong> Tarea o contacto no encontrado en la tarea</li>';
print '<li><strong>500:</strong> Error al eliminar contacto</li>';
print '</ul>';
print '</div>';

print '<h3>Códigos de Respuesta</h3>';
print '<ul>';
print '<li><strong>200:</strong> Operación exitosa</li>';
print '<li><strong>201:</strong> Recurso creado exitosamente</li>';
print '<li><strong>400:</strong> Solicitud incorrecta</li>';
print '<li><strong>401:</strong> No autorizado</li>';
print '<li><strong>404:</strong> Recurso no encontrado</li>';
print '<li><strong>409:</strong> Conflicto (ej: vinculación ya existe)</li>';
print '<li><strong>500:</strong> Error interno del servidor</li>';
print '</ul>';

// Get Email Templates
print '<div class="api-endpoint">';
print '<h4>16. Obtener Plantillas de Correo Electrónico</h4>';
print '<p><strong>GET</strong> <code>/emailtemplates</code></p>';
print '<p><strong>Descripción:</strong> Obtiene todas las plantillas de correo electrónico configuradas en Dolibarr con sus detalles completos.</p>';
print '<p><strong>Parámetros de Query (opcionales):</strong></p>';
print '<ul>';
print '<li><code>type_template</code> (string): Filtrar por tipo de plantilla (ej: "ticket", "invoice", "order", "thirdparty")</li>';
print '<li><code>lang</code> (string): Filtrar por código de idioma (ej: "es_ES", "en_US", "fr_FR")</li>';
print '<li><code>enabled</code> (int): Filtrar por estado habilitado (0 o 1)</li>';
print '<li><code>private</code> (int): Filtrar por privacidad (0=pública, 1=privada)</li>';
print '</ul>';
print '<p><strong>Ejemplos:</strong></p>';
print '<pre><code>// Obtener todas las plantillas
GET /api/index.php/dolibarrmodernfrontend/emailtemplates
DOLAPIKEY: your_api_key

// Filtrar por tipo (tickets)
GET /api/index.php/dolibarrmodernfrontend/emailtemplates?type_template=ticket
DOLAPIKEY: your_api_key

// Filtrar por idioma (español)
GET /api/index.php/dolibarrmodernfrontend/emailtemplates?lang=es_ES
DOLAPIKEY: your_api_key

// Solo plantillas habilitadas
GET /api/index.php/dolibarrmodernfrontend/emailtemplates?enabled=1
DOLAPIKEY: your_api_key

// Filtros combinados
GET /api/index.php/dolibarrmodernfrontend/emailtemplates?type_template=ticket&lang=es_ES&enabled=1
DOLAPIKEY: your_api_key</code></pre>';
print '<p><strong>Respuesta:</strong></p>';
print '<pre><code>{
    "success": true,
    "message": "Email templates retrieved successfully",
    "filters_applied": {
        "type_template": "ticket",
        "lang": "es_ES",
        "enabled": 1,
        "private": "all"
    },
    "templates": [
        {
            "id": 15,
            "entity": 1,
            "module": "ticket",
            "label": "Ticket - Respuesta al cliente",
            "type_template": "ticket",
            "lang": "es_ES",
            "private": 0,
            "subject": "Re: Ticket #{ticket_ref} - {ticket_subject}",
            "content": "&lt;p&gt;Estimado/a {contact_name},&lt;/p&gt;&lt;p&gt;En relación a su ticket __TICKET_REF__ con asunto __TICKET_SUBJECT__...&lt;/p&gt;",
            "content_lines": "",
            "joinfiles": 1,
            "enabled": "1",
            "active": 1,
            "position": 10,
            "date_created": "2023-01-15 10:30:00",
            "date_modified": "2023-11-20 14:45:00",
            "user_info": {
                "user_id": 1,
                "login": "admin",
                "fullname": "Administrador Sistema"
            },
            "variables": [
                "TICKET_REF",
                "TICKET_SUBJECT",
                "TICKET_MESSAGE",
                "TICKET_TRACKID"
            ],
            "is_public": true,
            "is_enabled": true
        },
        {
            "id": 16,
            "entity": 1,
            "module": "ticket",
            "label": "Ticket - Cierre automático",
            "type_template": "ticket",
            "lang": "es_ES",
            "private": 0,
            "subject": "Ticket __TICKET_REF__ cerrado",
            "content": "&lt;p&gt;Su ticket ha sido cerrado.&lt;/p&gt;",
            "content_lines": "",
            "joinfiles": 0,
            "enabled": "1",
            "active": 1,
            "position": 20,
            "date_created": "2023-02-10 09:00:00",
            "date_modified": null,
            "user_info": null,
            "variables": [
                "TICKET_REF"
            ],
            "is_public": true,
            "is_enabled": true
        }
    ],
    "total_count": 2,
    "available_types": [
        "ticket",
        "invoice",
        "order",
        "proposal",
        "thirdparty",
        "supplier_invoice",
        "supplier_order"
    ],
    "available_langs": [
        "es_ES",
        "en_US",
        "fr_FR",
        "de_DE"
    ],
    "timestamp": "2023-12-01 19:00:00",
    "usage_info": {
        "description": "Email templates can be filtered by type, language, enabled status, and privacy",
        "filter_examples": {
            "by_type": "/api/index.php/dolibarrmodernfrontend/emailtemplates?type_template=ticket",
            "by_lang": "/api/index.php/dolibarrmodernfrontend/emailtemplates?lang=es_ES",
            "enabled_only": "/api/index.php/dolibarrmodernfrontend/emailtemplates?enabled=1",
            "public_only": "/api/index.php/dolibarrmodernfrontend/emailtemplates?private=0",
            "combined": "/api/index.php/dolibarrmodernfrontend/emailtemplates?type_template=ticket&lang=es_ES&enabled=1"
        },
        "variables_info": "The \'variables\' field lists all template variables found in the format __VARIABLE__"
    }
}</code></pre>';
print '<p><strong>Campos de Respuesta:</strong></p>';
print '<ul>';
print '<li><code>id</code>: ID único de la plantilla</li>';
print '<li><code>label</code>: Nombre descriptivo de la plantilla</li>';
print '<li><code>type_template</code>: Tipo/módulo al que pertenece (ticket, invoice, etc.)</li>';
print '<li><code>lang</code>: Código de idioma (es_ES, en_US, etc.)</li>';
print '<li><code>subject</code>: Asunto del email</li>';
print '<li><code>content</code>: Contenido HTML del email</li>';
print '<li><code>content_lines</code>: Contenido adicional para líneas</li>';
print '<li><code>joinfiles</code>: Si adjunta archivos automáticamente (0 o 1)</li>';
print '<li><code>variables</code>: Array de variables disponibles en la plantilla (formato __VARIABLE__)</li>';
print '<li><code>is_public</code>: Si es pública (true) o privada (false)</li>';
print '<li><code>is_enabled</code>: Si está habilitada (true) o deshabilitada (false)</li>';
print '<li><code>user_info</code>: Información del usuario creador (si existe)</li>';
print '<li><code>available_types</code>: Lista de todos los tipos de plantillas disponibles</li>';
print '<li><code>available_langs</code>: Lista de todos los idiomas disponibles</li>';
print '</ul>';
print '<p><strong>Características:</strong></p>';
print '<ul>';
print '<li>✅ Obtiene todas las plantillas de correo de Dolibarr</li>';
print '<li>✅ Filtrado flexible por tipo, idioma, estado y privacidad</li>';
print '<li>✅ Extrae automáticamente las variables de las plantillas</li>';
print '<li>✅ Información completa de cada plantilla (asunto, contenido, configuración)</li>';
print '<li>✅ Lista de tipos y idiomas disponibles para referencia</li>';
print '<li>✅ Información del usuario creador de plantillas privadas</li>';
print '<li>✅ Soporta plantillas HTML y texto plano</li>';
print '<li>✅ Compatible con todas las entidades de Dolibarr</li>';
print '</ul>';
print '<p><strong>Variables Comunes en Plantillas:</strong></p>';
print '<ul>';
print '<li><code>__TICKET_REF__</code> - Referencia del ticket</li>';
print '<li><code>__TICKET_SUBJECT__</code> - Asunto del ticket</li>';
print '<li><code>__TICKET_MESSAGE__</code> - Mensaje del ticket</li>';
print '<li><code>__TICKET_TRACKID__</code> - ID de seguimiento</li>';
print '<li><code>__INVOICE_REF__</code> - Referencia de factura</li>';
print '<li><code>__ORDER_REF__</code> - Referencia de pedido</li>';
print '<li><code>__THIRDPARTY_NAME__</code> - Nombre del tercero</li>';
print '<li><code>__USER_FULLNAME__</code> - Nombre completo del usuario</li>';
print '<li><code>__SIGNATURE__</code> - Firma del usuario</li>';
print '</ul>';
print '<p><strong>Casos de Uso:</strong></p>';
print '<ul>';
print '<li>📧 Obtener plantillas para selector de emails en frontend</li>';
print '<li>🌐 Listar plantillas por idioma para usuarios multilingües</li>';
print '<li>🎨 Previsualizar plantillas antes de enviar emails</li>';
print '<li>📝 Gestión de plantillas desde aplicaciones externas</li>';
print '<li>🔍 Búsqueda de plantillas por tipo de documento</li>';
print '<li>⚙️ Configuración de emails automatizados</li>';
print '</ul>';
print '<p><strong>Códigos de Error:</strong></p>';
print '<ul>';
print '<li><strong>401:</strong> Sin permisos de administrador o del módulo</li>';
print '<li><strong>500:</strong> Error al consultar la base de datos</li>';
print '</ul>';
print '</div>';

print '<div style="margin-top: 30px; padding: 15px; background-color: #e8f5e9; border-radius: 5px;">';
print '<strong>✅ Versión del Módulo:</strong> dolibarrmodernfrontend v1.2.3<br>';
print '<strong>🆕 Nuevo en v1.2.3:</strong> Endpoint para obtener plantillas de correo electrónico (/emailtemplates)<br>';
print '<strong>📅 Actualizado:</strong> ' . date('Y-m-d') . '<br>';
print '<strong>🔗 Archivos de Prueba:</strong> ';
print '<a href="test_api.php">test_api.php</a> | ';
print '<a href="test_emailtemplates_api.php">test_emailtemplates_api.php</a> | ';
print '<a href="test_email_api.php">test_email_api.php</a> | ';
print '<a href="test_contacts_api.php">test_contacts_api.php</a>';
print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();
?>
