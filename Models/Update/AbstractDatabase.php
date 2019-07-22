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
use ExpandableFAQ\Models\Validation\StaticValidator;

abstract class AbstractDatabase extends AbstractStack
{
    protected $conf 	                = NULL;
    protected $lang 		            = NULL;
    protected $debugMode 	            = 0; // 0 - off, 1 - standard, 2 - deep debug
    protected $blogId                   = 0;
    protected $pluginSemverInDatabase   = '0.0.0';
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

        // Set database semver
        $this->setPluginSemverInDatabase();
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
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and older
     */
    private function setPluginSemverInDatabase()
    {
        // In case if version is not found, we will use '0.0.0'
        $databaseSemver = '0.0.0';

        $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getPrefix()}settings` LIKE 'blog_id'";
        $blogIdColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);

        // As version is not yet set, we use blog column to check
        // Do V6 or later check
        if(!is_null($blogIdColumnResult))
        {
            // We are testing SMVC 6.0.0 or later database version
            $validBlogId = intval($this->blogId);

            // SMVC 6.0.1 and newer check
            $semverSQL = "
                SELECT conf_value AS plugin_semver
                FROM {$this->conf->getPrefix()}settings
                WHERE conf_key='conf_plugin_semver' AND blog_id='{$validBlogId}'
            ";
            $databaseSemverResult = $this->conf->getInternalWPDB()->get_var($semverSQL);
            if(!is_null($databaseSemverResult))
            {
                // SMVC 6.0.1 and newer
                $databaseSemver = StaticValidator::getValidSemver($databaseSemverResult, FALSE);
            } else
            {
                // SMVC 6.0.0 check
                $versionSQL = "
                    SELECT conf_value AS plugin_version
                    FROM {$this->conf->getPrefix()}settings
                    WHERE conf_key='conf_plugin_version' AND blog_id='{$validBlogId}'
                ";
                $databaseVersionResult = $this->conf->getInternalWPDB()->get_var($versionSQL);
                if(!is_null($databaseVersionResult))
                {
                    $databaseSemver = StaticValidator::getValidSemver($databaseVersionResult, FALSE);
                }
            }
        }

        $this->pluginSemverInDatabase = $databaseSemver;

        if($this->debugMode)
        {
            $debugMessage = "DB SEMVER: {$databaseSemver}";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".esc_html($debugMessage);
        }

        return $databaseSemver;
    }

    /**
     * This method for internal use only
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @param int $paramNewValue
     * @return int
     */
    protected function setCounter($paramNewValue)
    {
        $updated = FALSE;
        $validNewValue = $paramNewValue > 0 ? intval($paramNewValue) : 0;
        if(version_compare($this->pluginSemverInDatabase, '6.0.0', '>='))
        {
            // We are testing SMVC 6.0.0 or later database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				UPDATE {$this->conf->getPrefix()}settings SET conf_value='{$validNewValue}'
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
                $debugMessage = '<span style="font-weight:bold;color: red;">FAILED</span> TO SET DB UPDATE COUNTER TO: '.$validNewValue;
            } else
            {
                $debugMessage = 'DB UPDATE COUNTER SET TO: '.$validNewValue;
            }
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".esc_html($debugMessage);
        }

        return $updated;
    }

    /**
     * This method for internal use only
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and older
     */
    protected function getCounter()
    {
        // If that is not the newest semver, then for sure the database update counter is 0
        $updateCounter = 0;
        if(version_compare($this->pluginSemverInDatabase, '6.0.0', '>='))
        {
            // We are testing SMVC 6.0.0 or later database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				SELECT conf_value AS counter
				FROM {$this->conf->getPrefix()}settings
				WHERE conf_key='conf_updated' AND blog_id='{$validBlogId}'
			";
            $dbUpdateCounterValue = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if(!is_null($dbUpdateCounterValue) && $dbUpdateCounterValue > 0)
            {
                $updateCounter = intval($dbUpdateCounterValue);
            }
        }

        if($this->debugMode)
        {
            $debugMessage = "GOT CURRENT DB UPDATE COUNTER: {$updateCounter}";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
        }

        return $updateCounter;
    }

    /**
     * Insert/Update/Alter data to database
     * @param array $paramArrTrustedSQLs
     * @return bool
     */
    protected function executeQueries(array $paramArrTrustedSQLs)
    {
        $currentCounter = $this->getCounter();

        $completed = TRUE;
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
                if($ok === FALSE)
                {
                    // Stop executing any more queries
                    $completed = FALSE;
                    break;
                } else
                {
                    // Increase currently executed queries counter
                    $this->setCounter($this->internalCounter);
                }
            }
        }

        return $completed;
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
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_QUERY_FAILED_FOR_TABLE_ERROR_TEXT'), $this->blogId, $tableName, $this->internalCounter);
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