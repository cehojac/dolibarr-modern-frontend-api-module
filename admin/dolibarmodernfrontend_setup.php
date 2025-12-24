<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("admin", "dolibarmodernfrontend@dolibarmodernfrontend"));

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

/*
 * Actions
 */

if ($action == 'updateMask') {
    $maskconstorder = GETPOST('maskconstorder', 'alpha');
    $maskorder = GETPOST('maskorder', 'alpha');

    if ($maskconstorder && dol_strlen($maskorder) >= 1) {
        $res = dolibarr_set_const($db, $maskconstorder, $maskorder, 'chaine', 0, '', $conf->entity);
        if (!($res > 0)) {
            $error++;
        }
    }

    if (!$error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

/*
 * View
 */

$page_name = "DolibarmodernfrontendSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = dolibarmodernfrontendAdminPrepareHead();

print dol_get_fiche_head($head, 'settings', $langs->trans("ModuleDolibarmodernfrontendName"), -1, 'dolibarmodernfrontend@dolibarmodernfrontend');

// Setup page goes here
print '<span class="opacitymedium">'.$langs->trans("DolibarmodernfrontendSetupPage").'</span><br><br>';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Example of setup parameter
print '<tr class="oddeven">';
print '<td>'.$langs->trans("EnableAPILogging").'</td>';
print '<td>';
if ($conf->use_javascript_ajax) {
    print ajax_constantonoff('DOLIBARMODERNFRONTEND_ENABLE_API_LOGGING');
} else {
    $arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
    print $form->selectarray("DOLIBARMODERNFRONTEND_ENABLE_API_LOGGING", $arrval, $conf->global->DOLIBARMODERNFRONTEND_ENABLE_API_LOGGING);
}
print '</td>';
print '</tr>';

print '</table>';
print '</div>';

print '<br><div class="center">';
print '<input class="button button-save" type="submit" value="'.$langs->trans("Save").'">';
print '</div>';

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();

/**
 * Prepare admin pages header
 *
 * @return array
 */
function dolibarmodernfrontendAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("dolibarmodernfrontend@dolibarmodernfrontend");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/dolibarmodernfrontend/admin/dolibarmodernfrontend_setup.php", 1);
    $head[$h][1] = $langs->trans("Settings");
    $head[$h][2] = 'settings';
    $h++;

    $head[$h][0] = dol_buildpath("/dolibarmodernfrontend/admin/dolibarmodernfrontend_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@dolibarmodernfrontend:/dolibarmodernfrontend/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@dolibarmodernfrontend:/dolibarmodernfrontend/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, null, $head, $h, 'dolibarmodernfrontend');

    return $head;
}
?>
