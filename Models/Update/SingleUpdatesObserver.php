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
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\PrimitiveObserverInterface;

final class SingleUpdatesObserver implements PrimitiveObserverInterface
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
     * For updating single site plugin from 5.0.0 to V6.0.0
     */
    public function do602_UpdateTo610()
    {
        $updated = FALSE;

        // Create mandatory instances
        $objDB_Update = new Update610($this->conf, $this->lang, $this->conf->getBlogId());

        // Alter the database early structure
        $earlyStructAltered = $objDB_Update->alterDatabaseEarlyStructure();
        $dataUpdated = FALSE;
        $lateStructAltered = FALSE;

        // Process ONLY if the struct was updated - because what if it crashed in the middle of the process
        if($earlyStructAltered)
        {
            // Update the database data
            $dataUpdated = $objDB_Update->updateDatabaseData();
        }

        // Process ONLY if the data was updated - because what if it crashed in the middle of the process
        if($dataUpdated)
        {
            // Alter the database late structure
            $lateStructAltered = $objDB_Update->alterDatabaseLateStructure();
        }

        if($lateStructAltered)
        {
            // Update the database version to 6.1.0
            $updated = $objDB_Update->updateDatabaseSemver();

            // Update roles
            $objDB_Update->updateCustomRoles();
            // Update capabilities
            $objDB_Update->updateCustomCapabilities();
        }

        $this->saveAllMessages(array(
            'debug' => $objDB_Update->getDebugMessages(),
            'okay' => $objDB_Update->getOkayMessages(),
            'error' => $objDB_Update->getErrorMessages(),
        ));

        return $updated;
    }
}