<?php
/**
 * @package       Plugin System Jecs (Joomla! Extended Custom Script) for Joomla! 3.7
 * @author        Massimo Di Primio - http://www.diprimio.com
 * @copyright (C) 2006 - 2017 - Massimo Di Primio
 * @license       GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/

//-- No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
/**
 * Class plgSystemJecs ###
 */
class plgSystemJecs extends JPlugin   {

    private     $app;                         # @var object     - Joomla Global Application object
/*
    protected $autoloadLanguage = true;     # when true, language files is loaded automatically.
    protected $action = 0;                  # 0 = Unsuccessful login, 1 = Successfull login
    protected $_app;                        # 
    protected $_ip;                         # 
*/
    /*
     * Run the parent Constructor
     * so we do not forget or ignore anything that is run by jplugin 
     */
    public function __construct(&$subject, $params) {
        parent::__construct($subject, $params);
    }
    
    /**
     * Joomla trigger functions - onAfterRender()
     */
    function onAfterRender() {
        $this->app = JFactory::getApplication();
        
        // Get the Body of the HTML - 
        #$buffer = JResponse::getBody();     //$buffer = JResponse::getBody(); # have to do this twice to get the HTML ???
        $records = $this->allActiveRules();
        
        if(!$this->app->isAdmin() ) {
            foreach($records as $record)    {
                $this->parseRule($record);
            }
        }
    }
    
    /**
     * Parse one rule using arguments passed in the $rule array
     * @param array $rule - The row as returned by $db->loadAssocList()
     * @return boolean
     */
    function parseRule($rule) {
        $buffer = JResponse::getBody();     //$buffer = JResponse::getBody(); # Do I have to do this twice to get the HTML ???
            if (intval($rule['state']) != 1) {
                return;         # Not enabled? Skip this entry!
            }
            # Check to see if we are in the admin and if we should fire on backend
            //$tmpInt = intval($rule['side']);
            switch (intval($rule['side']))    {
                case 1:
                    if(!$this->app->isAdmin() ) { return;}
                    break;
                case 2:
                    if($this->app->isAdmin() ) { return;}
                    break;
                case 3:
                    break;
            }

            #
            # Alright man, this entry is enabled, go process it
            #
            # Verify whether to include 'inline' or 'file' 
            if (intval($rule['script_source']) == 0)	{
                # include the script as inline
                $jecs_code = $rule['script_inline'];
                if (intval($rule['script_minify']) == 1)   {
                    # minify the block of code (only js or css)
                    $jecs_code = $this->minifyScript($jecs_code, intval($rule['script_type']));
                }
            } else {
                # include the script html tag to load the file
                $jecsFilepath = $rule['file_path'];
                switch (intval($rule['script_type'])) {
                    case 0:					# javascript
                        $jecs_code = '<script type="application/javascript" src="'.$jecsFilepath.'"'.'></script>';
                        break;
                    case 1:					# stylesheet
                        /* $jecsCssMedia is for future release
                        if ($rule['script_css_media'] <> '') {
                            $jecsCssMedia =  ' media="'.$rule['script_css_media'].'"';
                        } else {
                            $jecsCssMedia =  ' media="all"';
                        }
                        */
                        $jecsCssMedia = "";
                        $jecs_code = '<link type="text/css" rel="stylesheet" href="'.$jecsFilepath.'"'.$jecsCssMedia.'>';#</script>';
                        break;
                    case 2:					# ld+json
                        $jecs_code = '<script type="application/json" src="'.$jecsFilepath.'"'.'></script>';
                        break;
                    case 3:					# ld+json
                        $jecs_code = '<script type="application/ld+json" src="'.$jecsFilepath.'"'.'></script>';
                        break;
                    case 4:					# Custom
                        $jecs_code = $jecsFilepath;
                        break;
                    default:
                        $jecs_code = "";	# For future releases
                }			
            }
            # Add Debug if needed
            if (intval($rule['debug']) == 1) {
                $jecs_code = "\n<!-- Jecs V.1.0.0 for Joomla. Debug->BEGIN for rule: [".$rule['rulename']."]:-->\n".
                            $jecs_code.
                             "\n<!-- Jecs V.1.0.0 for Joomla. Debug->END for rule: [".$rule['rulename']."]:-->\n";
            }
            
            # drop it in the chosen place
            switch (intval($rule['script_location'])) {
                case 0:			// immediately before "</head>"
                    $buffer = preg_replace ("/(<\/head(?!er).*>)/i", $jecs_code."$1", $buffer, 1);
                    break;
                case 1:			// immediately after "<body>"
                    $buffer = preg_replace ("/(<body.*?>)/is", "$1".$jecs_code, $buffer, 1);
                    break;
                case 2:			// immediately  before "</body>"
                    $buffer = preg_replace ("/(<\/body.*?>)/is", $jecs_code."$1", $buffer, 1);
                    break;
            }
        JResponse::setBody($buffer);
        return true;
    }

    /**
     * Minify on the fly only 'inline' javascript or stylesheet passd as string
     * @param   the string containing the script to minify
     * @param   an integer for the type (1 = JS, 2 = CSS)
     * @return  the minified script in a string
     * @since   1.0.0
     */
    function minifyScript($buf, $type) {
        if ($type == 1) {
            # Minify JS
            $buf = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buf);    // make it into one long line
            $buf = preg_replace('!\s+!', ' ', $buf);                                                // replace all multiple spaces by one space
            $buf = str_replace(array(' {',' }','{ ', '; '),array('{','}','{',';'), $buf);           // replace some unneeded spaces, modify as needed
        } else if ($type == 2) {
            # Minify CSS
            $buf = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buf);
            $buf = str_replace(': ', ':', $buf);
            $buf = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buf);
        }
        return $buf;
    }
    
    /**
     * Query the DB for 'rules' records filtered by a 'where' condition.
     * @param   A string containing the sql 'where' condition, if any.
     * @return  An indexed array of associated arrays from the table records returned by the query
     * @since   1.0.0
     */    
    protected function allActiveRules() {
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__jecs_rules WHERE state = 1 ORDER BY ordering";
        $db->setQuery($query);
        $results = $db->loadAssocList();       #$results = $db->loadObjectList();
        return $results;
    }
}
