<?php
/**
 * Plugin

 * @note - It does not have settings param in constructor on purpose!
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Install;
use ExpandableFAQ\Models\AbstractStack;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class Install extends AbstractStack implements StackInterface, InstallInterface
{
    private $conf           = NULL;
    private $lang 		    = NULL;
    private $debugMode 	    = 0;
    private $blogId         = 0;
    /**
     * @var array - table class names with full qualified namespace, ordered by table name
     */
    private static $tableClasses    = array(
        "\\ExpandableFAQ\\Models\\FAQ\\FAQsTable",
        "\\ExpandableFAQ\\Models\\Settings\\SettingsTable",
    );

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
     * @return array
     */
    public static function getTableClasses()
    {
        return static::$tableClasses;
    }

    /**
     * Insert all content
     * @note - for security and standardization reasons the concrete file name is encoded into this method
     * @return bool
     */
    public function insertContent()
    {
        // Language file already loaded, so we can use translated text

        // Insert SQL
        $inserted = TRUE;

        $installSQLFileNameWithPath = $this->conf->getRouting()->getSQLsPath('InstallSQL.php', TRUE);
        if($installSQLFileNameWithPath != '' && is_readable($installSQLFileNameWithPath))
        {
            // Clean the values
            $arrInsertSQL = array();
            $arrPluginInsertSQL = array();

            // Fill the values
            require $installSQLFileNameWithPath;

            // Insert data to WP tables
            foreach($arrInsertSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodes($sqlData);
                $sqlQuery = "INSERT INTO `{$this->conf->getBlogPrefix($this->blogId)}{$sqlTable}` {$replacedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === FALSE)
                {
                    $inserted = FALSE;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_WP_TABLE_INSERTION_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "INSERT FAILED TO WP TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }

            // Parse shortcodes and make SQL queries
            foreach($arrPluginInsertSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodes($sqlData);

                // Note: we don't use blog_id param for getPrefix, as it is always the same
                $sqlQuery = "INSERT INTO `{$this->conf->getPrefix()}{$sqlTable}` {$replacedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === FALSE)
                {
                    $inserted = FALSE;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_PLUGIN_TABLE_INSERTION_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "INSERT FAILED TO PLUGIN TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }
        }

        if($inserted === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_INSTALL_INSERTION_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_INSTALL_INSERTED_TEXT'), $this->blogId);
        }

        return $inserted;
    }

    /**
     * Replace special content
     * @note1 - fires every time when plugin is enabled, or enabled->disabled->enabled, etc.
     * @note2 - used mostly to set image dimensions right
     * @note3 - for security and standardization reasons the concrete file name is encoded into this method
     * @return bool
     */
    public function resetContent()
    {
        // Replace SQL
        $replaced = TRUE;

        $resetSQLFileNameWithPath = $this->conf->getRouting()->getSQLsPath('ResetSQL.php', TRUE);
        if($resetSQLFileNameWithPath != '' && is_readable($resetSQLFileNameWithPath))
        {
            // Clean the values
            $arrReplaceSQL = array();
            $arrPluginReplaceSQL = array();

            // Fill the values
            require $resetSQLFileNameWithPath;

            // Replace data to WP tables
            foreach($arrReplaceSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodes($sqlData);
                // Note - MySQL 'REPLACE INTO' works like MySQL 'INSERT INTO', except that if there is a row
                // with the same key you are trying to insert, it will be deleted on replace instead of giving you an error.
                $sqlQuery = "REPLACE INTO `{$this->conf->getBlogPrefix($this->blogId)}{$sqlTable}` {$replacedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === FALSE)
                {
                    $replaced = FALSE;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_WP_TABLE_REPLACE_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "REPLACE FAILED TO WP TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }

            // Parse shortcodes and make SQL queries
            foreach($arrPluginReplaceSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodes($sqlData);
                // Note: we don't use blog_id param for getPrefix, as it is always the same
                $sqlQuery = "REPLACE INTO `{$this->conf->getPrefix()}{$sqlTable}` {$replacedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);

                if($ok === FALSE)
                {
                    $replaced = FALSE;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_PLUGIN_TABLE_REPLACE_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "REPLACE FAILED TO PLUGIN TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }
        }

        if($replaced === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_INSTALL_REPLACE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_INSTALL_REPLACED_TEXT'), $this->blogId);
        }

        return $replaced;
    }

    /**
     * No parametrization here
     * @param string $trustedText
     * @return mixed
     */
    private function parseBBCodes(
        $trustedText
    ) {
        $validBlogId = intval($this->blogId);
        $pluginSemver = StaticValidator::getValidSemver($this->conf->getPluginSemver());

        $arrFrom = array(
            '[BLOG_ID]',
            '[PLUGIN_SEMVER]', '[TIMESTAMP]',
        );
        $arrTo = array(
            $validBlogId,
            $pluginSemver, time(),
        );
        $updatedText = str_replace($arrFrom, $arrTo, $trustedText);

        return $updatedText;
    }
}