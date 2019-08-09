<?php
/**
 * Network patches observer
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
use ExpandableFAQ\Models\Semver\Semver;
use ExpandableFAQ\Models\Status\SingleStatus;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class NetworkPatchesObserver implements PrimitiveObserverInterface
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
     * For updating across multisite the network-enabled plugin from X.Y.0 to X.Y.Z
     * @note - Works only with WordPress 4.6+
     * @param int $paramMajor
     * @param int $paramMinor
     * @return bool
     * @throws \Exception
     */
    public function doPatch($paramMajor, $paramMinor)
    {
        // Set defaults
        $allSitesSemverUpdated = FALSE;

        // Validate
        $validMajor = StaticValidator::getValidPositiveInteger($paramMajor, 0);
        $validMinor = StaticValidator::getValidPositiveInteger($paramMinor, 0);

        // NOTE: the '\' has to be escaped by \ char here bellow to work correctly
        $patchClass = "\\".ConfigurationInterface::PLUGIN_NAMESPACE."\\Models\\Update\\Patches{$validMajor}{$validMinor}Z";
        if(class_exists($patchClass))
        {
            // Create mandatory instances
            $objNetworkDB_Patch = new $patchClass($this->conf, $this->lang, $this->conf->getBlogId());
            $allSitesSemverUpdated = TRUE;

            $networkEarlyStructPatched = FALSE;
            if(method_exists($objNetworkDB_Patch, 'patchDatabaseEarlyStructure'))
            {
                // Alter the database structure for all sites (because they use same database tables)
                $networkEarlyStructPatched = $objNetworkDB_Patch->patchDatabaseEarlyStructure();
            }

            // NOTE: Network site is one of the sites. So it will update network site id as well.
            $sites = get_sites();
            foreach ($sites AS $site)
            {
                $blogId = $site->blog_id;
                switch_to_blog($blogId);

                $lang = new Language(
                    $this->conf->getTextDomain(), $this->conf->getGlobalPluginLangPath(), $this->conf->getLocalLangPath(), $this->conf->getBlogLocale($blogId), FALSE
                );

                // Update the database data
                $objSingleDB_Patch = new $patchClass($this->conf, $lang, $blogId);
                $objSingleStatus = new SingleStatus($this->conf, $lang, $blogId);
                $pluginSemverInDB = $objSingleStatus->getPluginSemverInDatabase();
                $objSingleSemver = new Semver($pluginSemverInDB, FALSE);

                // Process ONLY if the current blog has populated extension data, network struct is already updated
                // and current site database was not yet updated
                // NOTE: The bellow will process patching from 6.0.0 to 6.0.1+6.0.2, from 6.0.1 to 6.0.2 etc.
                if(
                    $networkEarlyStructPatched && $objSingleStatus->checkPluginDataExistsOf($validMajor.'.'.$validMinor.'.0')
                    && $objSingleSemver->getMajor() == $validMajor && $objSingleSemver->getMinor() == $validMinor
                    && method_exists($objSingleDB_Patch, 'patchData') && method_exists($objSingleDB_Patch, 'updateDatabaseSemver')
                ) {
                    $dataPatched = $objSingleDB_Patch->patchData();
                    if($dataPatched === FALSE)
                    {
                        $allSitesSemverUpdated = FALSE;
                    } else
                    {
                        // Update the current site database version to 6.0.0
                        $semverUpdated = $objSingleDB_Patch->updateDatabaseSemver();
                        if($semverUpdated == FALSE)
                        {
                            $allSitesSemverUpdated = FALSE;
                        }
                    }
                }

                if(
                    method_exists($objSingleDB_Patch, 'getDebugMessages')
                    && method_exists($objSingleDB_Patch, 'getOkayMessages')
                    && method_exists($objSingleDB_Patch, 'getErrorMessages')
                ) {
                    $this->saveAllMessages(array(
                        'debug' => $objSingleDB_Patch->getDebugMessages(),
                        'okay' => $objSingleDB_Patch->getOkayMessages(),
                        'error' => $objSingleDB_Patch->getErrorMessages(),
                    ));
                }
            }
            // Switch back to current network blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
            switch_to_blog($this->conf->getBlogId());

            // Process ONLY if the data was patched in ALL sites - because what if it crashed in the middle of the process
            if($allSitesSemverUpdated && method_exists($objNetworkDB_Patch, 'patchDatabaseLateStructure'))
            {
                // Patch the database late structure - we not going to pay attention if the crash will happen in here,
                // because the database is already valid with just extra data, which we may just skip
                $objNetworkDB_Patch->patchDatabaseLateStructure();
            }

            if(
                method_exists($objNetworkDB_Patch, 'getDebugMessages')
                && method_exists($objNetworkDB_Patch, 'getOkayMessages')
                && method_exists($objNetworkDB_Patch, 'getErrorMessages')
            ) {
                $this->saveAllMessages(array(
                    'debug' => $objNetworkDB_Patch->getDebugMessages(),
                    'okay' => $objNetworkDB_Patch->getOkayMessages(),
                    'error' => $objNetworkDB_Patch->getErrorMessages(),
                ));
            }
        } else
        {
            $error = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_PATCH_CLASS_S_DOES_NOT_EXIST_ERROR_TEXT'), $patchClass);
            $this->saveAllMessages(array('error' => array($error)));
        }

        return $allSitesSemverUpdated;
    }
}