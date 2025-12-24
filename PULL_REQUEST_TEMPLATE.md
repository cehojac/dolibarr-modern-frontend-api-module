# QUAL|Qual Add documentation and usage examples for Documents API with project tasks

## Description

This PR improves the developer experience when working with the Documents API for project tasks by providing comprehensive documentation and ready-to-use examples.

### Problem
The Documents API (`htdocs/api/class/api_documents.class.php`) already supports project_task operations, but lacks clear documentation and practical examples. This leads to common issues:
- Developers unsure about required parameters when listing task documents
- Confusion about how to properly upload documents to tasks  
- Unclear file encoding requirements for different file types
- Missing practical examples for API integration

### Solution
Created comprehensive documentation and test files that demonstrate:
- How to list documents from a project task (GET endpoint)
- How to upload documents to a project task (POST endpoint)
- Proper usage of plain text vs base64 encoding
- Ready-to-run test scripts in PHP and Bash

## Files Added

### Example JSON Files
1. **`upload_document_to_task_text.json`**
   - Example for uploading plain text files to tasks
   - Shows proper structure for `modulepart: project_task`
   - Demonstrates empty `fileencoding` for text files

2. **`upload_document_to_task_base64.json`**
   - Example for uploading binary files (PDF) to tasks
   - Shows base64 encoding usage
   - Contains real base64-encoded PDF example

### Test Scripts
3. **`test_upload_task_document.php`**
   - Complete PHP test script with 3 test scenarios
   - Tests: plain text, CSV, and PDF (base64) uploads
   - Includes configuration variables for easy customization
   - Colored console output with success/failure indicators
   - Detailed response display and summary report

4. **`test_upload_task_document.sh`**
   - Bash script for testing with curl
   - Uses the JSON example files
   - Tests both text and base64 uploads
   - Includes HTTP status code display

## API Endpoint Usage Documentation

### GET /documents - List task documents
**Required parameters:**
- `modulepart`: Must be `project_task` or `task`
- `id` OR `ref`: Task ID or reference (at least one is required)

**Example:**
```bash
GET /api/index.php/documents?modulepart=project_task&ref=T001
```

### POST /documents/upload - Upload document to task
**Required parameters:**
- `filename`: Name of file to create
- `modulepart`: `project_task` or `task`
- `ref`: Task reference

**Key points:**
- Use `fileencoding: ""` for text files
- Use `fileencoding: "base64"` for binary files (PDF, images, etc.)
- Documents stored in: `{project_documents}/{project_ref}/{task_ref}/`

**Example:**
```json
{
  "filename": "report.pdf",
  "modulepart": "project_task",
  "ref": "T001",
  "filecontent": "JVBERi0xLjQK...",
  "fileencoding": "base64",
  "overwriteifexists": 0,
  "createdirifnotexists": 1
}
```

## Testing

### Run PHP test script:
```bash
# 1. Configure in test_upload_task_document.php:
#    - $API_URL
#    - $DOLAPIKEY  
#    - $TASK_REF

# 2. Execute:
php test_upload_task_document.php
```

### Run bash test script:
```bash
# 1. Configure API_URL and DOLAPIKEY in test_upload_task_document.sh
# 2. Execute:
bash test_upload_task_document.sh
```

## Benefits

- **Improved Developer Experience**: Clear, practical examples reduce integration time
- **Reduced Support Burden**: Common questions answered with working examples
- **Better API Adoption**: Developers can quickly understand and use the API correctly
- **Testing Resources**: Ready-to-use scripts for validation and debugging
