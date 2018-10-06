<?php
/**
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin\FAQ;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\FAQ\FAQ;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Controllers\Admin\AbstractController;
use ExpandableFAQ\Models\Cache\StaticSession;

final class AddEditFAQ_Controller extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramFAQId)
    {
        $objFAQ = new FAQ($this->conf, $this->lang, $this->dbSets->getAll(), $paramFAQId);
        $objFAQ->delete();

        StaticSession::cacheHTMLArray('admin_debug_message', $objFAQ->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objFAQ->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objFAQ->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getPluginURL_Prefix().'faq-manager&tab=faqs');
        exit;
    }

    private function processSave($paramFAQ_Id)
    {
        // Create mandatory instances
        $objFAQ = new FAQ($this->conf, $this->lang, $this->dbSets->getAll(), $paramFAQ_Id);

        $saved = $objFAQ->save($_POST);
        if($saved && $this->lang->canTranslateSQL())
        {
            $objFAQ->registerForTranslation();
        }

        StaticSession::cacheHTMLArray('admin_debug_message', $objFAQ->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objFAQ->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objFAQ->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getPluginURL_Prefix().'faq-manager&tab=faqs');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_GET['delete_faq'])) { $this->processDelete($_GET['delete_faq']); }
        if(isset($_POST['save_faq'], $_POST['faq_id'])) { $this->processSave($_POST['faq_id']); }

        $paramFAQId = isset($_GET['faq_id']) ? $_GET['faq_id'] : 0;
        $objFAQ = new FAQ($this->conf, $this->lang, $this->dbSets->getAll(), $paramFAQId);
        $localDetails = $objFAQ->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'faq-manager&tab=faqs');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'add-edit-faq&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->faqId = $localDetails['faq_id'];
            $this->view->faqQuestion = $localDetails['edit_faq_question'];
            $this->view->faqAnswer = $localDetails['edit_faq_answer'];
            $this->view->faqOrder = $localDetails['faq_order'];
        } else
        {
            $this->view->faqId = 0;
            $this->view->faqQuestion = '';
            $this->view->faqAnswer = '';
            $this->view->faqOrder = '';
        }

        // Print the template
        $templateRelPathAndFileName = 'FAQ'.DIRECTORY_SEPARATOR.'AddEditFAQ_Form.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
