<?php
/**
 * NOTE: As this is non-extension based plugin, we only allow to populate / drop data of single plugin's instance if it is network-enabled
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
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Cache\StaticSession;
use ExpandableFAQ\Models\Status\SingleStatus;
use ExpandableFAQ\Models\Update\SinglePatchesObserver;
use ExpandableFAQ\Models\Update\SingleUpdatesObserver;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Views\PageView;

final class SingleController
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
     * Activate (enable+install or enable only) plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     * @throws \Exception
     */
    public function processPopulateData()
    {

        // Set defaults
        $completed = TRUE;

        // NOTE: As this is non-extensions based plugin, we only allow to populate data of single plugin's instance if it is network-enabled
        if($this->conf->isNetworkEnabled())
        {
            // Create mandatory instances
            $objStatus = new SingleStatus($this->conf, $this->lang, $this->conf->getBlogId());

            // We only allow to populate the data if the newest plugin database struct exists
            if ($objStatus->checkPluginDB_StructExistsOf($this->conf->getPluginSemver()))
            {
                $objInstaller = new InstallController($this->conf, $this->lang, $this->conf->getBlogId());

                // Populate the data (without table creation)
                // INFO: This plugin do not use custom roles
                $objInstaller->setCustomCapabilities();
                // INFO: This plugin do not use REST API
                // INFO: This plugin do not use custom post types
                $objInstaller->setContent();
                $objInstaller->replaceResettableContent();
                $objInstaller->registerAllForTranslation();
            } else
            {
                $completed = FALSE;
            }
        } else
        {
            $completed = FALSE;
        }

        if($completed === FALSE)
        {
            // Failed
            wp_safe_redirect(admin_url('plugins.php'));
        } else
        {
            // Completed
            wp_safe_redirect(admin_url('plugins.php?completed=1'));
        }
        exit;
    }

    /**
     * Note: for data drop, we do not drop the roles, to protect from issues to happen on other extensions
     */
    public function processDropData()
    {
        // Set defaults
        $completed = TRUE;

        // NOTE: As this is non-extensions based plugin, we only allow to drop data of single plugin's instance if it is network-enabled
        if($this->conf->isNetworkEnabled())
        {
            // Delete any old table content if exists
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $this->conf->getBlogId());
                    if(method_exists($objTable, 'deleteContent') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->deleteContent();
                        StaticSession::cacheHTML_Array('admin_debug_html', $objTable->getDebugMessages());
                        // We don't process okay messages here
                        StaticSession::cacheValueArray('admin_error_message', $objTable->getErrorMessages());
                    } else
                    {
                        $completed = FALSE;
                    }
                } else
                {
                    $completed = FALSE;
                }
            }
        } else
        {
            $completed = FALSE;
        }

        // Delete any old WP posts if exists
        // INFO: NOTHING for plugin - it does not use any custom post types
        // NOTE: To void a errors on WordPress page deletion error, we skip exception raising for them

        if($completed === FALSE)
        {
            // Failed
            wp_safe_redirect(admin_url('plugins.php'));
        } else
        {
            // Completed
            wp_safe_redirect(admin_url('plugins.php?completed=1'));
        }
        exit;
    }

    /**
     * @throws \Exception
     */
    private function processUpdate()
    {
        // Create mandatory instances
        $objStatus = new SingleStatus($this->conf, $this->lang, $this->conf->getBlogId());
        $objUpdatesObserver = new SingleUpdatesObserver($this->conf, $this->lang);
        $objPatchesObserver = new SinglePatchesObserver($this->conf, $this->lang);

        // Allow only one update at-a-time per site refresh. We need that to save resources of server to not to get to timeout phase
        $semverUpdated = FALSE;
        $latestSemver = $this->conf->getPluginSemver();

        // ----------------------------------------
        // NOTE: PLACE FOR UPDATE CODE
        // ----------------------------------------

        if($this->conf->isNetworkEnabled() === FALSE)
        {
            $currentPluginSemverInDatabase = $objStatus->getPluginSemverInDatabase();
            if(version_compare($currentPluginSemverInDatabase, '6.0.2', '=='))
            {
                $semverUpdated = $objUpdatesObserver->do602_UpdateTo610();
            } else if(version_compare($currentPluginSemverInDatabase, $latestSemver, '=='))
            {
                // It's a last version
                $semverUpdated = TRUE;
            }

            // Run patches
            // NOTE: Is import here to get plugin semver once again, to make sure we have up to date data
            $updatedPluginSemverInDatabase = $objStatus->getPluginSemverInDatabase();
            if(version_compare($updatedPluginSemverInDatabase, '6.0.0', '>=') && version_compare($updatedPluginSemverInDatabase, '6.1.0', '<'))
            {
                // Run 6.0.Z patches
                $semverUpdated = $objPatchesObserver->doPatch(6, 0);
            } else if(version_compare($updatedPluginSemverInDatabase, '6.1.0', '>=') && version_compare($updatedPluginSemverInDatabase, '6.2.0', '<'))
            {
                // Run 6.1.Z patches
                $semverUpdated = $objPatchesObserver->doPatch(6, 1);
            }

            // Cache update messages
            StaticSession::cacheHTML_Array('admin_debug_html', $objUpdatesObserver->getSavedDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objUpdatesObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objUpdatesObserver->getSavedErrorMessages());

            // Cache patch messages
            StaticSession::cacheHTML_Array('admin_debug_html', $objPatchesObserver->getSavedDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objPatchesObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objPatchesObserver->getSavedErrorMessages());
        }

        // Check if plugin is up-to-date
        $pluginUpToDate = $objStatus->isPluginDataUpToDateInDatabase();

        if($semverUpdated === FALSE || $pluginUpToDate === FALSE)
        {
            // Failed or if there is more updates to go
            wp_safe_redirect('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status&tab=status');
        } else
        {
            // Completed
            wp_safe_redirect('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status&tab=status&completed=1');
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
        $ksesedDebugHTML = StaticValidator::inWP_Debug() ? StaticSession::getKsesedHTML_Once('admin_debug_html') : '';
        $errorMessage = StaticSession::getValueOnce('admin_error_message');
        $okayMessage = StaticSession::getValueOnce('admin_okay_message');

        // Both - _POST and _GET supported
        if(isset($_GET['populate_data']) || isset($_POST['populate_data'])) { $this->processPopulateData(); }
        if(isset($_GET['drop_data']) || isset($_POST['drop_data'])) { $this->processDropData(); }
        if(isset($_GET['update']) || isset($_POST['update'])) { $this->processUpdate(); }

        // Create mandatory instances
        $objStatus = new SingleStatus($this->conf, $this->lang, $this->conf->getBlogId());

        // Create view
        $objView = new PageView();

        // 1. Set the view variables - Tabs
        $objView->tabs = StaticFormatter::getTabParams(array('status'), 'status', isset($_GET['tab']) ? $_GET['tab'] : '');

        // 2. Set the view variables - other
        $objView->staticURLs = $this->conf->getRouting()->getFolderURLs();
        $objView->lang = $this->lang->getAll();
        $objView->ksesedDebugHTML = $ksesedDebugHTML;
        $objView->errorMessage = $errorMessage;
        $objView->okayMessage = $okayMessage;
        $objView->statusTabFormAction = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status&noheader=true');
        $objView->networkEnabled = $this->conf->isNetworkEnabled();
        $objView->goToNetworkAdmin = $this->conf->isNetworkEnabled() ? TRUE : FALSE;
        $objView->updateExists = $objStatus->checkPluginUpdateExists();
        $objView->updateAvailable = $objStatus->canUpdatePluginDataInDatabase();
        $objView->majorUpgradeAvailable = $objStatus->canMajorlyUpgradePluginDataInDatabase();
        $objView->canUpdate = $objStatus->canUpdatePluginDataInDatabase();
        $objView->canMajorlyUpgrade = $objStatus->canMajorlyUpgradePluginDataInDatabase();
        $objView->databaseMatchesCodeSemver = $objStatus->isPluginDataUpToDateInDatabase();
        $objView->databaseSemver = $objStatus->getPluginSemverInDatabase();
        $objView->newestExistingSemver = $this->conf->getPluginSemver();
        $objView->newestSemverAvailable = $this->conf->getPluginSemver();

        // Print the template
        $templateRelPathAndFileName = 'Status'.DIRECTORY_SEPARATOR.'SingleTabs.php';
        echo $objView->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
