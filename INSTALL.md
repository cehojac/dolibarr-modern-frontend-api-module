# Instalación del Módulo dolibarmodernfrontend

## Requisitos Previos

- Dolibarr 11.0 o superior
- PHP 7.0 o superior
- Módulos de Dolibarr activados: Tickets, Intervenciones

## Pasos de Instalación

### 1. Copiar el módulo
```bash
# Copiar la carpeta completa del módulo a:
/custom/dolibarmodernfrontend/
```

### 2. Activar el módulo
1. Ir al panel de administración de Dolibarr
2. Navegar a **Configuración → Módulos/Aplicaciones**
3. Buscar "Frontend Moderno" en la lista
4. Hacer clic en **Activar**

### 3. Configurar permisos
1. Ir a **Configuración → Usuarios y Grupos**
2. Editar los usuarios que necesiten acceso
3. En la pestaña **Permisos**, activar:
   - ✅ **Leer vinculaciones de intervenciones y tickets**
   - ✅ **Crear/modificar vinculaciones de intervenciones y tickets**
   - ✅ **Eliminar vinculaciones de intervenciones y tickets**
   - ✅ **Administrar módulo de frontend moderno** (solo administradores)

### 4. Verificar instalación
1. Navegar a **Herramientas → Frontend Moderno**
2. Acceder a **API Documentation** para ver los endpoints disponibles
3. Opcionalmente, ejecutar `/custom/dolibarmodernfrontend/test_api.php` para verificar el funcionamiento

## Características del Sistema

### ✅ Ventajas de usar el sistema nativo
- **No requiere SQL adicional** - Usa `llx_element_element`
- **100% compatible** con Dolibarr estándar
- **Instalación simple** - Solo activar el módulo
- **Mantenimiento fácil** - Aprovecha la infraestructura nativa

### 🔗 Endpoints de la API
- `POST /api/index.php/dolibarmodernfrontend/link/{ticket_id}/{intervention_id}`
- `DELETE /api/index.php/dolibarmodernfrontend/unlink/{ticket_id}/{intervention_id}`
- `GET /api/index.php/dolibarmodernfrontend/ticket/{ticket_id}/interventions`
- `GET /api/index.php/dolibarmodernfrontend/intervention/{intervention_id}/tickets`

### 🔑 Autenticación API
Todas las llamadas a la API requieren el header:
```
DOLAPIKEY: your_api_key_here
```

## Uso Básico

### Vincular un ticket con una intervención
```bash
curl -X POST \
  'http://tu-dolibarr.com/api/index.php/dolibarmodernfrontend/link/123/456' \
  -H 'DOLAPIKEY: tu_api_key'
```

### Obtener intervenciones de un ticket
```bash
curl -X GET \
  'http://tu-dolibarr.com/api/index.php/dolibarmodernfrontend/ticket/123/interventions' \
  -H 'DOLAPIKEY: tu_api_key'
```

## Solución de Problemas

### Error: "Módulo no encontrado"
- Verificar que la carpeta esté en `/custom/dolibarmodernfrontend/`
- Verificar permisos de archivos

### Error: "Access forbidden"
- Verificar que el usuario tenga los permisos correctos
- Verificar que el módulo esté activado

### Error: "API Key invalid"
- Generar una nueva API Key en Dolibarr
- Verificar que el header DOLAPIKEY esté presente

## Soporte

Para reportar problemas o solicitar nuevas características:
- Revisar la documentación en `/custom/dolibarmodernfrontend/README.md`
- Ejecutar el test de diagnóstico en `/custom/dolibarmodernfrontend/test_api.php`

---

**Versión:** 1.0.0  
**Compatibilidad:** Dolibarr 11.0+  
**Sistema:** Usa tabla nativa `llx_element_element`
