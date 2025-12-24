# Endpoint: Email Templates

## 📧 Nuevo Endpoint v1.2.3

El módulo **dolibarmodernfrontend** ahora incluye un endpoint completo para obtener todas las plantillas de correo electrónico configuradas en Dolibarr.

---

## 🎯 Endpoint

```
GET /api/index.php/dolibarmodernfrontend/emailtemplates
```

---

## 📋 Descripción

Obtiene todas las plantillas de correo electrónico de Dolibarr desde la tabla `llx_c_email_templates` con información completa incluyendo:

- ✅ Detalles completos de cada plantilla
- ✅ Extracción automática de variables
- ✅ Filtrado flexible por múltiples criterios
- ✅ Información del usuario creador
- ✅ Lista de tipos y idiomas disponibles

---

## 🔐 Permisos Requeridos

- Permisos de administrador **O**
- Permisos de lectura del módulo `dolibarmodernfrontend`

---

## 📥 Parámetros de Query (Opcionales)

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `type_template` | string | Filtrar por tipo de plantilla | `ticket`, `invoice`, `order` |
| `lang` | string | Filtrar por código de idioma | `es_ES`, `en_US`, `fr_FR` |
| `enabled` | int | Filtrar por estado habilitado | `0` (deshabilitado), `1` (habilitado) |
| `private` | int | Filtrar por privacidad | `0` (pública), `1` (privada) |

---

## 📤 Ejemplos de Uso

### 1. Obtener todas las plantillas

```bash
curl -X GET "http://localhost/api/index.php/dolibarmodernfrontend/emailtemplates" \
  -H "DOLAPIKEY: your_api_key_here"
```

### 2. Filtrar por tipo (tickets)

```bash
curl -X GET "http://localhost/api/index.php/dolibarmodernfrontend/emailtemplates?type_template=ticket" \
  -H "DOLAPIKEY: your_api_key_here"
```

### 3. Filtrar por idioma (español)

```bash
curl -X GET "http://localhost/api/index.php/dolibarmodernfrontend/emailtemplates?lang=es_ES" \
  -H "DOLAPIKEY: your_api_key_here"
```

### 4. Solo plantillas habilitadas

```bash
curl -X GET "http://localhost/api/index.php/dolibarmodernfrontend/emailtemplates?enabled=1" \
  -H "DOLAPIKEY: your_api_key_here"
```

### 5. Filtros combinados

```bash
curl -X GET "http://localhost/api/index.php/dolibarmodernfrontend/emailtemplates?type_template=ticket&lang=es_ES&enabled=1" \
  -H "DOLAPIKEY: your_api_key_here"
```

---

## 📊 Respuesta Exitosa

```json
{
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
      "subject": "Re: Ticket __TICKET_REF__ - __TICKET_SUBJECT__",
      "content": "<p>Estimado/a cliente,</p><p>En relación a su ticket __TICKET_REF__...</p>",
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
    }
  ],
  "total_count": 1,
  "available_types": [
    "ticket",
    "invoice",
    "order",
    "proposal",
    "thirdparty"
  ],
  "available_langs": [
    "es_ES",
    "en_US",
    "fr_FR"
  ],
  "timestamp": "2023-12-01 19:00:00",
  "usage_info": {
    "description": "Email templates can be filtered by type, language, enabled status, and privacy",
    "filter_examples": {
      "by_type": "/api/index.php/dolibarmodernfrontend/emailtemplates?type_template=ticket",
      "by_lang": "/api/index.php/dolibarmodernfrontend/emailtemplates?lang=es_ES",
      "enabled_only": "/api/index.php/dolibarmodernfrontend/emailtemplates?enabled=1",
      "public_only": "/api/index.php/dolibarmodernfrontend/emailtemplates?private=0",
      "combined": "/api/index.php/dolibarmodernfrontend/emailtemplates?type_template=ticket&lang=es_ES&enabled=1"
    },
    "variables_info": "The 'variables' field lists all template variables found in the format __VARIABLE__"
  }
}
```

---

## 📝 Campos de Respuesta

### Campos Principales

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único de la plantilla |
| `entity` | int | Entidad de Dolibarr |
| `module` | string | Módulo asociado |
| `label` | string | Nombre descriptivo |
| `type_template` | string | Tipo de plantilla |
| `lang` | string | Código de idioma |
| `private` | int | 0=pública, 1=privada |
| `subject` | string | Asunto del email |
| `content` | string | Contenido HTML |
| `content_lines` | string | Contenido adicional |
| `joinfiles` | int | Adjuntar archivos (0/1) |
| `enabled` | string | Estado habilitado |
| `active` | int | Estado activo |
| `position` | int | Orden de posición |
| `date_created` | string | Fecha de creación |
| `date_modified` | string | Fecha de modificación |
| `user_info` | object | Info del creador |
| `variables` | array | Variables extraídas |
| `is_public` | boolean | Si es pública |
| `is_enabled` | boolean | Si está habilitada |

### Campos Adicionales de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `total_count` | int | Total de plantillas |
| `available_types` | array | Tipos disponibles |
| `available_langs` | array | Idiomas disponibles |
| `filters_applied` | object | Filtros aplicados |
| `usage_info` | object | Información de uso |

---

## 🔤 Variables Comunes en Plantillas

Las plantillas de Dolibarr usan variables en formato `__VARIABLE__`:

### Variables de Tickets
- `__TICKET_REF__` - Referencia del ticket
- `__TICKET_SUBJECT__` - Asunto del ticket
- `__TICKET_MESSAGE__` - Mensaje del ticket
- `__TICKET_TRACKID__` - ID de seguimiento
- `__TICKET_URL__` - URL del ticket

### Variables de Facturas
- `__INVOICE_REF__` - Referencia de factura
- `__INVOICE_DATE__` - Fecha de factura
- `__INVOICE_AMOUNT__` - Importe de factura

### Variables de Pedidos
- `__ORDER_REF__` - Referencia de pedido
- `__ORDER_DATE__` - Fecha de pedido

### Variables Generales
- `__THIRDPARTY_NAME__` - Nombre del tercero
- `__USER_FULLNAME__` - Nombre completo del usuario
- `__USER_EMAIL__` - Email del usuario
- `__SIGNATURE__` - Firma del usuario
- `__MYCOMPANY_NAME__` - Nombre de la empresa

---

## 🎨 Casos de Uso

### 1. Selector de Plantillas en Frontend
```javascript
// Obtener plantillas de tickets en español
fetch('/api/index.php/dolibarmodernfrontend/emailtemplates?type_template=ticket&lang=es_ES&enabled=1', {
  headers: {
    'DOLAPIKEY': 'your_api_key'
  }
})
.then(response => response.json())
.then(data => {
  // Crear selector con las plantillas
  const select = document.getElementById('template-selector');
  data.templates.forEach(template => {
    const option = document.createElement('option');
    option.value = template.id;
    option.textContent = template.label;
    select.appendChild(option);
  });
});
```

### 2. Previsualización de Plantilla
```javascript
// Obtener y mostrar contenido de plantilla
async function previewTemplate(templateId) {
  const response = await fetch('/api/index.php/dolibarmodernfrontend/emailtemplates');
  const data = await response.json();
  
  const template = data.templates.find(t => t.id === templateId);
  if (template) {
    document.getElementById('preview-subject').textContent = template.subject;
    document.getElementById('preview-content').innerHTML = template.content;
    document.getElementById('preview-variables').textContent = template.variables.join(', ');
  }
}
```

### 3. Filtrado Dinámico
```javascript
// Filtrar plantillas según selección del usuario
async function filterTemplates(type, lang) {
  const url = `/api/index.php/dolibarmodernfrontend/emailtemplates?type_template=${type}&lang=${lang}&enabled=1`;
  const response = await fetch(url, {
    headers: { 'DOLAPIKEY': 'your_api_key' }
  });
  const data = await response.json();
  
  return data.templates;
}
```

---

## ⚠️ Códigos de Error

| Código | Descripción |
|--------|-------------|
| `200` | ✅ Operación exitosa |
| `401` | ❌ Sin permisos de administrador o del módulo |
| `500` | ❌ Error al consultar la base de datos |

---

## 🧪 Archivo de Prueba

Se incluye un archivo de prueba completo:

```
test_emailtemplates_api.php
```

Este archivo prueba:
- ✅ Obtener todas las plantillas
- ✅ Filtrar por tipo (ticket)
- ✅ Filtrar por idioma (es_ES)
- ✅ Filtrar solo habilitadas
- ✅ Filtros combinados

**Uso:**
1. Configurar `API_KEY` en el archivo
2. Acceder desde navegador: `http://localhost/custom/dolibarmodernfrontend/test_emailtemplates_api.php`
3. O ejecutar desde CLI: `php test_emailtemplates_api.php`

---

## 🔧 Características Técnicas

### Base de Datos
- **Tabla:** `llx_c_email_templates`
- **JOIN:** `llx_user` (para información del creador)
- **Filtros:** Entity, tipo, idioma, estado, privacidad

### Procesamiento
- Extracción automática de variables mediante regex: `/__([A-Z_]+)__/`
- Ordenamiento por posición y label
- Soporte multi-entidad

### Seguridad
- Verificación de permisos de administrador o módulo
- Escape de parámetros SQL
- Validación de entidad

---

## 📚 Integración con Otros Endpoints

Este endpoint se complementa perfectamente con:

### Envío de Emails
```bash
# 1. Obtener plantilla
GET /emailtemplates?type_template=ticket&lang=es_ES

# 2. Usar plantilla para enviar email
POST /tickets/{ticket_id}/sendemail
{
  "subject": "...",  // Usar template.subject
  "message": "...",  // Usar template.content
  "recipients": ["cliente@email.com"]
}
```

### Gestión de Contactos
```bash
# 1. Obtener contactos del ticket
GET /tickets/{id}/contacts

# 2. Obtener plantilla apropiada
GET /emailtemplates?type_template=ticket

# 3. Enviar email personalizado
POST /tickets/{id}/sendemail
```

---

## 🚀 Ventajas

1. **Centralización:** Acceso unificado a todas las plantillas
2. **Filtrado Flexible:** Múltiples criterios de búsqueda
3. **Información Completa:** Todos los detalles en una sola llamada
4. **Variables Extraídas:** No necesitas parsear el contenido
5. **Multi-idioma:** Soporte completo para plantillas localizadas
6. **Compatibilidad:** 100% compatible con sistema nativo de Dolibarr

---

## 📖 Documentación Adicional

- **Documentación completa:** `/custom/dolibarmodernfrontend/api_doc.php`
- **Archivo de prueba:** `/custom/dolibarmodernfrontend/test_emailtemplates_api.php`
- **Wiki Dolibarr:** https://wiki.dolibarr.org/index.php/Customize_the_email_sending_message

---

## 🆕 Changelog v1.2.3

### Nuevo Endpoint
- ✅ `GET /emailtemplates` - Obtener plantillas de correo

### Características
- ✅ Filtrado por tipo, idioma, estado y privacidad
- ✅ Extracción automática de variables
- ✅ Información del usuario creador
- ✅ Lista de tipos y idiomas disponibles
- ✅ Soporte multi-entidad
- ✅ Documentación completa
- ✅ Archivo de prueba incluido

---

**Versión:** 1.2.3  
**Fecha:** 2024  
**Módulo:** dolibarmodernfrontend  
**Autor:** Cascade AI Assistant
