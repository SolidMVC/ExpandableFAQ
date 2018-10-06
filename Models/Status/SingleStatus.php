<?php
/**
 * Plugin

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

final class SingleStatus extends AbstractStack implements StackInterface, SingleStatusInterface
{
    private $conf           = NULL;
    private $lang 		    = NULL;
    private $debugMode 	    = 0;
    private $blogId         = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->blogId = intval($paramBlogId);
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
     * Get additional links to show in local plugins manager
     * @return array
     */
    public function getActionLinks()
    {
        $retLinks = array();

        if($this->conf->isNetworkEnabled())
        {
            // Additional local links to show, but only if the plugin is network-enabled
            // Note: for network-enabled plugins the update link is not displayed here, it is shown under plugin's network admin action links
            if($this->isPluginDataUpToDateInDatabase())
            {
                if($this->checkPluginDataExists($this->conf->getPluginVersion()))
                {
                    // Show additional locally-enabled plugin links only if the plugin is up-to-date, and has existing extension data for Blog ID=X
                    $dropDataPageUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status&drop_data=1&noheader=true');
                    $retLinks[] = '<a href="'.$dropDataPageUrl.'">'.$this->lang->getPrint('LANG_SETTINGS_DROP_DATA_TEXT').'</a>';
                } else
                {
                    // Show additional locally-enabled plugin links only if the plugin is up-to-date, and doesn't have existing extension data for Blog ID=X
                    $populateDataPageUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status&populate_data=1&noheader=true');
                    $retLinks[] = '<a href="'.$populateDataPageUrl.'">'.$this->lang->getPrint('LANG_SETTINGS_POPULATE_DATA_TEXT').'</a>';
                }
            }
        } else
        {
            // Additional local links to show, but only if the plugin is locally enabled
            if($this->isPluginDataUpToDateInDatabase())
            {
                if($this->checkPluginDataExists($this->conf->getPluginVersion()))
                {
                    // Show additional locally-enabled plugin links only if the plugin is up-to-date, and has existing extension data for Blog ID=X
                    // INFO: As this is non-extensions based plugin, we only allow to drop data of single plugin's instance if it is network-enabled
                } else
                {
                    // Show additional locally-enabled plugin links only if the plugin is up-to-date, and doesn't have existing extension data for Blog ID=X
                    // INFO: As this is non-extensions based plugin, we only allow to populate data of single plugin's instance if it is network-enabled
                }
            } else if($this->canUpdatePluginDataInDatabase())
            {
                // Show update link, but only if the plugin is not network enabled and is allowed to update from current version
                $updatePageUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status&update=1');
                $retLinks[] = '<a href="'.$updatePageUrl.'">'.$this->lang->getPrint('LANG_UPDATE_TEXT').'</a>';
            }
        }

        return $retLinks;
    }

    /**
     * Additional links to show in next to local plugin description
     * @return array
     */
    public function getInfoLinks()
    {
        $retLinks = array();

        if($this->conf->isNetworkEnabled())
        {
            // Additional local links to show, but only if the plugin is network-enabled
            if($this->isPluginDataUpToDateInDatabase())
            {
                // Show additional info links only if the plugin is up-to-date
                $statusUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status');
                $retLinks[] = '<a href="'.$statusUrl.'">'.$this->lang->getPrint('LANG_STATUS_TEXT').'</a>';
            }
        } else
        {
            // Additional local links to show, but only if the plugin is locally enabled
            if($this->isPluginDataUpToDateInDatabase())
            {
                // Show additional info links only if the plugin is up-to-date
                $statusUrl = admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'single-status');
                $retLinks[] = '<a href="'.$statusUrl.'">'.$this->lang->getPrint('LANG_STATUS_TEXT').'</a>';
            }
        }

        return $retLinks;
    }

    /**
     * @note - This function is not compatible with NS versions before 6.0
     * @return bool
     */
    private function checkV60SettingsTableExists()
    {
        // NOTE: We use getPrefix() here that supports multiple extensions
        $tableToCheck = $this->conf->getPrefix().'settings';
        $sqlQuery = "SHOW TABLES LIKE '{$tableToCheck}'";
        $settingsTableResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $settingsTableExists = (!is_null($settingsTableResult) && $settingsTableResult === $tableToCheck) ? TRUE : FALSE;

        return $settingsTableExists;
    }

    /**
     * @note - This function is not compatible with NS versions before 6.0
     * @return bool
     */
    private function checkV60BlogIdColumnExists()
    {
        // NOTE: We use getPrefix() here that supports multiple extensions
        $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getPrefix()}settings` LIKE 'blog_id'";
        $blogIdColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $blogIdColumnExists = !is_null($blogIdColumnResult) ? TRUE : FALSE;

        return $blogIdColumnExists;
    }

    /**
     * @note1 - This function maintains backwards compatibility to NS V4.3 and newer
     * @note2 - This function says if there are plugin struct
     * @param float $paramRequiredPluginVersion
     * @return bool
     */
    public function checkPluginDBStructExists($paramRequiredPluginVersion)
    {
        $tableExists = FALSE;
        $columnExists = FALSE;

        if($paramRequiredPluginVersion >= 6.0)
        {
            // We are looking for NS V6.0 or later database table
            $tableExists = $this->checkV60SettingsTableExists();
            if($tableExists)
            {
                // We are looking for NS V6.0 or later database table column
                $columnExists = $this->checkV60BlogIdColumnExists();
            }
        }
        $structExist = $tableExists && $columnExists;

        // DEBUG
        if($this->debugMode)
        {
            $structText = $structExist ? "Yes" : "No";
            $tableText = $tableExists ? "Yes" : "No";
            $columnText = $columnExists ? "Yes" : "No";
            $debugMessage = "Debug: checkPluginDBStructExists(): {$structText} (Table - {$tableText}, Column - {$columnText})<br />";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used for ajax
            // echo "<br />".$debugMessage;
        }

        return $structExist;
    }

    /**
     * @note1 - This function maintains backwards compatibility to NS V6.0 and newer
     * @note2 - This function says if there data exists for at least one extension
     * @param float $paramRequiredPluginVersion
     * @return bool
     */
    public function checkPluginDataExists($paramRequiredPluginVersion)
    {
        $retExists = FALSE;
        $sqlQuery = "";

        if($paramRequiredPluginVersion >= 6.0 && $this->checkV60SettingsTableExists() && $this->checkV60BlogIdColumnExists())
        {
            // We are testing NS V6.0 or later database version
            $validBlogId = intval($this->blogId);
            // Note: SELECT 1 is not supported by WordPress, Php, or get_var, so it has to be an exact field name
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getPrefix()}settings WHERE blog_id='{$validBlogId}'";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // NS plugins is installed or not for this blog_id
            $retExists = !is_null($hasSettings) ? TRUE : FALSE;
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkPluginDataExists(): ".($retExists ? "Yes" : "No")."<br />SQL: {$sqlQuery}<br />";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used for ajax
            // echo "<br />".$debugMessage;
        }

        return $retExists;
    }

    /**
     * @note - This function maintains backwards compatibility to NS V6.0 and newer
     * @return float
     */
    public function getPluginVersionInDatabase()
    {
        // 0.0 is the version that can be either older than oldest compatible version, or when the version is not detected
        // I.e. in oldest versions the chosen row of plugin version did not existed at all
        $databaseVersion = 0.0;

        if($this->checkV60SettingsTableExists())
        {
            // NOTE: The section bellow were moved to internal statement to save SQL queries for scenario when table exists but not column
            if($this->checkV60BlogIdColumnExists())
            {
                // We are testing NS 6.0 or later database version
                $validBlogId = intval($this->blogId);
                $sql = "
                    SELECT conf_value AS plugin_version
                    FROM {$this->conf->getPrefix()}settings
                    WHERE conf_key='conf_plugin_version' AND blog_id='{$validBlogId}'
                ";
                $databaseVersionResult = $this->conf->getInternalWPDB()->get_var($sql);
                if(!is_null($databaseVersionResult))
                {
                    $databaseVersion = floatval($databaseVersionResult);
                }
            }
        }

        return $databaseVersion;
    }

    /**
     * Is the NS database version is same as code version. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isPluginDataUpToDateInDatabase()
    {
        $pluginVersionInDatabase = $this->getPluginVersionInDatabase();
        $codeVersion = $this->conf->getPluginVersion();

        // DEBUG
        //echo "DB VERSION: {$pluginVersionInDatabase}<br />";
        //echo "CODE VERSION: {$codeVersion}<br />";

        return $pluginVersionInDatabase == $codeVersion ? TRUE : FALSE;
    }

    /**
     * @return bool
     */
    public function canUpdatePluginDataInDatabase()
    {
        $canUpdate = FALSE;
        $pluginVersionInDatabase = $this->getPluginVersionInDatabase();
        $codeVersion = $this->conf->getPluginVersion();
        $oldestCompatibleVersion = $this->conf->getOldestCompatiblePluginVersion();
        if($pluginVersionInDatabase >= $oldestCompatibleVersion && floatval($pluginVersionInDatabase) < floatval($codeVersion))
        {
            $canUpdate = TRUE;
        }
        return $canUpdate;
    }

    /**
     * Can we do a major upgrade, i.e. from V1 to V2 etc., not V1 to V1.1
     * @return bool
     */
    public function canMajorlyUpgradePluginDataInDatabase()
    {
        $majorUpgrade = FALSE;
        $extVersionInDatabase = $this->getPluginVersionInDatabase();
        $codeVersion = $this->conf->getPluginVersion();
        $oldestCompatibleVersion = $this->conf->getOldestCompatiblePluginVersion();
        if($extVersionInDatabase >= $oldestCompatibleVersion && intval($extVersionInDatabase) < intval($codeVersion))
        {
            $majorUpgrade = TRUE;
        }
        return $majorUpgrade;
    }
}