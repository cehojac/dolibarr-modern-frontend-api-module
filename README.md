# Dolibarr Modern Frontend Module

## Description

The **dolibarrmodernfrontend** module provides a modern API to manage links between tickets and interventions in Dolibarr using the **native linking system** (`llx_element_element`). It lets you create, read, and delete relationships between these elements programmatically without any additional tables.

## Features

- **Full REST API** for relationship management
- **Uses Dolibarr’s native system** (`llx_element_element`)
- **No extra tables** – compatible with the standard schema
- **Direct linking** between tickets and interventions
- **Optimized queries** to retrieve related data
- **Web interface** for manual management
- **Integrated API documentation**
- **Granular permission system**
- **Multi-language support** (Spanish included)

## Installation

1. Copy the module into `custom/dolibarrmodernfrontend/`
2. Enable the module from Dolibarr’s administration panel
3. Configure user permissions as needed

**Note:** No additional SQL scripts are required because the module relies on Dolibarr’s native `llx_element_element` table.

## Module Structure

```
dolibarrmodernfrontend/
├── admin/
│   └── dolibarrmodernfrontend_setup.php    # Module configuration page
├── class/
│   ├── ticketinterventionlink.class.php   # Core class (uses llx_element_element)
│   └── api_dolibarrmodernfrontend.class.php # REST API
├── core/
│   └── modules/
│       └── modDolibarrmodernfrontend.class.php # Module definition
├── langs/
│   └── es_ES/
│       └── dolibarrmodernfrontend.lang      # Spanish translations
├── api_doc.php                             # API documentation
├── interventions_list.php                  # Management interface
└── README.md                               # This file
```

## API Endpoints

### Base URL
```
/api/index.php/dolibarrmodernfrontend
```

### Authentication
All API calls require the `DOLAPIKEY` header with a valid API key.

### Available Endpoints

#### 1. Link a Ticket to an Intervention
```http
POST /link/{ticket_id}/{intervention_id}
```

**Parameters:**
- `ticket_id` (int): Ticket ID
- `intervention_id` (int): Intervention ID
- `link_type` (string, optional): Link type (manual, automatic, system)
- `description` (string, optional): Link description

#### 2. Unlink a Ticket from an Intervention
```http
DELETE /unlink/{ticket_id}/{intervention_id}
```

#### 3. Get Interventions Linked to a Ticket
```http
GET /ticket/{ticket_id}/interventions
```

#### 4. Get Tickets Linked to an Intervention
```http
GET /intervention/{intervention_id}/tickets
```

#### 5. Create a Ticket Message with a Custom Contact
```http
POST /tickets/{ticket_id}/newmessage
```

**URL Parameters:**
- `ticket_id` (int, required): Ticket ID in the URL

**Body Parameters:**
- `message` (string, required): Message content
- `contact_id` (int, optional): ID of the contact creating the message (default: 0 = API user)
- `private` (int, optional): Private message flag (0 = public, 1 = private, default: 0)
- `send_email` (int, optional): Send email notification (0 = no, 1 = yes, default: 0)

**Note:** Subject is not required; the ticket’s subject is used automatically.

**Description:**
Creates a message in a ticket and optionally attributes it to a specific contact. Useful for API integrations that must credit the correct contact associated with the ticket’s company. Uses Dolibarr’s native `newMessage()` method.

**Usage Example:**
```bash
curl -X POST \
  'http://your-dolibarr.com/api/index.php/dolibarrmodernfrontend/tickets/123/newmessage' \
  -H 'DOLAPIKEY: your_api_key' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'message=Test message&contact_id=115&private=0&send_email=0'
```

**Response:**
```json
{
  "success": true,
  "message": "Message added successfully to ticket",
  "ticket_id": 123,
  "ticket_ref": "TK2310-0001",
  "message_id": 456,
  "created_by_contact_id": 115,
  "created_by_user_id": 0,
  "created_by_login": "contact@company.com",
  "created_by_name": "John Doe",
  "private": false,
  "send_email": false,
  "timestamp": "2025-10-19 22:45:00"
}
```

#### 6. Retrieve Professional ID Validation URLs
```http
GET /idprofvalidatorurl
GET /idprofvalidatorurl?country=ES
GET /idprofvalidatorurl?all=1
```

**Description:**
Returns validation URLs for professional identifiers (SIREN, NIF, CIF, TIN, etc.) by country. By default it returns URLs for your company’s country configured in Dolibarr. Based on Dolibarr’s native `id_prof_url` function.

**Optional Parameters:**
- `country` (string): Specific country code (FR, ES, GB, etc.)
- `all` (int): 1 to fetch all available countries

**Modes of Operation:**
- **No parameters:** Returns only the company country (mysoc)
- **?country=XX:** Returns only the specified country
- **?all=1:** Returns all available countries

**Supported Countries:**
- FR (France): SIREN
- GB/UK (United Kingdom): Company Number
- ES (Spain): NIF/CIF
- IN (India): TIN
- DZ (Algeria): NIF
- PT (Portugal): NIF

**Response (company mode):**
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

## Database

The module **does NOT create additional tables**. It relies on Dolibarr’s native table:

### llx_element_element (native table)
- `rowid`: Link unique ID
- `fk_source`: Source element ID (ticket)
- `sourcetype`: Source element type ('ticket')
- `fk_target`: Target element ID (intervention)
- `targettype`: Target element type ('intervention')

This implementation is **100% compatible** with Dolibarr’s standard system and does not require database changes.

## Permissions

The module defines the following permissions:

- **Read**: View existing links
- **Write**: Create and update links
- **Delete**: Remove links
- **Administer**: Configure the module

## Configuration

Go to `Tools > Modern Frontend > Setup` to adjust module options.

## API Usage

### Example: Link a ticket to an intervention

```bash
curl -X POST \
  'http://your-dolibarr.com/api/index.php/dolibarrmodernfrontend/link/123/456' \
  -H 'DOLAPIKEY: your_api_key' \
  -H 'Content-Type: application/json' \
  -d '{
    "link_type": "manual",
    "description": "Manual link between ticket and intervention"
  }'
```

### Example: Get interventions from a ticket

```bash
curl -X GET \
  'http://your-dolibarr.com/api/index.php/dolibarrmodernfrontend/ticket/123/interventions' \
  -H 'DOLAPIKEY: your_api_key'
```

## Module Info

- **Module number**: 105003
- **Version**: 1.2.6
- **Family**: interface
- **Author**: DolibarrModules
- **Compatibility**: Dolibarr 11.0+, PHP 7.0+

## Support

To report issues or request new features, contact the module maintainer.

## License

This module is distributed under the same license as Dolibarr.
