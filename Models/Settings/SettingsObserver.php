<?php
/**
 * Control Root Class - we use it in initializer, so it cant be abstract
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Settings;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\PrimitiveObserverInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class SettingsObserver implements PrimitiveObserverInterface
{
    private $conf 	                    = NULL;
    private $lang 		                = NULL;
    private $debugMode 	                = 0;
    private $settings                   = array();
    private static $cachedSettings      = array();
    private static $lastPrefix          = ""; // SQL Optimization
    private static $lastBlogId          = ""; // SQL Optimization

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
     * We want to keep this public, as we not always sure, if the settings are needed to be set
     */
    public function setAll()
    {
        // SQL OPTIMIZATION: If the same query already ran
        if($this->conf->getPrefix() == static::$lastPrefix && $this->conf->getBlogId() == static::$lastBlogId)
        {
            // SQL OPTIMIZATION: Pull settings from cache
            $this->settings = static::$cachedSettings;
        } else
        {
            // Process regular query
            $rows = $this->conf->getInternalWPDB()->get_results("
                SELECT conf_key, conf_value, conf_translatable
                FROM {$this->conf->getPrefix()}settings
                WHERE blog_id='{$this->conf->getBlogId()}'
            ", ARRAY_A);

            foreach ($rows AS $row)
            {
                if($row['conf_key'])
                {
                    // make edit ready
                    $key = sanitize_key($row['conf_key']);
                    $value = stripslashes(trim($row['conf_value']));
                    $translatedValue = $row['conf_translatable'] == 1 ? $this->lang->getTranslated($row['conf_key'], $row['conf_value']) : $row['conf_value'];

                    $this->settings[$key] = $value;
                    $this->settings[$key.'_translatable'] = $row['conf_translatable'];
                    $this->settings['translated_'.$key] = $translatedValue;
                }
            }

            // Update cache details
            static::$lastPrefix = $this->conf->getPrefix();
            static::$lastBlogId = $this->conf->getBlogId();
            static::$cachedSettings = $this->settings;
        }
    }

    /**
     * Returns with conf_ prefix
     * @return array
     */
    public function getAll()
    {
        return $this->settings;
    }

    public function getTranslated($key, $paramDefaultValue = '')
    {
        return $this->get($key, $paramDefaultValue, TRUE);
    }

    public function get($key, $paramDefaultValue = '', $paramTranslated = FALSE)
    {
        $ret = sanitize_text_field($paramDefaultValue);
        $sanitizedKey = sanitize_key($key);
        if($sanitizedKey != "")
        {
            if($paramTranslated == TRUE)
            {
                $ret = isset($this->settings['translated_'.$sanitizedKey]) ? $this->settings['translated_'.$sanitizedKey] : sanitize_text_field($paramDefaultValue);
            } else
            {
                $ret = isset($this->settings[$sanitizedKey]) ? $this->settings[$sanitizedKey] : sanitize_text_field($paramDefaultValue);
            }
        }

        return $ret;
    }
}