<?php
/**
 * Network status

 * @note - It does not have settings param in constructor on purpose!
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Status;
use ExpandableFAQ\Models\AbstractStack;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Semver\Semver;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class NetworkStatus extends AbstractStack implements StackInterface, NetworkStatusInterface
{
    private $conf           = NULL;
    private $lang 		    = NULL;
    private $debugMode 	    = 0;

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

    /**
     * Additional links to show in network plugins manager
     * @return array
     */
    public function getAdditionalActionLinks()
    {
        $retLinks = array();

        if($this->isAllBlogsWithPluginDataUpToDate())
        {
            // Additional links to show if the plugin is up-to-date
            if($this->checkPluginDataExistsInSomeBlog($this->conf->getPluginSemver()))
            {
                // Show additional locally-enabled plugin links only if the plugin is up-to-date, and has existing extension data for Blog ID=X
                $networkDropDataPageUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'network-status&drop_data=1&noheader=true');
                $retLinks[] = '<a href="'.$networkDropDataPageUrl.'">'.$this->lang->getPrint('LANG_SETTINGS_DROP_DATA_TEXT').'</a>';
            } else
            {
                // Show additional locally-enabled plugin links only if the plugin is up-to-date, and doesn't have existing extension data for Blog ID=X
                $networkPopulateDataPageUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'network-status&populate_data=1&noheader=true');
                $retLinks[] = '<a href="'.$networkPopulateDataPageUrl.'">'.$this->lang->getPrint('LANG_SETTINGS_POPULATE_DATA_TEXT').'</a>';
            }
        } else if($this->canUpdatePluginDataInSomeBlog())
        {
            // Show the network-update link, but only if it is allowed to update from current version
            $networkUpdatePageUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'network-status&update=1');
            $retLinks[] = '<a href="'.$networkUpdatePageUrl.'">'.$this->lang->getPrint('LANG_UPDATE_TEXT').'</a>';
        }

        return $retLinks;
    }

    /**
     * Additional links to show in next to network plugin description
     * @return array
     */
    public function getInfoLinks()
    {
        $retLinks = array();

        // Additional local links to show, but only if the plugin is network-enabled
        if($this->isAllBlogsWithPluginDataUpToDate())
        {
            // Show additional info links only if the plugin is up-to-date
            $statusUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'network-status');
            $retLinks[] = '<a href="'.$statusUrl.'">'.$this->lang->getPrint('LANG_STATUS_TEXT').'</a>';
        }

        return $retLinks;
    }

    /**
     * @note - This function is not compatible with SMVC versions before 6.0.0
     * @return bool
     */
    private function checkV600SettingsTableExists()
    {
        // NOTE: We use getPrefix() here that supports multiple extensions
        $tableToCheck = $this->conf->getPrefix().'settings';
        $sqlQuery = "SHOW TABLES LIKE '{$tableToCheck}'";
        $settingsTableResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $settingsTableExists = (!is_null($settingsTableResult) && $settingsTableResult === $tableToCheck) ? TRUE : FALSE;

        return $settingsTableExists;
    }

    /**
     * @note - This function is not compatible with SMVC versions before 6.0.0
     * @return bool
     */
    private function checkV600BlogIdColumnExists()
    {
        // NOTE: We use getPrefix() here that supports multiple extensions
        $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getPrefix()}settings` LIKE 'blog_id'";
        $blogIdColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $blogIdColumnExists = !is_null($blogIdColumnResult) ? TRUE : FALSE;

        return $blogIdColumnExists;
    }

    /**
     * @note1 - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @note2 - This function says if there are plugin struct
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDB_StructExists($paramRequiredPluginSemver)
    {
        $tableExists = FALSE;
        $columnExists = FALSE;
        $validRequiredPluginSemver = StaticValidator::getValidSemver($paramRequiredPluginSemver, FALSE);

        if(version_compare($validRequiredPluginSemver, '6.0.0', '>='))
        {
            // We are looking for SMVC 6.0.0 or later database table
            $tableExists = $this->checkV600SettingsTableExists();
            if($tableExists)
            {
                // We are looking for SMVC 6.0.0 or later database table column
                $columnExists = $this->checkV600BlogIdColumnExists();
            }
        }
        $structExist = $tableExists && $columnExists;

        // DEBUG
        if($this->debugMode)
        {
            $structText = $structExist ? "Yes" : "No";
            $tableText = $tableExists ? "Yes" : "No";
            $columnText = $columnExists ? "Yes" : "No";
            $debugMessage = "Debug: checkPluginDB_StructExists(): {$structText} (Table - {$tableText}, Column - {$columnText})<br />";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used for ajax
            // echo "<br />".$debugMessage;
        }

        return $structExist;
    }

    /**
     * @note1 - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @note2 - This function says if there data exists for at least one extension
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDataExistsInSomeBlog($paramRequiredPluginSemver)
    {
        $retExists = FALSE;
        $sqlQuery = "";
        $validRequiredPluginSemver = StaticValidator::getValidSemver($paramRequiredPluginSemver, FALSE);

        if(version_compare($validRequiredPluginSemver, '6.0.0', '>=') && $this->checkV600SettingsTableExists() && $this->checkV600BlogIdColumnExists())
        {
            // We are testing SMVC V6.0.0 or later database version
            // Note: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getPrefix()}settings WHERE 1";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // NS plugin is installed or not for this blog_id
            $retExists = !is_null($hasSettings) ? TRUE : FALSE;
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkPluginDataExistsInSomeBlog(): ".($retExists ? "Yes" : "No")."<br />SQL: {$sqlQuery}<br />";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used for ajax
            // echo "<br />".$debugMessage;
        }

        return $retExists;
    }

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return array
     */
    public function getAllPluginSemversInDatabase()
    {
        // '0.0.0' is the semver that can be either older than oldest compatible semver, or when the semver is not detected
        // I.e. in oldest semvers the chosen row of plugin semver did not existed at all
        $arrDatabaseSemvers = array();

        if($this->checkV600SettingsTableExists())
        {
            // NOTE: The section bellow were moved to internal statement to save SQL queries for scenario when table exists but not column
            if($this->checkV600BlogIdColumnExists())
            {
                // We are testing SMVC 6.0.0 or later database version
                // NOTE: This query is compatible with SMVC 6.0.0
                $sql = "
                    SELECT conf_value AS plugin_semver
                    FROM {$this->conf->getPrefix()}settings
                    WHERE conf_key IN ('conf_plugin_semver', 'conf_plugin_version')
                ";
                $arrTmpDatabaseSemvers = $this->conf->getInternalWPDB()->get_col($sql);
                foreach($arrTmpDatabaseSemvers AS $databaseSemver)
                {
                    $arrDatabaseSemvers[] = StaticValidator::getValidSemver($databaseSemver, FALSE);
                }
            }
        }

        // If no database semvers were found
        if(sizeof($arrDatabaseSemvers) == 0)
        {
            // Then add '0.0.0' semver
            $arrDatabaseSemvers[] = '0.0.0';
        }

        return $arrDatabaseSemvers;
    }

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getMinPluginSemverInDatabase()
    {
        $semvers = $this->getAllPluginSemversInDatabase();

        // Select minimum database semver, or, if no semvers found, return the '0.0.0' semver
        $minSemver = '0.0.0';
        foreach($semvers AS $semver)
        {
            if($semver != "0.0.0" && version_compare($semver, $minSemver, '<'))
            {
                $minSemver = $semver;
            }
        }

        return $minSemver;
    }

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getEditMinPluginSemverInDatabase()
    {
        return esc_attr($this->getMinPluginSemverInDatabase());
    }

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getPrintMinPluginSemverInDatabase()
    {
        return esc_html($this->getMinPluginSemverInDatabase());
    }

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getMaxPluginSemverInDatabase()
    {
        $semvers = $this->getAllPluginSemversInDatabase();

        // Select maximum database semver, or, if no semvers found, return the '0.0.0' semver
        $maxSemver = '0.0.0';
        foreach($semvers AS $semver)
        {
            if(version_compare($semver, $maxSemver, '>'))
            {
                $maxSemver = $semver;
            }
        }

        return $maxSemver;
    }

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getEditMaxPluginSemverInDatabase()
    {
        return esc_attr($this->getMaxPluginSemverInDatabase());
    }

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getPrintMaxPluginSemverInDatabase()
    {
        return esc_html($this->getMaxPluginSemverInDatabase());
    }

    /**
     * Is the NS database semver is newer or same as code semver. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isAllBlogsWithPluginDataUpToDate()
    {
        $minPluginSemverInDatabase = $this->getMinPluginSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $isUpToDate = version_compare($minPluginSemverInDatabase, $codeSemver, '==') ? TRUE : FALSE;

        // DEBUG
        //echo "MIN. DB SEMVER: {$minPluginSemverInDatabase}<br />";
        //echo "CODE SEMVER: {$codeSemver}<br />";
        //echo "ALL BLOGS IS UP TO DATE: {$isUpToDate}<br />";

        return $isUpToDate;
    }

    /**
     * NOTE: Update may exist, but the system might be not compatible for update
     * @return bool
     */
    public function checkPluginUpdateExistsForSomeBlog()
    {
        $canUpdate = FALSE;
        $minPluginSemverInDatabase = $this->getMinPluginSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        if(version_compare($minPluginSemverInDatabase, $codeSemver, '<'))
        {
            $canUpdate = TRUE;
        }

        // DEBUG
        //echo "MIN DB SEMVER: {$minPluginSemverInDatabase}<br />";
        //echo "CODE SEMVER: {$codeSemver}<br />";
        //echo "UPDATE EXISTS: ".var_export($canUpdate, TRUE)."<br />";

        return $canUpdate;
    }

    /**
     * @return bool
     */
    public function canUpdatePluginDataInSomeBlog()
    {
        $canUpdate = FALSE;
        $minPluginSemverInDatabase = $this->getMinPluginSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $oldestCompatibleSemver = $this->conf->getOldestCompatiblePluginSemver();
        if(version_compare($minPluginSemverInDatabase, $oldestCompatibleSemver, '>=') && version_compare($minPluginSemverInDatabase, $codeSemver, '<'))
        {
            $canUpdate = TRUE;
        }
        return $canUpdate;

        // DEBUG
        //echo "MIN DB SEMVER: {$minPluginSemverInDatabase}<br />";
        //echo "OLDEST-COMPAT SEMVER: {$oldestCompatibleSemver}<br />";
        //echo "CODE SEMVER: {$codeSemver}<br />";
        //echo "UPDATE EXISTS: ".var_export($canUpdate, TRUE)."<br />";
    }

    /**
     * Can we do a major upgrade in some blog, i.e. from V1.*.* to V2.*.* etc., not V1.0.* to V1.1.*
     * @return bool
     */
    public function canMajorlyUpgradePluginDataInSomeBlog()
    {
        $majorUpgrade = FALSE;
        $minPluginSemverInDatabase = $this->getMinPluginSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $oldestCompatibleSemver = $this->conf->getOldestCompatiblePluginSemver();
        $dbMinSemverMajor = (new Semver($minPluginSemverInDatabase, FALSE))->getMajor();
        $codeSemverMajor = (new Semver($codeSemver, FALSE))->getMajor();
        if(version_compare($minPluginSemverInDatabase, $oldestCompatibleSemver, '>=') && $dbMinSemverMajor < $codeSemverMajor)
        {
            $majorUpgrade = TRUE;
        }
        return $majorUpgrade;
    }
}