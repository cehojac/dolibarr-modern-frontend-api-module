# Endpoint de Documentos del Usuario - dolibarmodernfrontend v1.2.3

## 📋 Descripción General

Nuevo endpoint que permite acceder a los **directorios manuales** y **archivos** de un usuario desde el módulo ECM (Gestión Electrónica de Documentos) de Dolibarr a través de la API REST.

## 🚀 Endpoint

```
GET /api/index.php/dolibarmodernfrontend/user/{id}/documents
```

### Parámetros

- **`id`** (int, requerido): ID del usuario del cual obtener los documentos

### Headers

```
DOLAPIKEY: your_api_key
```

## 📊 Respuesta

### Estructura JSON

```json
{
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
            "files": [...]
        }
    ],
    "total_user_directories": 2,
    "total_common_directories": 1,
    "timestamp": "2023-12-01 16:45:00"
}
```

## 📁 Campos de la Respuesta

### Usuario
- **`user_id`**: ID del usuario
- **`user_login`**: Login del usuario
- **`user_fullname`**: Nombre completo del usuario

### Directorios
- **`directories`**: Array de directorios específicos del usuario (donde `fk_user` = user_id)
- **`common_directories`**: Array de directorios comunes/compartidos (donde `fk_user` IS NULL)

### Estructura de Directorio
- **`directory_id`**: ID del directorio en `llx_ecm_directories`
- **`label`**: Nombre del directorio
- **`description`**: Descripción del directorio
- **`parent_id`**: ID del directorio padre (null si es raíz)
- **`relativepath`**: Ruta relativa del directorio
- **`date_created`**: Fecha de creación
- **`date_modified`**: Fecha de última modificación
- **`files_count`**: Número de archivos en el directorio
- **`files`**: Array de archivos en el directorio

### Estructura de Archivo
- **`name`**: Nombre del archivo
- **`size`**: Tamaño en bytes
- **`date`**: Fecha de modificación del archivo físico
- **`type`**: Tipo MIME del archivo
- **`relativepath`**: Ruta relativa completa del archivo
- **`download_url`**: URL para descargar el archivo
- **`file_info`**: Metadatos adicionales desde `llx_ecm_files` (si existe)

### Metadatos de Archivo (file_info)
- **`file_id`**: ID del archivo en `llx_ecm_files`
- **`label`**: Etiqueta personalizada del archivo
- **`gen_or_uploaded`**: Indica si fue generado o subido manualmente
- **`date_c`**: Fecha de creación en la base de datos
- **`date_m`**: Fecha de modificación en la base de datos

## 🔐 Permisos

El endpoint requiere uno de los siguientes permisos:

- `dolibarmodernfrontend->read` (Permisos del módulo)
- `ecm->read` (Permisos del módulo ECM)

## 💡 Casos de Uso

### 1. Listar documentos personales del usuario
```bash
curl -X GET "http://localhost/dolibarr/api/index.php/dolibarmodernfrontend/user/1/documents" \
     -H "DOLAPIKEY: your_api_key"
```

### 2. Integración con frontend
```javascript
fetch('/api/index.php/dolibarmodernfrontend/user/1/documents', {
    headers: {
        'DOLAPIKEY': 'your_api_key'
    }
})
.then(response => response.json())
.then(data => {
    // Mostrar directorios del usuario
    data.directories.forEach(dir => {
        console.log(`Directorio: ${dir.label} (${dir.files_count} archivos)`);
        
        // Listar archivos
        dir.files.forEach(file => {
            console.log(`  - ${file.name} (${formatBytes(file.size)})`);
            console.log(`    Descargar: ${file.download_url}`);
        });
    });
    
    // Mostrar directorios comunes
    console.log('\nDirectorios comunes:');
    data.common_directories.forEach(dir => {
        console.log(`- ${dir.label}`);
    });
});
```

### 3. Obtener URLs de descarga
```javascript
// Obtener todos los archivos PDF del usuario
fetch('/api/index.php/dolibarmodernfrontend/user/1/documents', {
    headers: {'DOLAPIKEY': 'your_api_key'}
})
.then(response => response.json())
.then(data => {
    const pdfFiles = [];
    
    data.directories.forEach(dir => {
        dir.files
            .filter(file => file.type === 'application/pdf')
            .forEach(file => {
                pdfFiles.push({
                    name: file.name,
                    directory: dir.label,
                    url: file.download_url,
                    size: file.size
                });
            });
    });
    
    console.log('Archivos PDF encontrados:', pdfFiles);
});
```

## 🗂️ Directorios Manuales en Dolibarr

### ¿Qué son los Directorios Manuales?

Los **directorios manuales** en Dolibarr son carpetas personalizadas que los usuarios pueden crear en el módulo ECM (Gestión Electrónica de Documentos) para organizar documentos que no están vinculados a un objeto específico (como facturas, propuestas, etc.).

### Cómo crear Directorios Manuales

1. Ir a **Documentos > Directorios manuales** en Dolibarr
2. Hacer clic en **Nuevo directorio** (botón con +)
3. Completar los campos:
   - **Nombre**: Nombre del directorio (ej: "Base de conocimientos")
   - **Descripción**: Descripción opcional
   - **Usuario**: Asignar a un usuario específico (opcional)
4. Guardar el directorio
5. Subir archivos al directorio creado

### Tipos de Directorios

**Directorios de Usuario** (`directories`)
- Asignados a un usuario específico (`fk_user` > 0)
- Solo accesibles por ese usuario (según permisos)
- Aparecen en el campo `directories` de la respuesta

**Directorios Comunes** (`common_directories`)
- No asignados a un usuario específico (`fk_user` IS NULL)
- Accesibles para todos los usuarios (según permisos)
- Ejemplos: "Base de conocimientos", "Branding", "Documentación"
- Aparecen en el campo `common_directories` de la respuesta

## 🔧 Tablas de Base de Datos Utilizadas

### llx_ecm_directories
Almacena la estructura de directorios:
- `rowid`: ID del directorio
- `label`: Nombre del directorio
- `description`: Descripción
- `fk_user`: ID del usuario propietario (NULL para comunes)
- `fk_parent`: ID del directorio padre
- `fullrelativename`: Ruta relativa completa
- `date_c`: Fecha de creación
- `date_m`: Fecha de modificación
- `cachenbofdoc`: Caché del número de documentos

### llx_ecm_files
Almacena metadatos de archivos:
- `rowid`: ID del archivo
- `filename`: Nombre del archivo
- `label`: Etiqueta personalizada
- `filepath`: Ruta del directorio
- `gen_or_uploaded`: Tipo de archivo
- `date_c`: Fecha de creación
- `date_m`: Fecha de modificación

## 🛠️ Testing

### Archivo de Prueba
El módulo incluye `test_user_documents_api.php` para verificar:

1. ✅ Módulos activados (dolibarmodernfrontend, ECM, API)
2. ✅ Permisos de usuario
3. ✅ Existencia de tablas ECM
4. ✅ Métodos de la API
5. ✅ Directorios del usuario actual
6. ✅ Estructura de directorios ECM
7. ✅ Ejemplos de uso

### Acceder al Test
```
http://localhost/dolibarr/custom/dolibarmodernfrontend/test_user_documents_api.php
```

## 📝 Códigos de Respuesta HTTP

- **200 OK**: Solicitud exitosa
- **401 Unauthorized**: Falta API key o permisos insuficientes
- **404 Not Found**: Usuario no encontrado
- **500 Internal Server Error**: Error en el servidor

## ⚠️ Notas Importantes

1. **Módulo ECM**: Aunque no es obligatorio tener el módulo ECM activado, es altamente recomendado para gestionar los directorios manuales.

2. **Permisos**: El usuario debe tener permisos de lectura en ECM o en el módulo dolibarmodernfrontend.

3. **Archivos físicos**: El endpoint lee los archivos del sistema de archivos usando `dol_dir_list()`, por lo que los archivos deben existir físicamente en el servidor.

4. **Metadatos opcionales**: El campo `file_info` solo aparece si el archivo está registrado en la tabla `llx_ecm_files`.

5. **URLs de descarga**: Las URLs generadas usan el módulo `document.php` nativo de Dolibarr con `modulepart=ecm`.

## 🔗 Referencias

- [Documentación API Dolibarr](https://wiki.dolibarr.org/index.php/API_REST)
- [Módulo ECM](https://wiki.dolibarr.org/index.php/Module_ECM)
- Directorio físico: `$conf->ecm->dir_output`

## 📅 Historial de Versiones

### v1.2.3 (2025-10-01)
- ✅ Implementación inicial del endpoint `/user/{id}/documents`
- ✅ Soporte para directorios de usuario y comunes
- ✅ Información completa de archivos con metadatos
- ✅ URLs de descarga directa
- ✅ Archivo de prueba `test_user_documents_api.php`

---

**Módulo**: dolibarmodernfrontend  
**Versión**: 1.2.3  
**Fecha**: 2025-10-01  
**Autor**: DolibarrModules
