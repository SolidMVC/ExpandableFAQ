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
                        StaticSession::cacheHTMLArray('admin_debug_message', $objTable->getDebugMessages());
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
        $pluginSemverInDatabase = $objStatus->getPluginSemverInDatabase();
        $latestSemver = $this->conf->getPluginSemver();

        // ----------------------------------------
        // NOTE: PLACE FOR UPDATE CODE
        // ----------------------------------------

        if($this->conf->isNetworkEnabled() === FALSE)
        {
            if(version_compare($pluginSemverInDatabase, $latestSemver, '=='))
            {
                // It's a last version
                $semverUpdated = TRUE;
            }

            // Run 6.0.Z patches
            if(version_compare($pluginSemverInDatabase, '6.0.0', '>=') && version_compare($pluginSemverInDatabase, '6.1.0', '<'))
            {
                $semverUpdated = $objPatchesObserver->doPatch(6, 0);
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
        $printDebugMessage = StaticValidator::inWP_Debug() ? StaticSession::getHTMLOnce('admin_debug_message') : '';
        $printErrorMessage = StaticSession::getValueOnce('admin_error_message');
        $printOkayMessage = StaticSession::getValueOnce('admin_okay_message');

        // Both - _POST and _GET supported
        if(isset($_GET['populate_data']) || isset($_POST['populate_data'])) { $this->processPopulateData(); }
        if(isset($_GET['drop_data']) || isset($_POST['drop_data'])) { $this->processDropData(); }
        if(isset($_GET['update']) || isset($_POST['update'])) { $this->processUpdate(); }

        // Create mandatory instances
        $objStatus = new SingleStatus($this->conf, $this->lang, $this->conf->getBlogId());

        // Get the tab values
        $tabs = StaticFormatter::getTabParams(array('status'), 'status', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Create view
        $objView = new PageView();

        // 1. Set the view variables - Tab settings
        $objView->statusTabChecked = !empty($tabs['status']) ? ' checked="checked"' : '';

        // 2. Set the view variables - other
        $objView->staticURLs = $this->conf->getRouting()->getFolderURLs();
        $objView->lang = $this->lang->getAll();
        $objView->debugMessage = $printDebugMessage;
        $objView->errorMessage = $printErrorMessage;
        $objView->okayMessage = $printOkayMessage;
        $objView->statusTabFormAction = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status&noheader=true');
        $objView->isNetworkEnabled = $this->conf->isNetworkEnabled();
        $objView->networkEnabled = $this->conf->isNetworkEnabled() ? $this->lang->getPrint('LANG_YES_TEXT') : $this->lang->getPrint('LANG_NO_TEXT');
        $objView->goToNetworkAdmin = $this->conf->isNetworkEnabled() ? TRUE : FALSE;
        $objView->updateExists = $objStatus->checkPluginUpdateExists();
        $objView->updateAvailable = $objStatus->canUpdatePluginDataInDatabase();
        $objView->majorUpgradeAvailable = $objStatus->canMajorlyUpgradePluginDataInDatabase();
        $objView->canUpdate = $objStatus->canUpdatePluginDataInDatabase();
        $objView->canMajorlyUpgrade = $objStatus->canMajorlyUpgradePluginDataInDatabase();
        $objView->databaseMatchesCodeSemver = $objStatus->isPluginDataUpToDateInDatabase();
        $objView->databaseSemver = $objStatus->getPrintPluginSemverInDatabase();
        $objView->newestExistingSemver = $this->conf->getPrintPluginSemver();
        $objView->newestSemverAvailable = $this->conf->getPrintPluginSemver();

        // Print the template
        $templateRelPathAndFileName = 'Status'.DIRECTORY_SEPARATOR.'SingleTabs.php';
        echo $objView->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
