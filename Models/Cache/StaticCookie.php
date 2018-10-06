<?php
/**
 * Note: Data caching via cookies-only is slower & less secure than data caching via sessions
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Cache;

final class StaticCookie implements StaticCacheInterface
{
    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramHTMLs
     */
    public static function cacheHTMLArray($paramKey, array $paramHTMLs)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $arrKsesedHTMLs = array();
        foreach($paramHTMLs AS $paramHTML)
        {
            // HTML is allowed here
            $arrKsesedHTMLs[] = wp_kses_post($paramHTML);
        }

        if(sizeof($arrKsesedHTMLs) > 0)
        {
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $_COOKIE[$sanitizedKey].'<br />'.implode('<br />', $arrKsesedHTMLs);
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = implode('<br />', $arrKsesedHTMLs);
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
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
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $_COOKIE[$sanitizedKey].'<br />'.implode('<br />', $arrSanitizedValues);
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = implode('<br />', $arrSanitizedValues);
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
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
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $_COOKIE[$sanitizedKey].'<br />'.$ksesedHTML;
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $ksesedHTML;
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
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
            if(isset($_COOKIE[$sanitizedKey]))
            {
                $_COOKIE[$sanitizedKey] .= '<br />'.$sanitizedValue;
            } else
            {
                $_COOKIE[$sanitizedKey] = $sanitizedValue;
            }
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $_COOKIE[$sanitizedKey].'<br />'.$sanitizedValue;
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $sanitizedValue;
                setcookie("TestCookie", $cookieValue, time()+3600*24, '/');
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
        if(isset($_COOKIE[$sanitizedKey]))
        {
            $arrRAW_HTMLs = explode("<br />", $_COOKIE[$sanitizedKey]);
            $arrHTMLs = array();
            foreach($arrRAW_HTMLs AS $rawHTML)
            {
                // HTML is allowed here
                $arrHTMLs[] = wp_kses_post($rawHTML);
            }
            $retHTML = implode("<br />", $arrHTMLs);

            // All done with cookie - now unset it
            unset($_COOKIE[$sanitizedKey]);
            setcookie($sanitizedKey, 0, time()-3600, '/'); // empty value and old timestamp
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
        if(isset($_COOKIE[$sanitizedKey]))
        {
            $arrRAW_Values = explode("<br />", $_COOKIE[$sanitizedKey]);
            $arrValues = array();
            foreach($arrRAW_Values AS $rawValue)
            {
                // Only text is allowed
                $arrValues[] = esc_html(sanitize_text_field($rawValue));
            }
            $retValue = implode("<br />", $arrValues);

            // All done with cookie - now unset it
            unset($_COOKIE[$sanitizedKey]);
            setcookie($sanitizedKey, 0, time()-3600, '/'); // empty value and old timestamp
        }

        return $retValue;
    }

    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     */
    public static function unsetKey($paramKey)
    {
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_COOKIE[$sanitizedKey]))
        {
            unset($_COOKIE[$sanitizedKey]);
            setcookie($sanitizedKey, 0, time()-3600, '/'); // empty value and old timestamp
        }
    }
}