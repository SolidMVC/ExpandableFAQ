<?php
/**
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin\Settings;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Formatting\StaticFormatter;
use ExpandableFAQ\Models\Country\CountriesObserver;
use ExpandableFAQ\Models\Notification\PhoneNotificationsObserver;
use ExpandableFAQ\Models\Style\StylesObserver;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Controllers\Admin\AbstractController;

final class SettingsController extends AbstractController
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
        // Tab - global settings
        $objStylesObserver = new StylesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $this->view->globalSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'change-global-settings&noheader=true');
        $this->view->systemStylesDropdownOptions = $objStylesObserver->getDropdownOptions($this->dbSets->get('conf_system_style'));
        $this->view->arrGlobalSettings = (new ChangeGlobalSettingsController($this->conf, $this->lang))->getSettings();

        // Get the tab values
        $tabs = StaticFormatter::getTabParams(array(
            'global-settings'
        ), 'global-settings', isset($_GET['tab']) ? $_GET['tab'] : '');


        // Set the view variables - Tab settings
        $this->view->globalSettingsTabChecked = !empty($tabs['global-settings']) ? ' checked="checked"' : '';


        // Print the template
        $templateRelPathAndFileName = 'Settings'.DIRECTORY_SEPARATOR.'Tabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
