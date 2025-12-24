# Changelog - dolibarmodernfrontend

Todas las modificaciones importantes de este proyecto serán documentadas en este archivo.

## [1.2.6] - 2025-10-19

### ✅ Añadido - Endpoint de Creación de Mensajes con Contacto Personalizado
- **Nuevo endpoint POST**: `POST /tickets/{ticket_id}/newmessage` - Crear mensaje en ticket con contacto personalizado
- **Atribución flexible**: Permite especificar qué contacto crea el mensaje (ej: contact_id 115)
- **Método nativo**: Usa `newMessage()` de Dolibarr para máxima compatibilidad
- **Registro automático**: Se registra en historial con el contacto especificado
- **Soporte completo**: Mensajes públicos/privados, con/sin notificación email

### 🔧 Características del Endpoint
- **Atribución de contacto**: Especifica el ID del contacto que crea el mensaje
- **Flexibilidad**: Si no se especifica contact_id, usa el usuario de la API
- **Subject automático**: Usa el subject del ticket automáticamente
- **Mensajes privados**: Parámetro `private` para mensajes internos (0=público, 1=privado)
- **Notificación email**: Parámetro `send_email` para enviar notificación (0=no, 1=sí)
- **Permisos flexibles**: Acepta permisos de ticket o dolibarmodernfrontend
- **Contactos de empresa**: Usa contactos relacionados con la empresa del ticket

### 📋 Parámetros del Endpoint
- `ticket_id` (int, requerido): ID del ticket (en la URL)
- `message` (string, requerido): Contenido del mensaje
- `contact_id` (int, opcional): ID del contacto que crea el mensaje (por defecto: 0 = usuario API)
- `private` (int, opcional): Mensaje privado (0=público, 1=privado, por defecto: 0)
- `send_email` (int, opcional): Enviar notificación email (0=no, 1=sí, por defecto: 0)

**Nota**: El subject no es necesario, se usa automáticamente el asunto del ticket.

### 🎯 Formato de Entrada
```bash
POST /api/index.php/dolibarmodernfrontend/tickets/123/newmessage
Content-Type: application/x-www-form-urlencoded

message=Mensaje de prueba&contact_id=115&private=0&send_email=0
```

### 📋 Información Retornada
```json
{
  "success": true,
  "message": "Message added successfully to ticket",
  "ticket_id": 123,
  "ticket_ref": "TK2310-0001",
  "message_id": 456,
  "subject": "Asunto del ticket",
  "message_content": "Mensaje de prueba",
  "private": false,
  "send_email": false,
  "created_by_contact_id": 115,
  "created_by_user_id": 0,
  "created_by_login": "contacto@empresa.com",
  "created_by_name": "Juan Pérez",
  "timestamp": "2025-10-19 22:45:00",
  "method": "native_dolibarr_newMessage"
}
```

### 🔄 Casos de Uso
- 🤖 **Integraciones API**: Crear mensajes desde sistemas externos atribuyéndolos al usuario correcto
- 📱 **Apps móviles**: Permitir que usuarios creen mensajes desde apps móviles
- 🔄 **Sincronización**: Importar mensajes de otros sistemas manteniendo autoría original
- 🎯 **Automatización**: Scripts que crean mensajes en nombre de usuarios específicos
- 📧 **Webhooks**: Recibir mensajes de plataformas externas y registrarlos con el usuario correcto

### 🧪 Testing
- **Nuevo archivo**: `test_newmessage_api.php` con verificación completa
- **Pruebas incluidas**: Usuario API, usuario personalizado, mensajes privados, notificaciones email
- **Ejemplos de uso**: cURL y JavaScript para integración
- **Escenarios de prueba**: 7 casos de uso diferentes documentados

## [1.2.5] - 2025-10-15

### ✅ Añadido - Endpoint de URLs de Validación de ID Profesionales
- **Nuevo endpoint GET**: `GET /idprofvalidatorurl` - Obtener URLs de validación de IDs profesionales por país
- **Detección automática**: Por defecto devuelve solo el país de la empresa de Dolibarr (mysoc)
- **Parámetros opcionales**: `?all=1` para todos los países, `?country=XX` para país específico
- **Cobertura internacional**: Soporte para FR, GB, UK, ES, IN, DZ, PT
- **Basado en Dolibarr nativo**: Extrae las mismas URLs que usa la función `id_prof_url` de Dolibarr
- **Sin llamadas externas**: Devuelve solo las plantillas de URL para que el frontend las use

### 🔧 Características del Endpoint
- **Detección inteligente**: Detecta automáticamente el país de la empresa configurada en Dolibarr
- **Filtrado flexible**: 3 modos de operación (company, specific, all)
- **URLs por país**: Devuelve plantillas de URL para validar IDs profesionales según código de país
- **Información completa**: Nombre del ID (SIREN, NIF, TIN, etc.), descripción y URL template
- **Placeholder dinámico**: Usa `{IDPROF}` como marcador para reemplazar con el ID real
- **Países soportados**: Francia (SIREN), Reino Unido (Company Number), España (NIF/CIF), India (TIN), Argelia (NIF), Portugal (NIF)
- **Permisos flexibles**: Acepta permisos de societe o dolibarmodernfrontend
- **Manejo de errores**: Respuesta informativa si el país no tiene URLs disponibles

### 📋 Información Retornada
- ✅ **Modo de filtro**: Indica si se muestra el país de la empresa, específico o todos
- ✅ **País de la empresa**: Código y nombre del país configurado en Dolibarr
- ✅ **Código de país**: ISO 3166-1 alpha-2 (FR, ES, GB, etc.)
- ✅ **Nombre del país**: Nombre completo en inglés
- ✅ **Tipo de ID**: Nombre del identificador profesional (SIREN, NIF, etc.)
- ✅ **URL template**: Plantilla con placeholder `{IDPROF}` para reemplazar
- ✅ **Descripción**: Descripción del servicio de validación
- ✅ **Instrucciones de uso**: Ejemplo de cómo usar las URLs
- ✅ **Notas contextuales**: Información sobre el modo de filtrado aplicado

### 🎯 Modos de Operación

**1. Modo Company (por defecto)**
```bash
GET /idprofvalidatorurl
# Devuelve solo el país de la empresa configurada en Dolibarr
```

**2. Modo Specific**
```bash
GET /idprofvalidatorurl?country=ES
# Devuelve solo el país solicitado (España en este caso)
```

**3. Modo All**
```bash
GET /idprofvalidatorurl?all=1
# Devuelve todos los países disponibles
```

### 🎯 Formato de Respuesta (Modo Company)
```json
{
  "success": true,
  "message": "ID professional validator URLs retrieved successfully",
  "filter_mode": "company",
  "company_country_code": "ES",
  "company_country_name": "Spain",
  "countries_count": 1,
  "validator_urls": {
    "ES": {
      "country_code": "ES",
      "country_name": "Spain",
      "idprof1": {
        "name": "NIF/CIF",
        "url_template": "http://www.e-informa.es/servlet/app/portal/ENTP/screen/SProducto/prod/ETIQUETA_EMPRESA/nif/{IDPROF}",
        "description": "Spanish company information",
        "placeholder": "{IDPROF}"
      }
    }
  },
  "usage": {
    "description": "Replace {IDPROF} in url_template with the actual professional ID number (without spaces)",
    "example": "For France SIREN 123456789: https://annuaire-entreprises.data.gouv.fr/entreprise/123456789"
  },
  "note": "Showing only your company country. Use ?all=1 to get all countries."
}
```

### 🔄 Casos de Uso
- 🔍 Validar IDs profesionales de empresas desde el frontend
- 🌍 Obtener URLs de validación según el país de la empresa automáticamente
- 📋 Mostrar enlaces de verificación en fichas de terceros
- 🔗 Integración con formularios de creación/edición de empresas
- 🎯 Simplificar la interfaz mostrando solo el país relevante por defecto
- 🌐 Permitir consulta de otros países cuando sea necesario

## [1.2.4] - 2025-10-05

### ✅ Añadido - Endpoints de Documentos de Tareas de Proyectos
- **Nuevo endpoint GET**: `GET /task/{id}/documents` - Obtener documentos de una tarea específica
- **Nuevo endpoint POST**: `POST /task/{id}/documents` - Subir documento a una tarea específica
- **Nuevo endpoint GET**: `GET /project/{id}/tasks/documents` - Obtener documentos de todas las tareas de un proyecto
- **Integración Proyectos**: Acceso completo a documentos subidos a tareas de proyectos
- **Sistema nativo**: Usa estructura nativa de directorios de Dolibarr para proyectos

### 🔧 Correcciones - Endpoint GET Task Documents
- **Búsqueda mejorada**: Ahora busca en múltiples ubicaciones posibles de directorios
- **Consulta llx_ecm_files**: Usa `src_object_type='project_task'` y `src_object_id` para encontrar archivos
- **Rutas dinámicas**: Usa el `filepath` de la base de datos cuando está disponible
- **Mayor compatibilidad**: Detecta archivos independientemente de la estructura de directorios
- **Debug info**: Incluye información de depuración para facilitar troubleshooting

### 🔧 Correcciones - Endpoint POST Task Documents
- **Estructura nativa**: Guarda archivos en `projet/{projectref}/` como lo hace Dolibarr nativamente
- **Vinculación correcta**: Usa `src_object_type` y `src_object_id` en llx_ecm_files para vincular a la tarea
- **Archivo temporal**: Maneja correctamente archivos temporales antes de moverlos al destino
- **Permisos correctos**: Establece permisos 0644 en archivos guardados
- **Gestión de errores**: Mejor manejo de errores y limpieza de archivos temporales
- **Respuesta completa**: Incluye ruta física, vinculación y estado del registro ECM

### 🔧 Características de Endpoints de Tareas
- **Documentos por tarea**: Lista todos los archivos subidos a una tarea específica
- **Subida de archivos**: Carga archivos mediante base64 directamente a las tareas
- **Vista consolidada**: Obtiene documentos de todas las tareas de un proyecto
- **Información completa**: Tarea, proyecto, archivos con metadatos
- **Metadatos ECM**: Información adicional desde `llx_ecm_files` (label, fechas)
- **URLs de descarga**: Enlaces directos con `modulepart=project_task`
- **Estructura nativa**: Usa rutas estándar `{project_ref}/task/{task_ref}/`
- **Creación automática**: Directorios se crean automáticamente al subir el primer archivo
- **Prevención duplicados**: Opción para evitar sobrescritura accidental de archivos

### 📋 Información Retornada - Task Documents
- ✅ **Datos de tarea**: ID, ref, label
- ✅ **Datos de proyecto**: ID, ref, title
- ✅ **Archivos**: Nombre, tamaño, tipo MIME, fecha, ruta relativa, URL descarga
- ✅ **Directorio físico**: Ruta y verificación de existencia
- ✅ **Metadatos**: Label personalizado, fecha creación/modificación
- ✅ **Contadores**: Total de documentos por tarea

### 📋 Información Retornada - Project Tasks Documents
- ✅ **Datos del proyecto**: ID, ref, title
- ✅ **Array de tareas**: Cada tarea con sus documentos
- ✅ **Documentos por tarea**: Lista completa con metadatos
- ✅ **Contadores totales**: Total de tareas y total de documentos del proyecto
- ✅ **Vista consolidada**: Todos los documentos del proyecto en una sola llamada

### 🎯 Formato de Respuesta - Task Documents
```json
{
  "task_id": 45,
  "task_ref": "T001",
  "task_label": "Desarrollo del módulo",
  "project_id": 10,
  "project_ref": "PROJ2023-001",
  "project_title": "Sistema de Gestión",
  "upload_dir": "/path/to/documents",
  "dir_exists": true,
  "documents": [...],
  "total_documents": 3
}
```

### 🎯 Formato de Respuesta - Project Tasks Documents
```json
{
  "project_id": 10,
  "project_ref": "PROJ2023-001",
  "project_title": "Sistema de Gestión",
  "tasks": [
    {
      "task_id": 45,
      "task_ref": "T001",
      "task_label": "Desarrollo",
      "documents": [...],
      "total_documents": 2
    }
  ],
  "total_tasks": 5,
  "total_documents": 12
}
```

### 🔄 Casos de Uso
- 📁 Listar documentos técnicos de una tarea específica
- 📊 Dashboard del proyecto con estadísticas de documentos
- 📚 Explorador de archivos del proyecto completo
- 📦 Descarga masiva de documentación por proyecto
- 🔍 Búsqueda de archivos en todas las tareas
- 📄 Gestión centralizada de documentación del proyecto

### 🧪 Testing
- **Nuevo archivo**: `test_task_documents_api.php` con verificación completa
- **Pruebas incluidas**: Sistema, clases, tablas, estructura de proyectos/tareas
- **Listado de archivos**: Muestra tareas con documentos disponibles
- **Ejemplos de uso**: cURL y JavaScript para ambos endpoints

## [1.2.3] - 2025-10-01

### ✅ Añadido - Endpoint de Documentos del Usuario (ECM)
- **Nuevo endpoint GET**: `GET /user/{id}/documents` - Obtener directorios manuales y archivos del usuario
- **Integración ECM**: Acceso completo al módulo de Gestión Electrónica de Documentos
- **Directorios duales**: Lista directorios del usuario y directorios comunes compartidos
- **Sistema nativo**: Usa tablas nativas `llx_ecm_directories` y `llx_ecm_files`

### 🔧 Características del Endpoint de Documentos
- **Directorios del usuario**: Lista todos los directorios manuales asignados al usuario específico
- **Directorios comunes**: Lista directorios compartidos (sin usuario específico)
- **Archivos completos**: Información detallada de cada archivo en los directorios
- **Metadatos ECM**: Información adicional desde `llx_ecm_files` (label, fecha creación/modificación)
- **URLs de descarga**: Enlaces directos para descargar archivos
- **Jerarquía**: Soporte para estructura de directorios con parent_id

### 📋 Información Retornada
- ✅ **Datos del usuario**: ID, login, nombre completo
- ✅ **Directorios propios**: Con label, descripción, ruta relativa, fechas
- ✅ **Directorios comunes**: Carpetas compartidas como "Base de conocimientos", "Branding", etc.
- ✅ **Archivos**: Nombre, tamaño, tipo MIME, fecha, ruta relativa, URL de descarga
- ✅ **Metadatos de archivos**: Label personalizado, fecha de creación/modificación
- ✅ **Contadores**: Total de directorios del usuario y directorios comunes

### 🎯 Formato de Respuesta
```json
{
  "user_id": 1,
  "user_login": "admin",
  "user_fullname": "Administrador Sistema",
  "directories": [...],
  "common_directories": [...],
  "total_user_directories": 2,
  "total_common_directories": 1,
  "timestamp": "2025-10-01 16:45:00"
}
```

### 🔄 Casos de Uso
- 📁 Listar documentos personales del usuario
- 📚 Acceder a base de conocimientos y documentación común
- 📄 Obtener URLs de descarga para integración con frontend
- 🗂️ Navegar estructura de directorios del usuario

### 🧪 Testing
- **Nuevo archivo**: `test_user_documents_api.php` con verificación completa
- **Pruebas incluidas**: Sistema, clases, tablas ECM, estructura de directorios
- **Ejemplos de uso**: cURL y JavaScript para integración

## [1.2.2] - 2025-09-29

### ✅ Añadido - Gestión de Contactos de Tickets
- **Nuevo endpoint GET**: `GET /tickets/{id}/contacts` - Obtener contactos asociados a un ticket
- **Nuevo endpoint POST**: `POST /tickets/{id}/contacts` - Agregar contacto a un ticket
- **Nuevo endpoint DELETE**: `DELETE /tickets/{id}/contacts/{contact_id}/{contact_source}` - Eliminar contacto de un ticket
- **Soporte completo**: Para contactos externos (socpeople) y usuarios internos
- **Sistema nativo**: Usa métodos nativos `add_contact()` y `delete_contact()` de Dolibarr

### 🔧 Características de Gestión de Contactos
- **Detección automática**: Diferencia entre contactos internos y externos
- **Validación robusta**: Verifica existencia de contactos y tipos de contacto válidos
- **Prevención de duplicados**: No permite agregar el mismo contacto dos veces
- **Información completa**: Retorna datos detallados de contactos (nombre, email, teléfono, empresa)
- **Tipos de contacto**: Soporte para todos los tipos configurados en Dolibarr (CUSTOMER, SUPPORTTEC, etc.)

### 📋 Funcionalidades de Contactos
- ✅ **GET contactos**: Lista completa con información detallada de contactos internos y externos
- ✅ **POST agregar**: Agrega contactos externos o usuarios internos con validación completa
- ✅ **DELETE eliminar**: Elimina contactos específicos usando el sistema nativo
- ✅ **Validación de tipos**: Verifica que el tipo de contacto existe y es válido para tickets
- ✅ **Información enriquecida**: Datos de empresa, teléfonos, emails y roles
- ✅ **Compatibilidad total**: Con el sistema nativo de contactos de Dolibarr

### 🎯 Formato de Entrada para Agregar Contactos
```json
{
  "contact_id": 456,
  "contact_type": "CUSTOMER",
  "contact_source": "external"
}
```

### 📋 Campos Soportados
- `contact_id` (int, requerido): ID del contacto o usuario
- `contact_type` (string, requerido): Código del tipo de contacto
- `contact_source` (string, opcional): "external" o "internal" (por defecto: "external")

### 🔄 Documentación Actualizada
- **API Documentation**: Agregados ejemplos completos para los 3 nuevos endpoints
- **Casos de uso**: Ejemplos para contactos externos e internos
- **Códigos de respuesta**: Documentación de errores específicos (409 para duplicados)

## [1.2.1] - 2025-09-29

### 🔧 Corregido - Error 401 en API
- **Problema resuelto**: Error 401 "Unauthorized: Access denied" en endpoints de la API
- **Verificación de permisos mejorada**: Ahora acepta permisos nativos de Dolibarr como alternativa
- **Compatibilidad ampliada**: Funciona con usuarios que tengan permisos del módulo Tickets
- **Retrocompatibilidad**: Mantiene soporte para permisos específicos del módulo

### 📋 Cambios en Verificación de Permisos
- ✅ **Métodos de lectura**: Acepta `dolibarmodernfrontend->read` O `ticket->read`
- ✅ **Métodos de escritura**: Acepta `dolibarmodernfrontend->write` O `ticket->write`
- ✅ **Métodos de eliminación**: Acepta `dolibarmodernfrontend->delete` O `ticket->write`
- ✅ **Mensajes de error mejorados**: Indica qué permisos son necesarios

### 🎯 Métodos Actualizados
- `get()` - Obtener vinculación específica
- `index()` - Listar vinculaciones
- `post()` - Crear vinculación
- `put()` - Actualizar vinculación
- `delete()` - Eliminar vinculación
- `getInterventionsByTicket()` - Obtener intervenciones por ticket
- `getTicketsByIntervention()` - Obtener tickets por intervención
- `sendTicketEmail()` - Enviar email básico
- `sendTicketEmailCustom()` - Enviar email con adjuntos

### 🧪 Testing
- **Archivo de prueba**: `test_email_api.php` para verificar funcionamiento del endpoint de emails
- **Documentación**: `FIX_401_PERMISSIONS.md` con instrucciones detalladas

## [1.2.0] - 2025-01-17

### ✅ Añadido - Endpoint de Email con Archivos Adjuntos
- **Endpoint mejorado**: `POST /tickets/{ticket_id}/sendemail` con formato personalizado
- **Soporte completo para archivos adjuntos**: Procesamiento de archivos en base64
- **Destinatarios personalizables**: Array de emails específicos o automáticos desde contactos
- **Contenido HTML**: Soporte completo para mensajes HTML
- **Validación avanzada**: Verificación de base64, tipos MIME y tamaños de archivo
- **Limpieza automática**: Gestión de archivos temporales

### 🔧 Características del Nuevo Endpoint
- **Formato de entrada personalizado**: JSON con `subject`, `message`, `recipients`, `attachments`
- **Archivos adjuntos**: Soporte para múltiples archivos en base64 (máximo 10MB cada uno)
- **Tipos MIME**: Detección y validación automática de tipos de archivo
- **Sanitización**: Nombres de archivos seguros y validados
- **CMailFile nativo**: Usa la clase nativa de Dolibarr para máxima compatibilidad

### 📋 Funcionalidades Avanzadas
- ✅ **Archivos adjuntos base64**: Procesamiento completo con validación
- ✅ **Destinatarios flexibles**: Lista personalizada o automática desde contactos del ticket
- ✅ **HTML en mensajes**: Soporte completo para contenido HTML
- ✅ **Validación robusta**: Verificación de base64, tamaños y tipos MIME
- ✅ **Archivos temporales**: Creación, uso y limpieza automática
- ✅ **Registro en historial**: Compatible con el sistema nativo de tickets
- ✅ **Manejo de errores**: Gestión individual por destinatario y archivo

### 🎯 Formato de Entrada Soportado
```json
{
  "subject": "Re: Ticket #123",
  "message": "<p>Contenido HTML</p>",
  "recipients": ["email1@example.com", "email2@example.com"],
  "attachments": [
    {
      "name": "archivo1.pdf",
      "size": 1024000,
      "type": "application/pdf",
      "content": "base64_content_here"
    }
  ]
}
```

## [1.1.0] - 2025-01-17

### ✅ Añadido - Endpoint de Email Básico
- **Endpoint de envío de emails**: `POST /tickets/{ticket_id}/sendemail`
- **Método nativo**: Usa `newMessage()` de Dolibarr para máxima compatibilidad
- **Registro automático**: Se registra con códigos `TICKET_MSG_SENTBYMAIL` nativos
- **Soporte para mensajes privados**: Parámetro `private` para mensajes internos
- **Soporte para contactos internos**: Opción para incluir usuarios internos
- **Manejo de errores**: Gestión individual de fallos de envío
- **100% compatible**: Con el historial y funcionalidad nativa de tickets

### 🔧 Características del Endpoint de Email Básico
- **URL**: `POST /tickets/{ticket_id}/sendemail`
- **Parámetros requeridos**: `subject`, `message`
- **Parámetros opcionales**: `private` (boolean), `send_to_internal` (boolean)
- **Método nativo**: Usa `newMessage()`, `createTicketMessage()`, `sendTicketMessageByEmail()`
- **Respuesta detallada**: Incluye emails enviados y fallos

### 📋 Funcionalidades de Email Básico
- ✅ **Método nativo**: Usa `newMessage()` igual que la interfaz web de Dolibarr
- ✅ **Códigos nativos**: Se registra con `TICKET_MSG_SENTBYMAIL` o `TICKET_MSG_PRIVATE_SENTBYMAIL`
- ✅ **Filtrado automático**: Solo contactos externos por defecto
- ✅ **Validación de emails**: Verifica direcciones válidas antes de enviar
- ✅ **Trazabilidad completa**: Compatible con el historial nativo de tickets
- ✅ **Manejo de errores**: Reporta fallos individuales sin detener el proceso

## [1.0.0] - 2025-01-17

### ✅ Añadido
- **Sistema nativo de vinculaciones**: Implementación completa usando `llx_element_element`
- **API REST completa**: 4 endpoints para gestión de vinculaciones
- **Interfaz web**: Página de gestión manual de vinculaciones
- **Sistema de permisos**: Permisos granulares (read, write, delete, admin)
- **Documentación integrada**: API documentation accesible desde el menú
- **Archivo de pruebas**: `test_api.php` para verificar funcionamiento
- **Soporte multiidioma**: Traducciones en español

### 🔧 Características Técnicas
- **Número de módulo**: 105003
- **Familia**: interface
- **Compatibilidad**: Dolibarr 11.0+, PHP 7.0+
- **Base de datos**: Usa tabla nativa `llx_element_element`
- **Instalación**: Sin scripts SQL adicionales requeridos

### 📋 API Endpoints
- `POST /link/{ticket_id}/{intervention_id}` - Vincular ticket con intervención
- `DELETE /unlink/{ticket_id}/{intervention_id}` - Desvincular ticket de intervención  
- `GET /ticket/{ticket_id}/interventions` - Obtener intervenciones por ticket
- `GET /intervention/{intervention_id}/tickets` - Obtener tickets por intervención
- `POST /tickets/{ticket_id}/sendemail` - Enviar email a contactos del ticket

### 🎯 Ventajas del Sistema Nativo
- ✅ **Sin tablas adicionales**: Usa infraestructura existente de Dolibarr
- ✅ **100% compatible**: Con el sistema estándar de Dolibarr
- ✅ **Instalación simple**: Solo activar el módulo
- ✅ **Mantenimiento fácil**: Aprovecha métodos nativos de CommonObject
- ✅ **Mejor rendimiento**: Consultas optimizadas a `llx_element_element`

### 📁 Estructura de Archivos
```
dolibarmodernfrontend/
├── admin/
│   └── dolibarmodernfrontend_setup.php
├── class/
│   ├── ticketinterventionlink.class.php
│   └── api_dolibarmodernfrontend.class.php
├── core/
│   └── modules/
│       └── modDolibarmodernfrontend.class.php
├── langs/
│   └── es_ES/
│       └── dolibarmodernfrontend.lang
├── api_doc.php
├── interventions_list.php
├── test_api.php
├── README.md
├── INSTALL.md
└── CHANGELOG.md
```

### 🔄 Migración desde Versiones Anteriores
Si tenías una versión anterior con tablas personalizadas:
1. Desactivar el módulo anterior
2. Eliminar tablas personalizadas (si las había)
3. Instalar esta versión que usa el sistema nativo
4. Las vinculaciones existentes en `llx_element_element` se mantendrán

### 🧪 Testing
- Archivo de pruebas incluido: `test_api.php`
- Verifica: instanciación, conexión DB, tabla nativa, permisos
- Muestra información del sistema y endpoints disponibles

---

**Nota**: Esta versión representa una reescritura completa para usar el sistema nativo de Dolibarr, eliminando la necesidad de tablas personalizadas y mejorando la compatibilidad a largo plazo.
