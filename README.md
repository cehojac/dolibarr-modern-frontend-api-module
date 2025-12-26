# Dolibarr Modern Frontend Module

## Descripción

El módulo **dolibarrmodernfrontend** proporciona una API moderna para la gestión de vinculaciones entre tickets e intervenciones en Dolibarr usando el **sistema nativo de vinculaciones** (`llx_element_element`). Permite crear, consultar y eliminar relaciones entre estos elementos de manera programática sin necesidad de tablas adicionales.

## Características

- **API REST completa** para gestión de vinculaciones
- **Usa el sistema nativo de Dolibarr** (`llx_element_element`)
- **Sin tablas adicionales** - Compatible con la estructura estándar
- **Vinculación directa** entre tickets e intervenciones
- **Consultas optimizadas** para obtener datos relacionados
- **Interfaz web** para gestión manual de vinculaciones
- **Documentación integrada** de la API
- **Sistema de permisos** granular
- **Soporte multiidioma** (español incluido)

## Instalación

1. Copiar el módulo en el directorio `custom/dolibarrmodernfrontend/`
2. Activar el módulo desde el panel de administración de Dolibarr
3. Configurar los permisos de usuario según sea necesario

**Nota:** No se requiere ejecutar scripts SQL adicionales ya que el módulo usa la tabla nativa `llx_element_element` de Dolibarr.

## Estructura del Módulo

```
dolibarrmodernfrontend/
├── admin/
│   └── dolibarrmodernfrontend_setup.php    # Configuración del módulo
├── class/
│   ├── ticketinterventionlink.class.php   # Clase principal (usa llx_element_element)
│   └── api_dolibarrmodernfrontend.class.php # API REST
├── core/
│   └── modules/
│       └── modDolibarrmodernfrontend.class.php # Definición del módulo
├── langs/
│   └── es_ES/
│       └── dolibarrmodernfrontend.lang      # Traducciones en español
├── api_doc.php                             # Documentación de la API
├── interventions_list.php                  # Interfaz de gestión
└── README.md                               # Este archivo
```

## API Endpoints

### Base URL
```
/api/index.php/dolibarrmodernfrontend
```

### Autenticación
Todas las llamadas a la API requieren el header `DOLAPIKEY` con una clave API válida.

### Endpoints Disponibles

#### 1. Vincular Ticket con Intervención
```http
POST /link/{ticket_id}/{intervention_id}
```

**Parámetros:**
- `ticket_id` (int): ID del ticket
- `intervention_id` (int): ID de la intervención
- `link_type` (string, opcional): Tipo de vinculación (manual, automatic, system)
- `description` (string, opcional): Descripción de la vinculación

#### 2. Desvincular Ticket de Intervención
```http
DELETE /unlink/{ticket_id}/{intervention_id}
```

#### 3. Obtener Intervenciones por Ticket
```http
GET /ticket/{ticket_id}/interventions
```

#### 4. Obtener Tickets por Intervención
```http
GET /intervention/{intervention_id}/tickets
```

#### 5. Crear Mensaje en Ticket con Contacto Personalizado
```http
POST /tickets/{ticket_id}/newmessage
```

**Parámetros de URL:**
- `ticket_id` (int, requerido): ID del ticket en la URL

**Parámetros del body:**
- `message` (string, requerido): Contenido del mensaje
- `contact_id` (int, opcional): ID del contacto que crea el mensaje (por defecto: 0 = usuario API)
- `private` (int, opcional): Mensaje privado (0=público, 1=privado, por defecto: 0)
- `send_email` (int, opcional): Enviar notificación email (0=no, 1=sí, por defecto: 0)

**Nota**: El subject no es necesario, se usa automáticamente el asunto del ticket.

**Descripción:**
Permite crear un mensaje en un ticket especificando qué contacto lo crea. Útil para integraciones API donde se necesita atribuir el mensaje al contacto correcto relacionado con la empresa del ticket. Usa el método nativo `newMessage()` de Dolibarr.

**Ejemplo de uso:**
```bash
curl -X POST \
  'http://tu-dolibarr.com/api/index.php/dolibarrmodernfrontend/tickets/123/newmessage' \
  -H 'DOLAPIKEY: tu_api_key' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'message=Mensaje de prueba&contact_id=115&private=0&send_email=0'
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Message added successfully to ticket",
  "ticket_id": 123,
  "ticket_ref": "TK2310-0001",
  "message_id": 456,
  "created_by_contact_id": 115,
  "created_by_user_id": 0,
  "created_by_login": "contacto@empresa.com",
  "created_by_name": "Juan Pérez",
  "private": false,
  "send_email": false,
  "timestamp": "2025-10-19 22:45:00"
}
```

#### 6. Obtener URLs de Validación de IDs Profesionales
```http
GET /idprofvalidatorurl
GET /idprofvalidatorurl?country=ES
GET /idprofvalidatorurl?all=1
```

**Descripción:**
Devuelve las URLs de validación de identificadores profesionales (SIREN, NIF, CIF, TIN, etc.) según el país. Por defecto devuelve solo el país de la empresa configurada en Dolibarr. Basado en la función nativa `id_prof_url` de Dolibarr.

**Parámetros opcionales:**
- `country` (string): Código de país específico (FR, ES, GB, etc.)
- `all` (int): 1 para obtener todos los países disponibles

**Modos de operación:**
- **Sin parámetros**: Devuelve solo el país de la empresa (mysoc)
- **?country=XX**: Devuelve solo el país especificado
- **?all=1**: Devuelve todos los países disponibles

**Países soportados:**
- FR (Francia): SIREN
- GB/UK (Reino Unido): Company Number
- ES (España): NIF/CIF
- IN (India): TIN
- DZ (Argelia): NIF
- PT (Portugal): NIF

**Respuesta (modo company):**
```json
{
  "success": true,
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

## Base de Datos

El módulo **NO crea tablas adicionales**. Utiliza la tabla nativa de Dolibarr:

### llx_element_element (tabla nativa)
- `rowid`: ID único de la vinculación
- `fk_source`: ID del elemento origen (ticket)
- `sourcetype`: Tipo del elemento origen ('ticket')
- `fk_target`: ID del elemento destino (intervención)
- `targettype`: Tipo del elemento destino ('intervention')

Esta implementación es **100% compatible** con el sistema estándar de Dolibarr y no requiere modificaciones en la base de datos.

## Permisos

El módulo define los siguientes permisos:

- **Leer**: Ver vinculaciones existentes
- **Escribir**: Crear y modificar vinculaciones
- **Eliminar**: Eliminar vinculaciones
- **Administrar**: Configurar el módulo

## Configuración

Acceder a `Herramientas > Frontend Moderno > Configuración` para ajustar las opciones del módulo.

## Uso de la API

### Ejemplo: Vincular un ticket con una intervención

```bash
curl -X POST \
  'http://tu-dolibarr.com/api/index.php/dolibarrmodernfrontend/link/123/456' \
  -H 'DOLAPIKEY: tu_api_key' \
  -H 'Content-Type: application/json' \
  -d '{
    "link_type": "manual",
    "description": "Vinculación manual entre ticket y intervención"
  }'
```

### Ejemplo: Obtener intervenciones de un ticket

```bash
curl -X GET \
  'http://tu-dolibarr.com/api/index.php/dolibarrmodernfrontend/ticket/123/interventions' \
  -H 'DOLAPIKEY: tu_api_key'
```

## Información del Módulo

- **Número de módulo**: 105003
- **Versión**: 1.2.6
- **Familia**: interface
- **Autor**: DolibarrModules
- **Compatibilidad**: Dolibarr 11.0+, PHP 7.0+

## Soporte

Para reportar problemas o solicitar nuevas características, contactar al desarrollador del módulo.

## Licencia

Este módulo se distribuye bajo la misma licencia que Dolibarr.
