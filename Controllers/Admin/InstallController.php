<?php
/**
 * Install controller to handle all install/network install and uninstall procedures
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Install\Install;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Language\LanguagesObserver;
use ExpandableFAQ\Models\Administrator\AdministratorRole;
use ExpandableFAQ\Models\Status\SingleStatus;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class InstallController
{
    private $conf 	                = NULL;
    private $lang 		            = NULL;
    private $blogId 	            = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->blogId = intval($paramBlogId);
    }

    /**
     * @throws \Exception
     */
    public function setTables()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);

        // @Note - even if this is multisite or 2nd or later extension install, tables will be created only once for the main site only (with blog_id = '0' or '1')
        if ($objSingleSiteStatus->checkPluginDB_StructExists($this->conf->getOldestCompatiblePluginSemver()) === FALSE)
        {
            // First - drop all tables if exists any to have a clean install as expected
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                    if(method_exists($objTable, 'drop') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->drop();
                        $this->processDebug($objTable->getDebugMessages());
                        $this->throwExceptionOnFailure($objTable->getErrorMessages());
                    }
                }
            }

            // Then - create all tables
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                    if(method_exists($objTable, 'create') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->create();
                        $this->processDebug($objTable->getDebugMessages());
                        $this->throwExceptionOnFailure($objTable->getErrorMessages());
                    }
                }
            }
        }
    }

    /**
     * Note: this method should called only on first activation of plugin in specific blog-id
     */
    public function setCustomCapabilities()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);
        $objAdministratorRole = new AdministratorRole($this->conf, $this->lang);

        // Note - the section below is extension-independent, which means that it runs only once per install of first extension, despite how many extensions there are
        if($objSingleSiteStatus->checkPluginDataExists($this->conf->getOldestCompatiblePluginSemver()) === FALSE)
        {
            // First - remove, if exists
            $objAdministratorRole->removeCapabilities();

            // Then - add
            $objAdministratorRole->addCapabilities();
        }
    }

    /**
     * @throws \Exception
     */
    public function setContent()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);
        $objSingleSiteInstall = new Install($this->conf, $this->lang, $this->blogId);

        if($objSingleSiteStatus->checkPluginDataExists($this->conf->getOldestCompatiblePluginSemver()) === FALSE)
        {
            // Delete any old table content if exists
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                    if(method_exists($objTable, 'deleteContent') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->deleteContent();
                        $this->processDebug($objTable->getDebugMessages());
                        $this->throwExceptionOnFailure($objTable->getErrorMessages());
                    }
                }
            }
            // INFO: This plugin does not use any posts or post types

            // Then insert all content
            $objSingleSiteInstall->insertContent();
            $this->processDebug($objSingleSiteInstall->getDebugMessages());
            $this->throwExceptionOnFailure($objSingleSiteInstall->getErrorMessages());
        }
    }

    /**
     * @throws \Exception
     */
    public function replaceResettableContent()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);
        $objSingleSiteInstall = new Install($this->conf, $this->lang, $this->blogId);

        // Check if the database is up to date
        if($objSingleSiteStatus->isPluginDataUpToDateInDatabase())
        {
            // Then replace resettable content
            $objSingleSiteInstall->resetContent();
            $this->processDebug($objSingleSiteInstall->getDebugMessages());
            $this->throwExceptionOnFailure($objSingleSiteInstall->getErrorMessages());
        }
    }

    public function registerAllForTranslation()
    {
        // Create mandatory instances
        $objLanguagesObserver = new LanguagesObserver($this->conf, $this->lang);
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);

        // Check if the database is up to date & WPML is enabled
        // Even if the data existed before, having this code out of IF DATA EXISTS scope, means that we allow
        // to re-register language text to WMPL and elsewhere (this will help us to add not-added texts if some is missing)
        if($objSingleSiteStatus->isPluginDataUpToDateInDatabase() && $this->lang->canTranslateSQL())
        {
            $objLanguagesObserver->registerAllForTranslation();
        }
    }

    /**
     * Only deletes the content. Does not delete the tables
     * @throws \Exception
     */
    public function deleteContent()
    {
        // Delete any old table content if exists
        foreach(Install::getTableClasses() AS $tableClass)
        {
            if(class_exists($tableClass))
            {
                $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                if(method_exists($objTable, 'deleteContent') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                {
                    $objTable->deleteContent();
                    $this->processDebug($objTable->getDebugMessages());
                    $this->throwExceptionOnFailure($objTable->getErrorMessages());
                }
            }
        }
        // INFO: This plugin does not use any post types
    }

    /**
     * Removes the roles
     */
    public function removeCustomCapabilities()
    {
        // Create mandatory instances
        $objAdministratorRole = new AdministratorRole($this->conf, $this->lang);

        // 1. Process actions
        $objAdministratorRole->removeCapabilities();
    }

    /**
     * Deletes roles and drops tables
     * @note1 - it drops the tables
     * @note2 - unfortunately it is not possible to delete only extensions folder
     * @throws \Exception
     */
    public function dropTables()
    {
        foreach(Install::getTableClasses() AS $tableClass)
        {
            if(class_exists($tableClass))
            {
                $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                if(method_exists($objTable, 'drop') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                {
                    $objTable->drop();
                    $this->processDebug($objTable->getDebugMessages());
                    $this->throwExceptionOnFailure($objTable->getErrorMessages());
                }
            }
        }
    }

    /**
     * @param array $paramErrorMessages
     * @throws \Exception
     */
    protected function throwExceptionOnFailure(array $paramErrorMessages)
    {
        $errorMessagesToAdd = array();
        foreach($paramErrorMessages AS $paramErrorMessage)
        {
            $errorMessagesToAdd[] = sanitize_text_field($paramErrorMessage);
        }

        if(sizeof($errorMessagesToAdd) > 0)
        {
            $throwMessage = implode('<br />', $errorMessagesToAdd);
            throw new \Exception($throwMessage);
        }
    }

    /**
     * @param array $paramDebugMessages
     */
    protected function processDebug(array $paramDebugMessages)
    {
        $debugMessagesToAdd = array();
        foreach($paramDebugMessages AS $paramDebugMessage)
        {
            // HTML is allowed here
            $debugMessagesToAdd[] = wp_kses_post($paramDebugMessage);
        }

        if(StaticValidator::inWP_Debug() && sizeof($debugMessagesToAdd) > 0)
        {
            echo '<br />'.implode('<br />', $debugMessagesToAdd);
        }
    }
}