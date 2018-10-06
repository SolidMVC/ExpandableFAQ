<?php
/**
 * Demo import manager

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Import;
use ExpandableFAQ\Models\AbstractStack;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\File\StaticFile;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class Demo extends AbstractStack implements StackInterface
{
    private $conf                   = NULL;
    private $lang                   = NULL;
    private $debugMode              = 0;
    private $demoId                 = 0;
    /**
     * @var array - plugin tables, ordered by table name
     */
    private static $pluginTables    = array(
        "faqs",
        "settings",
    );

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramDemoId)
    {
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->conf = $paramConf;
        $this->lang = $paramLang;

        $this->demoId = intval($paramDemoId);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    public function getId()
    {
        return $this->demoId;
    }

    private function replaceBBCodes($trustedSQLData)
    {
        // Spring
        $springStartTimestamp = strtotime(date("Y")."-03-01 00:00:00");
        $springEndTimestamp = strtotime(date("Y")."-05-31 23:59:59");

        // Summer
        $summerStartTimestamp = strtotime(date("Y")."-06-01 00:00:00");
        $summerEndTimestamp = strtotime(date("Y")."-08-31 23:59:59");

        // Autumn
        $autumnStartTimestamp = strtotime(date("Y")."-09-01 00:00:00");
        $autumnEndTimestamp = strtotime(date("Y")."-11-30 23:59:59");

        // Winter
        $winterStartTimestamp = strtotime(date("Y")."-12-01 00:00:00");
        $lastDateOfNextWinter = date("Y-m-t", strtotime((date("Y")+1)."-02-01"));
        $winterEndTimestamp = strtotime("{$lastDateOfNextWinter} 23:59:59");

        // Today & Yesterday
        $todayTimestamp = strtotime(date("Y-m-d H:00:00"));
        $yesterdayTimestamp = $todayTimestamp - 86400;

        // WP Prefix is used in demos only in one place - in WordPress user meta data table for blog prefix
        $arrFrom = array(
            '[WP_PREFIX]', '[BLOG_ID]',
            '[SPRING_START_TIMESTAMP]', '[SPRING_END_TIMESTAMP]',
            '[SUMMER_START_TIMESTAMP]', '[SUMMER_END_TIMESTAMP]',
            '[AUTUMN_START_TIMESTAMP]', '[AUTUMN_END_TIMESTAMP]',
            '[WINTER_START_TIMESTAMP]', '[WINTER_END_TIMESTAMP]',
            '[TODAY_TIMESTAMP]', '[YESTERDAY_TIMESTAMP]'
        );
        $arrTo = array(
            $this->conf->getBlogPrefix(), $this->conf->getBlogId(),
            $springStartTimestamp, $springEndTimestamp,
            $summerStartTimestamp, $summerEndTimestamp,
            $autumnStartTimestamp, $autumnEndTimestamp,
            $winterStartTimestamp, $winterEndTimestamp,
            $todayTimestamp, $yesterdayTimestamp
        );
        $replacedSQLData = str_replace($arrFrom, $arrTo, $trustedSQLData);

        return $replacedSQLData;
    }

    /**
     * Replace all content
     * @note - Replace mode helps us here to avoid conflicts with already existing regular WordPress posts
     * @return bool
     */
    public function replaceContent()
    {
        // Insert SQL
        $inserted = TRUE;
        // If importable demo file is provided and it's file is readable
        $demoSQL_PathWithFilename = $this->getDemoSQL_PathWithFilename();

        // DEBUG
        if($this->debugMode > 0)
        {
            $debugMessage = "[DEMO] Demo SQL file with path: ".$demoSQL_PathWithFilename;
            $this->debugMessages[] = $debugMessage;
            //echo $debugMessage; // This class is used with redirect, do not output here
        }

        if($demoSQL_PathWithFilename != '' && is_readable($demoSQL_PathWithFilename))
        {
            // Clean the values
            $arrPluginReplaceSQL = array();

            // Fill the values
            require ($demoSQL_PathWithFilename);

            // Parse blog id and plugin version BB codes and replace data in plugin tables
            foreach($arrPluginReplaceSQL AS $sqlTable => $sqlData)
            {
                $replacedSQLData = $this->replaceBBCodes($sqlData);
                $sqlQuery = "
                    REPLACE INTO `{$this->conf->getPrefix()}{$sqlTable}` {$replacedSQLData}
                ";

                // DEBUG
                if($this->debugMode == 2)
                {
                    $debugMessage = "{$sqlQuery};";
                    $this->debugMessages[] = $debugMessage;
                    //echo "<br />{$debugMessage}"; // This class is used with redirect, do not output here
                }

                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === FALSE)
                {
                    // DEBUG
                    if($this->debugMode > 0)
                    {
                        $debugMessage = "[DEMO] FAILED TO REPLACE IN PLUGIN TABLE: ".nl2br(esc_html($sqlQuery));
                        $this->debugMessages[] = $debugMessage;
                        //echo $debugMessage; // This class is used with redirect, do not output here
                    }
                    $inserted = FALSE;
                    // NOTE: Do not break the loop here - let it proceed and sum-up results at the end
                }
            }
        } else
        {
            $this->errorMessages[] = $this->lang->getPrint('LANG_DEMO_SQL_FILE_DOES_NOT_EXIST_OR_IS_NOT_READABLE_TEXT');
        }

        if($inserted === FALSE)
        {
            $this->errorMessages[] = $this->lang->getPrint('LANG_DEMO_INSERTION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getPrint('LANG_DEMO_INSERTED_TEXT');
        }

        return $inserted;
    }

    /**
     * @return bool
     */
    public function deleteContent()
    {
        // Clear all tables
        $deleted = TRUE;
        foreach(static::$pluginTables AS $paramPluginTable)
        {
            $validPluginTable = esc_sql(sanitize_text_field($paramPluginTable)); // for sql queries only
            if($validPluginTable == "settings")
            {
                // Settings table
                $ok = $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}".$validPluginTable."
                    WHERE conf_key NOT IN ('conf_plugin_version', 'conf_timestamp')
                    AND blog_id='{$this->conf->getBlogId()}'
                ");
            } else
            {
                // Other table
                $ok = $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}".$validPluginTable."
                    WHERE blog_id='{$this->conf->getBlogId()}'
                ");
            }

            if($ok === FALSE)
            {
                $deleted = FALSE;
            }
        }

        return $deleted;
    }

    /**
     * @return string
     */
    private function getDemoSQL_PathWithFilename()
    {
        $demoSQL_PathWithFilename = '';

        $extDemosPath = $this->conf->getRouting()->getSQLsPath('', FALSE);

        $phpFiles = array();
        if(is_dir($extDemosPath))
        {
            // Get PHP folder file list
            $tmpPhpFiles = StaticFile::getFolderFileList($extDemosPath, array("php"));
            $tmpFiles = array();
            foreach ($tmpPhpFiles AS $tmpPhpFile)
            {
                if(!in_array($tmpPhpFile, $tmpFiles))
                {
                    $tmpFiles[] = $tmpPhpFile;
                    $phpFiles[] = array(
                        "file_path" => $extDemosPath,
                        "file_name" => $tmpPhpFile,
                    );
                }
            }
        }

        foreach ($phpFiles AS $phpFile)
        {
            $break = FALSE;
            $validCurrentDemoId = 0;
            $currentDemoEnabled = FALSE;
            // Case-insensitive check - Find the position of the last occurrence of a case-insensitive substring in a string
            $firstPHP_DemoPos = stripos($phpFile['file_name'], "DemoSQL");
            $lastPHP_Pos = strripos($phpFile['file_name'], ".php");
            $requiredPHP_Pos = strlen($phpFile['file_name']) - strlen(".php");
            $phpDemoData = array();
            if($firstPHP_DemoPos !== FALSE && $lastPHP_Pos === $requiredPHP_Pos)
            {
                $phpDemoData = get_file_data($phpFile['file_path'].$phpFile['file_name'], array('DemoUID' => 'Demo UID', 'DemoName' => 'Demo Name', 'DemoEnabled' => 'Demo Enabled'));

                // Format data
                $validCurrentDemoId = intval($phpDemoData['DemoUID']);
                $currentDemoEnabled = intval($phpDemoData['DemoEnabled']) == 1 ? TRUE : FALSE;

                if($validCurrentDemoId == $this->demoId)
                {
                    $break = TRUE;
                    if($currentDemoEnabled == TRUE)
                    {
                        $validFilePath = sanitize_text_field($phpFile['file_path']);
                        $validFileName = sanitize_file_name($phpFile['file_name']);
                        $demoSQL_PathWithFilename = $validFilePath.$validFileName;
                    }
                }
            }

            // DEBUG
            if($this->debugMode == 2)
            {
                $debugMessage = "<br />[DEMO] Current Demo Id: {$validCurrentDemoId}";
                $debugMessage .= "<br />[DEMO] Current Demo Enabled: ".var_export($currentDemoEnabled, TRUE);
                $debugMessage .= "<br />[DEMO] \$phpDemoData: " . nl2br(print_r($phpDemoData, TRUE));
                $debugMessage .= "[DEMO] File path: {$phpFile['file_path']}";
                $debugMessage .= "<br />[DEMO] Filename: {$phpFile['file_name']}";
                $debugMessage .= "<br />[DEMO] Demo SQL path with filename: {$demoSQL_PathWithFilename}";
                $debugMessage .= "<br />[DEMO] \$firstPHP_DemoPos: {$firstPHP_DemoPos} === 0";
                $debugMessage .= "<br />[DEMO] \$lastPHP_Pos: {$lastPHP_Pos} === \$requiredPHP_Pos: {$requiredPHP_Pos}";
                $debugMessage .= "<br />[DEMO] BREAK (Match found): ".var_export($break, TRUE);
                $this->debugMessages[] = $debugMessage;
                // echo "<br />".$debugMessage; // This class is used with redirect, do not output here
            }

            if($break)
            {
                break;
            }
        }

        return $demoSQL_PathWithFilename;
    }
}