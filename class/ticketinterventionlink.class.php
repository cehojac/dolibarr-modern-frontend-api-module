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
     * Create object into database
     *
     * @param  User $user      User that creates
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, Id of created object if OK
     */
    public function create(User $user, $notrigger = false)
    {
        $resultcreate = $this->createCommon($user, $notrigger);

        if ($resultcreate < 0) {
            return $resultcreate;
        }

        return $resultcreate;
    }

    /**
     * Clone an object into another one
     *
     * @param  	User 	$user      	User that creates
     * @param  	int 	$fromid     Id of object to clone
     * @return 	mixed 				New object created, <0 if KO
     */
    public function createFromClone(User $user, $fromid)
    {
        global $langs, $extrafields;
        $error = 0;

        dol_syslog(__METHOD__, LOG_DEBUG);

        $object = new self($this->db);

        $this->db->begin();

        // Load source object
        $result = $object->fetchCommon($fromid);
        if ($result > 0 && !empty($object->table_element_line)) {
            $object->fetchLines();
        }

        // get lines so they will be clone
        //foreach($this->lines as $line)
        //	$line->fetch_optionals();

        // Reset some properties
        unset($object->id);
        unset($object->fk_user_creat);
        unset($object->import_key);

        // Clear fields
        if (property_exists($object, 'ref')) {
            $object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
        }
        if (property_exists($object, 'label')) {
            $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
        }
        if (property_exists($object, 'status')) {
            $object->status = self::STATUS_ACTIVE;
        }
        if (property_exists($object, 'date_creation')) {
            $object->date_creation = dol_now();
        }
        if (property_exists($object, 'date_modification')) {
            $object->date_modification = null;
        }
        // ...
        // Clear extrafields that are unique
        if (is_array($object->array_options) && count($object->array_options) > 0) {
            $extrafields->fetch_name_optionals_label($this->table_element);
            foreach ($object->array_options as $key => $option) {
                $shortkey = preg_replace('/options_/', '', $key);
                if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
                    //var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
                    unset($object->array_options[$key]);
                }
            }
        }

        // Create clone
        $object->context['createfromclone'] = 'createfromclone';
        $result = $object->createCommon($user);
        if ($result < 0) {
            $error++;
            $this->error = $object->error;
            $this->errors = $object->errors;
        }

        if (!$error) {
            // copy internal contacts
            if ($this->copy_linked_contact($object, 'internal') < 0) {
                $error++;
            }
        }

        if (!$error) {
            // copy external contacts if same company
            if (property_exists($this, 'fk_soc') && $this->fk_soc == $object->fk_soc) {
                if ($this->copy_linked_contact($object, 'external') < 0) {
                    $error++;
                }
            }
        }

        unset($object->context['createfromclone']);

        // End
        if (!$error) {
            $this->db->commit();
            return $object;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id   Id object
     * @param string $ref  Ref
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = null)
    {
        $result = $this->fetchCommon($id, $ref);
        if ($result > 0 && !empty($this->table_element_line)) {
            $this->fetchLines();
        }
        return $result;
    }

    /**
     * Load object lines in memory from the database
     *
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetchLines()
    {
        $this->lines = array();

        $result = $this->fetchLinesCommon();
        return $result;
    }

    /**
     * Update object into database
     *
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function update(User $user, $notrigger = false)
    {
        return $this->updateCommon($user, $notrigger);
    }

    /**
     * Delete object in database
     *
     * @param User $user       User that deletes
     * @param bool $notrigger  false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function delete(User $user, $notrigger = false)
    {
        return $this->deleteCommon($user, $notrigger);
    }

    /**
     * Vincular una intervención con un ticket
     *
     * @param int $ticket_id ID del ticket
     * @param int $intervention_id ID de la intervención
     * @param string $link_type Tipo de vinculación (manual, automatic, system)
     * @param string $description Descripción de la vinculación
     * @param User $user Usuario que crea la vinculación
     * @return int <0 if KO, >0 if OK
     */
    public function linkTicketIntervention($ticket_id, $intervention_id, $link_type = 'manual', $description = '', User $user)
    {
        global $conf;

        $this->fk_ticket = $ticket_id;
        $this->fk_intervention = $intervention_id;
        $this->link_type = $link_type;
        $this->description = $description;
        $this->fk_user_author = $user->id;
        $this->datec = dol_now();
        $this->status = self::STATUS_ACTIVE;

        return $this->create($user);
    }

    /**
     * Obtener intervenciones vinculadas a un ticket
     *
     * @param int $ticket_id ID del ticket
     * @return array Array de intervenciones vinculadas
     */
    public function getInterventionsByTicket($ticket_id)
    {
        $interventions = array();

        $sql = "SELECT til.rowid, til.fk_ticket, til.fk_intervention, til.link_type, til.description,";
        $sql .= " til.fk_user_author, til.datec, til.status,";
        $sql .= " f.ref as intervention_ref, f.label as intervention_label, f.description as intervention_description,";
        $sql .= " f.datei as intervention_date, f.fk_soc, f.fk_statut as intervention_status,";
        $sql .= " s.nom as client_name, s.code_client";
        $sql .= " FROM ".MAIN_DB_PREFIX."ticket_intervention_link til";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."fichinter f ON f.rowid = til.fk_intervention";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = f.fk_soc";
        $sql .= " WHERE til.fk_ticket = ".((int) $ticket_id);
        $sql .= " AND til.status = ".self::STATUS_ACTIVE;
        $sql .= " ORDER BY til.datec DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            for ($i = 0; $i < $num; $i++) {
                $obj = $this->db->fetch_object($resql);
                $interventions[] = array(
                    'link_id' => $obj->rowid,
                    'ticket_id' => $obj->fk_ticket,
                    'intervention_id' => $obj->fk_intervention,
                    'link_type' => $obj->link_type,
                    'link_description' => $obj->description,
                    'link_author' => $obj->fk_user_author,
                    'link_date' => $obj->datec,
                    'link_status' => $obj->status,
                    'intervention_ref' => $obj->intervention_ref,
                    'intervention_label' => $obj->intervention_label,
                    'intervention_description' => $obj->intervention_description,
                    'intervention_date' => $obj->intervention_date,
                    'intervention_status' => $obj->intervention_status,
                    'client_id' => $obj->fk_soc,
                    'client_name' => $obj->client_name,
                    'client_code' => $obj->code_client
                );
            }
            $this->db->free($resql);
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }

        return $interventions;
    }

    /**
     * Obtener tickets vinculados a una intervención
     *
     * @param int $intervention_id ID de la intervención
     * @return array Array de tickets vinculados
     */
    public function getTicketsByIntervention($intervention_id)
    {
        $tickets = array();

        $sql = "SELECT til.rowid, til.fk_ticket, til.fk_intervention, til.link_type, til.description,";
        $sql .= " til.fk_user_author, til.datec, til.status,";
        $sql .= " t.ref as ticket_ref, t.subject as ticket_subject, t.message as ticket_message,";
        $sql .= " t.datec as ticket_date, t.fk_soc, t.fk_statut as ticket_status,";
        $sql .= " s.nom as client_name, s.code_client";
        $sql .= " FROM ".MAIN_DB_PREFIX."ticket_intervention_link til";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket t ON t.rowid = til.fk_ticket";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = t.fk_soc";
        $sql .= " WHERE til.fk_intervention = ".((int) $intervention_id);
        $sql .= " AND til.status = ".self::STATUS_ACTIVE;
        $sql .= " ORDER BY til.datec DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            for ($i = 0; $i < $num; $i++) {
                $obj = $this->db->fetch_object($resql);
                $tickets[] = array(
                    'link_id' => $obj->rowid,
                    'ticket_id' => $obj->fk_ticket,
                    'intervention_id' => $obj->fk_intervention,
                    'link_type' => $obj->link_type,
                    'link_description' => $obj->description,
                    'link_author' => $obj->fk_user_author,
                    'link_date' => $obj->datec,
                    'link_status' => $obj->status,
                    'ticket_ref' => $obj->ticket_ref,
                    'ticket_subject' => $obj->ticket_subject,
                    'ticket_message' => $obj->ticket_message,
                    'ticket_date' => $obj->ticket_date,
                    'ticket_status' => $obj->ticket_status,
                    'client_id' => $obj->fk_soc,
                    'client_name' => $obj->client_name,
                    'client_code' => $obj->code_client
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
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."ticket_intervention_link";
        $sql .= " WHERE fk_ticket = ".((int) $ticket_id);
        $sql .= " AND fk_intervention = ".((int) $intervention_id);

        $resql = $this->db->query($sql);
        if ($resql) {
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
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
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."ticket_intervention_link";
        $sql .= " WHERE fk_ticket = ".((int) $ticket_id);
        $sql .= " AND fk_intervention = ".((int) $intervention_id);
        $sql .= " AND status = ".self::STATUS_ACTIVE;

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $this->db->free($resql);
            return ($num > 0);
        }
        return false;
    }
}
