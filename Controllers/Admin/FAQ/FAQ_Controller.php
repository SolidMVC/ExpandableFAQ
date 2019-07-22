<?php
/**
 * @package ExpandableFAQ
 * @note Variables prefixed with 'local' are not used in templates
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin\FAQ;
use ExpandableFAQ\Controllers\Admin\AbstractController;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\FAQ\FAQsObserver;
use ExpandableFAQ\Models\Formatting\StaticFormatter;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class FAQ_Controller extends AbstractController
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
        // Create mandatory instances
        $objFAQsObserver = new FAQsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(
            array('faqs'), 'faqs', isset($_GET['tab']) ? $_GET['tab'] : ''
        );

        // 2. Set the view variables - other variables
        $this->view->addNewFAQ_URL = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'add-edit-faq&amp;faq_id=0');
        $this->view->trustedAdminFAQ_ListHTML = $objFAQsObserver->getTrustedAdminListHTML();

        // Print the template
        $templateRelPathAndFileName = 'FAQ'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
