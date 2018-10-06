<?php
/**
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Front;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Settings\SettingsObserver;
use ExpandableFAQ\Views\PageView;

abstract class AbstractController
{
    protected $conf         = NULL;
    protected $lang 	    = NULL;
    protected $view 	    = NULL;
    protected $dbSets	    = NULL;

    // Limitation parameters
    protected $expandedFAQ = -1;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitation will kill the system speed
        $this->lang = $paramLang;
        // Set database settings
        $this->dbSets = new SettingsObserver($this->conf, $this->lang);
        $this->dbSets->setAll();

        // Initialize the page view and set it's conf and lang objects
        $this->view = new PageView();
        $this->view->staticURLs = $this->conf->getRouting()->getFolderURLs();
        $this->view->lang = $this->lang->getAll();
        $this->view->settings = $this->dbSets->getAll();

        //print_r($paramArrLimits);

        // Get shortcode parameter values for <PARAMETER>=X
        // Plus add support for passing <PARAMETER>=X parameter via URL, but only if it is NOT provided via shortcode
        // NOTE: We don't use a plugin prefix here, as this is a wide-use plugin

        if(isset($paramArrLimits['expanded_faq']))
        {
            $this->expandedFAQ = StaticValidator::getValidInteger($paramArrLimits['expanded_faq'], -1);
        } else if(isset($_GET['expanded_faq']))
        {
            $this->expandedFAQ = StaticValidator::getValidInteger($_GET['expanded_faq'], -1);
        }
    }

    /**
     * @param string $paramTemplateFolder
     * @param string $paramTemplateName
     * @param string $paramTemplateLayout (empty layout is supported)
     * @return string
     * @throws \Exception
     */
    protected function getTemplate($paramTemplateFolder, $paramTemplateName, $paramTemplateLayout)
    {
        $validTemplateFolder = '';
        $validTemplateName = '';
        if(!is_array($paramTemplateFolder) && $paramTemplateFolder != '')
        {
            $validTemplateFolder = preg_replace('[^0-9a-zA-Z]', '', $paramTemplateFolder).DIRECTORY_SEPARATOR; // No sanitization, uppercase needed
        }
        if(!is_array($paramTemplateName) && $paramTemplateName != '')
        {
            $validTemplateName = preg_replace('[^0-9a-zA-Z]', '', $paramTemplateName); // No sanitization, uppercase needed
        }

        $validTemplateLayout = '';
        if(in_array($paramTemplateLayout, array(
            '',
            'Slider', 'List', 'Grid', 'Table', 'Tabs',
            'Slider1', 'List1', 'Grid1', 'Table1', 'Tabs1',
            'Slider2', 'List2', 'Grid2', 'Table2', 'Tabs2',
            'Slider3', 'List3', 'Grid3', 'Table3', 'Tabs3',
            'Slider4', 'List4', 'Grid4', 'Table4', 'Tabs4',
        )))
        {
            $validTemplateLayout = $paramTemplateLayout;
        }
        $templateRelPathAndFileName = $validTemplateFolder.$validTemplateName.$validTemplateLayout.'.php';
        $retTemplate = $this->view->render($this->conf->getRouting()->getFrontTemplatesPath($templateRelPathAndFileName));

        return $retTemplate;
    }

}