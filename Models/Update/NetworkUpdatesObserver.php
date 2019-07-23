<?php
/**
 * Network updates observer
 *
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\Language;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\PrimitiveObserverInterface;
use ExpandableFAQ\Models\Status\SingleStatus;

final class NetworkUpdatesObserver implements PrimitiveObserverInterface
{
    private $conf 	                    = NULL;
    private $lang 		                = NULL;
    private $debugMode 	                = 0;
    private $savedMessages              = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    public function getSavedDebugMessages()
    {
        return isset($this->savedMessages['debug']) ? $this->savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset($this->savedMessages['okay']) ? $this->savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset($this->savedMessages['error']) ? $this->savedMessages['error'] : array();
    }

    private function saveAllMessages($paramArrMessages)
    {
        if(isset($paramArrMessages['debug']))
        {
            $this->savedMessages['debug'] = array_merge($this->getSavedDebugMessages(), $paramArrMessages['debug']);
        }
        if(isset($paramArrMessages['okay']))
        {
            $this->savedMessages['okay'] = array_merge($this->getSavedOkayMessages(), $paramArrMessages['okay']);
        }
        if(isset($paramArrMessages['error']))
        {
            $this->savedMessages['error'] = array_merge($this->getSavedErrorMessages(), $paramArrMessages['error']);
        }
    }

    /**
     * For updating across multisite the network-enabled plugin from 6.0.2 to 6.1.0 semantic version
     * @note - Works only with WordPress 4.6+
     * @return bool
     * @throws \Exception
     */
    public function do602_UpdateTo610()
    {
        // Create mandatory instances
        $objNetworkDB_Update = new Update610($this->conf, $this->lang, $this->conf->getBlogId());
        $allSitesSemverUpdated = TRUE;

        // Alter the database structure for all sites (because they use same database tables)
        $networkEarlyStructAltered = $objNetworkDB_Update->alterDatabaseEarlyStructure();

        // NOTE: Network site is one of the sites. So it will update network site id as well.
        $sites = get_sites();
        foreach ($sites AS $site)
        {
            $blogId = $site->blog_id;
            switch_to_blog($blogId);

            $lang = new Language(
                $this->conf->getTextDomain(), $this->conf->getGlobalLangPath(), $this->conf->getLocalLangPath(), get_locale()
            );

            // Update the database data
            $objSingleDB_Update = new Update610($this->conf, $lang, $blogId);
            $objSingleStatus = new SingleStatus($this->conf, $lang, $blogId);

            // Process ONLY if the current blog has populated extension data, network struct is already updated
            // and current site database was not yet updated
            if(
                $networkEarlyStructAltered && $objSingleStatus->checkPluginDataExistsOf('6.0.2')
                && version_compare($objSingleStatus->getPluginSemverInDatabase(), '6.0.2', '==')
            ) {
                $dataUpdated = $objSingleDB_Update->updateDatabaseData();
                if($dataUpdated === FALSE)
                {
                    $allSitesSemverUpdated = FALSE;
                } else
                {
                    // Update the current site database version to 6.0.0
                    $semverUpdated = $objSingleDB_Update->updateDatabaseSemver();

                    // Update roles
                    $objSingleDB_Update->updateCustomRoles();
                    // Update capabilities
                    $objSingleDB_Update->updateCustomCapabilities();

                    if($semverUpdated == FALSE)
                    {
                        $allSitesSemverUpdated = FALSE;
                    }
                }
            }

            $this->saveAllMessages(array(
                'debug' => $objSingleDB_Update->getDebugMessages(),
                'okay' => $objSingleDB_Update->getOkayMessages(),
                'error' => $objSingleDB_Update->getErrorMessages(),
            ));
        }
        // Switch back to current network blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
        switch_to_blog($this->conf->getBlogId());

        // Process ONLY if the data was updated in ALL sites - because what if it crashed in the middle of the process
        if($allSitesSemverUpdated)
        {
            // Alter the database late structure - we not going to pay attention if the crash will happen in here,
            // because the database is already valid with just extra data, which we may just skip
            $objNetworkDB_Update->alterDatabaseLateStructure();
        }

        $this->saveAllMessages(array(
            'debug' => $objNetworkDB_Update->getDebugMessages(),
            'okay' => $objNetworkDB_Update->getOkayMessages(),
            'error' => $objNetworkDB_Update->getErrorMessages(),
        ));

        return $allSitesSemverUpdated;
    }
}