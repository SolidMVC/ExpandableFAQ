<?php
/**
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin\Demos;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Formatting\StaticFormatter;
use ExpandableFAQ\Models\Import\DemosObserver;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Controllers\Admin\AbstractController;

final class DemosController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Tab - import demo
        $objDemosObserver = new DemosObserver($this->conf, $this->lang);
        $this->view->importDemoTabFormAction = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'import-demo&noheader=true');
        $this->view->trustedDemosDropdownOptionsHTML = $objDemosObserver->getTrustedDropdownOptionsHTML(0, 0, $this->lang->getText('LANG_DEMO_SELECT_TEXT'));

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'demos'
        ), 'demos', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Print the template
        $templateRelPathAndFileName = 'Demos'.DIRECTORY_SEPARATOR.'Tabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
