<?php
/**
 * Database updater
 * NOTE: This is in-the-middle class, so it must not be final, and it's variables should not be private

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;
use ExpandableFAQ\Models\AbstractStack;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

class AbstractUpdate extends AbstractStack
{
    protected $conf 	                = NULL;
    protected $lang 		            = NULL;
    protected $debugMode 	            = 0; // 0 - off, 1 - standard, 2 - deep debug
    protected $blogId                   = 0;

    // NOTE: The 3.2 version number here is ok, because it defines the case of older plugin versions,
    // when plugin version data was not saved to db
    protected $extVersionInDatabase     = 3.2;
    protected $internalCounter          = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->blogId = intval($paramBlogId);
        // Reset internal counter and use it class-wide to count all queries processed (but maybe not executed)
        $this->internalCounter = 0;

        // Set database version
        $this->setExtVersionInDatabase();
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    public function getId()
    {
        return $this->blogId;
    }

    /**
     * @note - This function maintains backwards compatibility to NS V4.3 and older
     */
    private function setExtVersionInDatabase()
    {
        // In case if version is not found, we use 0.0
        $databaseVersion = 0.0;

        $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getPrefix()}settings` LIKE 'blog_id'";
        $blogIdColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);

        // As version is not yet set, we use blog column to check
        // Do V6 or later check
        if(!is_null($blogIdColumnResult))
        {
            // We are testing NS 6.0 or later database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				SELECT conf_value AS plugin_version
				FROM {$this->conf->getPrefix()}settings
				WHERE conf_key='conf_plugin_version' AND blog_id='{$validBlogId}'
			";
            $databaseVersionResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if(!is_null($databaseVersionResult))
            {
                $databaseVersion = floatval($databaseVersionResult);
            }
        }

        $this->extVersionInDatabase = $databaseVersion;

        if($this->debugMode)
        {
            $debugMessage = "DB VERSION: {$databaseVersion}";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
        }

        return $databaseVersion;
    }

    /**
     * This method for internal use only
     * @note - This function maintains backwards compatibility to NS V4.3 and newer
     * @param $paramNewValue
     * @return int
     */
    protected function setCounter($paramNewValue)
    {
        $updated = FALSE;
        $validValue = $paramNewValue > 0 ? intval($paramNewValue) : 0;
        if($this->extVersionInDatabase >= 6.0)
        {
            // We are testing NS 6.0 or later database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				UPDATE {$this->conf->getPrefix()}settings SET conf_value='{$validValue}'
				WHERE conf_key='conf_updated' AND blog_id='{$validBlogId}'
			";
            $ok = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if($ok !== FALSE)
            {
                $updated = TRUE;
            }
        }

        if($this->debugMode == 2)
        {
            if($updated === FALSE)
            {
                $debugMessage = '<span style="font-weight:bold;color: red;">FAILED</span> TO SET DB UPDATE COUNTER TO: '.$validValue;
            } else
            {
                $debugMessage = 'DB UPDATE COUNTER SET TO: '.$validValue;
            }
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
        }

        return $updated;
    }

    /**
     * This method for internal use only
     * @note - This function maintains backwards compatibility to NS V4.3 and older
     */
    protected function getCounter()
    {
        // If that is not the newest version, then for sure the database update counter is 0
        $retUpdateCounter = 0;
        if($this->extVersionInDatabase >= 6.0)
        {
            // We are testing NS 6.0 or later database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				SELECT conf_value AS counter
				FROM {$this->conf->getPrefix()}settings
				WHERE conf_key='conf_updated' AND blog_id='{$validBlogId}'
			";
            $dbUpdateCounterValue = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if(!is_null($dbUpdateCounterValue) && $dbUpdateCounterValue > 0)
            {
                $retUpdateCounter = intval($dbUpdateCounterValue);
            }
        }

        if($this->debugMode)
        {
            $debugMessage = "GOT CURRENT DB UPDATE COUNTER: {$retUpdateCounter}";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
        }

        return $retUpdateCounter;
    }

    /**
     * Insert/Update/Alter data to database
     * @param array $paramArrTrustedSQLs
     * @return bool
     */
    protected function executeQueries(array $paramArrTrustedSQLs)
    {
        $currentCounter = $this->getCounter();

        $executed = TRUE;
        foreach($paramArrTrustedSQLs AS $sqlQuery)
        {
            // Increase internal queries counter
            $this->internalCounter = $this->internalCounter + 1;
            if($currentCounter > $this->internalCounter)
            {
                // Do nothing Just SKIP this query
            } else
            {
                $ok = $this->executeQuery($sqlQuery);
                if($ok)
                {
                    // Stop executing any more queries
                    break;
                } else
                {
                    // Increase currently executed queries counter
                    $this->setCounter($this->internalCounter);
                }
            }
        }

        return $executed;
    }

    /**
     * Insert/Update/Alter data to database
     * @param string $paramTrustedSQLQuery
     * @return bool
     */
    protected function executeQuery($paramTrustedSQLQuery)
    {
        // Try to execute current query
        $executed = $this->conf->getInternalWPDB()->query($paramTrustedSQLQuery);
        if($executed === FALSE)
        {
            $executed = FALSE;
            $startIdentifier = '`'.$this->conf->getPrefix();
            $endIdentifier = '`';
            $startCharPosOfTableName = strpos($paramTrustedSQLQuery, $startIdentifier) + strlen($startIdentifier);
            $tableLength = strpos($paramTrustedSQLQuery, $endIdentifier, $startCharPosOfTableName) - $startCharPosOfTableName;
            $tableName = '';
            if($startCharPosOfTableName > 0 && $tableLength > 0)
            {
                $tableName = substr($paramTrustedSQLQuery, $startCharPosOfTableName, $tableLength);
            }
            $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_QUERY_FAILED_FOR_TABLE_ERROR_TEXT'), $this->blogId, $tableName, $this->internalCounter);
            if($this->debugMode)
            {
                $debugMessage = "FAILED AT QUERY:<br />".nl2br($paramTrustedSQLQuery);
                $this->debugMessages[] = $debugMessage;
                // Do not echo here, as this class is used in redirect
                //echo "<br />".$debugMessage;
            }
        }

        return $executed;
    }
}