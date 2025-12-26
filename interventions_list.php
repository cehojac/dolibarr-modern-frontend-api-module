<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/dolibarrmodernfrontend/class/ticketinterventionlink.class.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';

// Load translation files required by the page
$langs->loadLangs(array("dolibarrmodernfrontend@dolibarrmodernfrontend"));

// Access control
if (!$user->rights->dolibarrmodernfrontend->read) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$ticket_id = GETPOST('ticket_id', 'int');
$intervention_id = GETPOST('intervention_id', 'int');

$object = new TicketInterventionLink($db);

/*
 * Actions
 */
if ($action == 'link' && $ticket_id && $intervention_id) {
    if ($user->rights->dolibarrmodernfrontend->write) {
        if (!$object->existsLink($ticket_id, $intervention_id)) {
            $result = $object->linkTicketIntervention($ticket_id, $intervention_id, $user);
            if ($result > 0) {
                setEventMessages($langs->trans("LinkCreatedSuccessfully"), null, 'mesgs');
            } else {
                setEventMessages($object->error, $object->errors, 'errors');
            }
        } else {
            setEventMessages($langs->trans("LinkAlreadyExists"), null, 'warnings');
        }
    }
}

if ($action == 'unlink' && $ticket_id && $intervention_id) {
    if ($user->rights->dolibarrmodernfrontend->delete) {
        $result = $object->unlinkTicketIntervention($ticket_id, $intervention_id);
        if ($result > 0) {
            setEventMessages($langs->trans("LinkRemovedSuccessfully"), null, 'mesgs');
        } else {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    }
}

/*
 * View
 */

llxHeader("", $langs->trans("InterventionTicketLinks"));

print load_fiche_titre($langs->trans("InterventionTicketLinks"), '', 'object_dolibarrmodernfrontend@dolibarrmodernfrontend');

print '<div class="fichecenter">';

// Form to create new link
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="link">';

print '<table class="border centpercent tableforfieldcreate">'."\n";

// Ticket selection
print '<tr><td class="fieldrequired">'.$langs->trans("Ticket").'</td><td>';
print $form->selectarray('ticket_id', array(), '', $langs->trans("SelectTicket"), 0, 0, '', 0, 0, 0, '', 'minwidth300', 1);
print '</td></tr>';

// Intervention selection  
print '<tr><td class="fieldrequired">'.$langs->trans("Intervention").'</td><td>';
print $form->selectarray('intervention_id', array(), '', $langs->trans("SelectIntervention"), 0, 0, '', 0, 0, 0, '', 'minwidth300', 1);
print '</td></tr>';

print '</table>';

print '<div class="center">';
print '<input type="submit" class="button" value="'.$langs->trans("CreateLink").'">';
print '</div>';

print '</form>';

// Mostrar vinculaciones existentes
print '<br><h3>'.$langs->trans("ExistingLinks").'</h3>';

$links = $object->getAllLinks();
if (is_array($links) && count($links) > 0) {
    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre">';
    print '<th>'.$langs->trans("Ticket").'</th>';
    print '<th>'.$langs->trans("Intervention").'</th>';
    print '<th>'.$langs->trans("Client").'</th>';
    print '<th>'.$langs->trans("Actions").'</th>';
    print '</tr>';
    
    foreach ($links as $link) {
        print '<tr class="oddeven">';
        print '<td>'.$link['ticket_ref'].' - '.$link['ticket_subject'].'</td>';
        print '<td>'.$link['intervention_ref'].' - '.$link['intervention_label'].'</td>';
        print '<td>'.$link['client_name'].'</td>';
        print '<td>';
        if ($user->rights->dolibarrmodernfrontend->delete) {
            print '<a href="'.$_SERVER["PHP_SELF"].'?action=unlink&ticket_id='.$link['ticket_id'].'&intervention_id='.$link['intervention_id'].'&token='.newToken().'" class="button buttonDelete">';
            print $langs->trans("Unlink").'</a>';
        }
        print '</td>';
        print '</tr>';
    }
    print '</table>';
} else {
    print '<div class="opacitymedium">'.$langs->trans("NoLinksFound").'</div>';
}

print '</div>';

// End of page
llxFooter();
$db->close();
?>
