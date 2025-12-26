<?php
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

class modDolibarrmodernfrontend extends DolibarrModules
{
    public function __construct($db)
    {
        global $langs, $conf;
        
        $this->db = $db;
        
        // Id for module (must be unique)
        $this->numero = 105003;
        
        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'dolibarrmodernfrontend';
        
        // Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (interface modules),'other','...'
        $this->family = "interface";
        
        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '90';
        
        // Module label (no space allowed), used if translation string 'ModuleDolibarrmodernfrontendName' not found
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        
        // Module description, used if translation string 'ModuleDolibarrmodernfrontendDesc' not found
        $this->description = "API moderna para gestión de intervenciones y tickets";
        // Used only if file README.md and README-LL.md not found.
        $this->descriptionlong = "Módulo que proporciona una API moderna para vincular intervenciones con tickets y consultar datos relacionados";
        
        // Author
        $this->editor_name = 'DolibarrModules';
        $this->editor_url = '';
        
        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '1.2.7';
        
        // Key used in llx_const table to save module status enabled/disabled
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        
        // Name of image file used for this module.
        $this->picto = 'technic';
        
        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = array(
            // Set this to 1 if module has its own trigger directory (core/triggers)
            'triggers' => 0,
            // Set this to 1 if module has its own login method file (core/login)
            'login' => 0,
            // Set this to 1 if module has its own substitution function file (core/substitutions)
            'substitutions' => 0,
            // Set this to 1 if module has its own menus handler directory (core/menus)
            'menus' => 0,
            // Set this to 1 if module overwrite template dir (core/tpl)
            'tpl' => 0,
            // Set this to 1 if module has its own barcode directory (core/modules/barcode)
            'barcode' => 0,
            // Set this to 1 if module has its own models directory (core/modules/xxx)
            'models' => 0,
            // Set this to 1 if module has its own printing directory (core/modules/printing)
            'printing' => 0,
            // Set this to 1 if module has its own theme directory (theme)
            'theme' => 0,
            // Set this to relative path of css file if module has its own css file
            'css' => array(),
            // Set this to relative path of js file if module must load a js on all pages
            'js' => array(),
            // Set here all hooks context managed by module
            'hooks' => array(),
            // Set this to 1 if features of module are opened to external users
            'moduleforexternal' => 0,
            'api' => 1,
        );
        
        // Data directories to create when module is enabled.
        $this->dirs = array("/dolibarrmodernfrontend");
        
        // Config pages. Put here list of php page, stored into dolibarrmodernfrontend/admin directory, to use to setup module.
        $this->config_page_url = array("dolibarrmodernfrontend_setup.php@dolibarrmodernfrontend");
        
        // Dependencies
        // A condition to hide module
        $this->hidden = false;
        // List of module class names as string that must be enabled if this module is enabled
        $this->depends = array();
        $this->requiredby = array(); // List of module class names as string to disable if this one is disabled
        $this->conflictwith = array(); // List of module class names as string this module is in conflict with
        
        // The language file dedicated to your module
        $this->langfiles = array("dolibarrmodernfrontend@dolibarrmodernfrontend");
        
        // Prerequisites
        $this->phpmin = array(7, 0); // Minimum version of PHP required by module
        $this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module
        
        // Messages at activation
        $this->warnings_activation = array(); // Warning to show when we activate module
        $this->warnings_activation_ext = array(); // Warning to show when we activate an external module
        
        // Constants
        $this->const = array();

        // Tablas de base de datos
        $this->tabs = array();

        // Diccionarios
        $this->dictionaries = array();

        // Cajas/Widgets
        $this->boxes = array();

        // Cronjobs
        $this->cronjobs = array();

        // SQL arrays for module installation (no custom tables needed, uses native llx_element_element)
        $this->sqlfiles = array();

        // Permisos
        $this->rights = array();
        $r = 0;

        // Permiso de lectura
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Leer vinculaciones de intervenciones y tickets';
        $this->rights[$r][4] = 'dolibarrmodernfrontend';
        $this->rights[$r][5] = 'read';
        $r++;

        // Permiso de escritura
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Crear/modificar vinculaciones de intervenciones y tickets';
        $this->rights[$r][4] = 'dolibarrmodernfrontend';
        $this->rights[$r][5] = 'write';
        $r++;

        // Permiso de eliminación
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Eliminar vinculaciones de intervenciones y tickets';
        $this->rights[$r][4] = 'dolibarrmodernfrontend';
        $this->rights[$r][5] = 'delete';
        $r++;

        // Permiso de administración
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Administrar módulo de frontend moderno';
        $this->rights[$r][4] = 'dolibarrmodernfrontend';
        $this->rights[$r][5] = 'admin';
        $r++;

        // Menús
        $this->menu = array();
        $r = 0;

        // Menú principal en Herramientas
        $this->menu[$r]['fk_menu'] = 'fk_mainmenu=tools';
        $this->menu[$r]['type'] = 'left';
        $this->menu[$r]['titre'] = 'Frontend Moderno';
        $this->menu[$r]['mainmenu'] = 'tools';
        $this->menu[$r]['leftmenu'] = 'dolibarrmodernfrontend';
        $this->menu[$r]['url'] = '/custom/dolibarrmodernfrontend/interventions_list.php';
        $this->menu[$r]['langs'] = 'dolibarrmodernfrontend@dolibarrmodernfrontend';
        $this->menu[$r]['position'] = 1100;
        $this->menu[$r]['enabled'] = 1;
        $this->menu[$r]['perms'] = '1';
        $this->menu[$r]['target'] = '';
        $this->menu[$r]['user'] = 2;
        $r++;

        // Submenú: Gestión de vinculaciones
        $this->menu[$r]['fk_menu'] = 'fk_mainmenu=tools,fk_leftmenu=dolibarrmodernfrontend';
        $this->menu[$r]['type'] = 'left';
        $this->menu[$r]['titre'] = 'Gestión de Vinculaciones';
        $this->menu[$r]['mainmenu'] = 'tools';
        $this->menu[$r]['leftmenu'] = 'dolibarrmodernfrontend_links';
        $this->menu[$r]['url'] = '/custom/dolibarrmodernfrontend/interventions_list.php';
        $this->menu[$r]['langs'] = 'dolibarrmodernfrontend@dolibarrmodernfrontend';
        $this->menu[$r]['position'] = 1101;
        $this->menu[$r]['enabled'] = 1;
        $this->menu[$r]['perms'] = '1';
        $this->menu[$r]['target'] = '';
        $this->menu[$r]['user'] = 2;
        $r++;

        // Submenú: API Documentation
        $this->menu[$r]['fk_menu'] = 'fk_mainmenu=tools,fk_leftmenu=dolibarrmodernfrontend';
        $this->menu[$r]['type'] = 'left';
        $this->menu[$r]['titre'] = 'API Documentation';
        $this->menu[$r]['mainmenu'] = 'tools';
        $this->menu[$r]['leftmenu'] = 'dolibarrmodernfrontend_api';
        $this->menu[$r]['url'] = '/custom/dolibarrmodernfrontend/api_doc.php';
        $this->menu[$r]['langs'] = 'dolibarrmodernfrontend@dolibarrmodernfrontend';
        $this->menu[$r]['position'] = 1102;
        $this->menu[$r]['enabled'] = 1;
        $this->menu[$r]['perms'] = '1';
        $this->menu[$r]['target'] = '';
        $this->menu[$r]['user'] = 2;
        $r++;
    }

    /**
     * Function called when module is enabled.
     * The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     * It also creates data directories
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int 1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs, $db;

        // No custom SQL needed, uses native llx_element_element table
        $sql = array();

        return $this->_init($sql, $options);
    }
}
