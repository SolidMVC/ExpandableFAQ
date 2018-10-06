<?php
/**
 * Setting class. It is on purpose don't have the $settings parameter
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Settings;
use ExpandableFAQ\Models\AbstractStack;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class Setting extends AbstractStack
{
    private $conf 	    = NULL;
    private $lang 		= NULL;
    private $debugMode 	= 0;
    private $confKey      = '';

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramKey)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set the key
        $this->confKey = sanitize_key($paramKey);
    }

    /**
     * For internal class use only
     * @param string $paramConfKey
     * @return mixed
     */
    private function getDataFromDatabaseByKey($paramConfKey)
    {
        $validConfKey = StaticValidator::getValidKey($paramConfKey, TRUE);
        $sqlData = "
            SELECT *
            FROM {$this->conf->getPrefix()}settings
            WHERE conf_key='{$validConfKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        $retData = $this->conf->getInternalWPDB()->get_row($sqlData, ARRAY_A);

        return $retData;
    }

    public function getConfKey()
    {
        return $this->confKey;
    }

    /**
     * @return string
     */
    public function getEditValue()
    {
        $editValue = '';
        $ret = $this->getDataFromDatabaseByKey($this->confKey);

        if(!is_null($ret))
        {
            $editValue = esc_attr(stripslashes($ret['conf_value']));
        }

        return $editValue;
    }

    public function getPrintValue()
    {
        $printValue = '';
        $ret = $this->getDataFromDatabaseByKey($this->confKey);

        if(!is_null($ret))
        {
            $printValue = esc_html(stripslashes($ret['conf_value']));
        }

        return $printValue;
    }

    public function getDetails($paramIncludeUnclassified = FALSE)
    {
        $ret = $this->getDataFromDatabaseByKey($this->confKey);

        if(!is_null($ret))
        {
            // Make raw
            $ret['conf_key'] = stripslashes($ret['conf_key']);
            $ret['conf_value'] = stripslashes($ret['conf_value']);

            // Process translation
            $ret['translated_conf_value'] = $ret['conf_translatable'] == 1 ? $this->lang->getTranslated($ret['conf_key'], $ret['conf_value']) : $ret['conf_value'];

            // Prepare output for print
            $ret['print_conf_key'] = esc_html($ret['conf_key']);
            $ret['print_conf_value'] = esc_html($ret['conf_value']);
            $ret['print_translated_conf_value'] = esc_html($ret['translated_conf_value']);

            // Prepare output for edit
            $ret['edit_conf_key'] = esc_attr($ret['conf_key']); // for input field
            $ret['edit_conf_value'] = esc_attr($ret['conf_value']); // for input field
        } else if($paramIncludeUnclassified === TRUE)
        {
            // Fields
            $ret = array();
            $ret['conf_key'] = '';
            $ret['conf_value'] = '';
            $ret['conf_translatable'] = 0;
            $ret['blog_id'] = $this->conf->getBlogId();

            // Process translation
            $ret['translated_conf_value'] = '';

            // Prepare output for print
            $ret['print_conf_key'] = '';
            $ret['print_conf_value'] = '';
            $ret['print_translated_conf_value'] = '';

            // Prepare output for edit
            $ret['edit_conf_key'] = ''; // for input field
            $ret['edit_conf_value'] = ''; // for input field
        }

        return $ret;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    public function saveCheckbox($paramValue)
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        $validValue = $paramValue == 1 ? 1 : 0;

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveTime($paramValue)
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        $validValue = StaticValidator::getValidISOTime($paramValue, 'H:i:s');

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveNumber($paramValue, $defaultValue = 0, $allowedValues = array(), $paramOnlyPositive = TRUE)
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        if($paramOnlyPositive)
        {
            // Only positive numbers allowed
            $validValue = StaticValidator::getValidPositiveInteger($paramValue, $defaultValue);
        } else
        {
            $validValue = StaticValidator::getValidInteger($paramValue, $defaultValue);
        }
        if(sizeof($allowedValues) > 0)
        {
            $validValue = in_array($validValue, $allowedValues) ? $validValue : $defaultValue;
        }

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function resetNumber()
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='0'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    /**
     * Note: Empty slugs are not allowed
     * @param string $paramValue
     * @param bool $paramAllowEmptySlugs
     * @param array $paramArrOtherValues
     * @return bool|false|int
     */
    public function saveSlug($paramValue, $paramAllowEmptySlugs = FALSE, $paramArrOtherValues = array())
    {
        $ok = TRUE;
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        $sanitizedValue = sanitize_key($paramValue);
        $validValue = esc_sql($sanitizedValue); // for sql queries only

        // Check if the slug is not empty, if the empty slugs are not allowed
        if($sanitizedValue == "" && $paramAllowEmptySlugs === FALSE)
        {
            $ok = FALSE;
            $this->errorMessages[] = $this->lang->getPrint('LANG_SETTING_EMPTY_SLUG_NOT_ALLOWED_ERROR_TEXT');
        }

        foreach($paramArrOtherValues AS $paramOtherValue)
        {
            $sanitizedOtherSlugValue = sanitize_key($paramOtherValue);
            if($sanitizedOtherSlugValue == $sanitizedValue)
            {
                $ok = FALSE;
                $this->errorMessages[] = $this->lang->getPrint('LANG_SETTING_ALL_SLUGS_HAS_TO_DIFFER_ERROR_TEXT');
            }
        }

        $updated = FALSE;
        if($ok)
        {
            $sqlQuery = "
                UPDATE {$this->conf->getPrefix()}settings
                SET conf_value='{$validValue}'
                WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
            ";

            // DEBUG
            //echo nl2br($sqlQuery);

            $updated = $this->conf->getInternalWPDB()->query($sqlQuery);
        }

        return $updated;
    }

    public function saveKey($paramValue, $paramArrAllowedValues = array(), $paramToUppercase = TRUE, $paramSpacesAllowed = TRUE, $paramDotsAllowed = FALSE)
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        $validValue = StaticValidator::getValidCode($paramValue, '', $paramToUppercase, $paramSpacesAllowed, $paramDotsAllowed);
        if(sizeof($paramArrAllowedValues) > 0)
        {
            // Required Php 5.4+
            $validValue = in_array($validValue, $paramArrAllowedValues) ? $validValue : array_values($paramArrAllowedValues)[0];
        }

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveText($paramValue, $paramTransformToUFT8Code = FALSE)
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        if($paramTransformToUFT8Code)
        {
            $validValue = htmlentities(sanitize_text_field($paramValue), ENT_COMPAT, 'utf-8');
        } else
        {
            $validValue = esc_sql(sanitize_text_field($paramValue)); // for sql queries only
        }

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveUsername($paramValue, $paramToLowercase = TRUE)
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        $validValue = StaticValidator::getValidUsername($paramValue, '', $paramToLowercase); // for sql queries only

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveEmail($paramValue)
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', FALSE, FALSE, FALSE);
        $validValue = esc_sql(sanitize_email($paramValue)); // for sql queries only

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function registerForTranslation()
    {
        $settingDetails = $this->getDetails();
        if(!is_null($settingDetails))
        {
            $this->lang->register($settingDetails['conf_key'], $settingDetails['conf_value']);
            $this->okayMessages[] = $this->lang->getPrint('LANG_SETTING_REGISTERED_TEXT');
        }
    }
}