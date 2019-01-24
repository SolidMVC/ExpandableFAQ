<?php
/**
 * NOTE: As this is non-extension based plugin, there is no data network-populate / network-drop data links if the plugin is network-enabled
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin\Status;
use ExpandableFAQ\Controllers\Admin\InstallController;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Formatting\StaticFormatter;
use ExpandableFAQ\Models\Install\Install;
use ExpandableFAQ\Models\Language\Language;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Cache\StaticSession;
use ExpandableFAQ\Models\Status\NetworkStatus;
use ExpandableFAQ\Models\Update\NetworkPatchesObserver;
use ExpandableFAQ\Models\Update\NetworkUpdatesObserver;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Views\PageView;

final class NetworkController
{
    protected $conf         = NULL;
    protected $lang 	    = NULL;
    protected $view 	    = NULL;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * @throws \Exception
     */
    private function processUpdate()
    {
        // Create mandatory instances
        $objStatus = new NetworkStatus($this->conf, $this->lang);
        $objUpdatesObserver = new NetworkUpdatesObserver($this->conf, $this->lang);
        $objPatchesObserver = new NetworkPatchesObserver($this->conf, $this->lang);

        // Allow only one update at-a-time per site refresh. We need that to save resources of server to not to get to timeout phase
        $allUpdatableSitesSemverUpdated = FALSE;
        $minPluginSemverInDatabase = $objStatus->getMinPluginSemverInDatabase();
        $maxPluginSemverInDatabase = $objStatus->getMaxPluginSemverInDatabase();
        $latestSemver = $this->conf->getPluginSemver();

        // ----------------------------------------
        // NOTE: A PLACE FOR UPDATE CODE
        // ----------------------------------------

        if($this->conf->isNetworkEnabled())
        {
            if(version_compare($minPluginSemverInDatabase, $latestSemver, '=='))
            {
                // It's a last version
                $allUpdatableSitesSemverUpdated = TRUE;
            }

            // Process 6.0.Z patches
            if(version_compare($minPluginSemverInDatabase, '6.0.0', '>=') && version_compare($maxPluginSemverInDatabase, '6.1.0', '<'))
            {
                $allUpdatableSitesSemverUpdated = $objPatchesObserver->doPatch(6, 0);
            }

            // Cache update messages
            StaticSession::cacheHTMLArray('admin_debug_message', $objUpdatesObserver->getSavedDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objUpdatesObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objUpdatesObserver->getSavedErrorMessages());

            // Cache patch messages
            StaticSession::cacheHTMLArray('admin_debug_message', $objPatchesObserver->getSavedDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objPatchesObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objPatchesObserver->getSavedErrorMessages());
        }

        // Check if plugin is up-to-date
        $pluginUpToDate = $objStatus->isAllBlogsWithPluginDataUpToDate();

        if($allUpdatableSitesSemverUpdated === FALSE || $pluginUpToDate === FALSE)
        {
            // Failed or if there is more updates to go
            wp_safe_redirect('admin.php?page='.$this->conf->getPluginURL_Prefix().'network-status&tab=status');
        } else
        {
            // Completed
            wp_safe_redirect('admin.php?page='.$this->conf->getPluginURL_Prefix().'network-status&tab=status&completed=1');
        }
        exit;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function printContent()
    {
        // Message handler - should always be at the begging of method (in the very first line)
        $printDebugMessage = StaticValidator::inWP_Debug() ? StaticSession::getHTMLOnce('admin_debug_message') : '';
        $printErrorMessage = StaticSession::getValueOnce('admin_error_message');
        $printOkayMessage = StaticSession::getValueOnce('admin_okay_message');

        // Both - _POST and _GET supported
        if(isset($_GET['update']) || isset($_POST['update'])) { $this->processUpdate(); }

        // Create mandatory instances
        $objStatus = new NetworkStatus($this->conf, $this->lang);

        // Get the tab values
        $tabs = StaticFormatter::getTabParams(array('status', 'license'), 'status', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Create view
        $objView = new PageView();

        // 1. Set the view variables - Tab settings
        $objView->statusTabChecked = !empty($tabs['status']) ? ' checked="checked"' : '';
        $objView->licenseTabChecked = !empty($tabs['license']) ? ' checked="checked"' : '';

        // 2. Set the view variables - other
        $objView->staticURLs = $this->conf->getRouting()->getFolderURLs();
        $objView->lang = $this->lang->getAll();
        $objView->debugMessage = $printDebugMessage;
        $objView->errorMessage = $printErrorMessage;
        $objView->okayMessage = $printOkayMessage;
        $objView->statusTabFormAction = network_admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'network-status&noheader=true');
        $objView->isNetworkEnabled = TRUE;
        $objView->networkEnabled = $this->lang->getPrint('LANG_YES_TEXT');
        $objView->goToNetworkAdmin = FALSE;
        $objView->updateExists = $objStatus->checkPluginUpdateExistsForSomeBlog();
        $objView->updateAvailable = $objStatus->canUpdatePluginDataInSomeBlog();
        $objView->majorUpgradeAvailable = $objStatus->canMajorlyUpgradePluginDataInSomeBlog();
        $objView->canUpdate = $objStatus->canUpdatePluginDataInSomeBlog();
        $objView->canMajorlyUpgrade = $objStatus->canMajorlyUpgradePluginDataInSomeBlog();
        $objView->databaseMatchesCodeSemver = $objStatus->isAllBlogsWithPluginDataUpToDate();
        $objView->databaseSemver = $objStatus->getPrintMinPluginSemverInDatabase();
        $objView->newestExistingSemver = $this->conf->getPrintPluginSemver();
        $objView->newestSemverAvailable = $this->conf->getPrintPluginSemver();

        // Print the template
        $templateRelPathAndFileName = 'Status'.DIRECTORY_SEPARATOR.'NetworkTabs.php';
        echo $objView->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
