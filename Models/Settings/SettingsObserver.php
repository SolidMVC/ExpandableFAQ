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
use ExpandableFAQ\Models\Country\CountriesObserver;
use ExpandableFAQ\Models\PrimitiveObserverInterface;
use ExpandableFAQ\Models\DecisionMakerType\DecisionMakerType;
use ExpandableFAQ\Models\DecisionMakerType\DecisionMakerTypesObserver;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class SettingsObserver implements PrimitiveObserverInterface
{
    private $conf 	                    = NULL;
    private $lang 		                = NULL;
    private $debugMode 	                = 0;
    private $settings                   = array();

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
                $this->settings['print_'.$key] = esc_html($value);
                $this->settings['print_translated_'.$key] = esc_html($translatedValue);
                $this->settings['edit_'.$key] = esc_attr($value);
            }
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

    public function getPrint($key, $paramDefaultValue = '', $paramTranslated = FALSE)
    {
        $ret = sanitize_text_field($paramDefaultValue);
        $sanitizedKey = sanitize_key($key);
        if($sanitizedKey != "")
        {
            if($paramTranslated == TRUE)
            {
                $ret = isset($this->settings['print_translated_'.$sanitizedKey]) ? esc_html($this->settings['print_translated_'.$sanitizedKey]) : esc_html(sanitize_text_field($paramDefaultValue));
            } else
            {
                $ret = isset($this->settings['print_'.$sanitizedKey]) ? esc_html($this->settings['print_'.$sanitizedKey]) : esc_html(sanitize_text_field($paramDefaultValue));
            }
        }

        return $ret;
    }


    /*****************************************************************************/
    /***************************** SETTINGS SECTION ******************************/
    /*****************************************************************************/

    /**
     * @param int $val
     * @param string $type - "YES/NO" (DEFAULT), "SHOW/HIDE", "ENABLED/DISABLED"
     * @return string
     */
    public function generateOption($val = 0, $type = "YES/NO")
    {
        $htmlOption = '';
        if($type == "SHOW/HIDE")
        {
            $arr = array(
                1 => $this->lang->getPrint('LANG_VISIBLE_TEXT'),
                0 => $this->lang->getPrint('LANG_HIDDEN_TEXT'),
            );
        } else if($type == "ENABLED/DISABLED")
        {
            $arr = array(
                1 => $this->lang->getPrint('LANG_ENABLED_TEXT'),
                0 => $this->lang->getPrint('LANG_DISABLED_TEXT'),
            );
        } else
        {
            $arr = array(
                1 => $this->lang->getPrint('LANG_YES_TEXT'),
                0 => $this->lang->getPrint('LANG_NO_TEXT'),
            );
        }

        foreach($arr as $key => $value)
        {
            if($val == $key)
            {
                $htmlOption .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
            } else
            {
                $htmlOption .= '<option value="'.$key.'">'.$value.'</option>';
            }
        }
        return $htmlOption;
    }
}