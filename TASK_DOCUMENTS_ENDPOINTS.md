# Endpoints de Documentos de Tareas de Proyectos - dolibarmodernfrontend v1.2.4

## 📋 Descripción General

Nuevos endpoints que permiten acceder a los **documentos subidos a tareas de proyectos** en Dolibarr a través de la API REST. Estos endpoints proporcionan acceso tanto a documentos de tareas individuales como a una vista consolidada de todos los documentos de un proyecto.

## 🚀 Endpoints

### 1. Obtener Documentos de una Tarea (GET)

```
GET /api/index.php/dolibarmodernfrontend/task/{id}/documents
```

#### Parámetros

- **`id`** (int, requerido): ID de la tarea

#### Headers

```
DOLAPIKEY: your_api_key
```

#### Respuesta

```json
{
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
}
```

### 2. Subir Documento a una Tarea (POST)

```
POST /api/index.php/dolibarmodernfrontend/task/{id}/documents
```

#### Parámetros

- **`id`** (int, requerido): ID de la tarea

#### Headers

```
DOLAPIKEY: your_api_key
Content-Type: application/json
```

#### Cuerpo de la Solicitud (JSON)

```json
{
    "filename": "especificaciones.pdf",
    "filecontent": "JVBERi0xLjQKJeLjz9MKMSAwIG9iago8PAovQ...",
    "overwriteifexists": false,
    "label": "Especificaciones Técnicas v1.0",
    "description": "Documento con las especificaciones completas del proyecto"
}
```

**Campos:**
- **`filename`** (string, requerido): Nombre del archivo
- **`filecontent`** (string, requerido): Contenido del archivo codificado en base64
- **`overwriteifexists`** (boolean, opcional): Sobrescribir si existe. Por defecto: false
- **`label`** (string, opcional): Etiqueta del archivo para llx_ecm_files
- **`description`** (string, opcional): Descripción del archivo

#### Respuesta

```json
{
    "success": true,
    "message": "File uploaded successfully",
    "task_id": 45,
    "task_ref": "T001",
    "task_label": "Desarrollo del módulo de pagos",
    "project_id": 10,
    "project_ref": "PROJ2023-001",
    "project_title": "Sistema de Gestión Comercial",
    "file": {
        "name": "especificaciones.pdf",
        "size": 1024000,
        "type": "application/pdf",
        "relativepath": "projet/PROJ2023-001/task/T001/especificaciones.pdf",
        "download_url": "/document.php?modulepart=project_task&file=PROJ2023-001%2Ftask%2FT001%2Fespecificaciones.pdf",
        "ecm_file_id": 456
    },
    "timestamp": "2023-12-01 18:00:00"
}
```

### 3. Obtener Documentos de Todas las Tareas de un Proyecto

```
GET /api/index.php/dolibarmodernfrontend/project/{id}/tasks/documents
```

#### Parámetros

- **`id`** (int, requerido): ID del proyecto

#### Headers

```
DOLAPIKEY: your_api_key
```

#### Respuesta

```json
{
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
}
```

## 📁 Campos de la Respuesta

### Endpoint: Task Documents

**Información de la Tarea:**
- **`task_id`**: ID de la tarea
- **`task_ref`**: Referencia de la tarea
- **`task_label`**: Etiqueta/nombre de la tarea

**Información del Proyecto:**
- **`project_id`**: ID del proyecto asociado
- **`project_ref`**: Referencia del proyecto
- **`project_title`**: Título del proyecto

**Directorio:**
- **`upload_dir`**: Ruta física del directorio de documentos
- **`dir_exists`**: Indica si el directorio existe

**Documentos:**
- **`documents`**: Array de archivos subidos a la tarea
- **`total_documents`**: Contador total de documentos

### Endpoint: Project Tasks Documents

**Información del Proyecto:**
- **`project_id`**: ID del proyecto
- **`project_ref`**: Referencia del proyecto
- **`project_title`**: Título del proyecto

**Tareas:**
- **`tasks`**: Array de tareas con sus documentos
- **`total_tasks`**: Contador total de tareas
- **`total_documents`**: Contador total de documentos en todas las tareas

### Estructura de Documento

- **`name`**: Nombre del archivo
- **`size`**: Tamaño en bytes
- **`date`**: Fecha de modificación del archivo físico
- **`type`**: Tipo MIME del archivo
- **`relativepath`**: Ruta relativa completa del archivo
- **`download_url`**: URL para descargar el archivo
- **`file_info`**: Metadatos adicionales desde `llx_ecm_files` (opcional)

### Metadatos de Archivo (file_info)

- **`file_id`**: ID del archivo en `llx_ecm_files`
- **`label`**: Etiqueta personalizada del archivo
- **`gen_or_uploaded`**: Indica si fue generado o subido manualmente
- **`date_c`**: Fecha de creación en la base de datos
- **`date_m`**: Fecha de modificación en la base de datos

## 🔐 Permisos

Los endpoints requieren uno de los siguientes permisos:

- `dolibarmodernfrontend->read` (Permisos del módulo)
- `projet->lire` (Permisos del módulo Proyectos)

## 💡 Casos de Uso

### 1. Obtener documentos de una tarea específica

```bash
curl -X GET "http://localhost/dolibarr/api/index.php/dolibarmodernfrontend/task/45/documents" \
     -H "DOLAPIKEY: your_api_key"
```

**Uso:** Listar especificaciones técnicas, diseños, o archivos relacionados con una tarea específica.

### 1b. Subir documento a una tarea

```bash
# Convertir archivo a base64 y subir
FILE_BASE64=$(base64 -w 0 documento.pdf)

curl -X POST "http://localhost/dolibarr/api/index.php/dolibarmodernfrontend/task/45/documents" \
     -H "DOLAPIKEY: your_api_key" \
     -H "Content-Type: application/json" \
     -d "{
       \"filename\": \"documento.pdf\",
       \"filecontent\": \"$FILE_BASE64\",
       \"overwriteifexists\": false,
       \"label\": \"Especificaciones Técnicas\",
       \"description\": \"Documento con las especificaciones del módulo\"
     }"
```

**Uso:** Subir documentación técnica, diseños, archivos de requisitos a una tarea desde aplicaciones externas.

### 2. Vista consolidada del proyecto

```bash
curl -X GET "http://localhost/dolibarr/api/index.php/dolibarmodernfrontend/project/10/tasks/documents" \
     -H "DOLAPIKEY: your_api_key"
```

**Uso:** Obtener todos los documentos del proyecto agrupados por tarea para crear dashboards o reportes.

### 3. Integración con frontend - Explorador de archivos

```javascript
// Dashboard del proyecto con estadísticas
fetch('/api/index.php/dolibarmodernfrontend/project/10/tasks/documents', {
    headers: {
        'DOLAPIKEY': 'your_api_key'
    }
})
.then(response => response.json())
.then(data => {
    console.log(`Proyecto: ${data.project_title}`);
    console.log(`Total tareas: ${data.total_tasks}`);
    console.log(`Total documentos: ${data.total_documents}`);
    
    // Crear vista por tarea
    data.tasks.forEach(task => {
        console.log(`\n${task.task_label} (${task.total_documents} docs)`);
        
        task.documents.forEach(doc => {
            console.log(`  - ${doc.name} (${formatBytes(doc.size)})`);
            console.log(`    Descargar: ${doc.download_url}`);
        });
    });
});
```

### 4. Buscar archivos específicos en el proyecto

```javascript
// Buscar todos los PDFs del proyecto
fetch('/api/index.php/dolibarmodernfrontend/project/10/tasks/documents', {
    headers: {'DOLAPIKEY': 'your_api_key'}
})
.then(response => response.json())
.then(data => {
    const pdfFiles = [];
    
    data.tasks.forEach(task => {
        task.documents
            .filter(doc => doc.type === 'application/pdf')
            .forEach(doc => {
                pdfFiles.push({
                    task: task.task_label,
                    name: doc.name,
                    url: doc.download_url,
                    size: doc.size
                });
            });
    });
    
    console.log('PDFs encontrados:', pdfFiles);
});
```

### 5. Descarga masiva de documentación

```javascript
// Generar lista de URLs para descarga masiva
async function downloadProjectDocs(projectId) {
    const response = await fetch(
        `/api/index.php/dolibarmodernfrontend/project/${projectId}/tasks/documents`,
        {headers: {'DOLAPIKEY': 'your_api_key'}}
    );
    
    const data = await response.json();
    const downloadUrls = [];
    
    data.tasks.forEach(task => {
        task.documents.forEach(doc => {
            downloadUrls.push({
                filename: `${task.task_ref}_${doc.name}`,
                url: doc.download_url
            });
        });
    });
    
    return downloadUrls;
}
```

## 📂 Estructura de Directorios

### Patrón de Ruta Física

```
{projet_dir_output}/{project_ref}/task/{task_ref}/
```

**Ejemplo:**
```
/var/www/dolibarr/documents/projet/PROJ2023-001/task/T001/especificaciones.pdf
```

### Parámetro modulepart

Para descargar archivos de tareas, usar:
```
modulepart=project_task
```

### URL de Descarga

```
/document.php?modulepart=project_task&file={project_ref}/task/{task_ref}/{filename}
```

**Ejemplo:**
```
/document.php?modulepart=project_task&file=PROJ2023-001/task/T001/especificaciones.pdf
```

## 🗂️ Cómo Funcionan los Documentos de Tareas

### Estructura en Dolibarr

1. **Proyecto**: Entidad principal con referencia única
2. **Tarea**: Pertenece a un proyecto, puede tener su propia referencia
3. **Documentos**: Archivos subidos se almacenan en `documents/projet/{project_ref}/task/{task_ref}/`

### Subir Documentos a una Tarea

1. Ir a **Proyectos** en el menú
2. Seleccionar el proyecto deseado
3. Ir a la pestaña **Tareas**
4. Hacer clic en la tarea específica
5. Ir a la pestaña **Documentos**
6. Usar el botón de carga para subir archivos
7. Los archivos se almacenarán automáticamente en la estructura correcta

### Referencia de Tarea

- Si la tarea tiene `ref` definido, se usa ese valor
- Si no tiene `ref`, se usa el `rowid` (ID) de la tarea
- La referencia se sanitiza para crear un nombre de directorio válido

## 🔧 Tablas de Base de Datos Utilizadas

### llx_projet
Tabla de proyectos:
- `rowid`: ID del proyecto
- `ref`: Referencia única del proyecto
- `title`: Título del proyecto
- `entity`: Entidad multi-empresa

### llx_projet_task
Tabla de tareas:
- `rowid`: ID de la tarea
- `ref`: Referencia de la tarea (opcional)
- `label`: Nombre/etiqueta de la tarea
- `fk_projet`: ID del proyecto al que pertenece

### llx_ecm_files
Metadatos de archivos (opcional):
- `rowid`: ID del archivo
- `filename`: Nombre del archivo
- `label`: Etiqueta personalizada
- `filepath`: Ruta del directorio
- `gen_or_uploaded`: Tipo de archivo
- `date_c`: Fecha de creación
- `date_m`: Fecha de modificación

## 🛠️ Testing

### Archivo de Prueba

El módulo incluye `test_task_documents_api.php` para verificar:

1. ✅ Módulos activados (dolibarmodernfrontend, Proyectos, API)
2. ✅ Permisos de usuario
3. ✅ Existencia de tablas (projet, projet_task, ecm_files)
4. ✅ Métodos de la API
5. ✅ Estructura de proyectos y tareas
6. ✅ Tareas con documentos disponibles
7. ✅ Ejemplos de uso

### Acceder al Test

```
http://localhost/dolibarr/custom/dolibarmodernfrontend/test_task_documents_api.php
```

## 📝 Códigos de Respuesta HTTP

- **200 OK**: Solicitud exitosa
- **401 Unauthorized**: Falta API key o permisos insuficientes
- **404 Not Found**: Tarea o proyecto no encontrado
- **500 Internal Server Error**: Error en el servidor

## ⚠️ Notas Importantes

1. **Módulo Proyectos**: Debe estar activado para que los endpoints funcionen correctamente.

2. **Permisos**: El usuario debe tener permisos de lectura en Proyectos o en el módulo dolibarmodernfrontend.

3. **Directorio de tarea**: Se crea automáticamente al subir el primer archivo a la tarea.

4. **Referencia de tarea**: Si la tarea no tiene referencia definida, se usará su ID como nombre de directorio.

5. **Metadatos opcionales**: El campo `file_info` solo aparece si el archivo está registrado en `llx_ecm_files`.

6. **modulepart**: Importante usar `project_task` para acceder correctamente a los documentos.

## 🔗 Diferencias con Otros Endpoints de Documentos

| Endpoint | Tipo | Estructura | modulepart |
|----------|------|------------|------------|
| `/user/{id}/documents` | Directorios ECM | `ecm/{path}/` | `ecm` |
| `/task/{id}/documents` | Tareas de proyecto | `projet/{ref}/task/{ref}/` | `project_task` |
| `/project/{id}/tasks/documents` | Consolidado proyecto | `projet/{ref}/task/{ref}/` | `project_task` |

## 📊 Casos de Uso Avanzados

### Dashboard de Proyecto

Crear un dashboard que muestre:
- Total de tareas
- Total de documentos
- Documentos por tarea
- Tareas sin documentación

### Sistema de Búsqueda

Implementar búsqueda de documentos por:
- Nombre de archivo
- Tipo de archivo
- Tarea
- Fecha de modificación

### Exportación de Documentación

Generar un archivo ZIP con toda la documentación del proyecto organizada por tareas.

### Alertas de Documentación

Identificar tareas sin documentos o con documentación desactualizada.

## 📅 Historial de Versiones

### v1.2.4 (2025-10-05)
- ✅ Implementación de `/task/{id}/documents`
- ✅ Implementación de `/project/{id}/tasks/documents`
- ✅ Soporte para metadatos desde `llx_ecm_files`
- ✅ URLs de descarga con modulepart=project_task
- ✅ Archivo de prueba `test_task_documents_api.php`
- ✅ Vista consolidada de documentos del proyecto

---

**Módulo**: dolibarmodernfrontend  
**Versión**: 1.2.4  
**Fecha**: 2025-10-05  
**Autor**: DolibarrModules
