<?php
/**
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Cache\StaticSession;
use ExpandableFAQ\Models\Settings\SettingsObserver;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Views\PageView;

abstract class AbstractController
{
    protected $conf         = NULL;
    protected $lang 	    = NULL;
    protected $view 	    = NULL;
    protected $dbSets	    = NULL;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set database settings
        $this->dbSets = new SettingsObserver($this->conf, $this->lang);
        $this->dbSets->setAll();

        // Message handler - should always be at the begging of method
        $ksesedDebugHTML = StaticValidator::inWP_Debug() ? StaticSession::getKsesedHTML_Once('admin_debug_html') : '';
        $errorMessage = StaticSession::getValueOnce('admin_error_message');
        $okayMessage = StaticSession::getValueOnce('admin_okay_message');

        // Initialize the page view and set it's conf and lang objects
        $this->view = new PageView();
        $this->view->staticURLs = $this->conf->getRouting()->getFolderURLs();
        $this->view->lang = $this->lang->getAll();
        $this->view->settings = $this->dbSets->getAll();
        $this->view->ksesedDebugHTML = $ksesedDebugHTML;
        $this->view->errorMessage = $errorMessage;
        $this->view->okayMessage = $okayMessage;
    }
}