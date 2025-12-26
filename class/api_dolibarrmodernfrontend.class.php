<?php
require_once DOL_DOCUMENT_ROOT.'/api/class/api.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/dolibarrmodernfrontend/class/ticketinterventionlink.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/dolibarrmodernfrontend/core/modules/modDolibarrmodernfrontend.class.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';

use Luracast\Restler\RestException;

/**
 * API class for dolibarrmodernfrontend module
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class DolibarrmodernfrontendApi extends DolibarrApi
{
    /**
     * @var array   $FIELDS     Mandatory fields, checked when create and update object
     */
    static $FIELDS = array(
        'fk_ticket',
        'fk_intervention'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        global $db, $conf;
        $this->db = $db;
    }

    /**
     * Get module version and metadata
     *
     * @return array
     *
     * @url GET /version
     *
     * @throws RestException
     */
    public function getModuleVersion()
    {
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend)
            && DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket)
            && DolibarrApiAccess::$user->rights->ticket->read;

        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket or dolibarrmodernfrontend read permissions');
        }

        $module = new modDolibarrmodernfrontend($this->db);

        return array(
            'module' => $module->name,
            'description' => $module->description,
            'version' => $module->version,
            'editor' => $module->editor_name,
        );
    }

    /**
     * Get properties of a ticket intervention link object
     *
     * Return an array with ticket intervention link informations
     *
     * @param 	int 	$id ID of ticket intervention link
     * @return  array|mixed data without useless information
     *
     * @throws 	RestException
     */
    public function get($id)
    {
        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->read;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket read permissions or dolibarrmodernfrontend read permissions');
        }

        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'Ticket intervention link not found');
        }

        if (!DolibarrApi::_checkAccessToResource('ticketinterventionlink', $result->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        return $this->_cleanObjectDatas($result);
    }

    /**
     * List ticket intervention links
     *
     * Get a list of ticket intervention links
     *
     * @param string	$sortfield	Sort field
     * @param string	$sortorder	Sort order
     * @param int		$limit		Limit for list
     * @param int		$page		Page number
     * @param string   	$sqlfilters Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
     * @return  array Array of ticket intervention link objects
     *
     * @throws RestException
     */
    public function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '')
    {
        global $db, $conf;

        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->read;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket read permissions or dolibarrmodernfrontend read permissions');
        }

        $obj_ret = array();

        // case of external user, $societe param is ignored and replaced by user's socid
        //$socid = DolibarrApiAccess::$user->socid ? DolibarrApiAccess::$user->socid : $societe;

        $sql = "SELECT t.rowid";
        $sql .= " FROM ".MAIN_DB_PREFIX."ticket_intervention_link as t";
        $sql .= ' WHERE 1 = 1';
        // Add sql filters
        if ($sqlfilters) {
            $errormessage = '';
            $sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
            if ($errormessage) {
                throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
            }
        }

        $sql .= $this->db->order($sortfield, $sortorder);
        if ($limit) {
            if ($page < 0) {
                $page = 0;
            }
            $offset = $limit * $page;

            $sql .= $this->db->plimit($limit + 1, $offset);
        }

        dol_syslog("API Rest request");
        $result = $this->db->query($sql);

        if ($result) {
            $num = $this->db->num_rows($result);
            $min = min($num, ($limit <= 0 ? $num : $limit));
            $i = 0;
            while ($i < $min) {
                $obj = $this->db->fetch_object($result);
                $ticketinterventionlink_static = new TicketInterventionLink($this->db);
                if ($ticketinterventionlink_static->fetch($obj->rowid)) {
                    $obj_ret[] = $this->_cleanObjectDatas($ticketinterventionlink_static);
                }
                $i++;
            }
        } else {
            throw new RestException(503, 'Error when retrieve ticket intervention link list : '.$this->db->lasterror());
        }
        if (!count($obj_ret)) {
            throw new RestException(404, 'No ticket intervention link found');
        }
        return $obj_ret;
    }

    /**
     * Create ticket intervention link object
     *
     * @param array $request_data   Request data
     * @return int  ID of ticket intervention link
     */
    public function post($request_data = null)
    {
        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->write;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket write permissions or dolibarrmodernfrontend write permissions');
        }

        // Check mandatory fields
        $result = $this->_validate($request_data);

        foreach ($request_data as $field => $value) {
            $this->ticketinterventionlink->$field = $value;
        }
        $this->ticketinterventionlink->fk_user_author = DolibarrApiAccess::$user->id;
        $this->ticketinterventionlink->datec = dol_now();
        $this->ticketinterventionlink->status = TicketInterventionLink::STATUS_ACTIVE;

        if ($this->ticketinterventionlink->create(DolibarrApiAccess::$user) < 0) {
            throw new RestException(500, "Error creating ticket intervention link", array_merge(array($this->ticketinterventionlink->error), $this->ticketinterventionlink->errors));
        }

        return $this->ticketinterventionlink->id;
    }

    /**
     * Update ticket intervention link
     *
     * @param int   $id             Id of ticket intervention link to update
     * @param array $request_data   Datas
     * @return int
     */
    public function put($id, $request_data = null)
    {
        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->write;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket write permissions or dolibarrmodernfrontend write permissions');
        }

        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'Ticket intervention link not found');
        }

        if (!DolibarrApi::_checkAccessToResource('ticketinterventionlink', $this->ticketinterventionlink->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        foreach ($request_data as $field => $value) {
            if ($field == 'id') continue;
            $this->ticketinterventionlink->$field = $value;
        }

        if ($this->ticketinterventionlink->update(DolibarrApiAccess::$user) > 0) {
            return $this->get($id);
        } else {
            throw new RestException(500, $this->ticketinterventionlink->error);
        }
    }

    /**
     * Delete ticket intervention link
     *
     * @param   int     $id   Ticket intervention link ID
     * @return  array
     */
    public function delete($id)
    {
        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->delete;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->write;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket write permissions or dolibarrmodernfrontend delete permissions');
        }
        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'Ticket intervention link not found');
        }

        if (!DolibarrApi::_checkAccessToResource('ticketinterventionlink', $this->ticketinterventionlink->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        if (!$this->ticketinterventionlink->delete(DolibarrApiAccess::$user)) {
            throw new RestException(401, 'Error when delete ticket intervention link : '.$this->ticketinterventionlink->error);
        }

        return array(
            'success' => array(
                'code' => 200,
                'message' => 'Ticket intervention link deleted'
            )
        );
    }

    /**
     * Link an intervention to a ticket using native Dolibarr system
     *
     * @param int $ticket_id ID of the ticket
     * @param int $intervention_id ID of the intervention
     * @return array
     *
     * @url POST /link/{ticket_id}/{intervention_id}
     */
    public function linkTicketIntervention($ticket_id, $intervention_id)
    {
        global $db, $user;

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Verificar que la intervención existe
        require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
        $intervention = new Fichinter($db);
        if ($intervention->fetch($intervention_id) <= 0) {
            throw new RestException(404, 'Intervention not found');
        }

        // Verificar si ya existe la vinculación
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."element_element";
        $sql .= " WHERE fk_source = ".((int) $ticket_id);
        $sql .= " AND fk_target = ".((int) $intervention_id);
        $sql .= " AND sourcetype = 'ticket'";
        $sql .= " AND targettype = 'fichinter'";

        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            throw new RestException(409, 'Link already exists between this ticket and intervention');
        }

        // Usar el método nativo de Dolibarr para crear la vinculación
        $result = $ticket->add_object_linked('fichinter', $intervention_id);
        
        if ($result < 0) {
            throw new RestException(500, 'Error creating link: '.$ticket->error);
        }

        return array(
            'success' => array(
                'code' => 201,
                'message' => 'Link created successfully using native Dolibarr system',
                'ticket_id' => $ticket_id,
                'ticket_ref' => $ticket->ref,
                'intervention_id' => $intervention_id,
                'intervention_ref' => $intervention->ref
            )
        );
    }

    /**
     * Unlink an intervention from a ticket
     *
     * @param int $ticket_id ID of the ticket
     * @param int $intervention_id ID of the intervention
     * @return array
     *
     * @url DELETE /unlink/{ticket_id}/{intervention_id}
     */
    public function unlinkTicketIntervention($ticket_id, $intervention_id)
    {
        global $db, $user;

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Verificar si existe la vinculación
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."element_element";
        $sql .= " WHERE fk_source = ".((int) $ticket_id);
        $sql .= " AND fk_target = ".((int) $intervention_id);
        $sql .= " AND sourcetype = 'ticket'";
        $sql .= " AND targettype = 'fichinter'";

        $resql = $db->query($sql);
        if (!$resql || $db->num_rows($resql) == 0) {
            throw new RestException(404, 'Link not found between this ticket and intervention');
        }

        // Usar el método nativo de Dolibarr para eliminar la vinculación
        $result = $ticket->deleteObjectLinked(null, 'fichinter', $intervention_id);
        
        if ($result < 0) {
            throw new RestException(500, 'Error removing link: '.$ticket->error);
        }

        return array(
            'success' => array(
                'code' => 200,
                'message' => 'Link removed successfully using native Dolibarr system',
                'ticket_id' => $ticket_id,
                'ticket_ref' => $ticket->ref,
                'intervention_id' => $intervention_id
            )
        );
    }

    /**
     * Get interventions linked to a ticket
     *
     * @param int $ticket_id ID of the ticket
     * @return array Array of linked interventions
     *
     * @url GET /ticket/{ticket_id}/interventions
     */
    public function getInterventionsByTicket($ticket_id)
    {
        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->read;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket read permissions or dolibarrmodernfrontend read permissions');
        }

        // Verificar que el ticket existe
        $ticket = new Ticket($this->db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        $ticketinterventionlink = new TicketInterventionLink($this->db);
        $interventions = $ticketinterventionlink->getInterventionsByTicket($ticket_id);
        
        if ($interventions === -1) {
            throw new RestException(500, 'Error retrieving interventions: '.$ticketinterventionlink->error);
        }

        if (empty($interventions)) {
            return array(
                'message' => 'No interventions found for this ticket',
                'interventions' => array()
            );
        }

        return array(
            'ticket_id' => $ticket_id,
            'ticket_ref' => $ticket->ref,
            'interventions_count' => count($interventions),
            'interventions' => $interventions
        );
    }

    /**
     * Get tickets linked to an intervention
     *
     * @param int $intervention_id ID of the intervention
     * @return array Array of linked tickets
     *
     * @url GET /intervention/{intervention_id}/tickets
     */
    public function getTicketsByIntervention($intervention_id)
    {
        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->read;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket read permissions or dolibarrmodernfrontend read permissions');
        }

        // Verificar que la intervención existe
        $intervention = new Fichinter($this->db);
        if ($intervention->fetch($intervention_id) <= 0) {
            throw new RestException(404, 'Intervention not found');
        }

        $ticketinterventionlink = new TicketInterventionLink($this->db);
        $tickets = $ticketinterventionlink->getTicketsByIntervention($intervention_id);
        
        if ($tickets === -1) {
            throw new RestException(500, 'Error retrieving tickets: '.$ticketinterventionlink->error);
        }

        if (empty($tickets)) {
            return array(
                'message' => 'No tickets found for this intervention',
                'tickets' => array()
            );
        }

        return array(
            'intervention_id' => $intervention_id,
            'intervention_ref' => $intervention->ref,
            'tickets_count' => count($tickets),
            'tickets' => $tickets
        );
    }

    /**
     * Validate fields before create or update object
     *
     * @param array $data   Array with data to verify
     * @return array
     * @throws RestException
     */
    private function _validate($data)
    {
        $ticketinterventionlink = array();
        foreach (DolibarrmodernfrontendApi::$FIELDS as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
            $ticketinterventionlink[$field] = $data[$field];
        }
        return $ticketinterventionlink;
    }

    /**
     * Fetch properties of a ticket intervention link object
     *
     * @param int $id ID of ticket intervention link
     * @return Object Object with cleaned properties
     *
     * @throws RestException
     */
    private function _fetch($id)
    {
        $this->ticketinterventionlink = new TicketInterventionLink($this->db);

        $result = $this->ticketinterventionlink->fetch($id);
        if (!$result) {
            throw new RestException(404, 'Ticket intervention link not found');
        }

        return $this->ticketinterventionlink;
    }

    /**
     * Clean sensible object datas
     *
     * @param   Object  $object    Object to clean
     * @return  Object             Object with cleaned properties
     */
    protected function _cleanObjectDatas($object)
    {
        $object = parent::_cleanObjectDatas($object);

        unset($object->rowid);
        unset($object->canvas);

        return $object;
    }

    /**
     * Get documents attached to a ticket
     *
     * @param int $ticket_id ID of the ticket
     * @return array Array of documents
     *
     * @url GET /ticket/{ticket_id}/documents
     */
    public function getTicketDocuments($ticket_id)
    {
        global $db, $conf;

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Directorio de documentos del ticket
        $upload_dir = $conf->ticket->multidir_output[$ticket->entity] . "/" . $ticket->ref;
        
        $documents = array();
        
        if (is_dir($upload_dir)) {
            require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
            $files = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', 'date', SORT_DESC);
            
            foreach ($files as $file) {
                $documents[] = array(
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'date' => $file['date'],
                    'type' => dol_mimetype($file['name']),
                    'download_url' => DOL_URL_ROOT . '/document.php?modulepart=ticket&file=' . urlencode($ticket->ref . '/' . $file['name'])
                );
            }
        }

        return array(
            'ticket_id' => $ticket_id,
            'ticket_ref' => $ticket->ref,
            'ticket_subject' => $ticket->subject,
            'documents' => $documents,
            'count' => count($documents)
        );
    }

    /**
     * Upload a document to a ticket using base64
     *
     * @param int $ticket_id ID of the ticket
     * @param array $request_data Request data with filename and file_content (base64)
     * @return array Upload result
     *
     * @url POST /ticket/{ticket_id}/documents
     */
    public function uploadTicketDocument($ticket_id, $request_data = null)
    {
        global $db, $conf, $user;

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Verificar datos requeridos
        if (empty($request_data) || !isset($request_data['filename']) || !isset($request_data['file_content'])) {
            throw new RestException(400, 'Missing required fields: filename and file_content (base64)');
        }

        $filename = $request_data['filename'];
        $file_content_base64 = $request_data['file_content'];

        // Validar que el contenido base64 es válido
        if (!base64_decode($file_content_base64, true)) {
            throw new RestException(400, 'Invalid base64 content');
        }

        // Decodificar el contenido
        $file_content = base64_decode($file_content_base64);
        
        // Validar tamaño del archivo (máximo 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB
        if (strlen($file_content) > $max_size) {
            throw new RestException(400, 'File too large. Maximum size: 10MB');
        }

        // Directorio de destino
        $upload_dir = $conf->ticket->multidir_output[$ticket->entity] . "/" . $ticket->ref;
        
        // Crear directorio si no existe
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        if (!is_dir($upload_dir)) {
            if (!dol_mkdir($upload_dir)) {
                throw new RestException(500, 'Failed to create upload directory');
            }
        }

        // Nombre del archivo (sanitizado)
        $filename = dol_sanitizeFileName($filename);
        $dest_file = $upload_dir . "/" . $filename;

        // Verificar que el archivo no existe ya
        if (file_exists($dest_file)) {
            throw new RestException(409, 'File already exists: ' . $filename);
        }

        // Guardar el archivo
        if (file_put_contents($dest_file, $file_content) === false) {
            throw new RestException(500, 'Failed to save file');
        }

        // Establecer permisos
        dolChmod($dest_file);

        return array(
            'success' => true,
            'message' => 'Document uploaded successfully',
            'ticket_id' => $ticket_id,
            'ticket_ref' => $ticket->ref,
            'filename' => $filename,
            'size' => filesize($dest_file),
            'mime_type' => dol_mimetype($filename),
            'upload_date' => date('Y-m-d H:i:s'),
            'download_url' => DOL_URL_ROOT . '/document.php?modulepart=ticket&file=' . urlencode($ticket->ref . '/' . $filename)
        );
    }

    /**
     * Delete a document from a ticket
     *
     * @param int $ticket_id ID of the ticket
     * @param string $filename Name of the file to delete
     * @return array Delete result
     *
     * @url DELETE /ticket/{ticket_id}/documents/{filename}
     */
    public function deleteTicketDocument($ticket_id, $filename)
    {
        global $db, $conf, $user;

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Sanitizar nombre del archivo
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        $filename = dol_sanitizeFileName($filename);
        
        // Ruta completa del archivo
        $upload_dir = $conf->ticket->multidir_output[$ticket->entity] . "/" . $ticket->ref;
        $file_path = $upload_dir . "/" . $filename;

        // Verificar que el archivo existe
        if (!file_exists($file_path)) {
            throw new RestException(404, 'Document not found: ' . $filename);
        }

        // Eliminar el archivo
        if (!unlink($file_path)) {
            throw new RestException(500, 'Failed to delete document');
        }

        return array(
            'success' => true,
            'message' => 'Document deleted successfully',
            'ticket_id' => $ticket_id,
            'ticket_ref' => $ticket->ref,
            'filename' => $filename
        );
    }

    /**
     * Get contacts related to a ticket
     *
     * @param int $id ID of the ticket
     * @return array Array of contacts
     *
     * @url GET /tickets/{id}/contacts
     */
    public function getTicketContacts($id)
    {
        global $db, $conf;

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        $contacts = array();

        // Consulta SQL con la columna source de c_type_contact para determinar interno/externo
        $sql = "SELECT ec.rowid, ec.fk_socpeople as contact_id, ec.fk_c_type_contact, ec.statut,";
        $sql .= " sp.lastname, sp.firstname, sp.email, sp.phone, sp.phone_perso, sp.phone_mobile,";
        $sql .= " sp.fk_soc, s.nom as company_name,";
        $sql .= " tc.code as contact_type_code, tc.libelle as contact_type_label, tc.source as contact_source";
        $sql .= " FROM ".MAIN_DB_PREFIX."element_contact ec";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople sp ON sp.rowid = ec.fk_socpeople";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = sp.fk_soc";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON tc.rowid = ec.fk_c_type_contact";
        $sql .= " WHERE ec.element_id = ".((int) $id);
        $sql .= " AND ec.fk_socpeople > 0";
        $sql .= " ORDER BY sp.lastname, sp.firstname";

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            for ($i = 0; $i < $num; $i++) {
                $obj = $db->fetch_object($resql);
                
                // Detectar si es interno por source o por company_name
                $is_internal = ($obj->contact_source == 'internal') || ($obj->company_name == 'Internal User');
                $user_data = null;
                
                // Si es interno, el contact_id es realmente un user_id, buscar directamente por ID
                if ($is_internal) {
                    $sql_user = "SELECT rowid, lastname, firstname, email, office_phone, user_mobile FROM ".MAIN_DB_PREFIX."user";
                    $sql_user .= " WHERE rowid = ".((int) $obj->contact_id)." AND statut = 1";
                    
                    $resql_user = $db->query($sql_user);
                    if ($resql_user && $db->num_rows($resql_user) > 0) {
                        $user_data = $db->fetch_object($resql_user);
                        $db->free($resql_user);
                    }
                }
                
                // Usar datos del usuario interno si existe, sino usar datos del contacto
                $contact_data = array(
                    'contact_id' => (int) $obj->contact_id,
                    'element_contact_id' => (int) $obj->rowid,
                    'user_id' => $is_internal && $user_data ? (int) $user_data->rowid : null,
                    'lastname' => $is_internal && $user_data ? $user_data->lastname : ($obj->lastname ?: ''),
                    'firstname' => $is_internal && $user_data ? $user_data->firstname : ($obj->firstname ?: ''),
                    'fullname' => '',
                    'email' => $is_internal && $user_data ? $user_data->email : ($obj->email ?: ''),
                    'phone' => $is_internal && $user_data ? ($user_data->office_phone ?: '') : ($obj->phone ?: ''),
                    'phone_perso' => $is_internal && $user_data ? ($user_data->user_mobile ?: '') : ($obj->phone_perso ?: ''),
                    'phone_mobile' => $is_internal && $user_data ? ($user_data->user_mobile ?: '') : ($obj->phone_mobile ?: ''),
                    'company_id' => $obj->fk_soc ? (int) $obj->fk_soc : null,
                    'company_name' => $is_internal ? 'Internal User' : ($obj->company_name ?: ''),
                    'contact_type_code' => $obj->contact_type_code ?: '',
                    'contact_type_label' => $obj->contact_type_label ?: '',
                    'contact_source' => $obj->contact_source ?: 'external',
                    'status' => (int) $obj->statut
                );
                
                // Construir fullname después de tener los datos correctos
                $contact_data['fullname'] = trim($contact_data['firstname'] . ' ' . $contact_data['lastname']);
                
                $contacts[] = $contact_data;
            }
            $db->free($resql);
        } else {
            throw new RestException(500, 'Error retrieving contacts: '.$db->lasterror());
        }

        return array(
            'ticket_id' => $id,
            'ticket_ref' => $ticket->ref,
            'ticket_subject' => $ticket->subject,
            'contacts' => $contacts,
            'count' => count($contacts)
        );
    }

    /**
     * Add a contact to a ticket
     *
     * @param int $id ID of the ticket
     * @param array $request_data Request data with contact information
     * @return array Add contact result
     *
     * @url POST /tickets/{id}/contacts
     */
    public function addTicketContact($id, $request_data = null)
    {
        global $db, $conf;

        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->write;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket write permissions or dolibarrmodernfrontend write permissions');
        }

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Verificar datos requeridos
        if (empty($request_data) || !isset($request_data['contact_id']) || !isset($request_data['contact_type'])) {
            throw new RestException(400, 'Missing required fields: contact_id and contact_type');
        }

        $contact_id = (int) $request_data['contact_id'];
        $contact_type = $request_data['contact_type'];
        $contact_source = isset($request_data['contact_source']) ? $request_data['contact_source'] : 'external';

        // Validar contact_source
        if (!in_array($contact_source, array('external', 'internal'))) {
            throw new RestException(400, 'contact_source must be either "external" or "internal"');
        }

        // Verificar que el contacto existe según su tipo
        if ($contact_source == 'external') {
            // Verificar que el contacto externo existe
            require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
            $contact = new Contact($db);
            if ($contact->fetch($contact_id) <= 0) {
                throw new RestException(404, 'External contact not found');
            }
        } else {
            // Verificar que el usuario interno existe
            require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
            $user = new User($db);
            if ($user->fetch($contact_id) <= 0) {
                throw new RestException(404, 'Internal user not found');
            }
        }

        // Obtener el ID del tipo de contacto
        $contact_type_id = null;
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."c_type_contact";
        $sql .= " WHERE element = 'ticket' AND code = '".$db->escape($contact_type)."'";
        $sql .= " AND source = '".$db->escape($contact_source)."'";
        
        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $obj = $db->fetch_object($resql);
            $contact_type_id = $obj->rowid;
            $db->free($resql);
        } else {
            throw new RestException(400, 'Invalid contact_type: ' . $contact_type . ' for source: ' . $contact_source);
        }

        // Verificar si el contacto ya está asociado al ticket
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."element_contact";
        $sql .= " WHERE element_id = ".((int) $id);
        $sql .= " AND fk_socpeople = ".((int) $contact_id);
        $sql .= " AND fk_c_type_contact = ".((int) $contact_type_id);

        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $db->free($resql);
            throw new RestException(409, 'Contact already associated with this ticket');
        }

        // Usar el método nativo de Dolibarr para agregar el contacto
        $result = $ticket->add_contact($contact_id, $contact_type, $contact_source);
        
        if ($result < 0) {
            throw new RestException(500, 'Error adding contact to ticket: ' . $ticket->error);
        }

        // Obtener información del contacto agregado
        $contact_info = array();
        if ($contact_source == 'external') {
            $contact_info = array(
                'contact_id' => $contact_id,
                'lastname' => $contact->lastname ?: '',
                'firstname' => $contact->firstname ?: '',
                'fullname' => trim(($contact->firstname ?: '') . ' ' . ($contact->lastname ?: '')),
                'email' => $contact->email ?: '',
                'phone' => $contact->phone_pro ?: '',
                'company_id' => $contact->fk_soc ?: null,
                'contact_source' => 'external'
            );
        } else {
            $contact_info = array(
                'contact_id' => $contact_id,
                'user_id' => $contact_id,
                'lastname' => $user->lastname ?: '',
                'firstname' => $user->firstname ?: '',
                'fullname' => trim(($user->firstname ?: '') . ' ' . ($user->lastname ?: '')),
                'email' => $user->email ?: '',
                'phone' => $user->office_phone ?: '',
                'company_name' => 'Internal User',
                'contact_source' => 'internal'
            );
        }

        return array(
            'success' => true,
            'message' => 'Contact added successfully to ticket',
            'ticket_id' => $id,
            'ticket_ref' => $ticket->ref,
            'contact_type' => $contact_type,
            'contact_source' => $contact_source,
            'contact_info' => $contact_info,
            'element_contact_id' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        );
    }

    /**
     * Remove a contact from a ticket
     *
     * @param int $id ID of the ticket
     * @param int $contact_id ID of the contact to remove
     * @param string $contact_source Source of the contact (external or internal)
     * @return array Remove contact result
     *
     * @url DELETE /tickets/{id}/contacts/{contact_id}/{contact_source}
     */
    public function removeTicketContact($id, $contact_id, $contact_source = 'external')
    {
        global $db, $conf;

        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->write;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket write permissions or dolibarrmodernfrontend write permissions');
        }

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Validar contact_source
        if (!in_array($contact_source, array('external', 'internal'))) {
            throw new RestException(400, 'contact_source must be either "external" or "internal"');
        }

        $contact_id = (int) $contact_id;

        // Verificar que el contacto está asociado al ticket
        $sql = "SELECT ec.rowid, ec.fk_c_type_contact FROM ".MAIN_DB_PREFIX."element_contact ec";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON tc.rowid = ec.fk_c_type_contact";
        $sql .= " WHERE ec.element_id = ".((int) $id);
        $sql .= " AND ec.fk_socpeople = ".((int) $contact_id);
        $sql .= " AND tc.source = '".$db->escape($contact_source)."'";

        $resql = $db->query($sql);
        if (!$resql || $db->num_rows($resql) == 0) {
            throw new RestException(404, 'Contact not found in this ticket');
        }

        $obj = $db->fetch_object($resql);
        $element_contact_id = $obj->rowid;
        $contact_type_id = $obj->fk_c_type_contact;
        $db->free($resql);

        // Usar el método nativo de Dolibarr para eliminar el contacto
        $result = $ticket->delete_contact($element_contact_id);
        
        if ($result < 0) {
            throw new RestException(500, 'Error removing contact from ticket: ' . $ticket->error);
        }

        return array(
            'success' => true,
            'message' => 'Contact removed successfully from ticket',
            'ticket_id' => $id,
            'ticket_ref' => $ticket->ref,
            'contact_id' => $contact_id,
            'contact_source' => $contact_source,
            'element_contact_id' => $element_contact_id,
            'timestamp' => date('Y-m-d H:i:s')
        );
    }

    /**
     * Send email to external contacts related to a ticket using native Dolibarr methods
     *
     * @param int $ticket_id ID of the ticket
     * @param array $request_data Request data with subject, message, and optional parameters
     * @return array Send result
     *
     * @url POST /tickets/{ticket_id}/sendemail
     */
    public function sendTicketEmail($ticket_id, $request_data = null)
    {
        global $db, $conf, $user, $langs;

        // Verificar permisos: usar permisos nativos de tickets si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->write;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket write permissions or dolibarrmodernfrontend write permissions');
        }

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Verificar datos requeridos
        if (empty($request_data) || !isset($request_data['subject']) || !isset($request_data['message'])) {
            throw new RestException(400, 'Missing required fields: subject and message');
        }

        $subject = $request_data['subject'];
        $message = $request_data['message'];
        $private_message = isset($request_data['private']) ? (bool)$request_data['private'] : false;
        $send_to_internal = isset($request_data['send_to_internal']) ? (bool)$request_data['send_to_internal'] : false;

        // Obtener contactos del ticket
        $contacts = $this->getTicketEmailContacts($ticket_id, $send_to_internal);
        
        if (empty($contacts)) {
            throw new RestException(404, 'No contacts with valid email addresses found for this ticket');
        }

        $sent_emails = array();
        $failed_emails = array();
        $total_sent = 0;

        // Usar el método nativo de Dolibarr para crear el mensaje y enviarlo
        foreach ($contacts as $contact) {
            if (empty($contact['email'])) {
                $failed_emails[] = array(
                    'contact' => $contact['fullname'],
                    'email' => 'N/A',
                    'error' => 'No email address'
                );
                continue;
            }

            try {
                // Simular los datos POST que usa newMessage() nativamente
                $_POST['subject'] = $subject;
                $_POST['message'] = $message;
                $_POST['send_email'] = '1';
                $_POST['private_message'] = $private_message ? '1' : '0';
                
                // Usar el método nativo newMessage() de Dolibarr
                // Este método maneja automáticamente:
                // - createTicketMessage() para registrar en historial
                // - sendTicketMessageByEmail() para envío físico
                // - Códigos apropiados (TICKET_MSG_SENTBYMAIL, etc.)
                $action = 'add_message'; // Debe ser una variable (se pasa por referencia)
                $result = $ticket->newMessage(DolibarrApiAccess::$user, $action, $private_message, 0);
                
                if ($result > 0) {
                    $sent_emails[] = array(
                        'contact' => $contact['fullname'],
                        'email' => $contact['email'],
                        'contact_type' => $contact['contact_source'],
                        'sent_at' => date('Y-m-d H:i:s'),
                        'message_id' => $result
                    );
                    $total_sent++;
                } else {
                    $failed_emails[] = array(
                        'contact' => $contact['fullname'],
                        'email' => $contact['email'],
                        'error' => 'Failed to send using native method: ' . $ticket->error
                    );
                }
            } catch (Exception $e) {
                $failed_emails[] = array(
                    'contact' => $contact['fullname'],
                    'email' => $contact['email'],
                    'error' => $e->getMessage()
                );
            }
        }

        // Limpiar variables POST
        unset($_POST['subject'], $_POST['message'], $_POST['send_email'], $_POST['private_message']);

        return array(
            'success' => true,
            'ticket_id' => $ticket_id,
            'ticket_ref' => $ticket->ref,
            'subject' => $subject,
            'message' => $message,
            'private' => $private_message,
            'total_contacts' => count($contacts),
            'emails_sent' => $total_sent,
            'emails_failed' => count($failed_emails),
            'sent_to' => $sent_emails,
            'failed' => $failed_emails,
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => 'native_dolibarr_newMessage'
        );
    }

    /**
     * Get contacts for a ticket with valid email addresses
     *
     * @param int $ticket_id ID of the ticket
     * @param bool $include_internal Whether to include internal contacts
     * @return array Array of contacts with email addresses
     */
    private function getTicketEmailContacts($ticket_id, $include_internal = false)
    {
        global $db;

        $contacts = array();

        // Consulta para contactos externos
        $sql = "SELECT ec.rowid, ec.fk_socpeople as contact_id, ec.fk_c_type_contact, ec.statut,";
        $sql .= " sp.lastname, sp.firstname, sp.email, sp.phone, sp.phone_perso, sp.phone_mobile,";
        $sql .= " sp.fk_soc, s.nom as company_name,";
        $sql .= " tc.code as contact_type_code, tc.libelle as contact_type_label, tc.source as contact_source";
        $sql .= " FROM ".MAIN_DB_PREFIX."element_contact ec";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople sp ON sp.rowid = ec.fk_socpeople";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = sp.fk_soc";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON tc.rowid = ec.fk_c_type_contact";
        $sql .= " WHERE ec.element_id = ".((int) $ticket_id);
        $sql .= " AND ec.fk_socpeople > 0";
        $sql .= " AND sp.email IS NOT NULL AND sp.email != ''";
        
        if (!$include_internal) {
            $sql .= " AND (tc.source = 'external' OR tc.source IS NULL)";
        }
        
        $sql .= " ORDER BY sp.lastname, sp.firstname";

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            for ($i = 0; $i < $num; $i++) {
                $obj = $db->fetch_object($resql);
                
                // Detectar si es interno por source
                $is_internal = ($obj->contact_source == 'internal');
                
                if ($is_internal && $include_internal) {
                    // Para contactos internos, buscar en la tabla user
                    $sql_user = "SELECT rowid, lastname, firstname, email FROM ".MAIN_DB_PREFIX."user";
                    $sql_user .= " WHERE rowid = ".((int) $obj->contact_id)." AND statut = 1";
                    $sql_user .= " AND email IS NOT NULL AND email != ''";
                    
                    $resql_user = $db->query($sql_user);
                    if ($resql_user && $db->num_rows($resql_user) > 0) {
                        $user_data = $db->fetch_object($resql_user);
                        
                        $contacts[] = array(
                            'contact_id' => (int) $user_data->rowid,
                            'lastname' => $user_data->lastname ?: '',
                            'firstname' => $user_data->firstname ?: '',
                            'fullname' => trim(($user_data->firstname ?: '') . ' ' . ($user_data->lastname ?: '')),
                            'email' => $user_data->email ?: '',
                            'company_name' => 'Internal User',
                            'contact_type_code' => $obj->contact_type_code ?: '',
                            'contact_type_label' => $obj->contact_type_label ?: '',
                            'contact_source' => 'internal'
                        );
                        
                        $db->free($resql_user);
                    }
                } elseif (!$is_internal) {
                    // Contacto externo
                    $contacts[] = array(
                        'contact_id' => (int) $obj->contact_id,
                        'lastname' => $obj->lastname ?: '',
                        'firstname' => $obj->firstname ?: '',
                        'fullname' => trim(($obj->firstname ?: '') . ' ' . ($obj->lastname ?: '')),
                        'email' => $obj->email ?: '',
                        'company_name' => $obj->company_name ?: '',
                        'contact_type_code' => $obj->contact_type_code ?: '',
                        'contact_type_label' => $obj->contact_type_label ?: '',
                        'contact_source' => 'external'
                    );
                }
            }
            $db->free($resql);
        }

        return $contacts;
    }

    /**
     * Send email with custom format and attachments support
     *
     * @param int $ticket_id ID of the ticket
     * @param array $request_data Request data with subject, message, recipients and attachments
     * @return array Send result
     *
     * @url POST /tickets/{ticket_id}/sendemail
     */
    public function sendTicketEmailCustom($ticket_id, $request_data = null)
    {
        global $db, $conf, $user, $langs;

        // Verificar permisos: usar permisos nativos de tickets si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_ticket_perms = isset(DolibarrApiAccess::$user->rights->ticket) && 
                           DolibarrApiAccess::$user->rights->ticket->write;
        
        if (!$has_module_perms && !$has_ticket_perms) {
            throw new RestException(401, 'Access denied: Need ticket write permissions or dolibarrmodernfrontend write permissions');
        }

        // Verificar que el ticket existe
        require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
        $ticket = new Ticket($db);
        if ($ticket->fetch($ticket_id) <= 0) {
            throw new RestException(404, 'Ticket not found');
        }

        // Verificar datos requeridos
        if (empty($request_data) || !isset($request_data['subject']) || !isset($request_data['message'])) {
            throw new RestException(400, 'Missing required fields: subject and message');
        }

        $subject = $request_data['subject'];
        $message = $request_data['message'];
        $recipients = isset($request_data['recipients']) ? $request_data['recipients'] : array();
        $attachments = isset($request_data['attachments']) ? $request_data['attachments'] : array();

        // Validar recipients
        if (!is_array($recipients)) {
            throw new RestException(400, 'Recipients must be an array');
        }

        // Si no se especifican recipients, usar contactos del ticket
        if (empty($recipients)) {
            $ticket_contacts = $this->getTicketEmailContacts($ticket_id, false);
            foreach ($ticket_contacts as $contact) {
                if (!empty($contact['email'])) {
                    $recipients[] = $contact['email'];
                }
            }
        }

        if (empty($recipients)) {
            throw new RestException(404, 'No valid email recipients found');
        }

        // Procesar archivos adjuntos
        $processed_attachments = array();
        $temp_files = array();
        
        if (!empty($attachments) && is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (!isset($attachment['name']) || !isset($attachment['content'])) {
                    throw new RestException(400, 'Each attachment must have name and content fields');
                }

                $filename = $attachment['name'];
                $content_base64 = $attachment['content'];
                $size = isset($attachment['size']) ? (int)$attachment['size'] : 0;
                $type = isset($attachment['type']) ? $attachment['type'] : 'application/octet-stream';

                // Validar contenido base64
                if (!base64_decode($content_base64, true)) {
                    throw new RestException(400, 'Invalid base64 content in attachment: ' . $filename);
                }

                // Decodificar contenido
                $file_content = base64_decode($content_base64);
                
                // Validar tamaño (máximo 10MB por archivo)
                $max_size = 10 * 1024 * 1024; // 10MB
                if (strlen($file_content) > $max_size) {
                    throw new RestException(400, 'Attachment too large: ' . $filename . '. Maximum size: 10MB');
                }

                // Crear archivo temporal
                require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
                $temp_dir = $conf->admin->dir_temp;
                if (!is_dir($temp_dir)) {
                    dol_mkdir($temp_dir);
                }

                $safe_filename = dol_sanitizeFileName($filename);
                $temp_file = $temp_dir . '/' . uniqid('attach_') . '_' . $safe_filename;
                
                if (file_put_contents($temp_file, $file_content) === false) {
                    throw new RestException(500, 'Failed to create temporary file for attachment: ' . $filename);
                }

                $processed_attachments[] = array(
                    'name' => $filename,
                    'path' => $temp_file,
                    'size' => strlen($file_content),
                    'type' => $type
                );
                
                $temp_files[] = $temp_file; // Para limpieza posterior
            }
        }

        // Enviar emails usando CMailFile de Dolibarr
        require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
        
        $sent_emails = array();
        $failed_emails = array();
        $total_sent = 0;

        foreach ($recipients as $recipient_email) {
            if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                $failed_emails[] = array(
                    'email' => $recipient_email,
                    'error' => 'Invalid email address'
                );
                continue;
            }

            try {
                // Configurar remitente
                $from_email = $conf->global->MAIN_MAIL_EMAIL_FROM ?: 'noreply@' . $_SERVER['HTTP_HOST'];
                $from_name = $conf->global->MAIN_INFO_SOCIETE_NOM ?: 'Dolibarr';

                // Preparar archivos adjuntos para CMailFile
                $files_to_attach = array();
                $files_to_attach_name = array();
                $files_to_attach_mimetype = array();

                foreach ($processed_attachments as $attachment) {
                    $files_to_attach[] = $attachment['path'];
                    $files_to_attach_name[] = $attachment['name'];
                    $files_to_attach_mimetype[] = $attachment['type'];
                }

                // Crear objeto CMailFile
                $mailfile = new CMailFile(
                    $subject,                    // subject
                    $recipient_email,           // to
                    $from_email,                // from
                    $message,                   // msg
                    $files_to_attach,          // files_to_attach
                    $files_to_attach_mimetype, // files_to_attach_mimetype
                    $files_to_attach_name,     // files_to_attach_name
                    '',                         // addr_cc
                    '',                         // addr_bcc
                    0,                          // deliveryreceipt
                    1,                          // msgishtml
                    '',                         // errors_to
                    '',                         // css
                    '',                         // trackid
                    '',                         // moreinheader
                    'ticket',                   // sendcontext
                    $from_name                  // fromname
                );

                // Enviar email
                $result = $mailfile->sendfile();
                
                if ($result) {
                    $sent_emails[] = array(
                        'email' => $recipient_email,
                        'sent_at' => date('Y-m-d H:i:s'),
                        'attachments_count' => count($processed_attachments)
                    );
                    $total_sent++;

                    // Registrar en historial del ticket
                    $ticket->createTicketMessage(
                        DolibarrApiAccess::$user,
                        'TICKET_MSG_SENTBYMAIL',
                        $message,
                        0, // private
                        $files_to_attach,
                        $files_to_attach_name,
                        $files_to_attach_mimetype
                    );
                } else {
                    $failed_emails[] = array(
                        'email' => $recipient_email,
                        'error' => 'Failed to send email: ' . $mailfile->error
                    );
                }
            } catch (Exception $e) {
                $failed_emails[] = array(
                    'email' => $recipient_email,
                    'error' => $e->getMessage()
                );
            }
        }

        // Limpiar archivos temporales
        foreach ($temp_files as $temp_file) {
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
        }

        return array(
            'success' => true,
            'ticket_id' => $ticket_id,
            'ticket_ref' => $ticket->ref,
            'subject' => $subject,
            'message' => $message,
            'recipients_total' => count($recipients),
            'attachments_total' => count($processed_attachments),
            'emails_sent' => $total_sent,
            'emails_failed' => count($failed_emails),
            'sent_to' => $sent_emails,
            'failed' => $failed_emails,
            'attachments_processed' => array_map(function($att) {
                return array(
                    'name' => $att['name'],
                    'size' => $att['size'],
                    'type' => $att['type']
                );
            }, $processed_attachments),
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => 'custom_format_with_attachments'
        );
    }

    /**
     * DEBUG: Get raw contact data for diagnosis
     *
     * @param int $id ID of the ticket
     * @return array Raw contact data
     *
     * @url GET /tickets/{id}/contacts/debug
     */
    public function debugTicketContacts($id)
    {
        global $db;

        $debug_data = array();

        // Consulta 1: Datos básicos de element_contact
        $sql1 = "SELECT ec.*, 'element_contact' as source_table FROM ".MAIN_DB_PREFIX."element_contact ec WHERE ec.element_id = ".((int) $id);
        $resql1 = $db->query($sql1);
        if ($resql1) {
            while ($obj = $db->fetch_object($resql1)) {
                $debug_data['element_contact'][] = (array) $obj;
            }
            $db->free($resql1);
        }

        // Consulta 2: Datos de socpeople para estos contactos
        $sql2 = "SELECT sp.*, 'socpeople' as source_table FROM ".MAIN_DB_PREFIX."socpeople sp";
        $sql2 .= " WHERE sp.rowid IN (SELECT ec.fk_socpeople FROM ".MAIN_DB_PREFIX."element_contact ec WHERE ec.element_id = ".((int) $id).")";
        $resql2 = $db->query($sql2);
        if ($resql2) {
            while ($obj = $db->fetch_object($resql2)) {
                $debug_data['socpeople'][] = (array) $obj;
            }
            $db->free($resql2);
        }

        // Consulta 3: Datos de user que podrían coincidir
        $sql3 = "SELECT u.*, 'user' as source_table FROM ".MAIN_DB_PREFIX."user u WHERE u.statut = 1";
        $resql3 = $db->query($sql3);
        if ($resql3) {
            while ($obj = $db->fetch_object($resql3)) {
                $debug_data['users'][] = (array) $obj;
            }
            $db->free($resql3);
        }

        // Consulta 4: JOIN completo como lo hacemos en la API
        $sql4 = "SELECT ec.rowid, ec.fk_socpeople as contact_id, ec.fk_c_type_contact, ec.statut,";
        $sql4 .= " sp.lastname, sp.firstname, sp.email, sp.phone, sp.phone_perso, sp.phone_mobile,";
        $sql4 .= " sp.fk_soc, s.nom as company_name,";
        $sql4 .= " tc.code as contact_type_code, tc.libelle as contact_type_label";
        $sql4 .= " FROM ".MAIN_DB_PREFIX."element_contact ec";
        $sql4 .= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople sp ON sp.rowid = ec.fk_socpeople";
        $sql4 .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = sp.fk_soc";
        $sql4 .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON tc.rowid = ec.fk_c_type_contact";
        $sql4 .= " WHERE ec.element_id = ".((int) $id);
        $sql4 .= " AND ec.fk_socpeople > 0";
        $sql4 .= " ORDER BY sp.lastname, sp.firstname";

        $resql4 = $db->query($sql4);
        if ($resql4) {
            while ($obj = $db->fetch_object($resql4)) {
                $debug_data['joined_data'][] = (array) $obj;
            }
            $db->free($resql4);
        }

        return array(
            'ticket_id' => $id,
            'debug_queries' => array(
                'element_contact' => "SELECT * FROM element_contact WHERE element_id = $id",
                'socpeople' => "SELECT * FROM socpeople WHERE rowid IN (...)",
                'users' => "SELECT * FROM user WHERE statut = 1",
                'joined' => $sql4
            ),
            'data' => $debug_data
        );
    }

    /**
     * Get user manual documents (directories and files from ECM)
     *
     * @param int $id ID of the user
     * @return array Array of directories and files
     *
     * @url GET /user/{id}/documents
     */
    public function getUserDocuments($id)
    {
        global $db, $conf;

        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_ecm_perms = isset(DolibarrApiAccess::$user->rights->ecm) && 
                        DolibarrApiAccess::$user->rights->ecm->read;
        
        if (!$has_module_perms && !$has_ecm_perms) {
            throw new RestException(401, 'Access denied: Need ECM read permissions or dolibarrmodernfrontend read permissions');
        }

        // Verificar que el usuario existe
        require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $user = new User($db);
        if ($user->fetch($id) <= 0) {
            throw new RestException(404, 'User not found');
        }

        // Obtener directorios manuales del módulo ECM
        require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmdirectory.class.php';
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $result = array(
            'user_id' => $id,
            'user_login' => $user->login,
            'user_fullname' => $user->getFullName($langs),
            'directories' => array()
        );

        // Consultar directorios manuales de la tabla llx_ecm_directories
        // Estos son los directorios que aparecen en "Directorios manuales"
        $sql = "SELECT d.rowid, d.label, d.description, d.fk_parent, d.fullrelativename,";
        $sql .= " d.date_c, d.date_m, d.cachenbofdoc";
        $sql .= " FROM ".MAIN_DB_PREFIX."ecm_directories d";
        $sql .= " WHERE d.fk_user = ".((int) $id);
        $sql .= " AND d.entity = ".$conf->entity;
        $sql .= " ORDER BY d.label ASC";

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            
            for ($i = 0; $i < $num; $i++) {
                $obj = $db->fetch_object($resql);
                
                // Obtener archivos de este directorio
                $files = array();
                
                // Ruta física del directorio
                $relativepath = $obj->fullrelativename;
                $upload_dir = $conf->ecm->dir_output . '/' . $relativepath;
                
                if (is_dir($upload_dir)) {
                    $filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', 'date', SORT_DESC);
                    
                    if (is_array($filearray)) {
                        foreach ($filearray as $file) {
                            // Consultar información adicional del archivo desde llx_ecm_files
                            $sql_file = "SELECT rowid, filename, label, fullpath_orig, gen_or_uploaded,";
                            $sql_file .= " extraparams, date_c, date_m, position, cover";
                            $sql_file .= " FROM ".MAIN_DB_PREFIX."ecm_files";
                            $sql_file .= " WHERE filepath = '".$db->escape($relativepath)."'";
                            $sql_file .= " AND filename = '".$db->escape($file['name'])."'";
                            $sql_file .= " AND entity = ".$conf->entity;
                            
                            $resql_file = $db->query($sql_file);
                            $file_info = null;
                            
                            if ($resql_file && $db->num_rows($resql_file) > 0) {
                                $obj_file = $db->fetch_object($resql_file);
                                $file_info = array(
                                    'file_id' => (int) $obj_file->rowid,
                                    'label' => $obj_file->label ?: '',
                                    'gen_or_uploaded' => $obj_file->gen_or_uploaded ?: 'uploaded',
                                    'date_c' => $obj_file->date_c ? date('Y-m-d H:i:s', $obj_file->date_c) : null,
                                    'date_m' => $obj_file->date_m ? date('Y-m-d H:i:s', $obj_file->date_m) : null
                                );
                                $db->free($resql_file);
                            }
                            
                            $files[] = array(
                                'name' => $file['name'],
                                'size' => $file['size'],
                                'date' => date('Y-m-d H:i:s', $file['date']),
                                'type' => dol_mimetype($file['name']),
                                'relativepath' => $relativepath . '/' . $file['name'],
                                'download_url' => DOL_URL_ROOT . '/document.php?modulepart=ecm&file=' . urlencode($relativepath . '/' . $file['name']),
                                'file_info' => $file_info
                            );
                        }
                    }
                }
                
                // Información del directorio
                $directory_data = array(
                    'directory_id' => (int) $obj->rowid,
                    'label' => $obj->label ?: '',
                    'description' => $obj->description ?: '',
                    'parent_id' => $obj->fk_parent ? (int) $obj->fk_parent : null,
                    'relativepath' => $obj->fullrelativename ?: '',
                    'date_created' => $obj->date_c ? date('Y-m-d H:i:s', $obj->date_c) : null,
                    'date_modified' => $obj->date_m ? date('Y-m-d H:i:s', $obj->date_m) : null,
                    'files_count' => (int) $obj->cachenbofdoc,
                    'files' => $files
                );
                
                $result['directories'][] = $directory_data;
            }
            
            $db->free($resql);
        } else {
            throw new RestException(500, 'Error retrieving directories: '.$db->lasterror());
        }

        // También obtener directorios de nivel raíz (sin fk_user específico pero accesibles)
        // Esto incluye carpetas comunes como "Base de conocimientos"
        $sql_common = "SELECT d.rowid, d.label, d.description, d.fk_parent, d.fullrelativename,";
        $sql_common .= " d.date_c, d.date_m, d.cachenbofdoc";
        $sql_common .= " FROM ".MAIN_DB_PREFIX."ecm_directories d";
        $sql_common .= " WHERE (d.fk_user IS NULL OR d.fk_user = 0)";
        $sql_common .= " AND d.entity = ".$conf->entity;
        $sql_common .= " AND d.fk_parent = 0"; // Solo raíz
        $sql_common .= " ORDER BY d.label ASC";

        $resql_common = $db->query($sql_common);
        if ($resql_common) {
            $num_common = $db->num_rows($resql_common);
            
            $result['common_directories'] = array();
            
            for ($i = 0; $i < $num_common; $i++) {
                $obj = $db->fetch_object($resql_common);
                
                // Obtener archivos de este directorio
                $files = array();
                
                // Ruta física del directorio
                $relativepath = $obj->fullrelativename;
                $upload_dir = $conf->ecm->dir_output . '/' . $relativepath;
                
                if (is_dir($upload_dir)) {
                    $filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', 'date', SORT_DESC);
                    
                    if (is_array($filearray)) {
                        foreach ($filearray as $file) {
                            $files[] = array(
                                'name' => $file['name'],
                                'size' => $file['size'],
                                'date' => date('Y-m-d H:i:s', $file['date']),
                                'type' => dol_mimetype($file['name']),
                                'relativepath' => $relativepath . '/' . $file['name'],
                                'download_url' => DOL_URL_ROOT . '/document.php?modulepart=ecm&file=' . urlencode($relativepath . '/' . $file['name'])
                            );
                        }
                    }
                }
                
                // Información del directorio
                $directory_data = array(
                    'directory_id' => (int) $obj->rowid,
                    'label' => $obj->label ?: '',
                    'description' => $obj->description ?: '',
                    'parent_id' => $obj->fk_parent ? (int) $obj->fk_parent : null,
                    'relativepath' => $obj->fullrelativename ?: '',
                    'date_created' => $obj->date_c ? date('Y-m-d H:i:s', $obj->date_c) : null,
                    'date_modified' => $obj->date_m ? date('Y-m-d H:i:s', $obj->date_m) : null,
                    'files_count' => (int) $obj->cachenbofdoc,
                    'files' => $files
                );
                
                $result['common_directories'][] = $directory_data;
            }
            
            $db->free($resql_common);
        }

        $result['total_user_directories'] = count($result['directories']);
        $result['total_common_directories'] = isset($result['common_directories']) ? count($result['common_directories']) : 0;
        $result['timestamp'] = date('Y-m-d H:i:s');

        return $result;
    }

    /**
     * Get task documents (files uploaded to a project task)
     *
     * @param int $id ID of the task
     * @return array Array of documents
     *
     * @url GET /task/{id}/documents
     */
    public function getTaskDocuments($id)
    {
        global $db, $conf;

        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_project_perms = isset(DolibarrApiAccess::$user->rights->projet) && 
                            DolibarrApiAccess::$user->rights->projet->lire;
        
        if (!$has_module_perms && !$has_project_perms) {
            throw new RestException(401, 'Access denied: Need project read permissions or dolibarrmodernfrontend read permissions');
        }

        // Verificar que la tarea existe
        require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
        require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        
        $task = new Task($db);
        if ($task->fetch($id) <= 0) {
            throw new RestException(404, 'Task not found');
        }

        // Obtener información del proyecto asociado
        $project = new Project($db);
        if ($task->fk_project && $project->fetch($task->fk_project) > 0) {
            $projectref = dol_sanitizeFileName($project->ref);
        } else {
            throw new RestException(404, 'Project not found for this task');
        }

        $result = array(
            'task_id' => (int) $task->id,
            'task_ref' => $task->ref ?: '',
            'task_label' => $task->label ?: '',
            'project_id' => (int) $task->fk_project,
            'project_ref' => $project->ref ?: '',
            'project_title' => $project->title ?: '',
            'documents' => array()
        );

        // Buscar archivos de la tarea consultando directamente llx_ecm_files
        // Dolibarr vincula archivos a tareas usando la tabla element_element o directamente en ecm_files
        $taskref = dol_sanitizeFileName($task->ref);
        if (empty($taskref)) {
            $taskref = (string) $task->id;
        }
        
        $filearray = array();
        $upload_dir = '';
        
        // Buscar en llx_ecm_files todos los archivos que puedan estar relacionados con esta tarea
        // Probar diferentes formas en que Dolibarr puede vincular archivos
        $sql_ecm = "SELECT e.rowid, e.filepath, e.filename, e.label, e.gen_or_uploaded,";
        $sql_ecm .= " e.fullpath_orig, e.date_c, e.date_m, e.src_object_type, e.src_object_id";
        $sql_ecm .= " FROM ".MAIN_DB_PREFIX."ecm_files e";
        $sql_ecm .= " WHERE e.entity = ".$conf->entity;
        $sql_ecm .= " AND (";
        // Opción 1: Archivo directamente vinculado a la tarea
        $sql_ecm .= "   (e.src_object_type = 'project_task' AND e.src_object_id = ".((int) $task->id).")";
        // Opción 2: Archivo en el directorio del proyecto que contiene el taskref en el nombre
        $sql_ecm .= "   OR (e.filepath LIKE '%".addslashes($projectref)."%' AND e.filename LIKE '%".addslashes($taskref)."%')";
        // Opción 3: Archivos en ruta que contenga el ref de la tarea
        $sql_ecm .= "   OR (e.filepath LIKE '%".addslashes($taskref)."%')";
        $sql_ecm .= " )";
        $sql_ecm .= " ORDER BY e.date_c DESC";
        
        $resql_ecm = $db->query($sql_ecm);
        if ($resql_ecm) {
            while ($obj_ecm = $db->fetch_object($resql_ecm)) {
                // Construir ruta completa del archivo
                $full_file_path = $conf->projet->dir_output . '/' . $obj_ecm->filepath;
                if (!empty($obj_ecm->filename)) {
                    $full_file_path .= '/' . $obj_ecm->filename;
                }
                
                // Verificar que el archivo existe físicamente
                if (file_exists($full_file_path)) {
                    if (empty($upload_dir)) {
                        $upload_dir = dirname($full_file_path);
                    }
                    
                    $filearray[] = array(
                        'name' => $obj_ecm->filename,
                        'path' => $full_file_path,
                        'size' => filesize($full_file_path),
                        'date' => filemtime($full_file_path),
                        'ecm_id' => $obj_ecm->rowid,
                        'filepath' => $obj_ecm->filepath,
                        'label' => $obj_ecm->label,
                        'gen_or_uploaded' => $obj_ecm->gen_or_uploaded,
                        'date_c' => $obj_ecm->date_c,
                        'date_m' => $obj_ecm->date_m
                    );
                }
            }
            $db->free($resql_ecm);
        }
        
        // Si no encontramos archivos, buscar físicamente en posibles directorios
        if (empty($filearray)) {
            $possible_paths = array(
                $conf->projet->dir_output . '/' . $projectref . '/task/' . $taskref,
                $conf->projet->dir_output . '/' . $projectref . '/' . $taskref,
                $conf->projet->dir_output . '/' . $projectref,
            );
            
            foreach ($possible_paths as $path) {
                if (is_dir($path)) {
                    $files = dol_dir_list($path, "files", 0, '', '(\.meta|_preview.*\.png)$', 'date', SORT_DESC);
                    if (is_array($files) && count($files) > 0) {
                        // Filtrar archivos que contengan el taskref en el nombre
                        foreach ($files as $file) {
                            if (stripos($file['name'], $taskref) !== false || empty($filearray)) {
                                $filearray[] = $file;
                                if (empty($upload_dir)) {
                                    $upload_dir = $path;
                                }
                            }
                        }
                        if (!empty($filearray)) {
                            break;
                        }
                    }
                }
            }
        }
        
        if (empty($upload_dir)) {
            $upload_dir = $conf->projet->dir_output . '/' . $projectref . '/task/' . $taskref;
        }
        
        $result['upload_dir'] = $upload_dir;
        $result['dir_exists'] = is_dir($upload_dir);

        // Procesar archivos encontrados
        if (is_array($filearray) && count($filearray) > 0) {
            foreach ($filearray as $file) {
                $filename = $file['name'];
                $filesize = $file['size'];
                $filedate = $file['date'];
                
                // Información ECM si está disponible en el array
                $file_info = null;
                $filepath_from_db = isset($file['filepath']) ? $file['filepath'] : '';
                
                if (isset($file['ecm_id']) && $file['ecm_id'] > 0) {
                    // Ya tenemos info de ECM desde la consulta anterior
                    $file_info = array(
                        'file_id' => (int) $file['ecm_id'],
                        'label' => isset($file['label']) ? $file['label'] : '',
                        'gen_or_uploaded' => isset($file['gen_or_uploaded']) ? $file['gen_or_uploaded'] : 'uploaded',
                        'date_c' => isset($file['date_c']) && $file['date_c'] ? date('Y-m-d H:i:s', $file['date_c']) : null,
                        'date_m' => isset($file['date_m']) && $file['date_m'] ? date('Y-m-d H:i:s', $file['date_m']) : null
                    );
                } else {
                    // Buscar info adicional en llx_ecm_files
                    $sql_file = "SELECT rowid, filename, label, fullpath_orig, gen_or_uploaded,";
                    $sql_file .= " extraparams, date_c, date_m, position, cover, filepath";
                    $sql_file .= " FROM ".MAIN_DB_PREFIX."ecm_files";
                    $sql_file .= " WHERE filename = '".$db->escape($filename)."'";
                    $sql_file .= " AND entity = ".$conf->entity;
                    $sql_file .= " ORDER BY date_c DESC LIMIT 1";
                    
                    $resql_file = $db->query($sql_file);
                    if ($resql_file && $db->num_rows($resql_file) > 0) {
                        $obj_file = $db->fetch_object($resql_file);
                        $filepath_from_db = $obj_file->filepath;
                        $file_info = array(
                            'file_id' => (int) $obj_file->rowid,
                            'label' => $obj_file->label ?: '',
                            'gen_or_uploaded' => $obj_file->gen_or_uploaded ?: 'uploaded',
                            'date_c' => $obj_file->date_c ? date('Y-m-d H:i:s', $obj_file->date_c) : null,
                            'date_m' => $obj_file->date_m ? date('Y-m-d H:i:s', $obj_file->date_m) : null
                        );
                        $db->free($resql_file);
                    }
                }
                
                // Usar filepath de la BD si está disponible
                if (!empty($filepath_from_db)) {
                    $relativepath = $filepath_from_db . '/' . $filename;
                    $file_for_url = str_replace('projet/', '', $filepath_from_db) . '/' . $filename;
                } else {
                    // Construir ruta basada en la estructura esperada
                    $relativepath = 'projet/' . $projectref . '/' . $filename;
                    $file_for_url = $projectref . '/' . $filename;
                }
                
                $result['documents'][] = array(
                    'name' => $filename,
                    'size' => $filesize,
                    'date' => date('Y-m-d H:i:s', $filedate),
                    'type' => dol_mimetype($filename),
                    'relativepath' => $relativepath,
                    'download_url' => DOL_URL_ROOT . '/document.php?modulepart=project_task&file=' . urlencode($file_for_url),
                    'file_info' => $file_info
                );
            }
        }

        $result['total_documents'] = count($result['documents']);
        $result['timestamp'] = date('Y-m-d H:i:s');
        
        // Debug info (remover en producción)
        $result['debug'] = array(
            'taskref' => $taskref,
            'projectref' => $projectref,
            'searched_paths' => array(
                $conf->projet->dir_output . '/' . $projectref . '/task/' . $taskref,
                $conf->projet->dir_output . '/' . $projectref . '/' . $taskref,
                $conf->projet->dir_output . '/' . $projectref,
            ),
            'query_patterns' => array(
                'src_object' => 'src_object_type=project_task AND src_object_id=' . $task->id,
                'filepath_like' => "filepath LIKE '%$projectref%' AND filename LIKE '%$taskref%'",
                'taskref_in_path' => "filepath LIKE '%$taskref%'"
            )
        );

        return $result;
    }

    /**
     * Upload a document to a project task
     *
     * @param int $id ID of the task
     * @param array $request_data Request data with filename, filecontent (base64), and optional overwriteifexists
     * @return array Upload result
     *
     * @url POST /task/{id}/documents
     */
    public function uploadTaskDocument($id, $request_data = null)
    {
        global $db, $conf, $user;

        // Verificar permisos
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_project_perms = isset(DolibarrApiAccess::$user->rights->projet) && 
                            DolibarrApiAccess::$user->rights->projet->creer;
        
        if (!$has_module_perms && !$has_project_perms) {
            throw new RestException(401, 'Access denied: Need project write permissions or dolibarrmodernfrontend write permissions');
        }

        // Verificar que la tarea existe
        require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
        require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        
        $task = new Task($db);
        if ($task->fetch($id) <= 0) {
            throw new RestException(404, 'Task not found');
        }

        // Obtener información del proyecto asociado
        $project = new Project($db);
        if ($task->fk_project && $project->fetch($task->fk_project) > 0) {
            $projectref = dol_sanitizeFileName($project->ref);
        } else {
            throw new RestException(404, 'Project not found for this task');
        }

        // Verificar datos requeridos
        if (empty($request_data) || !isset($request_data['filename']) || !isset($request_data['filecontent'])) {
            throw new RestException(400, 'Missing required fields: filename and filecontent (base64 encoded)');
        }

        $filename = $request_data['filename'];
        $filecontent_base64 = $request_data['filecontent'];
        $overwriteifexists = isset($request_data['overwriteifexists']) ? $request_data['overwriteifexists'] : false;

        // Sanitizar nombre de archivo
        $filename = dol_sanitizeFileName($filename);
        
        if (empty($filename)) {
            throw new RestException(400, 'Invalid filename');
        }

        // Decodificar contenido base64
        $filecontent = base64_decode($filecontent_base64, true);
        
        if ($filecontent === false) {
            throw new RestException(400, 'Invalid base64 content');
        }

        // Usar el método nativo de Dolibarr para agregar archivos a tareas
        // Esto asegura que se guarde en la ubicación correcta y se vincule apropiadamente
        require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
        
        // Preparar archivo temporal
        $temp_file = tempnam(sys_get_temp_dir(), 'dol_task_upload_');
        if (file_put_contents($temp_file, $filecontent) === false) {
            throw new RestException(500, 'Error creating temporary file');
        }
        
        // Simular $_FILES para usar el método addFile de Dolibarr
        $file_info = array(
            'name' => $filename,
            'tmp_name' => $temp_file,
            'size' => strlen($filecontent),
            'error' => 0
        );
        
        // Usar el método nativo de Task para agregar el archivo
        // Esto garantiza que se siga la estructura y vinculación nativa de Dolibarr
        $taskref = dol_sanitizeFileName($task->ref);
        if (empty($taskref)) {
            $taskref = (string) $task->id;
        }
        
        // Construir ruta donde Dolibarr guarda archivos de tareas
        // Usar la misma estructura que usa Dolibarr en la interfaz web
        $upload_dir = $conf->projet->dir_output . '/' . $projectref;
        
        // Crear directorio base del proyecto si no existe
        if (!is_dir($upload_dir)) {
            if (!dol_mkdir($upload_dir, DOL_DATA_ROOT)) {
                @unlink($temp_file);
                throw new RestException(500, 'Error creating project directory: ' . $upload_dir);
            }
        }
        
        // Archivo de destino (Dolibarr guarda archivos de tareas en el directorio del proyecto)
        // con el nombre que incluye la referencia de la tarea
        $destfile = $upload_dir . '/' . $filename;
        
        // Verificar si el archivo ya existe
        if (file_exists($destfile) && !$overwriteifexists) {
            @unlink($temp_file);
            throw new RestException(409, 'File already exists. Use overwriteifexists=true to overwrite');
        }
        
        // Mover archivo desde temporal a destino
        if (!rename($temp_file, $destfile)) {
            $result_copy = copy($temp_file, $destfile);
            @unlink($temp_file);
            if (!$result_copy) {
                throw new RestException(500, 'Error saving file to destination');
            }
        }
        
        // Establecer permisos correctos
        @chmod($destfile, 0644);
        
        // Registrar archivo en llx_ecm_files con vinculación a la tarea
        $ecm_file_id = null;
        require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
        
        $ecmfile = new EcmFiles($db);
        $ecmfile->filepath = 'projet/' . $projectref;
        $ecmfile->filename = $filename;
        $ecmfile->label = isset($request_data['label']) ? $request_data['label'] : '';
        $ecmfile->fullpath_orig = $destfile;
        $ecmfile->gen_or_uploaded = 'uploaded';
        $ecmfile->description = isset($request_data['description']) ? $request_data['description'] : '';
        $ecmfile->entity = $conf->entity;
        
        // IMPORTANTE: Vincular el archivo a la tarea usando src_object
        $ecmfile->src_object_type = 'project_task';
        $ecmfile->src_object_id = $task->id;
        
        $result_ecm = $ecmfile->create($user);
        if ($result_ecm > 0) {
            $ecm_file_id = $result_ecm;
        } else {
            // Si falla el registro en ECM, aún así el archivo se guardó
            // Log el error pero no fallar la operación
            dol_syslog("Warning: Could not register file in ECM: " . $ecmfile->error, LOG_WARNING);
        }

        // Obtener información del archivo guardado
        $filesize = filesize($destfile);
        $filetype = dol_mimetype($filename);

        return array(
            'success' => true,
            'message' => 'File uploaded successfully',
            'task_id' => (int) $task->id,
            'task_ref' => $task->ref ?: '',
            'task_label' => $task->label ?: '',
            'project_id' => (int) $project->id,
            'project_ref' => $project->ref ?: '',
            'project_title' => $project->title ?: '',
            'file' => array(
                'name' => $filename,
                'size' => $filesize,
                'type' => $filetype,
                'relativepath' => 'projet/' . $projectref . '/' . $filename,
                'download_url' => DOL_URL_ROOT . '/document.php?modulepart=project_task&file=' . urlencode($projectref . '/' . $filename),
                'physical_path' => $destfile,
                'ecm_file_id' => $ecm_file_id,
                'linked_to_task' => true
            ),
            'timestamp' => date('Y-m-d H:i:s')
        );
    }

    /**
     * Get all documents from all tasks of a project
     *
     * @param int $id ID of the project
     * @return array Array of tasks with their documents
     *
     * @url GET /project/{id}/tasks/documents
     */
    public function getProjectTasksDocuments($id)
    {
        global $db, $conf;

        // Verificar permisos
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_project_perms = isset(DolibarrApiAccess::$user->rights->projet) && 
                            DolibarrApiAccess::$user->rights->projet->lire;
        
        if (!$has_module_perms && !$has_project_perms) {
            throw new RestException(401, 'Access denied: Need project read permissions or dolibarrmodernfrontend read permissions');
        }

        // Verificar que el proyecto existe
        require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
        require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        
        $project = new Project($db);
        if ($project->fetch($id) <= 0) {
            throw new RestException(404, 'Project not found');
        }

        $result = array(
            'project_id' => (int) $project->id,
            'project_ref' => $project->ref ?: '',
            'project_title' => $project->title ?: '',
            'tasks' => array()
        );

        // Obtener todas las tareas del proyecto
        $sql = "SELECT t.rowid, t.ref, t.label, t.fk_projet";
        $sql .= " FROM ".MAIN_DB_PREFIX."projet_task t";
        $sql .= " WHERE t.fk_projet = ".((int) $id);
        $sql .= " ORDER BY t.ref, t.label";

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            
            for ($i = 0; $i < $num; $i++) {
                $obj = $db->fetch_object($resql);
                
                $task_data = array(
                    'task_id' => (int) $obj->rowid,
                    'task_ref' => $obj->ref ?: '',
                    'task_label' => $obj->label ?: '',
                    'documents' => array()
                );
                
                // Obtener documentos de esta tarea
                $projectref = dol_sanitizeFileName($project->ref);
                $taskref = $obj->ref ? dol_sanitizeFileName($obj->ref) : (string) $obj->rowid;
                
                $upload_dir = $conf->projet->dir_output . '/' . $projectref . '/task/' . $taskref;
                
                if (is_dir($upload_dir)) {
                    $filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', 'date', SORT_DESC);
                    
                    if (is_array($filearray) && count($filearray) > 0) {
                        foreach ($filearray as $file) {
                            $relativepath = 'projet/' . $projectref . '/task/' . $taskref . '/' . $file['name'];
                            
                            $task_data['documents'][] = array(
                                'name' => $file['name'],
                                'size' => $file['size'],
                                'date' => date('Y-m-d H:i:s', $file['date']),
                                'type' => dol_mimetype($file['name']),
                                'relativepath' => $relativepath,
                                'download_url' => DOL_URL_ROOT . '/document.php?modulepart=project_task&file=' . urlencode($projectref . '/task/' . $taskref . '/' . $file['name'])
                            );
                        }
                    }
                }
                
                $task_data['total_documents'] = count($task_data['documents']);
                
                // Solo agregar tareas que tengan documentos (o todas si se desea)
                $result['tasks'][] = $task_data;
            }
            
            $db->free($resql);
        } else {
            throw new RestException(500, 'Error retrieving tasks: '.$db->lasterror());
        }

        $result['total_tasks'] = count($result['tasks']);
        $total_docs = 0;
        foreach ($result['tasks'] as $task) {
            $total_docs += $task['total_documents'];
        }
        $result['total_documents'] = $total_docs;
        $result['timestamp'] = date('Y-m-d H:i:s');

        return $result;
    }

    /**
     * Get contacts/resources assigned to a task
     *
     * @param int $id ID of the task
     * @return array Array of contacts assigned to the task
     *
     * @url GET /task/{id}/contacts
     */
    public function getTaskContacts($id)
    {
        global $db, $conf;

        // Verificar permisos
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_project_perms = isset(DolibarrApiAccess::$user->rights->projet) && 
                            DolibarrApiAccess::$user->rights->projet->lire;
        
        if (!$has_module_perms && !$has_project_perms) {
            throw new RestException(401, 'Access denied: Need project read permissions or dolibarrmodernfrontend read permissions');
        }

        // Verificar que la tarea existe
        require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
        $task = new Task($db);
        if ($task->fetch($id) <= 0) {
            throw new RestException(404, 'Task not found');
        }

        $contacts = array();

        // Consulta SQL para obtener contactos de la tarea desde element_contact
        $sql = "SELECT ec.rowid, ec.fk_socpeople as contact_id, ec.fk_c_type_contact, ec.statut,";
        $sql .= " tc.code as contact_type_code, tc.libelle as contact_type_label, tc.source as contact_source";
        $sql .= " FROM ".MAIN_DB_PREFIX."element_contact ec";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON tc.rowid = ec.fk_c_type_contact";
        $sql .= " WHERE ec.element_id = ".((int) $id);
        $sql .= " AND tc.element = 'project_task'";
        $sql .= " AND ec.fk_socpeople > 0";
        $sql .= " ORDER BY tc.code";

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            for ($i = 0; $i < $num; $i++) {
                $obj = $db->fetch_object($resql);
                
                // Detectar si es interno por source
                $is_internal = ($obj->contact_source == 'internal');
                
                $contact_data = array(
                    'contact_id' => (int) $obj->contact_id,
                    'element_contact_id' => (int) $obj->rowid,
                    'contact_type_code' => $obj->contact_type_code ?: '',
                    'contact_type_label' => $obj->contact_type_label ?: '',
                    'contact_source' => $obj->contact_source ?: 'external',
                    'status' => (int) $obj->statut
                );
                
                // Si es interno, obtener datos del usuario
                if ($is_internal) {
                    $sql_user = "SELECT rowid, lastname, firstname, email, office_phone, user_mobile FROM ".MAIN_DB_PREFIX."user";
                    $sql_user .= " WHERE rowid = ".((int) $obj->contact_id);
                    
                    $resql_user = $db->query($sql_user);
                    if ($resql_user && $db->num_rows($resql_user) > 0) {
                        $user_data = $db->fetch_object($resql_user);
                        
                        $contact_data['user_id'] = (int) $user_data->rowid;
                        $contact_data['lastname'] = $user_data->lastname ?: '';
                        $contact_data['firstname'] = $user_data->firstname ?: '';
                        $contact_data['fullname'] = trim(($user_data->firstname ?: '') . ' ' . ($user_data->lastname ?: ''));
                        $contact_data['email'] = $user_data->email ?: '';
                        $contact_data['phone'] = $user_data->office_phone ?: '';
                        $contact_data['phone_mobile'] = $user_data->user_mobile ?: '';
                        $contact_data['company_name'] = 'Internal User';
                        
                        $db->free($resql_user);
                    }
                } else {
                    // Si es externo, obtener datos del contacto
                    require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
                    $contact = new Contact($db);
                    if ($contact->fetch($obj->contact_id) > 0) {
                        $contact_data['lastname'] = $contact->lastname ?: '';
                        $contact_data['firstname'] = $contact->firstname ?: '';
                        $contact_data['fullname'] = trim(($contact->firstname ?: '') . ' ' . ($contact->lastname ?: ''));
                        $contact_data['email'] = $contact->email ?: '';
                        $contact_data['phone'] = $contact->phone_pro ?: '';
                        $contact_data['phone_mobile'] = $contact->phone_mobile ?: '';
                        $contact_data['company_id'] = $contact->fk_soc ?: null;
                        
                        // Obtener nombre de la empresa si existe
                        if ($contact->fk_soc) {
                            require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
                            $societe = new Societe($db);
                            if ($societe->fetch($contact->fk_soc) > 0) {
                                $contact_data['company_name'] = $societe->name ?: '';
                            }
                        }
                    }
                }
                
                $contacts[] = $contact_data;
            }
            $db->free($resql);
        } else {
            throw new RestException(500, 'Error retrieving contacts: '.$db->lasterror());
        }

        return array(
            'task_id' => $id,
            'task_ref' => $task->ref ?: '',
            'task_label' => $task->label ?: '',
            'contacts' => $contacts,
            'count' => count($contacts)
        );
    }

    /**
     * Assign a contact/user to a task with a specific role
     *
     * @param int $id ID of the task
     * @param array $request_data Request data with user_id and role
     * @return array Assignment result
     *
     * @url POST /task/{id}/assign
     */
    public function assignTaskContact($id, $request_data = null)
    {
        global $db, $conf;

        // Verificar permisos
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_project_perms = isset(DolibarrApiAccess::$user->rights->projet) && 
                            DolibarrApiAccess::$user->rights->projet->creer;
        
        if (!$has_module_perms && !$has_project_perms) {
            throw new RestException(401, 'Access denied: Need project write permissions or dolibarrmodernfrontend write permissions');
        }

        // Verificar que la tarea existe
        require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
        $task = new Task($db);
        if ($task->fetch($id) <= 0) {
            throw new RestException(404, 'Task not found');
        }

        // Verificar datos requeridos
        if (empty($request_data) || !isset($request_data['user_id']) || !isset($request_data['role'])) {
            throw new RestException(400, 'Missing required fields: user_id and role');
        }

        $user_id = (int) $request_data['user_id'];
        $role = $request_data['role'];

        // Validar que el usuario existe
        require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $user = new User($db);
        if ($user->fetch($user_id) <= 0) {
            throw new RestException(404, 'User not found');
        }

        // Obtener el ID del tipo de contacto para tareas
        // Los roles típicos en Dolibarr para tareas son: TASKEXECUTIVE, TASKMANAGER, etc.
        $contact_type_id = null;
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."c_type_contact";
        $sql .= " WHERE element = 'project_task' AND code = '".$db->escape($role)."'";
        $sql .= " AND source = 'internal'"; // Para usuarios internos
        
        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $obj = $db->fetch_object($resql);
            $contact_type_id = $obj->rowid;
            $db->free($resql);
        } else {
            throw new RestException(400, 'Invalid role: ' . $role . '. Valid roles for tasks are: TASKEXECUTIVE, TASKMANAGER');
        }

        // Si el rol es TASKEXECUTIVE, eliminar cualquier TASKEXECUTIVE existente antes de asignar el nuevo
        $removed_previous = null;
        if ($role === 'TASKEXECUTIVE') {
            $sql = "SELECT ec.rowid, ec.fk_socpeople FROM ".MAIN_DB_PREFIX."element_contact ec";
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON tc.rowid = ec.fk_c_type_contact";
            $sql .= " WHERE ec.element_id = ".((int) $id);
            $sql .= " AND tc.element = 'project_task'";
            $sql .= " AND tc.code = 'TASKEXECUTIVE'";
            $sql .= " AND tc.source = 'internal'";

            $resql = $db->query($sql);
            if ($resql && $db->num_rows($resql) > 0) {
                while ($obj = $db->fetch_object($resql)) {
                    // Solo eliminar si no es el mismo usuario que estamos asignando
                    if ($obj->fk_socpeople != $user_id) {
                        $result_delete = $task->delete_contact($obj->rowid);
                        if ($result_delete > 0) {
                            $removed_previous = array(
                                'user_id' => (int) $obj->fk_socpeople,
                                'element_contact_id' => (int) $obj->rowid
                            );
                        }
                    }
                }
                $db->free($resql);
            }
        }

        // Verificar si el usuario ya está asignado con este rol
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."element_contact";
        $sql .= " WHERE element_id = ".((int) $id);
        $sql .= " AND fk_socpeople = ".((int) $user_id);
        $sql .= " AND fk_c_type_contact = ".((int) $contact_type_id);

        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $db->free($resql);
            throw new RestException(409, 'User already assigned to this task with this role');
        }

        // Usar el método nativo de Dolibarr para agregar el contacto
        $result = $task->add_contact($user_id, $role, 'internal');
        
        if ($result < 0) {
            throw new RestException(500, 'Error assigning user to task: ' . $task->error);
        }

        // Obtener información del usuario asignado
        $user_info = array(
            'user_id' => $user_id,
            'contact_id' => $user_id,
            'lastname' => $user->lastname ?: '',
            'firstname' => $user->firstname ?: '',
            'fullname' => trim(($user->firstname ?: '') . ' ' . ($user->lastname ?: '')),
            'email' => $user->email ?: '',
            'phone' => $user->office_phone ?: '',
            'role' => $role,
            'contact_source' => 'internal'
        );

        $response = array(
            'success' => true,
            'message' => 'User assigned successfully to task',
            'task_id' => $id,
            'task_ref' => $task->ref ?: '',
            'task_label' => $task->label ?: '',
            'role' => $role,
            'user_info' => $user_info,
            'element_contact_id' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        );

        // Si se removió un usuario anterior, incluir esa información
        if ($removed_previous !== null) {
            $response['previous_user_removed'] = $removed_previous;
            $response['message'] = 'Previous TASKEXECUTIVE removed and new user assigned successfully';
        }

        return $response;
    }

    /**
     * Remove a contact/user from a task
     *
     * @param int $id ID of the task
     * @param int $contact_id ID of the contact to remove
     * @param string $contact_source Source of the contact (external or internal)
     * @return array Remove contact result
     *
     * @url DELETE /task/{id}/contacts/{contact_id}/{contact_source}
     */
    public function removeTaskContact($id, $contact_id, $contact_source = 'internal')
    {
        global $db, $conf;

        // Verificar permisos
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->write;
        $has_project_perms = isset(DolibarrApiAccess::$user->rights->projet) && 
                            DolibarrApiAccess::$user->rights->projet->creer;
        
        if (!$has_module_perms && !$has_project_perms) {
            throw new RestException(401, 'Access denied: Need project write permissions or dolibarrmodernfrontend write permissions');
        }

        // Verificar que la tarea existe
        require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
        $task = new Task($db);
        if ($task->fetch($id) <= 0) {
            throw new RestException(404, 'Task not found');
        }

        // Validar contact_source
        if (!in_array($contact_source, array('external', 'internal'))) {
            throw new RestException(400, 'contact_source must be either "external" or "internal"');
        }

        $contact_id = (int) $contact_id;

        // Verificar que el contacto está asociado a la tarea
        $sql = "SELECT ec.rowid, ec.fk_c_type_contact FROM ".MAIN_DB_PREFIX."element_contact ec";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON tc.rowid = ec.fk_c_type_contact";
        $sql .= " WHERE ec.element_id = ".((int) $id);
        $sql .= " AND ec.fk_socpeople = ".((int) $contact_id);
        $sql .= " AND tc.source = '".$db->escape($contact_source)."'";

        $resql = $db->query($sql);
        if (!$resql || $db->num_rows($resql) == 0) {
            throw new RestException(404, 'Contact not found in this task');
        }

        $obj = $db->fetch_object($resql);
        $element_contact_id = $obj->rowid;
        $contact_type_id = $obj->fk_c_type_contact;
        $db->free($resql);

        // Usar el método nativo de Dolibarr para eliminar el contacto
        $result = $task->delete_contact($element_contact_id);
        
        if ($result < 0) {
            throw new RestException(500, 'Error removing contact from task: ' . $task->error);
        }

        return array(
            'success' => true,
            'message' => 'Contact removed successfully from task',
            'task_id' => $id,
            'task_ref' => $task->ref ?: '',
            'task_label' => $task->label ?: '',
            'contact_id' => $contact_id,
            'contact_source' => $contact_source,
            'element_contact_id' => $element_contact_id,
            'timestamp' => date('Y-m-d H:i:s')
        );
    }

    /**
     * Get ID professional validator URLs by country
     * 
     * Returns the validation URLs for professional IDs (like SIREN, VAT, etc.) 
     * based on the country code. This endpoint provides the same URLs used by 
     * Dolibarr's native id_prof_url function.
     * 
     * By default, returns only the URLs for the company's country (from mysoc).
     * Use ?all=1 to get all countries, or ?country=XX to get a specific country.
     *
     * @param string $country Optional country code (FR, ES, GB, etc.)
     * @param int $all Optional flag to return all countries (0 or 1)
     * @return array Array with country codes and their validator URLs
     *
     * @url GET /idprofvalidatorurl
     */
    public function getIdProfValidatorUrl($country = '', $all = 0)
    {
        global $mysoc;
        
        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_societe_perms = isset(DolibarrApiAccess::$user->rights->societe) && 
                            DolibarrApiAccess::$user->rights->societe->lire;
        
        if (!$has_module_perms && !$has_societe_perms) {
            throw new RestException(401, 'Access denied: Need societe read permissions or dolibarrmodernfrontend read permissions');
        }

        // Array con las URLs de validación por país
        // Basado en la función id_prof_url de societe.class.php
        $all_validator_urls = array(
            'FR' => array(
                'country_code' => 'FR',
                'country_name' => 'France',
                'idprof1' => array(
                    'name' => 'SIREN',
                    'url_template' => 'https://annuaire-entreprises.data.gouv.fr/entreprise/{IDPROF}',
                    'description' => 'French company directory',
                    'placeholder' => '{IDPROF}'
                )
            ),
            'GB' => array(
                'country_code' => 'GB',
                'country_name' => 'United Kingdom',
                'idprof1' => array(
                    'name' => 'Company Number',
                    'url_template' => 'https://beta.companieshouse.gov.uk/company/{IDPROF}',
                    'description' => 'UK Companies House',
                    'placeholder' => '{IDPROF}'
                )
            ),
            'UK' => array(
                'country_code' => 'UK',
                'country_name' => 'United Kingdom',
                'idprof1' => array(
                    'name' => 'Company Number',
                    'url_template' => 'https://beta.companieshouse.gov.uk/company/{IDPROF}',
                    'description' => 'UK Companies House',
                    'placeholder' => '{IDPROF}'
                )
            ),
            'ES' => array(
                'country_code' => 'ES',
                'country_name' => 'Spain',
                'idprof1' => array(
                    'name' => 'NIF/CIF',
                    'url_template' => 'http://www.e-informa.es/servlet/app/portal/ENTP/screen/SProducto/prod/ETIQUETA_EMPRESA/nif/{IDPROF}',
                    'description' => 'Spanish company information',
                    'placeholder' => '{IDPROF}'
                )
            ),
            'IN' => array(
                'country_code' => 'IN',
                'country_name' => 'India',
                'idprof1' => array(
                    'name' => 'TIN',
                    'url_template' => 'http://www.tinxsys.com/TinxsysInternetWeb/dealerControllerServlet?tinNumber={IDPROF};&searchBy=TIN&backPage=searchByTin_Inter.jsp',
                    'description' => 'Indian Tax Information Network',
                    'placeholder' => '{IDPROF}'
                )
            ),
            'DZ' => array(
                'country_code' => 'DZ',
                'country_name' => 'Algeria',
                'idprof1' => array(
                    'name' => 'NIF',
                    'url_template' => 'http://nif.mfdgi.gov.dz/nif.asp?Nif={IDPROF}',
                    'description' => 'Algerian tax identification',
                    'placeholder' => '{IDPROF}'
                )
            ),
            'PT' => array(
                'country_code' => 'PT',
                'country_name' => 'Portugal',
                'idprof1' => array(
                    'name' => 'NIF',
                    'url_template' => 'http://www.nif.pt/{IDPROF}',
                    'description' => 'Portuguese tax identification',
                    'placeholder' => '{IDPROF}'
                )
            )
        );

        // Determinar qué país(es) devolver
        $validator_urls = array();
        $target_country = '';
        $filter_mode = 'company'; // company, specific, all
        
        if ($all == 1) {
            // Devolver todos los países
            $validator_urls = $all_validator_urls;
            $filter_mode = 'all';
        } elseif (!empty($country)) {
            // Devolver país específico solicitado
            $country = strtoupper($country);
            if (isset($all_validator_urls[$country])) {
                $validator_urls[$country] = $all_validator_urls[$country];
                $target_country = $country;
                $filter_mode = 'specific';
            } else {
                throw new RestException(404, 'Country code not found: ' . $country . '. Available countries: ' . implode(', ', array_keys($all_validator_urls)));
            }
        } else {
            // Por defecto: devolver solo el país de la empresa (mysoc)
            if (!empty($mysoc->country_code)) {
                $company_country = strtoupper($mysoc->country_code);
                if (isset($all_validator_urls[$company_country])) {
                    $validator_urls[$company_country] = $all_validator_urls[$company_country];
                    $target_country = $company_country;
                } else {
                    // Si el país de la empresa no tiene URLs de validación, devolver mensaje informativo
                    return array(
                        'success' => true,
                        'message' => 'No validator URLs available for your company country',
                        'company_country_code' => $company_country,
                        'company_country_name' => $mysoc->country,
                        'validator_urls' => array(),
                        'available_countries' => array_keys($all_validator_urls),
                        'hint' => 'Use ?all=1 to get all countries or ?country=XX to get a specific country'
                    );
                }
            } else {
                // Si no hay país configurado en mysoc, devolver todos
                $validator_urls = $all_validator_urls;
                $filter_mode = 'all';
                $target_country = 'none';
            }
        }

        // Construir respuesta
        $response = array(
            'success' => true,
            'message' => 'ID professional validator URLs retrieved successfully',
            'filter_mode' => $filter_mode,
            'countries_count' => count($validator_urls),
            'validator_urls' => $validator_urls,
            'usage' => array(
                'description' => 'Replace {IDPROF} in url_template with the actual professional ID number (without spaces)',
                'example' => 'For France SIREN 123456789: https://annuaire-entreprises.data.gouv.fr/entreprise/123456789'
            )
        );

        // Agregar información adicional según el modo
        if ($filter_mode == 'company') {
            $response['company_country_code'] = $mysoc->country_code;
            $response['company_country_name'] = $mysoc->country;
            $response['note'] = 'Showing only your company country. Use ?all=1 to get all countries.';
        } elseif ($filter_mode == 'specific') {
            $response['requested_country'] = $target_country;
        } elseif ($filter_mode == 'all') {
            if ($target_country == 'none') {
                $response['note'] = 'Company country not configured. Showing all available countries.';
            } else {
                $response['note'] = 'Showing all available countries as requested.';
            }
        }

        return $response;
    }

    /**
     * Get all email templates from Dolibarr
     *
     * @param string $type_template Optional filter by template type (e.g., 'thirdparty', 'invoice', 'order', 'ticket', etc.)
     * @param string $lang Optional filter by language code (e.g., 'es_ES', 'en_US', 'fr_FR')
     * @param int $enabled Optional filter by enabled status (0 or 1)
     * @param int $private Optional filter by private status (0=public, 1=private)
     * @return array Array of email templates with details
     *
     * @url GET /emailtemplates
     */
    public function getEmailTemplates($type_template = '', $lang = '', $enabled = -1, $private = -1)
    {
        global $db, $conf;

        // Verificar permisos: usar permisos nativos si no tiene permisos del módulo
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_admin_perms = DolibarrApiAccess::$user->admin;
        
        if (!$has_module_perms && !$has_admin_perms) {
            throw new RestException(401, 'Access denied: Need admin permissions or dolibarrmodernfrontend read permissions');
        }

        $templates = array();

        // Construir consulta SQL
        $sql = "SELECT t.rowid, t.entity, t.module, t.label, t.type_template, t.lang,";
        $sql .= " t.private, t.fk_user, t.datec, t.tms,";
        $sql .= " t.topic, t.joinfiles, t.content, t.content_lines,";
        $sql .= " t.enabled, t.active, t.position,";
        $sql .= " u.login as user_login, u.firstname as user_firstname, u.lastname as user_lastname";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_email_templates t";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid = t.fk_user";
        $sql .= " WHERE t.entity IN (0, ".$conf->entity.")";

        // Aplicar filtros opcionales
        if (!empty($type_template)) {
            $sql .= " AND t.type_template = '".$db->escape($type_template)."'";
        }

        if (!empty($lang)) {
            $sql .= " AND t.lang = '".$db->escape($lang)."'";
        }

        if ($enabled >= 0) {
            $sql .= " AND t.enabled = ".((int) $enabled);
        }

        if ($private >= 0) {
            $sql .= " AND t.private = ".((int) $private);
        }

        // Ordenar por posición y label
        $sql .= " ORDER BY t.position ASC, t.label ASC";

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            
            for ($i = 0; $i < $num; $i++) {
                $obj = $db->fetch_object($resql);
                
                // Construir información del usuario creador si existe
                $user_info = null;
                if ($obj->fk_user) {
                    $user_info = array(
                        'user_id' => (int) $obj->fk_user,
                        'login' => $obj->user_login ?: '',
                        'fullname' => trim(($obj->user_firstname ?: '') . ' ' . ($obj->user_lastname ?: ''))
                    );
                }

                // Procesar el contenido para extraer variables disponibles
                $topic = $obj->topic ?: '';
                $content = $obj->content ?: '';
                $content_lines = $obj->content_lines ?: '';
                
                // Buscar variables en el formato __VARIABLE__ (en topic, content y content_lines)
                $variables = array();
                if (preg_match_all('/__([A-Z_]+)__/', $topic . ' ' . $content . ' ' . $content_lines, $matches)) {
                    $variables = array_unique($matches[1]);
                    sort($variables);
                }

                $template_data = array(
                    'id' => (int) $obj->rowid,
                    'entity' => (int) $obj->entity,
                    'module' => $obj->module ?: '',
                    'label' => $obj->label ?: '',
                    'type_template' => $obj->type_template ?: '',
                    'lang' => $obj->lang ?: '',
                    'private' => (int) $obj->private,
                    'subject' => $obj->topic ?: '',
                    'content' => $content,
                    'content_lines' => $content_lines,
                    'joinfiles' => (int) $obj->joinfiles,
                    'enabled' => $obj->enabled ?: '1',
                    'active' => (int) $obj->active,
                    'position' => (int) $obj->position,
                    'date_created' => $obj->datec ? date('Y-m-d H:i:s', strtotime($obj->datec)) : null,
                    'date_modified' => $obj->tms ? date('Y-m-d H:i:s', strtotime($obj->tms)) : null,
                    'user_info' => $user_info,
                    'variables' => $variables,
                    'is_public' => ((int) $obj->private == 0),
                    'is_enabled' => ($obj->enabled == '1' || $obj->enabled == 1)
                );

                $templates[] = $template_data;
            }
            
            $db->free($resql);
        } else {
            throw new RestException(500, 'Error retrieving email templates: '.$db->lasterror());
        }

        // Obtener tipos de plantillas disponibles para información adicional
        $available_types = array();
        $sql_types = "SELECT DISTINCT type_template FROM ".MAIN_DB_PREFIX."c_email_templates";
        $sql_types .= " WHERE entity IN (0, ".$conf->entity.")";
        $sql_types .= " AND type_template IS NOT NULL AND type_template != ''";
        $sql_types .= " ORDER BY type_template ASC";
        
        $resql_types = $db->query($sql_types);
        if ($resql_types) {
            while ($obj_type = $db->fetch_object($resql_types)) {
                $available_types[] = $obj_type->type_template;
            }
            $db->free($resql_types);
        }

        // Obtener idiomas disponibles
        $available_langs = array();
        $sql_langs = "SELECT DISTINCT lang FROM ".MAIN_DB_PREFIX."c_email_templates";
        $sql_langs .= " WHERE entity IN (0, ".$conf->entity.")";
        $sql_langs .= " AND lang IS NOT NULL AND lang != ''";
        $sql_langs .= " ORDER BY lang ASC";
        
        $resql_langs = $db->query($sql_langs);
        if ($resql_langs) {
            while ($obj_lang = $db->fetch_object($resql_langs)) {
                $available_langs[] = $obj_lang->lang;
            }
            $db->free($resql_langs);
        }

        return array(
            'success' => true,
            'message' => 'Email templates retrieved successfully',
            'filters_applied' => array(
                'type_template' => $type_template ?: 'all',
                'lang' => $lang ?: 'all',
                'enabled' => $enabled >= 0 ? $enabled : 'all',
                'private' => $private >= 0 ? $private : 'all'
            ),
            'templates' => $templates,
            'total_count' => count($templates),
            'available_types' => $available_types,
            'available_langs' => $available_langs,
            'timestamp' => date('Y-m-d H:i:s'),
            'usage_info' => array(
                'description' => 'Email templates can be filtered by type, language, enabled status, and privacy',
                'filter_examples' => array(
                    'by_type' => '/api/index.php/dolibarrmodernfrontend/emailtemplates?type_template=ticket',
                    'by_lang' => '/api/index.php/dolibarrmodernfrontend/emailtemplates?lang=es_ES',
                    'enabled_only' => '/api/index.php/dolibarrmodernfrontend/emailtemplates?enabled=1',
                    'public_only' => '/api/index.php/dolibarrmodernfrontend/emailtemplates?private=0',
                    'combined' => '/api/index.php/dolibarrmodernfrontend/emailtemplates?type_template=ticket&lang=es_ES&enabled=1'
                ),
                'variables_info' => 'The "variables" field lists all template variables found in the format __VARIABLE__'
            )
        );
    }

    /**
     * Get all available substitution variables for email templates
     *
     * Returns all substitution variables available in Dolibarr for use in email templates.
     * These variables can be used in email templates and will be replaced with actual values.
     *
     * @param string $context Optional context filter (e.g., 'ticket', 'invoice', 'thirdparty', 'user', 'mycompany', 'global')
     * @return array Array of substitution variables with their descriptions and current values
     *
     * @url GET /substitutionvariables
     */
    public function getSubstitutionVariables($context = '')
    {
        global $db, $conf, $mysoc, $langs;

        // Verificar permisos
        $has_module_perms = isset(DolibarrApiAccess::$user->rights->dolibarrmodernfrontend) && 
                           DolibarrApiAccess::$user->rights->dolibarrmodernfrontend->read;
        $has_admin_perms = DolibarrApiAccess::$user->admin;
        
        if (!$has_module_perms && !$has_admin_perms) {
            throw new RestException(401, 'Access denied: Need admin permissions or dolibarrmodernfrontend read permissions');
        }

        require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
        require_once DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';

        $user = DolibarrApiAccess::$user;
        $variables = array();

        // Variables del usuario actual
        $user_vars = array(
            '__USER_ID__' => array(
                'value' => $user->id,
                'description' => 'ID of current user',
                'category' => 'user'
            ),
            '__USER_LOGIN__' => array(
                'value' => $user->login,
                'description' => 'Login of current user',
                'category' => 'user'
            ),
            '__USER_EMAIL__' => array(
                'value' => $user->email ?: '__USER_EMAIL__',
                'description' => 'Email of current user',
                'category' => 'user'
            ),
            '__USER_PHONE__' => array(
                'value' => $user->office_phone ?: '__USER_PHONE__',
                'description' => 'Office phone of current user',
                'category' => 'user'
            ),
            '__USER_PHONEPRO__' => array(
                'value' => $user->office_phone ?: '__USER_PHONEPRO__',
                'description' => 'Professional phone of current user',
                'category' => 'user'
            ),
            '__USER_PHONEMOBILE__' => array(
                'value' => $user->user_mobile ?: '__USER_PHONEMOBILE__',
                'description' => 'Mobile phone of current user',
                'category' => 'user'
            ),
            '__USER_FAX__' => array(
                'value' => $user->office_fax ?: '__USER_FAX__',
                'description' => 'Fax of current user',
                'category' => 'user'
            ),
            '__USER_LASTNAME__' => array(
                'value' => $user->lastname,
                'description' => 'Last name of current user',
                'category' => 'user'
            ),
            '__USER_FIRSTNAME__' => array(
                'value' => $user->firstname,
                'description' => 'First name of current user',
                'category' => 'user'
            ),
            '__USER_FULLNAME__' => array(
                'value' => $user->getFullName($langs),
                'description' => 'Full name of current user',
                'category' => 'user'
            ),
            '__USER_SUPERVISOR_ID__' => array(
                'value' => $user->fk_user ?: '__USER_SUPERVISOR_ID__',
                'description' => 'ID of user supervisor',
                'category' => 'user'
            ),
            '__USER_JOB__' => array(
                'value' => $user->job ?: '__USER_JOB__',
                'description' => 'Job title of current user',
                'category' => 'user'
            ),
            '__USER_SIGNATURE__' => array(
                'value' => $user->signature ?: '__USER_SIGNATURE__',
                'description' => 'Signature of current user',
                'category' => 'user'
            ),
            '__USER_REMOTE_IP__' => array(
                'value' => getUserRemoteIP(),
                'description' => 'Remote IP of current user',
                'category' => 'user'
            )
        );

        // Variables de la empresa
        if (!is_object($mysoc)) {
            $mysoc = new stdClass();
        }
        
        $mycompany_vars = array(
            '__MYCOMPANY_NAME__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_NOM ?: '__MYCOMPANY_NAME__',
                'description' => 'Company name',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_EMAIL__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_MAIL ?: '__MYCOMPANY_EMAIL__',
                'description' => 'Company email',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_URL__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_WEB ?: '__MYCOMPANY_URL__',
                'description' => 'Company website URL',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_PHONE__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_TEL ?: '__MYCOMPANY_PHONE__',
                'description' => 'Company phone',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_FAX__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_FAX ?: '__MYCOMPANY_FAX__',
                'description' => 'Company fax',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_ADDRESS__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_ADDRESS ?: '__MYCOMPANY_ADDRESS__',
                'description' => 'Company address',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_ZIP__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_ZIP ?: '__MYCOMPANY_ZIP__',
                'description' => 'Company zip code',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_TOWN__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_TOWN ?: '__MYCOMPANY_TOWN__',
                'description' => 'Company town/city',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_COUNTRY__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_COUNTRY ?: '__MYCOMPANY_COUNTRY__',
                'description' => 'Company country name',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_COUNTRY_ID__' => array(
                'value' => $conf->global->MAIN_INFO_SOCIETE_COUNTRY ?: '__MYCOMPANY_COUNTRY_ID__',
                'description' => 'Company country ID',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_COUNTRY_CODE__' => array(
                'value' => getCountry($conf->global->MAIN_INFO_SOCIETE_COUNTRY, 3) ?: '__MYCOMPANY_COUNTRY_CODE__',
                'description' => 'Company country code (ISO)',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_CURRENCY_CODE__' => array(
                'value' => $conf->currency ?: '__MYCOMPANY_CURRENCY_CODE__',
                'description' => 'Company currency code',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_CAPITAL__' => array(
                'value' => $conf->global->MAIN_INFO_CAPITAL ?: '__MYCOMPANY_CAPITAL__',
                'description' => 'Company capital',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_PROFID1__' => array(
                'value' => $conf->global->MAIN_INFO_SIREN ?: '__MYCOMPANY_PROFID1__',
                'description' => 'Company professional ID 1 (SIREN, VAT, etc.)',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_PROFID2__' => array(
                'value' => $conf->global->MAIN_INFO_SIRET ?: '__MYCOMPANY_PROFID2__',
                'description' => 'Company professional ID 2',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_PROFID3__' => array(
                'value' => $conf->global->MAIN_INFO_APE ?: '__MYCOMPANY_PROFID3__',
                'description' => 'Company professional ID 3',
                'category' => 'mycompany'
            ),
            '__MYCOMPANY_PROFID4__' => array(
                'value' => $conf->global->MAIN_INFO_RCS ?: '__MYCOMPANY_PROFID4__',
                'description' => 'Company professional ID 4',
                'category' => 'mycompany'
            )
        );

        // Variables genéricas de objeto
        $object_vars = array(
            '__ID__' => array(
                'value' => '__ID__',
                'description' => 'ID of the object',
                'category' => 'object'
            ),
            '__REF__' => array(
                'value' => '__REF__',
                'description' => 'Reference of the object',
                'category' => 'object'
            ),
            '__NEWREF__' => array(
                'value' => '__NEWREF__',
                'description' => 'New reference of the object',
                'category' => 'object'
            ),
            '__LABEL__' => array(
                'value' => '__LABEL__',
                'description' => 'Label of the object',
                'category' => 'object'
            ),
            '__REF_CLIENT__' => array(
                'value' => '__REF_CLIENT__',
                'description' => 'Customer reference',
                'category' => 'object'
            ),
            '__REF_SUPPLIER__' => array(
                'value' => '__REF_SUPPLIER__',
                'description' => 'Supplier reference',
                'category' => 'object'
            ),
            '__NOTE_PUBLIC__' => array(
                'value' => '__NOTE_PUBLIC__',
                'description' => 'Public note',
                'category' => 'object'
            ),
            '__NOTE_PRIVATE__' => array(
                'value' => '__NOTE_PRIVATE__',
                'description' => 'Private note',
                'category' => 'object'
            )
        );

        // Variables de tercero (thirdparty)
        $thirdparty_vars = array(
            '__THIRDPARTY_ID__' => array(
                'value' => '__THIRDPARTY_ID__',
                'description' => 'Third party ID',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_NAME__' => array(
                'value' => '__THIRDPARTY_NAME__',
                'description' => 'Third party name',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_NAME_ALIAS__' => array(
                'value' => '__THIRDPARTY_NAME_ALIAS__',
                'description' => 'Third party name alias',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_CODE_CLIENT__' => array(
                'value' => '__THIRDPARTY_CODE_CLIENT__',
                'description' => 'Third party customer code',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_CODE_FOURNISSEUR__' => array(
                'value' => '__THIRDPARTY_CODE_FOURNISSEUR__',
                'description' => 'Third party supplier code',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_EMAIL__' => array(
                'value' => '__THIRDPARTY_EMAIL__',
                'description' => 'Third party email',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_PHONE__' => array(
                'value' => '__THIRDPARTY_PHONE__',
                'description' => 'Third party phone',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_FAX__' => array(
                'value' => '__THIRDPARTY_FAX__',
                'description' => 'Third party fax',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_ADDRESS__' => array(
                'value' => '__THIRDPARTY_ADDRESS__',
                'description' => 'Third party address',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_ZIP__' => array(
                'value' => '__THIRDPARTY_ZIP__',
                'description' => 'Third party zip code',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_TOWN__' => array(
                'value' => '__THIRDPARTY_TOWN__',
                'description' => 'Third party town/city',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_STATE__' => array(
                'value' => '__THIRDPARTY_STATE__',
                'description' => 'Third party state/province',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_COUNTRY__' => array(
                'value' => '__THIRDPARTY_COUNTRY__',
                'description' => 'Third party country',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_TVAINTRA__' => array(
                'value' => '__THIRDPARTY_TVAINTRA__',
                'description' => 'Third party VAT number',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_NOTE_PUBLIC__' => array(
                'value' => '__THIRDPARTY_NOTE_PUBLIC__',
                'description' => 'Third party public note',
                'category' => 'thirdparty'
            ),
            '__THIRDPARTY_NOTE_PRIVATE__' => array(
                'value' => '__THIRDPARTY_NOTE_PRIVATE__',
                'description' => 'Third party private note',
                'category' => 'thirdparty'
            )
        );

        // Variables de ticket
        $ticket_vars = array(
            '__TICKET_TRACKID__' => array(
                'value' => '__TICKET_TRACKID__',
                'description' => 'Ticket tracking ID',
                'category' => 'ticket'
            ),
            '__TICKET_SUBJECT__' => array(
                'value' => '__TICKET_SUBJECT__',
                'description' => 'Ticket subject',
                'category' => 'ticket'
            ),
            '__TICKET_TYPE__' => array(
                'value' => '__TICKET_TYPE__',
                'description' => 'Ticket type',
                'category' => 'ticket'
            ),
            '__TICKET_SEVERITY__' => array(
                'value' => '__TICKET_SEVERITY__',
                'description' => 'Ticket severity',
                'category' => 'ticket'
            ),
            '__TICKET_CATEGORY__' => array(
                'value' => '__TICKET_CATEGORY__',
                'description' => 'Ticket category',
                'category' => 'ticket'
            ),
            '__TICKET_MESSAGE__' => array(
                'value' => '__TICKET_MESSAGE__',
                'description' => 'Ticket message',
                'category' => 'ticket'
            ),
            '__TICKET_PROGRESSION__' => array(
                'value' => '__TICKET_PROGRESSION__',
                'description' => 'Ticket progression percentage',
                'category' => 'ticket'
            ),
            '__TICKET_USER_ASSIGN__' => array(
                'value' => '__TICKET_USER_ASSIGN__',
                'description' => 'Ticket assigned user',
                'category' => 'ticket'
            )
        );

        // Variables de proyecto
        $project_vars = array(
            '__PROJECT_ID__' => array(
                'value' => '__PROJECT_ID__',
                'description' => 'Project ID',
                'category' => 'project'
            ),
            '__PROJECT_REF__' => array(
                'value' => '__PROJECT_REF__',
                'description' => 'Project reference',
                'category' => 'project'
            ),
            '__PROJECT_NAME__' => array(
                'value' => '__PROJECT_NAME__',
                'description' => 'Project name',
                'category' => 'project'
            )
        );

        // Variables de fecha/hora
        $date_vars = array(
            '__NOW_TMS__' => array(
                'value' => dol_now(),
                'description' => 'Current timestamp',
                'category' => 'datetime'
            ),
            '__NOW_TMS_YMD__' => array(
                'value' => dol_print_date(dol_now(), 'day'),
                'description' => 'Current date formatted',
                'category' => 'datetime'
            ),
            '__DAY__' => array(
                'value' => dol_print_date(dol_now(), '%d'),
                'description' => 'Current day number',
                'category' => 'datetime'
            ),
            '__DAY_TEXT__' => array(
                'value' => dol_print_date(dol_now(), '%A'),
                'description' => 'Current day name (full)',
                'category' => 'datetime'
            ),
            '__DAY_TEXT_SHORT__' => array(
                'value' => dol_print_date(dol_now(), '%a'),
                'description' => 'Current day name (short)',
                'category' => 'datetime'
            ),
            '__MONTH__' => array(
                'value' => dol_print_date(dol_now(), '%m'),
                'description' => 'Current month number',
                'category' => 'datetime'
            ),
            '__MONTH_TEXT__' => array(
                'value' => dol_print_date(dol_now(), '%B'),
                'description' => 'Current month name (full)',
                'category' => 'datetime'
            ),
            '__MONTH_TEXT_SHORT__' => array(
                'value' => dol_print_date(dol_now(), '%b'),
                'description' => 'Current month name (short)',
                'category' => 'datetime'
            ),
            '__YEAR__' => array(
                'value' => dol_print_date(dol_now(), '%Y'),
                'description' => 'Current year',
                'category' => 'datetime'
            ),
            '__PREVIOUS_DAY__' => array(
                'value' => dol_print_date(dol_now() - 86400, '%d'),
                'description' => 'Previous day number',
                'category' => 'datetime'
            ),
            '__PREVIOUS_MONTH__' => array(
                'value' => dol_print_date(dol_time_plus_duree(dol_now(), -1, 'm'), '%m'),
                'description' => 'Previous month number',
                'category' => 'datetime'
            ),
            '__PREVIOUS_YEAR__' => array(
                'value' => dol_print_date(dol_now(), '%Y') - 1,
                'description' => 'Previous year',
                'category' => 'datetime'
            ),
            '__NEXT_DAY__' => array(
                'value' => dol_print_date(dol_now() + 86400, '%d'),
                'description' => 'Next day number',
                'category' => 'datetime'
            ),
            '__NEXT_MONTH__' => array(
                'value' => dol_print_date(dol_time_plus_duree(dol_now(), 1, 'm'), '%m'),
                'description' => 'Next month number',
                'category' => 'datetime'
            ),
            '__NEXT_YEAR__' => array(
                'value' => dol_print_date(dol_now(), '%Y') + 1,
                'description' => 'Next year',
                'category' => 'datetime'
            )
        );

        // Variables globales
        $global_vars = array(
            '__DOL_MAIN_URL_ROOT__' => array(
                'value' => DOL_MAIN_URL_ROOT,
                'description' => 'Dolibarr main URL root',
                'category' => 'global'
            )
        );

        // Combinar todas las variables según el contexto
        if (empty($context) || $context == 'all') {
            $variables = array_merge($user_vars, $mycompany_vars, $object_vars, $thirdparty_vars, 
                                    $ticket_vars, $project_vars, $date_vars, $global_vars);
        } else {
            switch ($context) {
                case 'user':
                    $variables = $user_vars;
                    break;
                case 'mycompany':
                    $variables = $mycompany_vars;
                    break;
                case 'object':
                    $variables = $object_vars;
                    break;
                case 'thirdparty':
                    $variables = $thirdparty_vars;
                    break;
                case 'ticket':
                    $variables = $ticket_vars;
                    break;
                case 'project':
                    $variables = $project_vars;
                    break;
                case 'datetime':
                    $variables = $date_vars;
                    break;
                case 'global':
                    $variables = $global_vars;
                    break;
                default:
                    $variables = array_merge($user_vars, $mycompany_vars, $object_vars, $thirdparty_vars, 
                                            $ticket_vars, $project_vars, $date_vars, $global_vars);
            }
        }

        // Agrupar por categoría
        $grouped_variables = array();
        foreach ($variables as $key => $data) {
            $category = $data['category'];
            if (!isset($grouped_variables[$category])) {
                $grouped_variables[$category] = array();
            }
            $grouped_variables[$category][$key] = array(
                'value' => $data['value'],
                'description' => $data['description']
            );
        }

        return array(
            'success' => true,
            'message' => 'Substitution variables retrieved successfully',
            'context_filter' => $context ?: 'all',
            'total_variables' => count($variables),
            'variables' => $variables,
            'variables_grouped' => $grouped_variables,
            'available_contexts' => array('all', 'user', 'mycompany', 'object', 'thirdparty', 'ticket', 'project', 'datetime', 'global'),
            'timestamp' => date('Y-m-d H:i:s'),
            'usage_info' => array(
                'description' => 'Substitution variables can be filtered by context',
                'examples' => array(
                    'all' => '/api/index.php/dolibarrmodernfrontend/substitutionvariables',
                    'user_only' => '/api/index.php/dolibarrmodernfrontend/substitutionvariables?context=user',
                    'ticket_only' => '/api/index.php/dolibarrmodernfrontend/substitutionvariables?context=ticket',
                    'mycompany_only' => '/api/index.php/dolibarrmodernfrontend/substitutionvariables?context=mycompany'
                ),
                'note' => 'Variables with __ prefix and suffix are placeholders that will be replaced with actual values when used in templates'
            )
        );
    }

    /**
     * Add a new message to a ticket with custom contact
     * 
     * Allows creating a message in a ticket specifying which contact created it.
     * This is useful for API integrations where you want to attribute the message
     * to a specific contact (e.g., contact ID 115) related to the ticket's company.
     * 
     * @param int    $ticket_id       ID of the ticket
     * @param string $message         Message content (required)
     * @param int    $contact_id      ID of the contact who creates the message (optional, defaults to API user)
     * @param int    $private         Whether the message is private (0=public, 1=private, default=0)
     * @param int    $send_email      Whether to send email notification (0=no, 1=yes, default=0)
     * 
     * @url POST tickets/{ticket_id}/newmessage
     * 
     * @return array
     * @throws RestException
     */
    public function postTicketNewMessage($ticket_id, $message, $contact_id = 0, $private = 0, $send_email = 0)
    {
        global $db, $user, $conf;
        
        // Log inicial
        error_log("DEBUG: postTicketNewMessage START - ticket_id=$ticket_id, contact_id=$contact_id, message=$message");
        
        try {
            error_log("DEBUG: Step 1 - Starting try block");
            
            // Verificar permisos de escritura - simplificado
            error_log("DEBUG: Step 2 - Checking permissions");
            if (!isset(DolibarrApiAccess::$user->rights->ticket)) {
                throw new RestException(401, 'Access denied: No ticket permissions');
            }
            
            $has_ticket_write = !empty(DolibarrApiAccess::$user->rights->ticket->write);
            error_log("DEBUG: Step 3 - Has ticket write: " . ($has_ticket_write ? 'YES' : 'NO'));
            
            if (!$has_ticket_write) {
                throw new RestException(401, 'Access denied: Need ticket write permissions');
            }

            // Validar parámetros requeridos
            error_log("DEBUG: Step 4 - Validating parameters");
            if (empty($ticket_id)) {
                throw new RestException(400, 'Missing required parameter: ticket_id');
            }
            if (empty($message)) {
                throw new RestException(400, 'Missing required parameter: message');
            }

            // Cargar el ticket
            error_log("DEBUG: Step 5 - Loading ticket");
            require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
            $ticket = new Ticket($db);
            $result = $ticket->fetch($ticket_id);
            error_log("DEBUG: Step 6 - Ticket fetch result: $result");
            
            if ($result <= 0) {
                throw new RestException(404, 'Ticket not found: ' . $ticket_id);
            }

            // Determinar el usuario/contacto que crea el mensaje
            error_log("DEBUG: Step 7 - Setting up message user");
            $message_user = DolibarrApiAccess::$user; // Siempre usar el usuario de la API para newMessage()
            $contact = null;
            $contact_name = '';
            
            if ($contact_id > 0) {
                error_log("DEBUG: Step 8 - Loading contact $contact_id");
                // Cargar el contacto especificado para obtener su información
                require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
                
                $contact = new Contact($db);
                $result = $contact->fetch($contact_id);
                error_log("DEBUG: Step 9 - Contact fetch result: $result");
                
                if ($result <= 0) {
                    throw new RestException(404, 'Contact not found: ' . $contact_id);
                }
                
                // Construir el nombre del contacto
                $contact_name = trim(($contact->firstname ? $contact->firstname . ' ' : '') . ($contact->lastname ? $contact->lastname : ''));
                if (empty($contact_name)) {
                    $contact_name = $contact->email ? $contact->email : 'Contact #' . $contact_id;
                }
                error_log("DEBUG: Step 10 - Contact name: $contact_name");
            }

            // Usar siempre el subject del ticket para mantener consistencia
            error_log("DEBUG: Step 11 - Setting up POST variables");
            $subject = $ticket->subject;
            
            // Debug: verificar estado del ticket
            error_log("DEBUG: Ticket info - ID: {$ticket->id}, Ref: {$ticket->ref}, fk_soc: {$ticket->fk_soc}, Status: {$ticket->fk_statut}");

            // Usar inserción directa en la base de datos para evitar problemas con validaciones
            error_log("DEBUG: Step 12 - Creating message directly in database");
            
            // Insertar el mensaje directamente en la tabla de eventos
            $now = dol_now();
            
            // Generar una referencia simple basada en el ID del ticket y timestamp
            $ref = $ticket->id . '_' . $now;
            
            // Determinar quién crea el mensaje
            // Si hay contact_id, dejamos los campos de usuario vacíos (como sistema público)
            // Si no hay contact_id, usamos el usuario de la API
            $fk_user_author = ($contact_id > 0) ? "NULL" : $message_user->id;
            $fk_user_action = ($contact_id > 0) ? "NULL" : $message_user->id;
            
            // Si hay contacto, agregar su nombre al label del mensaje
            $label_prefix = '';
            if ($contact_id > 0 && !empty($contact_name)) {
                $label_prefix = 'By ' . $contact_name . ' - ';
            }
            
            // Si hay contacto, usar su email como identificador
            $email_from = '';
            if ($contact_id > 0 && $contact) {
                $email_from = $contact->email;
            }
            
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (";
            $sql .= "ref, datep, datep2, fk_action, code, label, note, fk_element, elementtype,";
            $sql .= "fk_user_author, fk_user_action, email_from, datec, percent";
            $sql .= ") VALUES (";
            $sql .= "'".$db->escape($ref)."',";
            $sql .= "'".$db->idate($now)."',";
            $sql .= "'".$db->idate($now)."',";
            $sql .= "NULL,"; // fk_action
            $sql .= "'TICKET_MSG',"; // code
            $sql .= "'".$db->escape($label_prefix . ($private ? '(Private) ' : '') . 'Message')."',"; // label
            $sql .= "'".$db->escape($message)."',"; // note
            $sql .= $ticket->id.","; // fk_element
            $sql .= "'ticket',"; // elementtype
            $sql .= $fk_user_author.","; // fk_user_author (NULL si hay contacto)
            $sql .= $fk_user_action.","; // fk_user_action (NULL si hay contacto)
            $sql .= ($email_from ? "'".$db->escape($email_from)."'" : "NULL").","; // email_from
            $sql .= "'".$db->idate($now)."',"; // datec
            $sql .= ($private ? "-1" : "100"); // percent (-1 = private, 100 = done)
            $sql .= ")";
            
            error_log("DEBUG: SQL: $sql");
            $resql = $db->query($sql);
            
            if ($resql) {
                $result = $db->last_insert_id(MAIN_DB_PREFIX."actioncomm");
                error_log("DEBUG: Step 13 - Message created with ID: $result");
            } else {
                $result = -1;
                error_log("DEBUG: Step 13 - FAILED to create message: " . $db->lasterror());
            }

            // Limpiar variables POST
            unset($_POST['subject'], $_POST['message'], $_POST['send_email'], $_POST['private_message']);

            error_log("DEBUG: Step 14 - Checking result");
            if ($result > 0) {
                error_log("DEBUG: Step 15 - Success, building response");
                $response = array(
                    'success' => true,
                    'message' => 'Message added successfully to ticket',
                    'ticket_id' => $ticket_id,
                    'ticket_ref' => $ticket->ref,
                    'message_id' => $result,
                    'subject' => $subject,
                    'message_content' => $message,
                    'private' => (bool)$private,
                    'send_email' => (bool)$send_email,
                    'api_user_id' => $message_user->id,
                    'api_user_login' => $message_user->login,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'method' => 'native_dolibarr_newMessage'
                );
                
                // Agregar información del contacto si se especificó
                if ($contact_id > 0 && $contact) {
                    $response['attributed_to_contact'] = array(
                        'contact_id' => $contact_id,
                        'contact_name' => $contact_name,
                        'contact_email' => $contact->email,
                        'contact_phone' => $contact->phone_pro ? $contact->phone_pro : $contact->phone_mobile
                    );
                }
                
                return $response;
            } else {
                error_log("DEBUG: Step 16 - FAILED - Error: " . $ticket->error . " | Errors: " . json_encode($ticket->errors));
                $error_msg = 'Failed to create message';
                if (!empty($ticket->error)) {
                    $error_msg .= ': ' . $ticket->error;
                }
                if (!empty($ticket->errors)) {
                    $error_msg .= ' | ' . implode(', ', $ticket->errors);
                }
                throw new RestException(500, $error_msg);
            }
        } catch (RestException $e) {
            // Re-lanzar excepciones REST
            throw $e;
        } catch (Exception $e) {
            // Capturar cualquier otra excepción y dar detalles
            dol_syslog("Error in postTicketNewMessage: " . $e->getMessage(), LOG_ERR);
            throw new RestException(500, 'Internal error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
        }
    }

    /**
     * Get messages from a ticket
     * 
     * Returns all messages associated with a ticket, including information about who created each message.
     * If the message was created by a contact (via email_from), it will show the contact's information.
     * If it was created by a user, it will show the user's information.
     * 
     * @param int $ticket_id ID of the ticket
     * 
     * @url GET tickets/{ticket_id}/messages
     * 
     * @return array
     * @throws RestException
     */
    public function getTicketMessages($ticket_id)
    {
        global $db;
        
        try {
            // Verificar permisos de lectura
            if (!isset(DolibarrApiAccess::$user->rights->ticket)) {
                throw new RestException(401, 'Access denied: No ticket permissions');
            }
            
            $has_ticket_read = !empty(DolibarrApiAccess::$user->rights->ticket->read);
            
            if (!$has_ticket_read) {
                throw new RestException(401, 'Access denied: Need ticket read permissions');
            }

            // Validar parámetro requerido
            if (empty($ticket_id)) {
                throw new RestException(400, 'Missing required parameter: ticket_id');
            }

            // Cargar el ticket para verificar que existe
            require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
            $ticket = new Ticket($db);
            $result = $ticket->fetch($ticket_id);
            
            if ($result <= 0) {
                throw new RestException(404, 'Ticket not found: ' . $ticket_id);
            }

            // Obtener los mensajes del ticket desde la tabla actioncomm
            $sql = "SELECT a.id, a.ref, a.datec, a.datep, a.label, a.note as message,";
            $sql .= " a.fk_user_author, a.fk_user_action, a.email_from, a.percent,";
            $sql .= " u1.login as author_login, u1.firstname as author_firstname, u1.lastname as author_lastname,";
            $sql .= " u2.login as action_login, u2.firstname as action_firstname, u2.lastname as action_lastname";
            $sql .= " FROM ".MAIN_DB_PREFIX."actioncomm as a";
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u1 ON a.fk_user_author = u1.rowid";
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u2 ON a.fk_user_action = u2.rowid";
            $sql .= " WHERE a.fk_element = ".(int)$ticket_id;
            $sql .= " AND a.elementtype = 'ticket'";
            $sql .= " AND a.code LIKE 'TICKET_MSG%'";
            $sql .= " ORDER BY a.datec ASC";

            $resql = $db->query($sql);
            
            if (!$resql) {
                throw new RestException(500, 'Error fetching messages: ' . $db->lasterror());
            }

            $messages = array();
            
            while ($obj = $db->fetch_object($resql)) {
                $author_name = '';
                $author_type = '';
                $author_email = '';
                
                // Determinar quién creó el mensaje
                if (!empty($obj->email_from)) {
                    // Mensaje creado desde sistema público o API con contacto
                    $author_type = 'contact';
                    $author_email = $obj->email_from;
                    
                    // Buscar el contacto por email para obtener su nombre
                    require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
                    $contact = new Contact($db);
                    $result_contact = $contact->fetch(0, '', '', $obj->email_from);
                    
                    if ($result_contact > 0) {
                        $author_name = trim(($contact->firstname ? $contact->firstname . ' ' : '') . ($contact->lastname ? $contact->lastname : ''));
                        if (empty($author_name)) {
                            $author_name = $obj->email_from;
                        }
                    } else {
                        $author_name = $obj->email_from;
                    }
                } elseif (!empty($obj->fk_user_action)) {
                    // Mensaje creado por un usuario interno
                    $author_type = 'user';
                    $author_name = trim(($obj->action_firstname ? $obj->action_firstname . ' ' : '') . ($obj->action_lastname ? $obj->action_lastname : ''));
                    if (empty($author_name)) {
                        $author_name = $obj->action_login;
                    }
                } else {
                    // Sin autor identificado
                    $author_type = 'unknown';
                    $author_name = 'Unknown';
                }
                
                $messages[] = array(
                    'id' => $obj->id,
                    'ref' => $obj->ref,
                    'message' => $obj->message,
                    'label' => $obj->label,
                    'date_creation' => $obj->datec,
                    'date_event' => $obj->datep,
                    'private' => ($obj->percent == -1) ? 1 : 0,
                    'author' => array(
                        'type' => $author_type,
                        'name' => $author_name,
                        'email' => $author_email,
                        'user_id' => $obj->fk_user_action,
                        'login' => $obj->action_login
                    )
                );
            }
            
            $db->free($resql);
            
            return array(
                'success' => true,
                'ticket_id' => $ticket_id,
                'ticket_ref' => $ticket->ref,
                'total_messages' => count($messages),
                'messages' => $messages
            );
            
        } catch (RestException $e) {
            throw $e;
        } catch (Exception $e) {
            dol_syslog("Error in getTicketMessages: " . $e->getMessage(), LOG_ERR);
            throw new RestException(500, 'Internal error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
        }
    }
}
