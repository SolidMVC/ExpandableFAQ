<?php
/**
 * Note: Variable caching via session variables is faster & more secure than data caching via cookies-only
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Cache;

final class StaticSession implements StaticCacheInterface
{
    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramHTMLs
     */
    public static function cacheHTMLArray($paramKey, array $paramHTMLs)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $ksesedHTMLs = array();
        foreach($paramHTMLs AS $paramHTML)
        {
            // HTML is allowed here
            $ksesedHTMLs[] = wp_kses_post($paramHTML);
        }

        if(sizeof($ksesedHTMLs) > 0)
        {
            if(isset($_SESSION[$sanitizedKey]))
            {
                $_SESSION[$sanitizedKey] .= '<br />'.implode('<br />', $ksesedHTMLs);
            } else
            {
                $_SESSION[$sanitizedKey] = implode('<br />', $ksesedHTMLs);
            }
        }
    }

    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramValues
     */
    public static function cacheValueArray($paramKey, array $paramValues)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $arrSanitizedValues = array();
        foreach($paramValues AS $paramValue)
        {
            // Only text is allowed
            $arrSanitizedValues[] = sanitize_text_field($paramValue);
        }

        if(sizeof($arrSanitizedValues) > 0)
        {
            if(isset($_SESSION[$sanitizedKey]))
            {
                $_SESSION[$sanitizedKey] .= '<br />'.implode('<br />', $arrSanitizedValues);
            } else
            {
                $_SESSION[$sanitizedKey] = implode('<br />', $arrSanitizedValues);
            }
        }
    }

    /**
     * @param string $paramKey
     * @param string $paramHTML
     */
    public static function cacheHTML($paramKey, $paramHTML)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $ksesedHTML = wp_kses_post($paramHTML); // HTML is allowed here

        if(sizeof($ksesedHTML) > 0)
        {
            if(isset($_SESSION[$sanitizedKey]))
            {
                $_SESSION[$sanitizedKey] .= '<br />'.$ksesedHTML;
            } else
            {
                $_SESSION[$sanitizedKey] = $ksesedHTML;
            }
        }
    }

    /**
     * @param string $paramKey
     * @param string $paramValue
     */
    public static function cacheValue($paramKey, $paramValue)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $sanitizedValue = sanitize_text_field($paramValue); // Only text is allowed

        if(sizeof($sanitizedValue) > 0)
        {
            if(isset($_SESSION[$sanitizedKey]))
            {
                $_SESSION[$sanitizedKey] .= '<br />'.$sanitizedValue;
            } else
            {
                $_SESSION[$sanitizedKey] = $sanitizedValue;
            }
        }
    }

    /**
     * @param string $paramKey
     * @return string
     */
    public static function getHTMLOnce($paramKey)
    {
        $retHTML = "";
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_SESSION[$sanitizedKey]))
        {
            $arrRAW_HTMLs = explode("<br />", $_SESSION[$sanitizedKey]);
            $arrHTMLs = array();
            foreach($arrRAW_HTMLs AS $rawHTML)
            {
                // HTML is allowed here
                $arrHTMLs[] = wp_kses_post($rawHTML);
            }
            $retHTML = implode("<br />", $arrHTMLs);

            // All done with session variable - now unset it
            unset($_SESSION[$sanitizedKey]);
        }

        return $retHTML;
    }

    /**
     * @param string $paramKey
     * @return string
     */
    public static function getValueOnce($paramKey)
    {
        $retValue = "";
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_SESSION[$sanitizedKey]))
        {
            $arrRAW_Values = explode("<br />", $_SESSION[$sanitizedKey]);
            $arrValues = array();
            foreach($arrRAW_Values AS $rawValue)
            {
                // Only text is allowed
                $arrValues[] = esc_html(sanitize_text_field($rawValue));
            }
            $retValue = implode("<br />", $arrValues);

            // All done with session variable - now unset it
            unset($_SESSION[$sanitizedKey]);
        }

        return $retValue;
    }

    /**
     * @param string $paramKey
     * @return void
     */
    public static function unsetKey($paramKey)
    {
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_SESSION[$sanitizedKey]))
        {
            unset($_SESSION[$sanitizedKey]);
        }
    }
}