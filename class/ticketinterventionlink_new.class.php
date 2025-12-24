<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';

/**
 * Clase para gestionar las vinculaciones entre tickets e intervenciones usando el sistema nativo de Dolibarr (llx_element_element)
 */
class TicketInterventionLink
{
    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * @var string Error message
     */
    public $error;

    /**
     * @var array Error messages
     */
    public $errors = array();

    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * Vincular una intervención con un ticket usando el sistema nativo de Dolibarr
     *
     * @param int $ticket_id ID del ticket
     * @param int $intervention_id ID de la intervención
     * @param User $user Usuario que crea la vinculación
     * @return int <0 if KO, >0 if OK
     */
    public function linkTicketIntervention($ticket_id, $intervention_id, User $user)
    {
        // Verificar que el ticket existe
        $ticket = new Ticket($this->db);
        if ($ticket->fetch($ticket_id) <= 0) {
            $this->error = "Ticket not found";
            return -1;
        }

        // Verificar que la intervención existe
        $intervention = new Fichinter($this->db);
        if ($intervention->fetch($intervention_id) <= 0) {
            $this->error = "Intervention not found";
            return -1;
        }

        // Verificar si ya existe la vinculación
        if ($this->existsLink($ticket_id, $intervention_id)) {
            $this->error = "Link already exists";
            return -2;
        }

        // Usar el método nativo de CommonObject para crear la vinculación
        $result = $ticket->add_object_linked('intervention', $intervention_id);
        
        if ($result < 0) {
            $this->error = $ticket->error;
            $this->errors = $ticket->errors;
            return -3;
        }

        return 1;
    }

    /**
     * Obtener intervenciones vinculadas a un ticket
     *
     * @param int $ticket_id ID del ticket
     * @return array|int Array de intervenciones vinculadas o -1 si error
     */
    public function getInterventionsByTicket($ticket_id)
    {
        $interventions = array();

        // Verificar que el ticket existe
        $ticket = new Ticket($this->db);
        if ($ticket->fetch($ticket_id) <= 0) {
            $this->error = "Ticket not found";
            return -1;
        }

        // Obtener objetos vinculados usando el método nativo
        $linkedObjects = $ticket->linkedObjects;
        
        if (isset($linkedObjects['intervention']) && is_array($linkedObjects['intervention'])) {
            foreach ($linkedObjects['intervention'] as $intervention_id => $intervention_obj) {
                $intervention = new Fichinter($this->db);
                if ($intervention->fetch($intervention_id) > 0) {
                    $interventions[] = array(
                        'intervention_id' => $intervention->id,
                        'intervention_ref' => $intervention->ref,
                        'intervention_label' => $intervention->label,
                        'intervention_description' => $intervention->description,
                        'intervention_date' => $intervention->datei,
                        'intervention_status' => $intervention->fk_statut,
                        'client_id' => $intervention->fk_soc,
                        'client_name' => $intervention->thirdparty->name ?? '',
                    );
                }
            }
        } else {
            // Si no hay objetos cargados, hacer consulta directa a la base de datos
            $sql = "SELECT ee.fk_target as intervention_id,";
            $sql .= " f.ref as intervention_ref, f.label as intervention_label, f.description as intervention_description,";
            $sql .= " f.datei as intervention_date, f.fk_statut as intervention_status, f.fk_soc,";
            $sql .= " s.nom as client_name";
            $sql .= " FROM ".MAIN_DB_PREFIX."element_element ee";
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."fichinter f ON f.rowid = ee.fk_target";
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = f.fk_soc";
            $sql .= " WHERE ee.fk_source = ".((int) $ticket_id);
            $sql .= " AND ee.sourcetype = 'ticket'";
            $sql .= " AND ee.targettype = 'intervention'";
            $sql .= " ORDER BY f.datei DESC";

            $resql = $this->db->query($sql);
            if ($resql) {
                $num = $this->db->num_rows($resql);
                for ($i = 0; $i < $num; $i++) {
                    $obj = $this->db->fetch_object($resql);
                    $interventions[] = array(
                        'intervention_id' => $obj->intervention_id,
                        'intervention_ref' => $obj->intervention_ref,
                        'intervention_label' => $obj->intervention_label,
                        'intervention_description' => $obj->intervention_description,
                        'intervention_date' => $obj->intervention_date,
                        'intervention_status' => $obj->intervention_status,
                        'client_id' => $obj->fk_soc,
                        'client_name' => $obj->client_name,
                    );
                }
                $this->db->free($resql);
            } else {
                $this->error = $this->db->lasterror();
                return -1;
            }
        }

        return $interventions;
    }

    /**
     * Obtener tickets vinculados a una intervención
     *
     * @param int $intervention_id ID de la intervención
     * @return array|int Array de tickets vinculados o -1 si error
     */
    public function getTicketsByIntervention($intervention_id)
    {
        $tickets = array();

        // Verificar que la intervención existe
        $intervention = new Fichinter($this->db);
        if ($intervention->fetch($intervention_id) <= 0) {
            $this->error = "Intervention not found";
            return -1;
        }

        // Hacer consulta directa a la base de datos
        $sql = "SELECT ee.fk_source as ticket_id,";
        $sql .= " t.ref as ticket_ref, t.subject as ticket_subject, t.message as ticket_message,";
        $sql .= " t.datec as ticket_date, t.fk_statut as ticket_status, t.fk_soc,";
        $sql .= " s.nom as client_name";
        $sql .= " FROM ".MAIN_DB_PREFIX."element_element ee";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket t ON t.rowid = ee.fk_source";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = t.fk_soc";
        $sql .= " WHERE ee.fk_target = ".((int) $intervention_id);
        $sql .= " AND ee.sourcetype = 'ticket'";
        $sql .= " AND ee.targettype = 'intervention'";
        $sql .= " ORDER BY t.datec DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            for ($i = 0; $i < $num; $i++) {
                $obj = $this->db->fetch_object($resql);
                $tickets[] = array(
                    'ticket_id' => $obj->ticket_id,
                    'ticket_ref' => $obj->ticket_ref,
                    'ticket_subject' => $obj->ticket_subject,
                    'ticket_message' => $obj->ticket_message,
                    'ticket_date' => $obj->ticket_date,
                    'ticket_status' => $obj->ticket_status,
                    'client_id' => $obj->fk_soc,
                    'client_name' => $obj->client_name,
                );
            }
            $this->db->free($resql);
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }

        return $tickets;
    }

    /**
     * Desvincular una intervención de un ticket
     *
     * @param int $ticket_id ID del ticket
     * @param int $intervention_id ID de la intervención
     * @return int <0 if KO, >0 if OK
     */
    public function unlinkTicketIntervention($ticket_id, $intervention_id)
    {
        // Verificar que el ticket existe
        $ticket = new Ticket($this->db);
        if ($ticket->fetch($ticket_id) <= 0) {
            $this->error = "Ticket not found";
            return -1;
        }

        // Verificar si existe la vinculación
        if (!$this->existsLink($ticket_id, $intervention_id)) {
            $this->error = "Link not found";
            return -2;
        }

        // Usar el método nativo para eliminar la vinculación
        $result = $ticket->deleteObjectLinked(null, 'intervention', $intervention_id);
        
        if ($result < 0) {
            $this->error = $ticket->error;
            $this->errors = $ticket->errors;
            return -3;
        }

        return 1;
    }

    /**
     * Verificar si existe una vinculación entre ticket e intervención
     *
     * @param int $ticket_id ID del ticket
     * @param int $intervention_id ID de la intervención
     * @return bool true si existe, false si no existe
     */
    public function existsLink($ticket_id, $intervention_id)
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."element_element";
        $sql .= " WHERE fk_source = ".((int) $ticket_id);
        $sql .= " AND fk_target = ".((int) $intervention_id);
        $sql .= " AND sourcetype = 'ticket'";
        $sql .= " AND targettype = 'intervention'";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $this->db->free($resql);
            return ($num > 0);
        }
        return false;
    }

    /**
     * Obtener todas las vinculaciones existentes
     *
     * @return array Array de vinculaciones
     */
    public function getAllLinks()
    {
        $links = array();

        $sql = "SELECT ee.rowid, ee.fk_source as ticket_id, ee.fk_target as intervention_id,";
        $sql .= " t.ref as ticket_ref, t.subject as ticket_subject,";
        $sql .= " f.ref as intervention_ref, f.label as intervention_label,";
        $sql .= " s.nom as client_name";
        $sql .= " FROM ".MAIN_DB_PREFIX."element_element ee";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket t ON t.rowid = ee.fk_source";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."fichinter f ON f.rowid = ee.fk_target";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = t.fk_soc";
        $sql .= " WHERE ee.sourcetype = 'ticket'";
        $sql .= " AND ee.targettype = 'intervention'";
        $sql .= " ORDER BY ee.rowid DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            for ($i = 0; $i < $num; $i++) {
                $obj = $this->db->fetch_object($resql);
                $links[] = array(
                    'link_id' => $obj->rowid,
                    'ticket_id' => $obj->ticket_id,
                    'ticket_ref' => $obj->ticket_ref,
                    'ticket_subject' => $obj->ticket_subject,
                    'intervention_id' => $obj->intervention_id,
                    'intervention_ref' => $obj->intervention_ref,
                    'intervention_label' => $obj->intervention_label,
                    'client_name' => $obj->client_name,
                );
            }
            $this->db->free($resql);
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }

        return $links;
    }
}
