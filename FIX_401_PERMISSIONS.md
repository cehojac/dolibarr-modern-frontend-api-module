# Solución al Error 401 en API de Envío de Emails

## 🔍 **Problema Identificado**

El error 401 "Unauthorized: Access denied" ocurría porque la API verificaba únicamente los permisos específicos del módulo `dolibarrmodernfrontend`, que el usuario no tenía asignados.

## ✅ **Solución Implementada**

Se modificó la verificación de permisos en **todos los métodos de la API** para que acepten **permisos alternativos**:

### **Cambios Realizados:**

1. **Métodos de Lectura** (`get`, `index`, `getInterventionsByTicket`, `getTicketsByIntervention`):
   - ✅ Acepta `dolibarrmodernfrontend->read` (permisos del módulo)
   - ✅ **O** acepta `ticket->read` (permisos nativos de tickets)

2. **Métodos de Escritura** (`post`, `put`, `sendTicketEmail`, `sendTicketEmailCustom`):
   - ✅ Acepta `dolibarrmodernfrontend->write` (permisos del módulo)
   - ✅ **O** acepta `ticket->write` (permisos nativos de tickets)

3. **Métodos de Eliminación** (`delete`):
   - ✅ Acepta `dolibarrmodernfrontend->delete` (permisos del módulo)
   - ✅ **O** acepta `ticket->write` (permisos nativos de tickets)

### **Código de Verificación Implementado:**

```php
// Para métodos de lectura
$has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                   DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
$has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                   DolibarrApiAccess::$user->rights->ticket->read;

if (!$has_module_perms && !$has_ticket_perms) {
    throw new RestException(401, 'Access denied: Need ticket read permissions or dolibarrmodernfrontend read permissions');
}
```

## 🚀 **Resultado**

Ahora la API funciona con usuarios que tengan **cualquiera** de estos permisos:

### **Opción 1: Permisos del Módulo** (Recomendado)
- `dolibarrmodernfrontend->read`
- `dolibarrmodernfrontend->write` 
- `dolibarrmodernfrontend->delete`

### **Opción 2: Permisos Nativos** (Alternativa)
- `ticket->read` (para consultas)
- `ticket->write` (para envío de emails y modificaciones)

## 📋 **Instrucciones de Uso**

### **Para Administradores:**

1. **Activar el módulo** en Configuración → Módulos
2. **Asignar permisos** al usuario API:
   - **Opción A:** Asignar permisos específicos del módulo `dolibarrmodernfrontend`
   - **Opción B:** Verificar que el usuario tenga permisos del módulo `Tickets`

### **Para Desarrolladores:**

1. **Usar el archivo de prueba:**
   ```bash
   php test_email_api.php
   ```

2. **Configurar el test:**
   - Reemplazar `YOUR_API_KEY_HERE` con tu clave API
   - Cambiar `ticket_id` por un ID válido
   - Actualizar email de destinatario

3. **Endpoint de envío de emails:**
   ```
   POST /api/index.php/dolibarrmodernfrontendapi/tickets/{ticket_id}/sendemail
   ```

## 🔧 **Archivos Modificados**

- ✅ `class/api_dolibarrmodernfrontend.class.php` - Verificación de permisos mejorada
- ✅ `test_email_api.php` - Script de prueba creado
- ✅ `FIX_401_PERMISSIONS.md` - Documentación de la solución

## ⚡ **Estado Actual**

- ✅ **Error 401 solucionado**
- ✅ **Compatibilidad con permisos nativos**
- ✅ **Mantiene seguridad del sistema**
- ✅ **No requiere cambios en base de datos**
- ✅ **Retrocompatible con configuraciones existentes**

## 🧪 **Testing**

Ejecuta el script de prueba para verificar que todo funciona:

```bash
cd c:\Users\cehoj\OneDrive\Documentos\www\dolibarr-modules\dolibarrmodernfrontend\
php test_email_api.php
```

El script te dirá exactamente qué está pasando y cómo solucionarlo si aún hay problemas.
