<?php
/* Copyright (C) 2025
 *
 * This file is part of the dolibarrmodernfrontend module.
 */

require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/dolibarrmodernfrontend/core/modules/modDolibarrmodernfrontend.class.php';

// Load languages
$langs->loadLangs(array('dolibarrmodernfrontend@dolibarrmodernfrontend', 'admin'));

// Permission check: allow module readers or Dolibarr admins
if (empty($user->rights->dolibarrmodernfrontend->read) && empty($user->admin)) {
    accessforbidden();
}

$module = new modDolibarrmodernfrontend($db);

$page_name = $langs->trans('ModuleDolibarrmodernfrontendAboutTitle');
if ($page_name === 'ModuleDolibarrmodernfrontendAboutTitle') {
    $page_name = 'Dolibarr Modern Frontend – About';
}

$help_url = '';
llxHeader('', $page_name, $help_url);

print load_fiche_titre($page_name, '', 'technic');

echo '<div class="fichecenter">';
echo '<div class="fichehalfleft">';
echo '<table class="border centpercent">';

echo '<tr><td class="titlefield">'.$langs->trans('Name').'</td><td>'.dol_escape_htmltag($module->name).'</td></tr>';

echo '<tr><td>'.$langs->trans('Version').'</td><td>'.dol_escape_htmltag($module->version).'</td></tr>';

echo '<tr><td>'.$langs->trans('Description').'</td><td>'.dol_escape_htmltag($module->description).'</td></tr>';

echo '<tr><td>'.$langs->trans('Author').'</td><td>'.dol_escape_htmltag($module->editor_name).'</td></tr>';

echo '<tr><td>'.$langs->trans('ModuleStatus').'</td><td>'.(empty($conf->global->{$module->const_name}) ? $langs->trans('Disabled') : $langs->trans('Enabled')).'</td></tr>';

echo '</table>';
echo '</div>';

$apiBase = dol_buildpath('/dolibarrmodernfrontendapi', 1);

echo '<div class="fichehalfright">';
echo '<div class="ficheaddleft">';
echo '<h3>'.$langs->trans('Information').'</h3>';

echo '<p>'.$langs->trans('ModuleDolibarrmodernfrontendDesc').'</p>';

echo '<h3>API</h3>';
echo '<ul>';
echo '<li><strong>GET</strong> '.$apiBase.'/version</li>';
echo '<li><strong>GET</strong> '.$apiBase.'/tickets/{ticket_id}/messages</li>';
echo '<li><strong>GET</strong> '.$apiBase.'/ticket/{ticket_id}/interventions</li>';
echo '<li><strong>GET</strong> '.$apiBase.'/intervention/{intervention_id}/tickets</li>';
echo '</ul>';

echo '<p>'.$langs->trans('Permission').': '.$langs->trans('dolibarrmodernfrontendRightRead').'</p>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="clearboth"></div>';

llxFooter();
$db->close();
