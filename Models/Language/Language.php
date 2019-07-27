<?php
/**
 * Language Manager

 * @note1: This class is made to work without any other external plugin classes,
 * and it should remain that for independent include support.
 * @note2: We can use static:: here, because version check already happened before
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Language;

final class Language implements LanguageInterface
{
    // This is the error text before the language file will be loaded
    const LANG_ERROR_LANGUAGE_KEY_S_DO_NOT_EXIST_TEXT = 'Error: Language key %s do not exist!';
    const LANG_ERROR_UNABLE_TO_LOAD_LANGUAGE_FILE_TEXT = 'Unable to load %s language file from none of it&#39;s 2 paths.';
    private $locale = "en_US";
    private $lang = array();
    private $textDomain = 'unknown';
    private $WMPLEnabled = FALSE;
    /*
     * We can keep this ON until we don't have 1000's of entries in item list, options list, location list or extras list
     * It does not applies to orders table, nor respondents table, so in most of scenarios it will be ok.
     * @note - depends on $WMPLEnabled
     */
    private $translateDatabase = TRUE;

    /**
     * @param string $paramTextDomain
     * @param string $paramGlobalLangPath
     * @param string $paramLocalLangPath
     * @param string $paramLocale
     * @param bool $paramStrictLocale
     * @throws \Exception
     */
    public function __construct($paramTextDomain, $paramGlobalLangPath, $paramLocalLangPath, $paramLocale = "en_US", $paramStrictLocale = FALSE)
    {
        $this->setLocale($paramGlobalLangPath, $paramLocalLangPath, $paramLocale, $paramStrictLocale);
        $this->setTranslate($paramTextDomain);
    }

    /**
     * Load locale file
     * @param string $paramGlobalLangPath
     * @param string $paramLocalLangPath
     * @param string $paramLocale
     * @param bool $paramStrictLocale
     * @throws \Exception
     */
    private function setLocale($paramGlobalLangPath, $paramLocalLangPath, $paramLocale = "en_US", $paramStrictLocale = FALSE)
    {
        $validGlobalLangPath = sanitize_text_field($paramGlobalLangPath);
        $validLocalLangPath = sanitize_text_field($paramLocalLangPath);
        $validLocale = !is_array($paramLocale) ? preg_replace('[^-_0-9a-zA-Z]', '', $paramLocale) : 'en_US';

        // If the locale mode is NOT strict, and 'lt_LT.php' file does not exist
        // neither in /wp-content/languages/<EXT_FOLDER_NAME>/,
        // nor in /wp-content/plugins/ExpandableFAQ/Languages/<EXT_FOLDER_NAME>/ folders
        if(
            $paramStrictLocale === FALSE &&
            is_readable($validGlobalLangPath.$validLocale.'.php') === FALSE &&
            is_readable($validLocalLangPath.$validLocale.'.php') === FALSE
        )
        {
            // then set language default to en_US (with en_US.php as a corresponding file)
            $validLocale = "en_US";
        }

        if($validGlobalLangPath != "SKIP" && is_readable($validGlobalLangPath.$validLocale.'.php'))
        {
            // Set used system locale
            $this->locale = $validLocale;

            // Include the Unicode CLDR language file
            $unicodeCLRDFileToInclude = $validGlobalLangPath.$validLocale.'.php';
            $lang = include $unicodeCLRDFileToInclude;
        } else if($validLocalLangPath != "SKIP" && is_readable($validLocalLangPath.$validLocale.'.php'))
        {
            // Set used system locale
            $this->locale = $validLocale;

            // Include the Unicode CLDR language file
            $this->locale = $validLocale;
            $unicodeCLRDFileToInclude = $validLocalLangPath.$validLocale.'.php';
            $lang = include $unicodeCLRDFileToInclude;
        } else
        {
            // Set used system locale
            $this->locale = "";

            // Language file is not readable - do not include the language file
            throw new \Exception(sprintf(static::LANG_ERROR_UNABLE_TO_LOAD_LANGUAGE_FILE_TEXT, $validLocale));
        }

        // NOTE: This might be a system slowing-down process
        if(sizeof($lang) > 0)
        {
            foreach($lang AS $key => $value)
            {
                $this->addText($key, $value);
            }
        }
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    private function setTranslate($paramTextDomain)
    {
        $this->textDomain = sanitize_key($paramTextDomain);
        // For the front-end is_plugin_active(..) function is not included automatically
        if(!is_admin() && !is_network_admin())
        {
            include_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        // WMPL - Determine if WMPL string translation module is enabled
        $this->WMPLEnabled = is_plugin_active('wpml-string-translation/plugin.php');
    }

    /**
     * Add new text row
     * @param $paramKey
     * @param $paramValue
     */
    private function addText($paramKey, $paramValue)
    {
        // Sanitize key
        $sanitizedKey = strtoupper(sanitize_key($paramKey));
        if(strlen($sanitizedKey) > 0)
        {
            // Get print value, with line-breaks support
            $sanitizedValueArray = array_map('sanitize_text_field', explode("\n", $paramValue));
            $sanitizedMultilineValue = implode("\n", $sanitizedValueArray);

            // Assign the language internally
            $this->lang[$sanitizedKey] = $sanitizedMultilineValue;
        }
    }

    /**
     * NOTE #1: Supports multiline text
     * NOTE #2: Unescaped
     * @param string $paramKey
     * @return string
     */
    public function getText($paramKey)
    {
        // Get valid key
        $validKey = strtoupper(sanitize_key($paramKey));
        $retText = "";
        if(strlen($validKey) > 0)
        {
            if(isset($this->lang[$validKey]))
            {
                $retText = $this->lang[$validKey];
            } else
            {
                $retText = sprintf(static::LANG_ERROR_LANGUAGE_KEY_S_DO_NOT_EXIST_TEXT, $validKey);
            }
        }

        return $retText;
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escSQL($paramKey)
    {
        return esc_sql($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escAttr($paramKey)
    {
        return esc_attr($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escBrHTML($paramKey)
    {
        return esc_br_html($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escHTML($paramKey)
    {
        return esc_html($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escJS($paramKey)
    {
        return esc_js($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escTextarea($paramKey)
    {
        return esc_textarea($this->getText($paramKey));
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->lang;
    }

    /**
     * Is current language is right to left?
     * @return bool
     */
    public function isRTL()
    {
        return ((isset($this->lang['RTL']) && $this->lang['RTL'] == TRUE) ? TRUE : FALSE);
    }

    /**
     * Used for items and extras in order summary
     * Localize the amount text by quantity
     * @param $quantity
     * @param $singularText
     * @param $pluralText
     * @param $pluralText2
     * @return mixed
     */
    public function getQuantityText($quantity, $singularText, $pluralText, $pluralText2)
    {
        // Set default - plural text
        $unitsText = $pluralText;

        if($quantity == 1)
        {
            // Change to singular if it's 1
            $unitsText = $singularText;
        } else if($quantity == 0 || ($quantity % 10 == 0) || ($quantity >= 11 && $quantity <= 19))
        {
            // Change to plural 2, if it's 0, divides by 10 without fraction (10, 20, 30, 40, ..), or is between 11 to 19
            $unitsText = $pluralText2;
        }

        return $unitsText;
    }

    /**
     * Used for items and extras in order summary
     * Localize the amount text by quantity
     * @param $position
     * @param $textST
     * @param $textND
     * @param $textRD
     * @param $textTH
     * @return string
     */
    public function getPositionText($position, $textST, $textND, $textRD, $textTH)
    {
        // Set default - th
        $orderExt = $textTH;

        if($position == 1 || ($position % 10 == 1 && $position >= 20))
        {
            //-st. Change to text 1, if it's 1, divides by 10 with fraction = 1 and is more than 20 (21,31,41,..)
            $orderExt = $textST;
        } else if($position == 2 || ($position % 10 == 2 && $position >= 20))
        {
            //-nd. Change to text 1, if it's 2, divides by 10 with fraction = 2 and is more than 20 (22,32,42,..)
            $orderExt = $textND;
        } else if($position == 3 || ($position % 10 == 3 && $position >= 20))
        {
            //-rd. Change to text 1, if it's 3, divides by 10 with fraction = 3 and is more than 20 (23,33,43,..)
            $orderExt = $textRD;
        } else if($position == 0 || ($position % 10 == 0) || ($position >= 11 && $position <= 19))
        {
            //-th. Change to text 0, if it's 0, divides by 10 without fraction (10,20,30,40,..), or is between 11 to 19
            $orderExt = $textTH;
        }

        return $orderExt;
    }


    /**
     * Localize the time text by period
     * @param $number
     * @param $singularText
     * @param $pluralText
     * @param $pluralText2
     * @return mixed
     */
    public function getTimeText($number, $singularText, $pluralText, $pluralText2)
    {
        // Set default - plural text
        $timeText = $pluralText;

        if($number == 1)
        {
            // Change to singular if it's 1
            $timeText = $singularText;
        } else if($number == 0 || ($number % 10 == 0) || ($number >= 11 && $number <= 19))
        {
            // Change to plural 2, if it's 0, divides by 10 without fraction (10,20,30,40,..), or is between 11 to 19
            $timeText = $pluralText2;
        }

        return $timeText;
    }

    /*************** TRANSLATE PART *****************/
    public function canTranslateSQL()
    {
        if($this->WMPLEnabled === TRUE && $this->translateDatabase === TRUE)
        {
            return TRUE;
        } else
        {
            return FALSE;
        }
    }

    /**
     * Add new text row for translation
     * Used mostly on pre-loaders of all data to register all DB texts
     * @param $paramKey
     * @param $paramValue
     */
    public function register($paramKey, $paramValue)
    {
        // Sanitize key
        $sanitizedKey = strtolower(sanitize_key($paramKey));

        if(strlen($sanitizedKey) > 0)
        {
            // Sanitize value
            $sanitizedValue = sanitize_text_field($paramValue);

            // WPML - Register string for translation with WMPL
            // TODO: Check for multiline WPML support
            do_action('wpml_register_single_string', $this->textDomain, $sanitizedKey, $sanitizedValue);
        }
    }

    /**
     * @note - we should not do any value sanitization here, as it may break like breaks etc.
     *         All that is done elsewhere
     * @param $paramKey
     * @param $paramNonTranslatedValue
     * @return string
     */
    public function getTranslated($paramKey, $paramNonTranslatedValue)
    {
        $retValue = $paramNonTranslatedValue;

        // Process only if we allow translations
        if($this->canTranslateSQL())
        {
            // Sanitize key
            $sanitizedKey = strtolower(sanitize_key($paramKey));
            if(strlen($sanitizedKey) > 0)
            {
                // WPML - translate single string with WMPL
                $retValue = apply_filters('wpml_translate_single_string', $paramNonTranslatedValue, $this->textDomain, $sanitizedKey);
            }
        }

        return $retValue;
    }

    /**
     * Get's a translated link if WPML is used. In multisite scenario we never need that function, as get_permalink() is always correct with standard it
     * @uses SitePress::get_object_id
     * @param $paramPostId
     * @return false|string
     */
    public function getTranslatedUrl($paramPostId)
    {
        if($this->WMPLEnabled)
        {
            // WPML
            $elementType = 'any'; // Optional, default is 'post'. Use post, page, {custom post type name}, nav_menu, nav_menu_item, category, tag, etc.
                                  // You can also pass 'any', to let WPML guess the type, but this will only work for posts.
            $alwaysReturnValue = TRUE; // Optional, default is FALSE. If set to TRUE it will always return a value (the original value, if translation is missing).
            $url = get_permalink(apply_filters('wpml_object_id', $paramPostId, $elementType, $alwaysReturnValue));
        } else
        {
            // Standard
            $url = get_permalink($paramPostId);
        }

        return $url;
    }
}