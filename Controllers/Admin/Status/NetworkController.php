<?php
/**
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
        // Already sanitized before in it's constructor. Too much sanitation will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * Activate (enable+install or enable only) plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     * @throws \Exception
     */
    public function processPopulateData()
    {
        // Create mandatory instances
        $objNetworkStatus = new NetworkStatus($this->conf, $this->lang);

        // We only allow to populate the data if the newest plugin database struct exists
        if ($objNetworkStatus->checkPluginDBStructExists($this->conf->getPluginVersion()))
        {
            // Save original locale
            $orgLang = $this->lang;

            $sites = get_sites();
            foreach ($sites AS $site)
            {
                $blogId = $site->blog_id;
                switch_to_blog($blogId);

                $lang = new Language(
                    $this->conf->getTextDomain(), $this->conf->getGlobalLangPath(), $this->conf->getLocalLangPath(), $this->conf->getBlogLocale($blogId), FALSE
                );
                $objInstaller = new InstallController($this->conf, $lang, $blogId);

                // Populate the data (without table creation)
                // INFO: This plugin do not use custom roles
                $objInstaller->setCustomCapabilities();
                // INFO: This plugin do not use custom post types
                $objInstaller->setContent();
                $objInstaller->replaceResettableContent();
                $objInstaller->registerAllForTranslation();
            }
            // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
            switch_to_blog($this->conf->getBlogId());
            // Restore original locale
            $this->lang = $orgLang;
        }
    }

    /**
     * Note: for data drop, we do not drop the roles, to protect from issues to happen on other extensions
     */
    public function processDropData()
    {
        $sites = get_sites();
        foreach ($sites AS $site)
        {
            $blogId = $site->blog_id;
            switch_to_blog($blogId);

            // Delete any old table content if exists
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $blogId);
                    if(method_exists($objTable, 'deleteContent') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->deleteContent();
                        StaticSession::cacheHTMLArray('admin_debug_message', $objTable->getDebugMessages());
                        // We don't process okay messages here
                        StaticSession::cacheValueArray('admin_error_message', $objTable->getErrorMessages());
                    }
                }
            }
            // Delete any old WordPress posts if exists
            // INFO: NOTHING for plugin - it does not use any custom post types
            // NOTE: To void a errors on WordPress page deletion error, we skip exception raising for them
        }
    }

    /**
     * @throws \Exception
     */
    private function processUpdate()
    {
        // Create mandatory instances
        $objStatus = new NetworkStatus($this->conf, $this->lang);

        // Allow only one update at-a-time per site refresh. We need that to save resources of server to not to get to timeout phase
        $allUpdatableSitesUpdated = FALSE;
        $minPluginVersionInDatabase = $objStatus->getMinPluginVersionInDatabase();

        // -----------------------------------------------------------
        // A PLACE FOR UPDATE CODE
        // -----------------------------------------------------------

        if($this->conf->isNetworkEnabled() && $minPluginVersionInDatabase == 6.0)
        {
            // It's a last version
            $allUpdatableSitesUpdated = TRUE;
        }

        // Check if plugin is up-to-date
        $pluginUpToDate = $objStatus->isAllBlogsWithPluginDataUpToDate();

        if($allUpdatableSitesUpdated === FALSE || $pluginUpToDate === FALSE)
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
        $printDebugMessage = StaticValidator::inWPDebug() ? StaticSession::getHTMLOnce('admin_debug_message') : '';
        $printErrorMessage = StaticSession::getValueOnce('admin_error_message');
        $printOkayMessage = StaticSession::getValueOnce('admin_okay_message');

        // Both - _POST and _GET supported
        if(isset($_GET['populate_data']) || isset($_POST['populate_data'])) { $this->processPopulateData(); }
        if(isset($_GET['drop_data']) || isset($_POST['drop_data'])) { $this->processDropData(); }
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
        $objView->updateAvailable = $objStatus->canUpdatePluginDataInSomeBlog();
        $objView->majorUpgradeAvailable = $objStatus->canMajorlyUpgradePluginDataInSomeBlog();
        $objView->canUpdate = $objStatus->canUpdatePluginDataInSomeBlog();
        $objView->canMajorlyUpgrade = $objStatus->canMajorlyUpgradePluginDataInSomeBlog();
        $objView->databaseMatchesCodeVersion = $objStatus->isAllBlogsWithPluginDataUpToDate();
        $objView->databaseVersion = number_format_i18n($objStatus->getMinPluginVersionInDatabase(), 1);
        $objView->newestVersionAvailable = number_format_i18n($this->conf->getPluginVersion(), 1);

        // Print the template
        $templateRelPathAndFileName = 'Status'.DIRECTORY_SEPARATOR.'NetworkTabs.php';
        echo $objView->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
