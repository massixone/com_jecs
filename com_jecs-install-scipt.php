<?php
/**
 * @package         Jecs Extensions Installer
 * @author          Massimo Di Primio <info@diprimio.com>
 * @link            http://www.diprimio.com
 * @copyright       Copyright © 2015 Diprimio.Com All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

// Potential backward compatibility issues in Joomla 3 and Joomla Platform 12.2
// Please  refer to: http://docs.joomla.org/Potential_backward_compatibility_issues_in_Joomla_3_and_Joomla_Platform_12.2

if(!defined('DS')) {define('DS', DIRECTORY_SEPARATOR);}     // For backward compatibility

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class com_JecsInstallerScript {

    private $min_version_joomla = '3.4.1';      /* @var string - Minimum Joomla required version */
    private $min_version_php    = '5.6.0';      /* @var string - Minimum PHP required version */
    private $min_version_mysql  = '5.5.1';      /* @var string - Minimum PHP required version */
    private $app;                               /* @var object - Joomla Global Application object */
    #
    
    /*
     * $subExtensionUninstallMap Array
     * ---------------------------------
     * Describes what and how to Uninstall subextensions when the Component is Uninstalled
     * To be filled with appropriate values for plugins and modules
     * 
    $subExtensionUninstallMap = array (
        'Plugins' => array (
            array(  
                'Name'  => 'plg_system_jecs',       # the name of the plugin as known by Joomla
                'Element'   => 'jecs',              # the plugin element a known by Joomla
                'Folder'    => 'system'             # the plugin 'folder' (i.e. the plugin 'group' as in manifest)
                 )
            )
        );
     */
    private $subExtensionUninstallMap = array (
        'Plugins' => array (
            array(  'Name'      => 'plg_system_jecs',
                    'Element'   => 'jecs',
                    'Folder'    => 'system'
                )
            )
        );

    /*
     * $subExtentionInstallMap Array
     * -------------------------------
     * Describes what and how to Install subextensions when the Component is Installed or updated
     * To be filled with appropriate values for plugins and modules
     * 
    $subExtensionInstallMap = array (
        "Plugins" => array ( 
            array( 
                "Dirpath"   => 'packages',        # the base directory in the distribution zip file for the plugin
                "Name"      => 'plg_example1',    # the name of the subfolder containing the sub-extention to install 
                "Install"   => 1,                 # 1 = install, <> 1 = ignore
                "published" => 1),                # 1 = enable the plugin, once installed, <> 1 = ignore
            ...
            ),
            'Moludes' => array (
            array( 
                "Dirpath"   => 'packages',        # relative base directory in the distribution zip file
                "Name"      => 'mod_example3',    # the name of the folder containing the sub-extention to install 
                "Install"   => 1,                 # 1 = install, <> 1 = ignore
                "published" => 1),                # 1 = enable the plugin, once installed, <> 1 = ignore
            ...
            )   
        );
    */
    private $subExtensionInstallMap = array (
        "Plugins" => array ( 
            array(  "Dirpath"   => 'packages', 
                    "Name"      => 'plg_system_jecs',
                    "Install"   => 1, 
                    "published" => 1), 
            )
        );

    /**
     * Container for component  information
     * 
     * @var array   component
     */
    private $component = array(
        'Name'              => 'com_jecs'
    );
    /**
     *  Class Constructor
     */
    function __construct()  {
        $this->app = JFactory::getApplication();
    }

    /**
     * Trigger function - before installer installation
     * @param   type        type    
     * @param   \stdClass   parent
     * @return  bool
     */
    public function preflight($type, $parent)    {
        #JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JECS_COMPONENT_PREFLIGHT_START', 
        $this->app->enqueueMessage("»Component update: Begin Preflight verification for component ".
                $parent->get('manifest')->name. ' '. $parent->get('manifest')->version, 
                'Notice');

        // Check Joomla and PHP minimum required version
        if (!$this->checkMinRequiredVersion("joomla"))    {
            $this->uninstallInstaller();
            return false;
        }
        if (!$this->checkMinRequiredVersion("php"))       {
            $this->uninstallInstaller();
            return false;
        }
        if (!$this->checkMinRequiredVersion("mysql"))       {
            $this->uninstallInstaller();
            return false;
        }
        #JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JECS_COMPONENT_PREFLIGHT_END',
        $this->app->enqueueMessage("»Component update: End Preflight verification for component ".
                $parent->get('manifest')->name. ' '. $parent->get('manifest')->version, 
                'Notice');
        // To prevent XML not found error
        # $this->createExtensionRoot();     // No longer needed

        return true;
    }

    /**
     * Trigger function  - after installer installation
     * @param   object            route    
     * @param   JAdapterInstance  adapter  
     * @return  void
     */
    public function postflight($type, $parent)  {
        #JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JECS_COMPONENT_POSTFLIGHT_START', 
        $this->app->enqueueMessage("»Component update: Begin Postflight verification for component ".
                $parent->get('manifest')->name . ' '. $parent->get('manifest')->version, 
                'Notice');
        
        // Do something useful here!
        
        #JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JECS_COMPONENT_POSTFLIGHT_END', 
        $this->app->enqueueMessage("»Component update: End Postflight verification completed for component ".
                $parent->get('manifest')->name. ' '. $parent->get('manifest')->version, 
                'Notice');
    }
    
    /**
     * This method is called after a component is installed.
     * @param  \stdClass $parent - Parent object calling this method.
     * @return void
     */
    public function install($parent)    {
        $this->app->enqueueMessage("-»Component install: Begin installation of sub-extensions...", 'Notice');
        $this->installSubExtentions($parent);     // go ahead installing all needed sub extensions
        $this->fixInstalledSubExensions();
        $this->app->enqueueMessage("-»Component install: End installation of sub-extensions!", 'Notice');
        return;
    }
    
    /**
     * This method is called after a component is uninstalled.
     * @param  \stdClass $parent - Parent object calling this method.
     * @return void
     */
    public function uninstall ($parent) {
        #$this->app->enqueueMessage("»Component uninstall procedure started for: ".
        #        $parent->get('manifest')->name.' '.$parent->get('manifest')->version, 
        #        'Notice');
        $this->app->enqueueMessage("»Component <b>".
                $parent->get('manifest')->name.' '.$parent->get('manifest')->version.
                "</b> successfully uninstalled. ", 
                'Notice');


        // Uninstall all dependent sub-extensions
        $this->uninstallSubExtentions($parent);

        $this->app->enqueueMessage("»Component uninstall procedure terminated for: ".
                $parent->get('manifest')->name.' '.$parent->get('manifest')->version, 
                'Notice');
        $this->app->enqueueMessage("", 'Notice');
        return;
    }

    /**
     * This method is called after a component is updated.
     * @param  \stdClass $parent - Parent object calling object.
     * @return void
     */
    public function update($parent)     {
        #JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JECS_COMPONENT_UPDATE_START',
        $this->app->enqueueMessage("»Component update procedure started for: ".
                $parent->get('manifest')->name.' '.$parent->get('manifest')->version,
                'Notice');
        
        // update (i.e.install over) all needed sub-extensions 
        $this->installSubExtentions($parent);     // go ahead installing all needed sub extensions

        #JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JECS_COMPONENT_UPDATE_END',
        $this->app->enqueueMessage("»Component update procedure terminated for: ".
                $parent->get('manifest')->name.' '.$parent->get('manifest')->version,
                'Notice');
        return;
    }
    
    public function installSubExtentions($parent)    {
        /*
         * Following the code excerpt of how to install an extension within Joomla
         * # $path = $installer->getPath('source');
         * # $plugin_dir = $path.'/my_plugin_dir';
         * # $plugin_installer = new JInstaller();
         * # $plugin_installer->install($plugin_dir);
         */
        //$this->app->enqueueMessage("-»Component install: Begin installation of sub-extensions...", 'Notice');
        $package_folders = JFolder::folders(__DIR__ . '/packages' . DS, 
                $filter='', 
                $recurse=false, 
                $full=false, 
                $exclude=array('.svn','CVS','.DS_Store','__MACOSX', 
                    $excludefilter=array('^\..*'))
                );
        $packages = array_diff($package_folders, array($this->component['Name']));  #'com_jecs'));
        while ($extensionType = current($this->subExtensionInstallMap)){
            if (key($this->subExtensionInstallMap) == 'Plugins') {
                foreach ($extensionType as $value) {
                    $src = $parent->getParent()->getPath('source');
                    $packagePath = $src . DS . $value['Dirpath'] . DS . $value['Name'];    #$package;
                    $installer = new JInstaller; 
                    $installRes = $installer->install($packagePath);
                    if ($installRes)    {
                        $this->app->enqueueMessage("--»Component install: Installation of sub-extensions successful for <b>".$value['Name']."</b>", 'Notice');
                    }else {
                        $this->app->enqueueMessage("--»Component install: Error while installing additional package extention for <b>".$value['Name']."</b>", 'Error');
                    }
                    echo "SOURCE=$src | PACKAGEPATH=$packagePath | VALUE[DIRPATH]={$value['Dirpath']}";
                }
            }
            if (key($this->subExtensionInstallMap) == 'Plugins') {
                $a = 1;
            }
            next($this->subExtensionInstallMap);
        }
        /*
        //$installer = new JInstaller; 
        $package_folders = JFolder::folders(__DIR__ . '/packages' . DS, 
                $filter='', $recurse=false, $full=false, $exclude=array('.svn','CVS','.DS_Store','__MACOSX', $excludefilter=array('^\..*')));
        $packages = array_diff($package_folders, array('com_jecs'));
        foreach ($packages as $package) {
            $src = $parent->getParent()->getPath('source');
            $packagePath = $src . DS . 'packages' . DS . $package;
            $installer = new JInstaller; 
            $installRes = $installer->install($packagePath);
            if ($installRes)    {
                $this->app->enqueueMessage("--»Component install: Installation of sub-extensions successful for <b>".$package."</b>", 'Notice');
            } else {
                $this->app->enqueueMessage("--»Component install: Error while installing additional package extention for <b>".$package."</b>", 'Notice');
            }
        }
        //$this->app->enqueueMessage("-»Component install: End installation of sub-extensions!", 'Notice');
        */
        return;
    }
    
    /**
     * 
     * @param type $parent
     * @return type
     */
    private function uninstallSubExtentions($parent)    {
        while ($extensionType = current($this->subExtensionUninstallMap))  {
            if (key($this->subExtensionUninstallMap) == 'Plugins') {
                foreach ($extensionType as $value) {
                    $id = $this->queryJoomlaDbPlugin($value['Element'], $value['Folder']);
                    if ($id)   {
                        $installer = new JInstaller;
                        $result = $installer->uninstall('plugin', $id, 1);
                        #$status->plugins[] = array('name' => 'plg_' . $plugin,'group' => $folder,'result' => $result);
                    }
                }
            }
            if (key($this->subExtensionUninstallMap) == 'Moules') {
                foreach ($extensionType as $value) {
                    $id = $this->queryJoomlaDbModules($value['Element']);
                    if ($id)   {
                        $installer = new JInstaller;
                        $result = $installer->uninstall('module', $id, 1);
                        #$status->plugins[] = array('name' => 'mod_' . $module,'group' => $folder,'result' => $result);
                    }
                }
            }
            next($this->subExtensionUninstallMap);
        }
        return;
    }

    private function queryJoomlaDbPlugin($pluginElement, $pluginFolder)  {
        $db = JFactory::getDBO();
        $sql = $db->getQuery(true)
            ->select($db->qn('extension_id'))
            ->from($db->qn('#__extensions'))
            ->where($db->qn('type')    . ' = ' . $db->q('plugin'))
            ->where($db->qn('element') . ' = ' . $db->q($pluginElement))
            ->where($db->qn('folder')  . ' = ' . $db->q($pluginFolder));
        $db->setQuery($sql);
        $id = $db->loadResult();
        return $id;
    }
    
    private function queryJoomlaDbModules($moduleElement)  {
         $db = JFactory::getDBO();
        $sql = $db->getQuery(true)
            ->select($db->qn('extension_id'))
            ->from($db->qn('#__extensions'))
            ->where($db->qn('type')    . ' = ' . $db->q('module'))
            ->where($db->qn('element') . ' = ' . $db->q($$moduleElement));
        $db->setQuery($sql);
        $id = $db->loadResult();
        return $id;       
    }

    /**
     * Check minimum requirements
     * @param type $type - What to check (i.e. 'joomla', 'php'
     * @return boolean
     */
    private function checkMinRequiredVersion($type = "joomla")    {
        switch ($type)  {
            case 'joomla':
                if (version_compare(JVERSION, $this->min_version_joomla, '<'))  {
                    $this->app->enqueueMessage("--»Incompatible Joomla! version",
                        #JText::sprintf('COM_JECS_COMPONENT_COMPATIBLE_JOOMLA', JVERSION, $this->min_version_joomla),
                        'error'
                    );
                    return false;
                }
                break;
            case 'php':
                if (version_compare(PHP_VERSION, $this->min_version_php, 'l'))  {
                    $this->app->enqueueMessage("--»Incompatible PHP version",
                        'error'
                    );
                    return false;
                }
                break;
            case 'mysql':
                if (version_compare(PHP_VERSION, $this->min_version_mysql, 'l'))  {
                    $this->app->enqueueMessage("--»Incompatible MySql version",
                        'error'
                    );
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * WARNING! This will be implemented in some future release.
     * Take all needed actions on installed sub-extensions
     * @return void
     */
    private function fixInstalledSubExensions()   {
        return;
    }
}
    /* === End-of-file === */
?>

