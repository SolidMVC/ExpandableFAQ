<?php
/**
 * Single patches observer
 *
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\PrimitiveObserverInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class SinglePatchesObserver implements PrimitiveObserverInterface
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
        $semverUpdated = FALSE;

        // Validate
        $validMajor = StaticValidator::getValidPositiveInteger($paramMajor, 0);
        $validMinor = StaticValidator::getValidPositiveInteger($paramMinor, 0);

        // NOTE: the '\' has to be escaped by \ char here bellow to work correctly
        $patchClass = "\\".ConfigurationInterface::PLUGIN_NAMESPACE."\\Models\\Update\\Patches{$validMajor}{$validMinor}Z";
        if(class_exists($patchClass))
        {
            // Create mandatory instances
            $objDB_Patch = new $patchClass($this->conf, $this->lang, $this->conf->getBlogId());

            $earlyStructPatched = FALSE;
            if(method_exists($objDB_Patch, 'patchDatabaseEarlyStructure'))
            {
                $earlyStructPatched = $objDB_Patch->patchDatabaseEarlyStructure();
            }

            // NOTE: The bellow will process patching from 6.0.0 to 6.0.1+6.0.2, from 6.0.1 to 6.0.2 etc.
            $dataPatched = FALSE;
            if($earlyStructPatched && method_exists($objDB_Patch, 'patchData'))
            {
                $dataPatched = $objDB_Patch->patchData();
            }

            $lateStructAltered = FALSE;
            if($dataPatched && method_exists($objDB_Patch, 'patchDatabaseLateStructure'))
            {
                $lateStructAltered = $objDB_Patch->patchDatabaseLateStructure();
            }

            if($lateStructAltered && method_exists($objDB_Patch, 'updateDatabaseSemver'))
            {
                $semverUpdated = $objDB_Patch->updateDatabaseSemver();
            }

            if(
                method_exists($objDB_Patch, 'getDebugMessages')
                && method_exists($objDB_Patch, 'getOkayMessages')
                && method_exists($objDB_Patch, 'getErrorMessages')
            ) {
                $this->saveAllMessages(array(
                    'debug' => $objDB_Patch->getDebugMessages(),
                    'okay' => $objDB_Patch->getOkayMessages(),
                    'error' => $objDB_Patch->getErrorMessages(),
                ));
            }
        } else
        {
            $error = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_PATCH_CLASS_S_DOES_NOT_EXIST_ERROR_TEXT'), $patchClass);
            $this->saveAllMessages(array('error' => array($error)));
        }

        return $semverUpdated;
    }
}