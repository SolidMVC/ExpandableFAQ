<?php
/**
 * Modern data validator
 * Note 1: This model does not depend on any other class, except semver
 * Note 2: This model must be used in static context only
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Validation;

use ExpandableFAQ\Models\Semver\Semver;

final class StaticValidator
{
    protected static $debugMode = 0;

    public static function inWP_Debug()
    {
        $wpDebug = defined('WP_DEBUG') && WP_DEBUG == TRUE && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY == TRUE;

        return $wpDebug;
    }

    public static function wpDebugLog()
    {
        $wpDebug = defined('WP_DEBUG') && WP_DEBUG == TRUE && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG == TRUE;

        return $wpDebug;
    }

    public static function getValidPositiveInteger($paramValue, $defaultValue = 0)
    {
        $validValue = static::isPositiveInteger($paramValue) ? intval($paramValue) : $defaultValue;

        return $validValue;
    }

    public static function getValidInteger($paramValue, $defaultValue = 0)
    {
        $validValue = static::isInteger($paramValue) ? intval($paramValue) : $defaultValue;

        return $validValue;
    }

    public static function getValidPositiveFloat($paramValue, $defaultValue = 0.00)
    {
        $validValue = $paramValue > 0 ? floatval($paramValue) : $defaultValue;

        return $validValue;
    }

    function getFloatFromPriceFormattedNumber($paramPriceFormattedNumber)
    {
        return preg_replace("/([^0-9\\.])/i", "", $paramPriceFormattedNumber);
    }

    /**
     * Allow alpha-numeric chars, dashes and underscores
     * @note - This function is much faster that WordPress alternative esc_sql(sanitize_key($paramKey)),
     *         which using bunch of additional slow hooks and tree calls
     * @param string $paramKey
     * @param string $defaultKey
     * @param bool $paramToLowercase
     * @param bool $paramWildcardAllowed
     * @return string
     */
    public static function getValidKey($paramKey, $defaultKey = '', $paramToLowercase = TRUE, $paramWildcardAllowed = FALSE)
    {
        $regexp = $paramWildcardAllowed ? '[^-_0-9a-zA-Z\*]' : '[^-_0-9a-zA-Z]';
        $rawData = !is_array($paramKey) ? $paramKey : $defaultKey;
        $key = preg_replace($regexp, '', $rawData); // No sanitization, uppercase needed
        if(!is_null($key) && !is_array($key))
        {
            $validKey = $key;
        } else
        {
            $validKey = $defaultKey;
        }
        $validKey = $paramToLowercase ? strtolower($validKey) : $validKey;

        return $validKey;
    }

    /**
     * Allow alpha-numeric chars, dashes and underscores
     * NOTE #1: Spaces are mostly allowed for SKUs (item model SKU, extra SKU), codes (i.e. extension code, order code, coupon code, location code, special status code, payment method code),
     *          unique identifiers (item UID, location UID) and series (proforma series, invoice series).
     * NOTE #2: Spaces are mostly disallowed - for models (i.e. pricing model), types (i.e. transaction type, license type, counter type), group by (i.e. earnings group by), order by (i.e. customer order by)
     *          timeframe, sources (i.e. review source), actions (i.e. log action) and statuses
     * @param string $paramCode
     * @param string $defaultCode
     * @param bool $paramToUppercase
     * @param bool $paramSpacesAllowed
     * @param bool $paramDotsAllowed
     * @return string
     */
    public static function getValidCode($paramCode, $defaultCode = '', $paramToUppercase = TRUE, $paramSpacesAllowed = TRUE, $paramDotsAllowed = FALSE)
    {
        if($paramDotsAllowed)
        {
            $regexp = $paramSpacesAllowed ? '[^-_0-9a-zA-Z\. ]' : '[^-_0-9a-zA-Z\.]';
        } else
        {
            $regexp = $paramSpacesAllowed ?  '[^-_0-9a-zA-Z ]' : '[^-_0-9a-zA-Z]';
        }
        $rawData = !is_array($paramCode) ? $paramCode : $defaultCode;
        $code = preg_replace($regexp, '', $rawData); // No sanitization, uppercase needed
        if(!is_null($code) && !is_array($code))
        {
            $validCode = $code;
        } else
        {
            $validCode = $defaultCode;
        }
        $validCode = $paramToUppercase ? strtoupper($validCode) : $validCode;

        return $validCode;
    }

    /**
     * Get valid semantic version
     * @param string $paramSemver
     * @param bool $paramVersionWildcardsAllowed - used for admin side on 'any version'
     * @return string
     */
    public static function getValidSemver($paramSemver, $paramVersionWildcardsAllowed = FALSE)
    {
        $objSemver = new Semver($paramSemver, $paramVersionWildcardsAllowed);
        return $objSemver->getSemver();
    }

    /**
     * Compare two semantic versions
     * NOTE: Wildcards are supported, if allowed
     *
     * @param string $paramSemver1
     * @param string $paramSemver2
     * @param string $paramOperator
     * @param bool $paramSemver1VersionWildcardsAllowed
     * @param bool $paramSemver2VersionWildcardsAllowed
     * @return bool
     */
    public static function compareSemvers($paramSemver1, $paramSemver2, $paramOperator = '==', $paramSemver1VersionWildcardsAllowed = FALSE, $paramSemver2VersionWildcardsAllowed = FALSE)
    {
        $semver1Adjustment = 0;
        $semver2Adjustment = 0;
        if(in_array($paramOperator, array('<', 'lt')))
        {
            $semver1Adjustment = $paramSemver1VersionWildcardsAllowed ? -1 : 0; // In case of negativity, semver it will set it as '0'
            $semver2Adjustment = $paramSemver2VersionWildcardsAllowed ? 1 : 0;
        } else if(in_array($paramOperator, array('>', 'gt')))
        {
            $semver1Adjustment = $paramSemver1VersionWildcardsAllowed ? 1 : 0;
            $semver2Adjustment = $paramSemver2VersionWildcardsAllowed ? -1 : 0; // In case of negativity, semver it will set it as '0'
        }
        $objSemver1 = new Semver($paramSemver1, $paramSemver1VersionWildcardsAllowed);
        $objSemver2 = new Semver($paramSemver2, $paramSemver2VersionWildcardsAllowed);

        $originalSemver1 = $objSemver1->getSemver();
        $originalSemver2 = $objSemver2->getSemver();

        // Prepare major for comparing
        if($objSemver1->getMajor() == "*" && $objSemver2->getMajor() != "*")
        {
            $objSemver1->setMajor($objSemver2->getMajor() + $semver1Adjustment);
        } else if($objSemver1->getMajor() != "*" && $objSemver2->getMajor() == "*")
        {
            $objSemver2->setMajor($objSemver1->getMajor() + $semver2Adjustment);
        } else if($objSemver1->getMajor() == "*" && $objSemver2->getMajor() == "*")
        {
            $objSemver1->setMajor(0 + $semver1Adjustment);
            $objSemver2->setMajor(0 + $semver2Adjustment);
        }

        // Prepare minor for comparing
        if($objSemver1->getMinor() == "*" && $objSemver2->getMinor() != "*")
        {
            $objSemver1->setMinor($objSemver2->getMinor() + $semver1Adjustment);
        } else if($objSemver1->getMinor() != "*" && $objSemver2->getMinor() == "*")
        {
            $objSemver2->setMinor($objSemver1->getMinor() + $semver2Adjustment);
        } else if($objSemver1->getMinor() == "*" && $objSemver2->getMinor() == "*")
        {
            $objSemver1->setMinor(0 + $semver1Adjustment);
            $objSemver2->setMinor(0 + $semver2Adjustment);
        }

        // Prepare patch for comparing
        if($objSemver1->getPatch() == "*" && $objSemver2->getPatch() != "*")
        {
            $objSemver1->setPatch($objSemver2->getPatch() + $semver1Adjustment);
        } else if($objSemver1->getPatch() != "*" && $objSemver2->getPatch() == "*")
        {
            $objSemver2->setPatch($objSemver1->getPatch() + $semver2Adjustment);
        } else if($objSemver1->getPatch() == "*" && $objSemver2->getPatch() == "*")
        {
            $objSemver1->setPatch(0 + $semver1Adjustment);
            $objSemver2->setPatch(0 + $semver2Adjustment);
        }

        $finalSemver1 = $objSemver1->getSemver();
        $finalSemver2 = $objSemver2->getSemver();

        $compareResult = version_compare($finalSemver1, $finalSemver2, $paramOperator);

        if(static::$debugMode == 1)
        {
            $printOperator = esc_html(sanitize_text_field($paramOperator));
            echo "<br /><strong>[Compare]</strong> Original Semvers: &#39;{$originalSemver1}&#39; {$printOperator} &#39;{$originalSemver2}&#39;";
            echo "<br /><strong>[Compare]</strong> Adjustments: &#39;{$semver1Adjustment}&#39; (Semver 1), &#39;{$semver1Adjustment}&#39; (Semver 2)";
            echo "<br /><strong>[Compare]</strong> Final Compare: &#39;{$finalSemver1}&#39; {$printOperator} &#39;{$finalSemver2}&#39;";
            echo "<br /><strong>[Compare]</strong> Compare Result: ".var_export($compareResult, TRUE);
        }

        return $compareResult;
    }

    /**
     * Allow alpha-numeric chars, dashes, underscores and dots
     * @param string $paramUsername
     * @param string $defaultUsername
     * @param bool $paramToLowercase
     * @return string
     */
    public static function getValidUsername($paramUsername, $defaultUsername = '', $paramToLowercase = TRUE)
    {
        $rawData = !is_array($paramUsername) ? $paramUsername : $defaultUsername;
        $username = preg_replace('[^-_0-9a-zA-Z\.]', '', $rawData); // No sanitization, uppercase needed
        if(!is_null($username) && !is_array($username))
        {
            $validUsername = $username;
        } else
        {
            $validUsername = $defaultUsername;
        }
        $validUsername = $paramToLowercase ? strtolower($validUsername) : $validUsername;

        return $validUsername;
    }

    /**
     * @param string $paramString
     * @return bool
     */
    public static function isValidDomainName($paramString)
    {
        $isValid = FALSE;
        if(is_array($paramString))
        {
            // Domain regexp that supports xn-- domains as well, and unlimited sub-domains
            // See (the updated answer part): https://stackoverflow.com/a/26987741/232330
            $domainRegexp = '^(((?!-))(xn--|_{1,1})?[a-z0-9-]{0,61}[a-z0-9]{1,1}\.)*(xn--)?([a-z0-9\-]{1,61}|[a-z0-9-]{1,30}\.[a-z]{2,}))$';
            if(preg_match($domainRegexp, $paramString) === FALSE)
            {
                // No invalid chars fount
                $isValid = TRUE;
            }
        }

        return $isValid;
    }

    /**
     * @param string $paramString
     * @param string $defaultDomain
     * @return string
     */
    public static function getValidDomainName($paramString, $defaultDomain = '')
    {
        // Domain regexp that supports xn-- domains as well, and unlimited sub-domains
        // See (the updated answer part): https://stackoverflow.com/a/26987741/232330
        $domainRegexp = '^(((?!-))(xn--|_{1,1})?[a-z0-9-]{0,61}[a-z0-9]{1,1}\.)*(xn--)?([a-z0-9\-]{1,61}|[a-z0-9-]{1,30}\.[a-z]{2,}))$';
        $rawData = !is_array($paramString) ? $paramString : $defaultDomain;
        $domainName = preg_replace($domainRegexp, '', $rawData); // No sanitization, uppercase needed
        if(!is_null($domainName) && !is_array($domainName))
        {
            $validDomainName = $domainName;
        } else
        {
            $validDomainName = $defaultDomain;
        }

        return $validDomainName;
    }

    /**
     * Returns a valid date
     * @param string $paramDate - date to validate
     * @param string $paramFormat - 'Y-m-d', 'm/d/Y', 'd/m/Y'
     * @param string $paramDefaultValue
     * @return string
     */
    public static function getValidDate($paramDate, $paramFormat = 'Y-m-d', $paramDefaultValue = '')
    {
        $validDate = esc_html(sanitize_text_field($paramDefaultValue));
        if(static::isDate($paramDate, $paramFormat))
        {
            if($paramFormat == 'Y-m-d')
            {
                $dateParts = explode("-", $paramDate);
                $validDate = $dateParts[0]."-".$dateParts[1]."-".$dateParts[2];
            } else if($paramFormat == 'd/m/Y' || $paramFormat == 'm/d/Y')
            {
                $dateParts = explode("/", $paramDate);
                $validDate = $dateParts[0]."/".$dateParts[1]."/".$dateParts[2];
            }
        }

        return $validDate;
    }

    /**
     * Returns a valid date or 0000-00-00 if date is not valid
     * @param string $paramDate - date to validate
     * @param string $paramFormat - 'Y-m-d', 'm/d/Y', 'd/m/Y'
     * @return string
     */
    public static function getValidISO_Date($paramDate, $paramFormat = 'Y-m-d')
    {
        $validISODate = "0000-00-00";
        if(static::isDate($paramDate, $paramFormat))
        {
            if($paramFormat == 'Y-m-d')
            {
                $dateParts = explode("-", $paramDate);
                $validISODate = $dateParts[0]."-".$dateParts[1]."-".$dateParts[2];
            } else if($paramFormat == 'd/m/Y')
            {
                $dateParts = explode("/", $paramDate);
                $validISODate = $dateParts[2]."-".$dateParts[1]."-".$dateParts[0];
            } else if($paramFormat == 'm/d/Y')
            {
                $dateParts = explode("/", $paramDate);
                $validISODate = $dateParts[2]."-".$dateParts[0]."-".$dateParts[1];
            }
        }

        return $validISODate;
    }

    /**
     * Returns a valid time or 00:00:00 if time is not valid
     * @param $paramTime - time to validate
     * @param $paramFormat - 'Y-m-d'
     * @return string - valid time
     */
    public static function getValidISO_Time($paramTime, $paramFormat = 'H:i:s')
    {
        $validISOTime = "00:00:00";
        if(static::isTime($paramTime, $paramFormat))
        {
            if($paramFormat == 'H:i:s')
            {
                $timeParts = explode(":", $paramTime);
                $validISOTime = $timeParts[0].":".$timeParts[1].":".$timeParts[2];
            }
        }

        return $validISOTime;
    }

    /**
     * Security function to check if number is a positive integer
     * @param $input - value to check
     * @return bool
     */
    public static function isPositiveInteger($input)
    {
        if(!is_array($input))
        {
            return (ctype_digit(strval($input)));
        } else
        {
            return FALSE;
        }
    }

    /**
     * Security function to check if number is a integer
     * Tests:
     * (string) -10 -->     -10
     *      -10     -->     -10
     *      10.2    -->     -1
     *      8F      -->     -1
     * @param $input - value to check
     * @return bool
     */
    public static function isInteger($input)
    {
        if(!is_array($input))
        {
            $stringInput = "{$input}";
            //echo "<br />INPUT: {$stringInput}, [0]: ".$stringInput[0];
            if(isset($stringInput[0]) && $stringInput[0] == '-')
            {
                //echo ". Is negative.";
                return ctype_digit(substr($stringInput, 1));
            }
            return ctype_digit($stringInput);
        } else
        {
            return FALSE;
        }
    }

    /**
     * Security function to check date
     * @param string $paramDate - date to check
     * @param string $paramFormat - date format
     * @return bool
     */
    public static function isDate($paramDate, $paramFormat = "Y-m-d")
    {
        $ret = FALSE;
        if($paramFormat == "Y-m-d" && !is_array($paramDate))
        {
            $dateParts = explode("-", $paramDate);
            if(sizeof($dateParts) == 3)
            {
                $year = static::getValidPositiveInteger($dateParts[0], "0000");
                $month = static::getValidPositiveInteger($dateParts[1], "00");
                $day = static::getValidPositiveInteger($dateParts[2], "00");
                $ret = checkdate($month, $day, $year);
            }
        } else if($paramFormat == 'd/m/Y' && !is_array($paramDate))
        {
            $dateParts = explode("/", $paramDate);
            if(sizeof($dateParts) == 3)
            {
                $year = static::getValidPositiveInteger($dateParts[2], "0000");
                $month = static::getValidPositiveInteger($dateParts[1], "00");
                $day = static::getValidPositiveInteger($dateParts[0], "00");
                $ret = checkdate($month, $day, $year);
            }
        } else if($paramFormat == 'm/d/Y')
        {
            $dateParts = explode("/", $paramDate);
            if(sizeof($dateParts) == 3)
            {
                $year = static::getValidPositiveInteger($dateParts[2], "0000");
                $month = static::getValidPositiveInteger($dateParts[0], "00");
                $day = static::getValidPositiveInteger($dateParts[1], "00");
                $ret = checkdate($month, $day, $year);
            }
        }
        return $ret;
    }

    /**
     * Security function to check time
     * @param $paramTime - value to check
     * @param string $format - time format
     * @return bool
     */
    public static function isTime($paramTime, $format = "H:i:s")
    {
        $ret = false;
        if($format == "H:i:s" && !is_array($paramTime))
        {
            $timeParts = explode(":", $paramTime);
            if(sizeof($timeParts) == 3)
            {
                $hour = isset($timeParts[0]) ? static::getValidPositiveInteger($timeParts[0]) : "00";
                $min = isset($timeParts[1]) ? static::getValidPositiveInteger($timeParts[1]) : "00";
                $sec = isset($timeParts[2]) ? static::getValidPositiveInteger($timeParts[2]) : "00";
                $ret = static::checkTime($hour, $min, $sec);
            }
        }
        return $ret;
    }

    public static function checkTime($paramHour, $paramMin, $paramSec)
    {
        if($paramHour < 0 || $paramHour > 23 || !is_numeric($paramHour))
        {
            return false;
        }
        if($paramMin < 0 || $paramMin > 59 || !is_numeric($paramMin))
        {
            return false;
        }
        if($paramSec < 0 || $paramSec > 59 || !is_numeric($paramSec))
        {
            return false;
        }
        return true;
    }

    public static function isValidDateFormat($paramFormat)
    {
        $arrValidDateFormats = array("Y-m-d", "m/d/Y", "d/m/Y", "y-m-d", "m/d/y", "d/m/y", "D");
        return in_array($paramFormat, $arrValidDateFormats);
    }

    public static function isValidTimeFormat($paramFormat)
    {
        $arrValidDateFormats = array("H:i:s", "h:i:s", "H:i", "h:i");
        return in_array($paramFormat, $arrValidDateFormats);
    }

    public static function isAfterNoonTime($paramTimestamp, $paramNoonTime = "12:00:00")
    {
        $validTimestamp = $paramTimestamp > 0 ? intval($paramTimestamp) : 0;
        $validNoonTime = static::getValidISO_Time($paramNoonTime);

        $isAfterNoonTime = ($validTimestamp + get_option( 'gmt_offset' ) * 3600) > strtotime(date("Y-m-d")." ".$validNoonTime) ? TRUE : FALSE;

        return $isAfterNoonTime;
    }

    /**
     * Returns period, or zero if TILL before FROM
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param bool $negativeReturnAllowed - do we allow negative number to be returned
     * @return int
     */
    public static function getPeriod($paramFromTimestamp, $paramTillTimestamp, $negativeReturnAllowed = FALSE)
    {
        $period = intval($paramTillTimestamp - $paramFromTimestamp);
        if($period < 0 && $negativeReturnAllowed == FALSE)
        {
            $period = 0;
        }
        return $period;
    }

    /**
     * @param int $paramTimestamp
     * @param string $paramTimePeriod
     * @param int $paramMinimumPeriod - this is used only when i.e. we want to get not shorter than 30 minutes
     * @return int
     */
    public static function getSinglePeriod($paramTimestamp, $paramTimePeriod, $paramMinimumPeriod = 1)
    {
        $validTimestamp = $paramTimestamp > 0 ? intval($paramTimestamp) : 0;
        $validMinimumPeriod = $paramMinimumPeriod > 1 ? intval($paramMinimumPeriod) : 1;

        switch($paramTimePeriod)
        {
            case "IN_MINUTES_ONLY":
                $singlePeriod = 60;
                break;

            case "IN_HOURS_ONLY":
            case "IN_HOURS_AND_MINUTES":
                $singlePeriod = 3600;
                break;

            case "IN_DAYS_ONLY":
            case "IN_NIGHTS_ONLY":
            case "IN_DAYS_AND_HOURS":
            case "IN_NIGHTS_AND_HOURS":
                $singlePeriod = 86400;
                break;

            case "IN_MONTHS_ONLY":
                $singlePeriod = intval(strtotime(date("Y-m-d", $validTimestamp)." +1 month")); // return always has to be an integer
                break;

            case "IN_YEARS_ONLY":
            case "IN_YEARS_AND_MONTHS":
                $singlePeriod = intval(strtotime(date("Y-m-d", $validTimestamp)." +1 year")); // return always has to be an integer
                break;

            default:
                $singlePeriod = 1;
                break;
        }

        // If single period is less than minimum
        if($singlePeriod < $validMinimumPeriod)
        {
            // Then default it to minimum period
            $singlePeriod = $validMinimumPeriod;
        }

        return $singlePeriod;
    }

    public static function getPrintDateByTimestamp($paramTimestamp)
    {
        // WordPress bug
        // BAD: return date_i18n(get_option('date_format'), $this->pickupTimestamp);
        // OK: return date(get_option('date_format'), $this->pickupTimestamp + get_option( 'gmt_offset' ) * 3600);

        // WordPress bug WorkAround
        return date_i18n(get_option('date_format'), $paramTimestamp + get_option( 'gmt_offset' ) * 3600, TRUE);
    }

    public static function getPrintTimeByTimestamp($paramTimestamp)
    {
        // WordPress bug
        // BAD: return date_i18n(get_option('time_format'), $this->pickupTimestamp);
        // OK: return date(get_option('time_format'), $this->pickupTimestamp + get_option( 'gmt_offset' ) * 3600);
        return date_i18n(get_option('time_format'), $paramTimestamp + get_option( 'gmt_offset' ) * 3600, TRUE);
    }

    public static function getLocalISO_DateByTimestamp($paramTimestamp)
    {
        $localISO_Date = date("Y-m-d", $paramTimestamp + get_option('gmt_offset') * 3600);
        // DEBUG
        //echo "<br />LOCAL ISO DATE: {$localISO_Date}";

        return $localISO_Date;
    }

    public static function getLocalCurrentISO_Date()
    {
        $localCurrentISO_Date = date("Y-m-d", time() + get_option('gmt_offset') * 3600);
        // DEBUG
        //echo "<br />LOCAL CURRENT ISO DATE: {$localCurrentISO_Date}";

        return $localCurrentISO_Date;
    }

    /**
     * MUST BE GMT ADJUSTMENT - so that if user search for 2015-09-06 14:00, it would return back 2015-09-06 14:00
     * @param $paramTimestamp
     * @param $paramFormat - "Y-m-d", "m/d/Y", "d/m/Y", "D", "H:i:s", "H:i"
     * @return string
     */
    public static function getLocalDateByTimestamp($paramTimestamp, $paramFormat = "Y-m-d")
    {
        if(static::isValidDateFormat($paramFormat) || static::isValidTimeFormat($paramFormat))
        {
            $validFormat = sanitize_text_field($paramFormat);
        } else
        {
            $validFormat = "Y-m-d";
        }
        return date($validFormat, $paramTimestamp + get_option( 'gmt_offset' ) * 3600);
    }

    public static function getUTC_ISO_DateByTimestamp($paramTimestamp)
    {
        $utcISO_Date = date("Y-m-d", $paramTimestamp);
        // DEBUG
        //echo "<br />UTC ISO DATE: {$utcISO_Date}";

        return $utcISO_Date;
    }

    public static function getUTCTimestampFromLocalISODateTime($paramDate, $paramTime)
    {
        $UTCTimestamp = 0;
        $validISODate = static::getValidISO_Date($paramDate, 'Y-m-d');
        $validISOTime = static::getValidISO_Time($paramTime, 'H:i:s');
        if($validISODate != "0000-00-00")
        {
            $timezoneOffsetInSeconds = get_option('gmt_offset') * 3600;
            $UTCTimestamp = strtotime($validISODate." ".$validISOTime) - $timezoneOffsetInSeconds;
        }

        // DEBUG
        //echo "<br />UTC TIMESTAMP: {$UTCTimestamp}, LOCAL ISO DATE: {$validISODate}, LOCAL ISO TIME: {$validISOTime}";

        return $UTCTimestamp;
    }

    /*
     * Appears that this function is useless in the NS and nowhere used
     * While my brains still think that I want to add timezone offset, appears that correct way is to subtract it
     * via getUTCTimestampFromLocalISODateTime($paramDate, $paramTime)
     */
    public static function getLocalTimestampFromUTC_ISODateTime($paramDate, $paramTime)
    {
        $localTimestamp = 0;
        $validISODate = static::getValidISO_Date($paramDate, 'Y-m-d');
        $validISOTime = static::getValidISO_Time($paramTime, 'H:i:s');
        if($validISODate != "0000-00-00")
        {
            $timezoneOffsetInSeconds = get_option('gmt_offset') * 3600;
            $localTimestamp = strtotime($validISODate." ".$validISOTime) + $timezoneOffsetInSeconds;
        }
        return $localTimestamp;
    }

    public static function getLocalCurrentTimestamp()
    {
        $localCurrentTimestamp = time() + get_option('gmt_offset') * 3600;
        // DEBUG
        //echo "<br />LOCAL TIMESTAMP: {$localCurrentTimestamp}";

        return $localCurrentTimestamp;
    }

    public static function getTodayStartTimestamp()
    {
        $todayStartTimestamp = strtotime(date("Y-m-d")." 00:00:00");
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", TODAY&#39;S START: {$todayStartTimestamp}";

        return $todayStartTimestamp;
    }

    public static function getTodayNoonTimestamp($paramNoonTime = "12:00:00")
    {
        $validNoonTime = static::getValidISO_Time($paramNoonTime);
        $todayNoonTimestamp = strtotime(date("Y-m-d")." {$validNoonTime}");
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", TODAY&#39;S START: {$todayStartTimestamp}";

        return $todayNoonTimestamp;
    }

    public static function getTodayEndTimestamp()
    {
        $todayEndTimestamp = strtotime(date("Y-m-d")." 23:59:59");
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", TODAY&#39;S START: {$todayStartTimestamp}";

        return $todayEndTimestamp;
    }

    public static function getCurrentMonthStartTimestamp()
    {
        $currentMonthStartTimestamp = strtotime(date("Y")."-".date("m")."-01 00:00:00");
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS MONTH START: {$currentMonthStartTimestamp}";

        return $currentMonthStartTimestamp;
    }

    public static function getCurrentMonthEndTimestamp()
    {
        $currentMonthEndTimestamp = strtotime(date("Y")."-".date("m")."-".date("t")." 23:59:59");
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS MONTH END: {$currentMonthEndTimestamp}";

        return $currentMonthEndTimestamp;
    }

    public static function getCurrentYearStartTimestamp()
    {
        $currentYearStartTimestamp = strtotime(date("Y")."-01-01 00:00:00");
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS YEAR START: {$currentYearStartTimestamp}";

        return $currentYearStartTimestamp;
    }

    public static function getCurrentYearEndTimestamp()
    {
        $currentYearEndTimestamp = strtotime(date("Y")."-12-31 23:59:59");
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS YEAR END: {$currentYearEndTimestamp}";

        return $currentYearEndTimestamp;
    }


    public static function getDayStartTimestamp($paramTimestamp)
    {
        // Get valid timestamp
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);

        $dayStartTimestamp = strtotime(date("Y-m-d")." 00:00:00", $validTimestamp);
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", TODAY&#39;S START: {$dayStartTimestamp}";

        return $dayStartTimestamp;
    }

    public static function getDayNoonTimestamp($paramTimestamp, $paramNoonTime)
    {
        // Get valid timestamp
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);

        $validNoonTime = static::getValidISO_Time($paramNoonTime);
        $dayNoonTimestamp = strtotime(date("Y-m-d")." {$validNoonTime}", $validTimestamp);
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", TODAY&#39;S START: {$todayStartTimestamp}";

        return $dayNoonTimestamp;
    }

    public static function getDayEndTimestamp($paramTimestamp)
    {
        // Get valid timestamp
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);

        $dayEndTimestamp = strtotime(date("Y-m-d")." 23:59:59", $validTimestamp);
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", TODAY&#39;S START: {$todayStartTimestamp}";

        return $dayEndTimestamp;
    }

    public static function getMonthStartTimestamp($paramTimestamp)
    {
        // Get valid timestamp
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);

        $monthStartTimestamp = strtotime(date("Y")."-".date("m")."-01 00:00:00", $validTimestamp);
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS MONTH START: {$monthStartTimestamp}";

        return $monthStartTimestamp;
    }

    public static function getMonthEndTimestamp($paramTimestamp)
    {
        // Get valid timestamp
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);

        $monthEndTimestamp = strtotime(date("Y")."-".date("m")."-".date("t")." 23:59:59", $validTimestamp);
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS MONTH END: {$monthEndTimestamp}";

        return $monthEndTimestamp;
    }

    public static function getYearStartTimestamp($paramTimestamp)
    {
        // Get valid timestamp
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);

        $yearStartTimestamp = strtotime(date("Y")."-01-01 00:00:00", $validTimestamp);
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS YEAR START: {$yearStartTimestamp}";

        return $yearStartTimestamp;
    }

    public static function getYearEndTimestamp($paramTimestamp)
    {
        // Get valid timestamp
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);

        $yearEndTimestamp = strtotime(date("Y")."-12-31 23:59:59", $validTimestamp);
        // DEBUG
        //echo "<br />TIMESTAMP: ".time().", THIS YEAR END: {$yearEndTimestamp}";

        return $yearEndTimestamp;
    }

    /**
     * Get char instead of number if date is past
     * @param $number
     * @param $paramTimestamp
     * @param $paramTextToReplace
     * @return int
     */
    public static function getTextIfTimestampIsPast($number, $paramTimestamp, $paramTextToReplace = "-")
    {
        $ret = intval($number);
        $validCharToReplace = sanitize_text_field($paramTextToReplace);
        $validTimestamp = static::getValidPositiveInteger($paramTimestamp, 0);
        if(time() > $validTimestamp)
        {
            // Return chosen char instead, if that is a past date
            $ret = $validCharToReplace;
        }

        return $ret;
    }



    // ---------------------------------------------------------------------------------------------
    // MONTHS

    public static function getTotalDifferentMonthsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $validDateFrom = static::getValidISO_Date($paramISODateFrom, 'Y-m-d');
        $validDateTill = static::getValidISO_Date($paramISODateTill, 'Y-m-d');

        $totalDifferentMonths = 0;
        if($validDateFrom != "0000-00-00" && $validDateTill != "0000-00-00")
        {
            $timestampFrom = strtotime($validDateFrom." 00:00:00");
            $timestampTill = strtotime($validDateTill." 00:00:00");

            $yearFrom = date('Y', $timestampFrom);
            $yearTo = date('Y', $timestampTill);

            $monthFrom = date('n', $timestampFrom); // 1 through 12
            $monthTill = date('n', $timestampTill); // 1 through 12

            $totalDifferentMonths = (($yearTo - $yearFrom) * 12) + ($monthTill - $monthFrom);
            $totalDifferentMonths += 1; // Always +1 as we want to include current month
        }

        return $totalDifferentMonths;
    }

    public static function getTotalDifferentMonthsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $validTimestampFrom = static::getValidPositiveInteger($paramTimestampFrom);
        $validTimestampTill = static::getValidPositiveInteger($paramTimestampTill);

        $totalDifferentMonths = 0;
        if($validTimestampFrom > 0 && $validTimestampTill > 0)
        {
            $yearFrom = date('Y', $validTimestampFrom);
            $yearTill = date('Y', $validTimestampTill);

            $monthFrom = date('n', $validTimestampFrom); // 1 through 12
            $monthTill = date('n', $validTimestampTill); // 1 through 12

            $totalDifferentMonths = (($yearTill - $yearFrom) * 12) + ($monthTill - $monthFrom);
            $totalDifferentMonths += 1; // Always +1 as we want to include current month
        }

        return $totalDifferentMonths;
    }

    public static function getFloorTotalMonthsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $floorMonths = floor(static::getFloatTotalMonthsBetweenTwoISODates($paramISODateFrom, $paramISODateTill));

        return $floorMonths;
    }

    public static function getCeilTotalMonthsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $ceilMonths = ceil(static::getFloatTotalMonthsBetweenTwoISODates($paramISODateFrom, $paramISODateTill));

        return $ceilMonths;
    }

    public static function getFloatTotalMonthsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $validDateFrom = static::getValidISO_Date($paramISODateFrom, 'Y-m-d');
        $validDateTill = static::getValidISO_Date($paramISODateTill, 'Y-m-d');

        $monthsDifference = 0;
        if($validDateFrom != "0000-00-00" && $validDateTill != "0000-00-00")
        {
            $timestampFrom = strtotime($validDateFrom." 00:00:00");
            $timestampTill = strtotime($validDateTill." 00:00:00");

            $yearFrom = date('Y', $timestampFrom);
            $yearTo = date('Y', $timestampTill);

            $monthFrom = date('n', $timestampFrom); // 1 through 12
            $monthTill = date('n', $timestampTill); // 1 through 12
            $maxMonthDays = max(date('t', $timestampFrom), date('t', $timestampTill)); // 28 through 31

            $dayFrom = date('j', $timestampFrom); // 1 through 31
            $dayTill = date('j', $timestampTill); // 1 through 31

            $monthsDifference = (($yearTo - $yearFrom) * 12) + ($monthTill - $monthFrom) + (1 / $maxMonthDays * ($dayTill - $dayFrom));
        }

        return $monthsDifference;
    }

    public static function getFloorTotalMonthsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $floorMonths = floor(static::getFloatTotalMonthsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill));

        return $floorMonths;
    }

    public static function getCeilTotalMonthsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $ceilMonths = ceil(static::getFloatTotalMonthsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill));

        return $ceilMonths;
    }

    public static function getFloatTotalMonthsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $validTimestampFrom = static::getValidPositiveInteger($paramTimestampFrom);
        $validTimestampTill = static::getValidPositiveInteger($paramTimestampTill);

        $monthsDifference = 0;
        if($validTimestampFrom > 0 && $validTimestampTill > 0)
        {
            $yearFrom = date('Y', $validTimestampFrom);
            $yearTill = date('Y', $validTimestampTill);

            $monthFrom = date('n', $validTimestampFrom); // 1 through 12
            $monthTill = date('n', $validTimestampTill); // 1 through 12
            $maxMonthDays = max(date('t', $validTimestampFrom), date('t', $validTimestampTill)); // 28 through 31

            $dayFrom = date('j', $validTimestampFrom); // 1 through 31
            $dayTill = date('j', $validTimestampTill); // 1 through 31

            $monthsDifference = (($yearTill - $yearFrom) * 12) + ($monthTill - $monthFrom) + (1 / $maxMonthDays * ($dayTill - $dayFrom));
        }

        return $monthsDifference;
    }



    // ---------------------------------------------------------------------------------------------
    // YEARS

    public static function getTotalDifferentYearsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $validDateFrom = static::getValidISO_Date($paramISODateFrom, 'Y-m-d');
        $validDateTill = static::getValidISO_Date($paramISODateTill, 'Y-m-d');

        $totalDifferentYears = 0;
        if($validDateFrom != "0000-00-00" && $validDateTill != "0000-00-00")
        {
            $timestampFrom = strtotime($validDateFrom." 00:00:00");
            $timestampTill = strtotime($validDateTill." 00:00:00");

            $yearFrom = date('Y', $timestampFrom);
            $yearTill = date('Y', $timestampTill);

            $totalDifferentYears = $yearTill - $yearFrom;
            $totalDifferentYears += 1; // Always +1 as we want to include current year
        }

        return $totalDifferentYears;
    }

    public static function getTotalDifferentYearsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $validTimestampFrom = static::getValidPositiveInteger($paramTimestampFrom);
        $validTimestampTill = static::getValidPositiveInteger($paramTimestampTill);

        $totalDifferentYears = 0;
        if($validTimestampFrom > 0 && $validTimestampTill > 0)
        {
            $yearFrom = date('Y', $validTimestampFrom);
            $yearTill = date('Y', $validTimestampTill);

            $totalDifferentYears = $yearTill - $yearFrom;
            $totalDifferentYears += 1; // Always +1 as we want to include current year
        }

        return $totalDifferentYears;
    }


    public static function getFloorTotalYearsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $floorYears = floor(static::getFloatTotalYearsBetweenTwoISODates($paramISODateFrom, $paramISODateTill));

        return $floorYears;
    }

    public static function getCeilTotalYearsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $ceilYears = ceil(static::getFloatTotalYearsBetweenTwoISODates($paramISODateFrom, $paramISODateTill));

        return $ceilYears;
    }

    public static function getFloatTotalYearsBetweenTwoISODates($paramISODateFrom, $paramISODateTill)
    {
        $validDateFrom = static::getValidISO_Date($paramISODateFrom, 'Y-m-d');
        $validDateTill = static::getValidISO_Date($paramISODateTill, 'Y-m-d');

        $yearsDifference = 0;
        if($validDateFrom != "0000-00-00" && $validDateTill != "0000-00-00")
        {
            $timestampFrom = strtotime($validDateFrom." 00:00:00");
            $timestampTill = strtotime($validDateTill." 00:00:00");

            $yearFrom = date('Y', $timestampFrom);
            $yearTill = date('Y', $timestampTill);
            $isLeapYearFrom = date('L', $timestampFrom) == 1 ? TRUE : FALSE;
            $isLeapYearTill = date('L', $timestampTill) == 1 ? TRUE : FALSE;
            $maxYearDays = ($isLeapYearFrom || $isLeapYearTill) ? 366 : 365;

            $dayOfTheYearFrom = date('z', $timestampFrom)+1; // (0 through 365) +1
            $dayOfTheYearTill = date('z', $timestampTill)+1; // (0 through 365) +1

            $yearsDifference = ($yearTill - $yearFrom) + (1 / $maxYearDays * ($dayOfTheYearTill - $dayOfTheYearFrom));
        }

        return $yearsDifference;
    }


    public static function getFloorTotalYearsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $floorYears = floor(static::getFloatTotalYearsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill));

        return $floorYears;
    }

    public static function getCeilTotalYearsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $ceilYears = ceil(static::getFloatTotalYearsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill));

        return $ceilYears;
    }

    public static function getFloatTotalYearsBetweenTwoTimestamps($paramTimestampFrom, $paramTimestampTill)
    {
        $validTimestampFrom = static::getValidPositiveInteger($paramTimestampFrom);
        $validTimestampTill = static::getValidPositiveInteger($paramTimestampTill);

        $yearsDifference = 0;
        if($validTimestampFrom > 0 && $validTimestampTill > 0)
        {
            $yearFrom = date('Y', $validTimestampFrom);
            $yearTill = date('Y', $validTimestampTill);
            $isLeapYearFrom = date('L', $validTimestampFrom) == 1 ? TRUE : FALSE;
            $isLeapYearTill = date('L', $validTimestampTill) == 1 ? TRUE : FALSE;
            $maxYearDays = ($isLeapYearFrom || $isLeapYearTill) ? 366 : 365;

            $dayOfTheYearFrom = date('z', $validTimestampFrom)+1; // (0 through 365) +1
            $dayOfTheYearTill = date('z', $validTimestampTill)+1; // (0 through 365) +1

            $yearsDifference = ($yearTill - $yearFrom) + (1 / $maxYearDays * ($dayOfTheYearTill - $dayOfTheYearFrom));
        }

        return $yearsDifference;
    }

    /**
     * @param string $paramTimeCeiling - 'BY_TIME_COUNT', 'BY_NOON_COUNT' or 'BY_DATE_COUNT'
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param string $paramNoonTime
     * @return int
     */
    public static function getDateCount($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime)
    {
        $validFromTimestamp = $paramFromTimestamp > 0 ? intval($paramFromTimestamp) : 0;
        $validTillTimestamp = $paramTillTimestamp > 0 ? intval($paramTillTimestamp) : 0;

        // Set defaults
        $totalDates = 0;

        if($paramTimeCeiling == "BY_TIME_COUNT")
        {
            // BY TIME COUNT
            // Ex. 1st second on day 2 is 86400*1 + 1
            //     1st second on day 3 is 86400*2 + 1
            //     DIFFERENCE: 172801 - 86401 = 86400 (1 DAY EXACTLY)
            $period = intval($validTillTimestamp - $validFromTimestamp);
            $totalDates = $period > 0 ? ceil($period / 86400) : 0;
        } else if($paramTimeCeiling == "BY_NOON_COUNT")
        {
            // BY NOON COUNT. We add +1 day or night, if date difference is 0 or more, and we use 00:00:00 and 23:59:59 times
            $objDate1 = date_create(date("Y-m-d", $validFromTimestamp + get_option( 'gmt_offset' ) * 3600)." 00:00:00");
            $objDate2 = date_create(date("Y-m-d", $validTillTimestamp + get_option( 'gmt_offset' ) * 3600)." 23:59:59");
            $objInterval = date_diff($objDate1, $objDate2);

            // Add plus one here, to know actual dates
            // Ex:
            //      2015-06-02 00:00:00 -> 2015-06-02 23:59:59 = 1 DAY OR NIGHT
            //      2015-06-02 00:00:00 -> 2015-06-03 23:59:59 = 2 DAY OR NIGHT
            $totalDates = $objInterval->days >= 0 ? ($objInterval->days)+1 : 0;
            $localTillTimestamp = $validTillTimestamp + get_option( 'gmt_offset' ) * 3600;
            // NOTE: It is a fixed noon time, so there are no separation on local and UTC, but it is important that the date is correct (taken from LOCAL till timestamp)
            $noonTimestampOnTillDate = strtotime(date("Y-m-d", $localTillTimestamp)." ".$paramNoonTime);
            $isTillTimeInAfterNoon = $localTillTimestamp > $noonTimestampOnTillDate ? TRUE : FALSE;

            //  We subtract by -1 day / night, if the till time is not in afternoon, BUT ONLY IF there is more than 1 day / night at least
            if($totalDates > 1 && $isTillTimeInAfterNoon === TRUE)
            {
                $totalDates -= 1;
            }
        } else if($paramTimeCeiling == "BY_DATE_COUNT")
        {
            // BY DATE COUNT. We add +1 day, if date difference is 0 or more, and we use 00:00:00 and 23:59:59 times
            $objDate1 = date_create(date("Y-m-d", $validFromTimestamp + get_option( 'gmt_offset' ) * 3600)." 00:00:00");
            $objDate2 = date_create(date("Y-m-d", $validTillTimestamp + get_option( 'gmt_offset' ) * 3600)." 23:59:59");
            $objInterval = date_diff($objDate1, $objDate2);

            // Add plus one here, to know actual dates
            $totalDates = $objInterval->days >= 0 ? ($objInterval->days)+1 : 0;
        }

        return $totalDates;
    }

    /**
     * NOTE: This method should be used only with monthly & yearly time periods
     * @param string $paramTimeCeiling
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param string $paramNoonTime
     * @return int
     */
    public static function subtractTillTimestampByTimeCeiling($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime)
    {
        // Set defaults
        $subtractedTillTimestamp = 0;
        if($paramTimeCeiling == "BY_TIME_COUNT")
        {
            // Do nothing
            $subtractedTillTimestamp = StaticValidator::getValidPositiveInteger($paramTillTimestamp);
        } else if($paramTimeCeiling == "BY_NOON_COUNT")
        {
            // Subtract till timestamp by all time in seconds till noon, except the last second till noon
            $timeInSeconds = StaticValidator::timeToSeconds($paramNoonTime);
            $validFromTimestamp = StaticValidator::getValidPositiveInteger($paramFromTimestamp);
            $validTillTimestamp = StaticValidator::getValidPositiveInteger($paramTillTimestamp);
            if($validFromTimestamp + $timeInSeconds < $validTillTimestamp)
            {
                $subtractedTillTimestamp = $validTillTimestamp - ($timeInSeconds - 1);
            }
        } else if($paramTimeCeiling == "BY_DATE_COUNT")
        {
            // Subtract till timestamp by total seconds in day minus last second
            $timeInSeconds = StaticValidator::timeToSeconds($paramNoonTime);
            $validFromTimestamp = StaticValidator::getValidPositiveInteger($paramFromTimestamp);
            $validTillTimestamp = StaticValidator::getValidPositiveInteger($paramTillTimestamp);
            if($validFromTimestamp + $timeInSeconds < $validTillTimestamp)
            {
                $subtractedTillTimestamp = $validTillTimestamp - (86400 - 1);
            }
        }

        return $subtractedTillTimestamp;
    }

    /**
     * Returns date difference in days (hours does not matter here), and zero if TILL before FROM
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param bool $negativeReturnAllowed - do we allow negative number to be returned
     * @return int
     */
    public static function getDifferentDatesCount($paramFromTimestamp, $paramTillTimestamp, $negativeReturnAllowed = FALSE)
    {
        $objDate1 = date_create(date("Y-m-d", $paramFromTimestamp + get_option( 'gmt_offset' ) * 3600)." 00:00:00");
        $objDate2 = date_create(date("Y-m-d", $paramTillTimestamp + get_option( 'gmt_offset' ) * 3600)." 23:59:59");
        $objInterval = date_diff($objDate1, $objDate2);
        if($objInterval->days >= 0 || $negativeReturnAllowed === TRUE)
        {
            $differentDates = $objInterval->days + 1; // Always add +1 (current date)
        } else
        {
            $differentDates = 0;
        }

        return $differentDates;
    }

    public static function getMySqlDate($paramDate)
    {
        return date("Y-m-d", strtotime($paramDate." 00:00:00"));
    }

    /**
     * @param string $paramTime
     * @return int
     */
    public static function timeToSeconds($paramTime)
    {
        $timeExploded = explode(':', $paramTime);

        $seconds = 0;
        if(isset($timeExploded[0], $timeExploded[1], $timeExploded[2]))
        {
            $seconds = intval($timeExploded[0]) * 3600 + intval($timeExploded[1]) * 60 + intval($timeExploded[2]);
        } else if(isset($timeExploded[0], $timeExploded[1]))
        {
            $seconds = intval($timeExploded[0]) * 3600 + intval($timeExploded[1]) * 60;
        }

        return $seconds;
    }

    public static function getSecondsInThisYear()
    {
        $timestampToday = strtotime(date('Y-m-d'));
        $timestampNextYear = strtotime(date('Y-m-d', strtotime("+1 year")));
        $secondsInThisYear = $timestampNextYear - $timestampToday;

        return $secondsInThisYear;
    }

    public static function getSecondsInThisMonth()
    {
        $timestampToday = strtotime(date('Y-m-d'));
        $timestampNextMonth = strtotime(date('Y-m-d', strtotime("+1 month")));
        $secondsInThisMonth = $timestampNextMonth - $timestampToday;

        return $secondsInThisMonth;
    }

    /**
     * This function uses lower number to return, days not included
     * @param $paramSeconds
     * @return float
     */
    public static function getFloorMinutesOnLastHourFromSeconds($paramSeconds)
    {
        $intHoursOnly = floor($paramSeconds / 86400);      // 1
        $additionalSeconds = $paramSeconds - $intHoursOnly*3600;
        $intMinutesOnly = floor($additionalSeconds / 60);

        return $intMinutesOnly;
    }

    /**
     * This function uses higher number to return, days not included
     * @param $paramSeconds
     * @return float
     */
    public static function getCeilMinutesOnLastHourFromSeconds($paramSeconds)
    {
        $intHoursOnly = floor($paramSeconds / 86400);      // 1
        $additionalSeconds = $paramSeconds - $intHoursOnly*3600;
        $intMinutesOnly = ceil($additionalSeconds / 60);

        return $intMinutesOnly;
    }


    /**
     * This function uses lower number to return, days converted to hours
     * @param $paramSeconds
     * @return float
     */
    public static function getFloorMinutesFromSeconds($paramSeconds)
    {
        $intMinutesOnly = floor($paramSeconds / 60);  // 1

        return $intMinutesOnly;
    }

    /**
     * This function uses higher number to return, days converted to hours
     * @param $paramSeconds
     * @return float
     */
    public static function getCeilMinutesFromSeconds($paramSeconds)
    {
        $intMinutesOnly = ceil($paramSeconds / 60);  // 1

        return $intMinutesOnly;
    }

    /**
     * This function uses lower number to return, days not included
     * @param $paramSeconds
     * @return float
     */
    public static function getFloorHoursOnLastDayFromSeconds($paramSeconds)
    {
        $intDaysOnly = floor($paramSeconds / 86400);      // 1
        $additionalSeconds = $paramSeconds - $intDaysOnly*86400;
        $intHoursOnly = floor($additionalSeconds / 3600);

        return $intHoursOnly;
    }

    /**
     * This function uses higher number to return, days not included
     * @param $paramSeconds
     * @return float
     */
    public static function getCeilHoursOnLastDayFromSeconds($paramSeconds)
    {
        $intDaysOnly = floor($paramSeconds / 86400);      // 1
        $additionalSeconds = $paramSeconds - $intDaysOnly*86400;
        $intHoursOnly = ceil($additionalSeconds / 3600);

        return $intHoursOnly;
    }

    /**
     * This function uses lower number to return, days converted to hours
     * @param $paramSeconds
     * @return float
     */
    public static function getFloorHoursFromSeconds($paramSeconds)
    {
        $intHoursOnly = floor($paramSeconds / 3600);  // 1

        return $intHoursOnly;
    }

    /**
     * This function uses higher number to return, days converted to hours
     * @param $paramSeconds
     * @return float
     */
    public static function getCeilHoursFromSeconds($paramSeconds)
    {
        $intHoursOnly = ceil($paramSeconds / 3600);  // 1

        return $intHoursOnly;
    }

    /**
     * This function uses lower number to return
     * @param $paramSeconds
     * @return float
     */
    public static function getFloorDaysFromSeconds($paramSeconds)
    {
        $intDaysOnly = floor($paramSeconds / 86400);      // 1

        return $intDaysOnly;
    }

    /**
     * This function uses higher number to return
     * @param $paramSeconds
     * @return float
     */
    public static function getCeilDaysFromSeconds($paramSeconds)
    {
        $intDaysOnly = ceil($paramSeconds / 86400);      // 1

        return $intDaysOnly;
    }

    public static function getFloorDaysAndFloorHoursFromSeconds($paramSeconds)
    {
        $combined = array(
            "days" => static::getFloorDaysFromSeconds($paramSeconds),
            "hours" => static::getFloorHoursOnLastDayFromSeconds($paramSeconds),
        );

        return $combined;
    }

    public static function getFloorDaysAndCeilHoursFromSeconds($paramSeconds)
    {
        $combined = array(
            "days" => static::getFloorDaysFromSeconds($paramSeconds),
            "hours" => static::getCeilHoursOnLastDayFromSeconds($paramSeconds),
        );

        return $combined;
    }

    public static function getFloorDaysFloorHoursAndFloorMinutesFromSeconds($paramSeconds)
    {
        $combined = array(
            "days" => static::getFloorDaysFromSeconds($paramSeconds),
            "hours" => static::getFloorHoursOnLastDayFromSeconds($paramSeconds),
            "minutes" => static::getFloorMinutesOnLastHourFromSeconds($paramSeconds),
        );

        return $combined;
    }

    public static function getFloorDaysFloorHoursAndCeilMinutesFromSeconds($paramSeconds)
    {
        $combined = array(
            "days" => static::getFloorDaysFromSeconds($paramSeconds),
            "hours" => static::getFloorHoursOnLastDayFromSeconds($paramSeconds),
            "minutes" => static::getCeilMinutesOnLastHourFromSeconds($paramSeconds),
        );

        return $combined;
    }

    /**
     * This function uses higher number to return, days not included
     * @param $paramMonths
     * @return float
     */
    public static function getMonthsOnLastYearFromMonths($paramMonths)
    {
        $intYearsOnly = floor($paramMonths / 12);      // 1
        $intMonthsOnLastYear = $paramMonths - $intYearsOnly*12;

        return $intMonthsOnLastYear;
    }

    /**
     * This function uses lower number to return
     * @param $paramMonths
     * @return float
     */
    public static function getFloorYearsFromMonths($paramMonths)
    {
        $intYearsOnly = floor($paramMonths / 12);

        return $intYearsOnly;
    }

    /**
     * This function uses higher number to return
     * @param $paramMonths
     * @return float
     */
    public static function getCeilYearsFromMonths($paramMonths)
    {
        $intYearsOnly = ceil($paramMonths / 12);

        return $intYearsOnly;
    }

    public static function getYearsAndMonthsFromMonths($paramMonths)
    {
        $combined = array(
            "years" => static::getFloorYearsFromMonths($paramMonths),
            "months" => static::getMonthsOnLastYearFromMonths($paramMonths),
        );

        return $combined;
    }

    /**
     * Validates the array input
     * @param array $paramVarTypes - array where to search for data SESSION, POST, SERVER, ...
     * @param string $paramKey
     * @param string $paramValidation
     * @return array
     * @internal param $paramDefaultArray
     */
    public static function getValidArrayInput(array $paramVarTypes, $paramKey, $paramValidation = "guest_text_validation")
    {
        $varTypeUsed = "";
        // Set default array
        $retArray = array();
        $tmpArray = array();
        foreach($paramVarTypes AS $varType)
        {
            $stack = array();
            switch($varType)
            {
                case "POST":
                    $stack = $_POST;
                    break;
                case "GET":
                    $stack = $_GET;
                    break;
                case "SESSION":
                    $stack = $_SESSION;
                    break;
                case "COOKIE":
                    $stack = $_COOKIE;
                    break;
                case "REQUEST":
                    $stack = $_REQUEST;
                    break;
                case "SERVER":
                    $stack = $_SERVER;
                    break;
            }

            if(isset($stack[$paramKey]))
            {
                $varTypeUsed = $varType;
                $tmpArray = $stack[$paramKey];
                // break when first success was found - that's why order - POST, GET, SESSION, COOKIE - is important
                break;
            }
        }

        if(is_array($tmpArray))
        {
            foreach($tmpArray AS $key => $value)
            {
                if(static::isPositiveInteger($key) && !is_array($value))
                {
                    // We will be strict and process only if array member is integer and nothing else
                    // Plus we will be strict once again, any only process ig $value is NOT an array!
                    $validKey = static::getValidPositiveInteger($key, 0);
                    $retArray[$validKey] = static::getValidValue($value, $paramValidation);
                }
            }
        }

        if(static::$debugMode == 1)
        {
            echo "<br /><strong>[Security]</strong> ";
            //echo "Types: [".implode(", ", $varTypes)."], ";
            echo "Used: {$varTypeUsed}, ";
            echo "Param: $paramKey, ";
            echo "Validation: {$paramValidation}, Array: ".var_export($retArray, TRUE);
        }

        return $retArray;
    }

    /**
     * Validates the input
     * @param array $paramVarTypes - array where to search for data SESSION, POST, SERVER, ...
     * @param string $paramKey
     * @param $paramDefaultValue
     * @param string $paramValidation
     * @return bool
     */
    public static function getValidValueInput(array $paramVarTypes, $paramKey, $paramDefaultValue, $paramValidation = "guest_text_validation")
    {
        // Define default value first
        $ret = static::getValidValue($paramDefaultValue, $paramValidation, $paramDefaultValue);
        $tmpValue = FALSE;
        $varTypeUsed = "";
        foreach($paramVarTypes AS $varType)
        {
            $stack = array();
            switch($varType)
            {
                case "POST":
                    $stack = $_POST;
                    break;
                case "GET":
                    $stack = $_GET;
                    break;
                case "SESSION":
                    $stack = $_SESSION;
                    break;
                case "COOKIE":
                    $stack = $_COOKIE;
                    break;
                case "REQUEST":
                    $stack = $_REQUEST;
                    break;
                case "SERVER":
                    $stack = $_SERVER;
                    break;
            }

            if(isset($stack[$paramKey]))
            {
                $varTypeUsed = $varType;
                $tmpValue = $stack[$paramKey];
                // break when first success was found - that's why order - POST, GET, SESSION - is important
                break;
            }
        }

        if($tmpValue !== FALSE)
        {
            // Validate value if it is not failed yet
            $tmpValue = static::getValidValue($tmpValue, $paramValidation, $paramDefaultValue);
        }

        if($tmpValue !== FALSE && $tmpValue != "" && $tmpValue != "0" && $tmpValue != "0000-00-00")
        {
            // OK
            $ret = $tmpValue;
        }

        if(static::$debugMode == 1)
        {
            echo "<br /><strong>[Security]</strong> ";
            //echo "Types: [".implode(", ", $varTypes)."], ";
            echo "Used: {$varTypeUsed}, ";
            echo "Param: $paramKey, ";
            echo "Validation: {$paramValidation}, Value: ".esc_html(var_export($tmpValue, TRUE))." Ret: ".var_export($ret, TRUE);
        }

        return $ret;
    }

    public static function getValidArray($paramValuesArray, $paramValidation, $paramDefaultValue)
    {
        $ret = array();
        if(is_array($paramValuesArray))
        {
            foreach($paramValuesArray AS $key => $value)
            {
                if(static::isPositiveInteger($key) && !is_array($value))
                {
                    // We will be strict and process only if array member is integer and nothing else
                    // Plus we will be strict once again, any only process ig $value is NOT an array!
                    $validKey = static::getValidPositiveInteger($key, 0);

                    $ret[$validKey] = static::getValidValue($value, $paramValidation, $paramDefaultValue);
                }
            }
        }

        return $ret;
    }

    public static function getValidValue($paramValue, $paramValidation, $paramDefaultValue = '')
    {
        $retValue = FALSE;
        if(!is_array($paramValue))
        {
            // Very cool array validation
            if($paramValidation == 'positive_integer')
            {
                // Only positive integers allowed
                $retValue = static::isPositiveInteger($paramValue) ? intval($paramValue) : static::getValidPositiveInteger($paramDefaultValue, 0);
            } else if($paramValidation == 'intval')
            {
                // Both - positive and negative integers allowed
                $retValue = static::isInteger($paramValue) ? intval($paramValue) : intval($paramDefaultValue);
            } else if($paramValidation == 'spaced_uppercase_code')
            {
                $retValue = strtoupper(preg_replace('[^-_0-9a-zA-Z ]', '', $paramValue)); // No sanitization, uppercase needed
            } else if($paramValidation == 'zip_code')
            {
                // NOTE: ZIP code can have dots, spaces and alpha-numeric chars
                $retValue = strtoupper(preg_replace('[^-_0-9a-zA-Z\. ]', '', $paramValue)); // No sanitization, uppercase needed
            } else if($paramValidation == 'Y-m-d')
            {
                $retValue = static::isDate($paramValue, 'Y-m-d') ? static::getValidISO_Date($paramValue, 'Y-m-d') : static::getValidISO_Date($paramDefaultValue, 'Y-m-d');
            } else if($paramValidation == "d/m/Y")
            {
                $retValue = static::isDate($paramValue, 'd/m/Y') ? static::getValidISO_Date($paramValue, 'd/m/Y') : static::getValidISO_Date($paramDefaultValue, 'd/m/Y');
            } else if($paramValidation == "m/d/Y")
            {
                $retValue = static::isDate($paramValue, 'm/d/Y') ? static::getValidISO_Date($paramValue, 'm/d/Y') : static::getValidISO_Date($paramDefaultValue, 'm/d/Y');
            } else if($paramValidation == "time_validation")
            {
                $retValue = static::isTime($paramValue, 'H:i:s') ? static::getValidISO_Time($paramValue, 'H:i:s') : static::getValidISO_Time($paramDefaultValue, 'H:i:s');
            } else if($paramValidation == "email_validation")
            {
                // We don't want to be strict, and allow that we can default email to blank if needed
                $retValue = is_email($paramValue) ? sanitize_email($paramValue) : sanitize_text_field($paramDefaultValue);
            } else if($paramValidation == "guest_text_validation")
            {
                // for names, and input text
                $retValue = sanitize_text_field($paramValue);
            } else if($paramValidation == "guest_multiline_text_validation")
            {
                $retValue = implode("\n", array_map('sanitize_text_field', explode("\n", $paramValue)));
            }
        }

        return $retValue;
    }


    /**
     * Returns valid class settings. Used when creating new class instances
     * @param $paramSettings
     * @param $paramIndex
     * @param string $paramValidation
     * @param int $paramDefaultValue
     * @param array $paramAllowedValues
     * @return float|int|string
     */
    public static function getValidSetting($paramSettings, $paramIndex, $paramValidation = 'positive_integer', $paramDefaultValue = 0, $paramAllowedValues = array())
    {
        $value = isset($paramSettings[$paramIndex]) ? $paramSettings[$paramIndex] : '';
        // Data validation
        if(!is_array($value) && $paramValidation == 'positive_integer')
        {
            // Only positive integers allowed
            $validValue = static::isPositiveInteger($value) ? intval($value) : static::getValidPositiveInteger($paramDefaultValue);
        } elseif(!is_array($value) && $paramValidation == 'intval')
        {
            // Both - positive and negative integers allowed
            $validValue = static::isInteger($value) ? intval($value) : intval($paramDefaultValue);
        } elseif(!is_array($value) && $paramValidation == "floatval")
        {
            $validValue = floatval($value);
        } elseif(!is_array($value) && $paramValidation == "textval")
        {
            $validValue = esc_html(sanitize_text_field($value));
        }

        // Spaced
        elseif(!is_array($value) && $paramValidation == "spaced_uppercase_code_with_dots")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, TRUE, TRUE, TRUE);
        } elseif(!is_array($value) && $paramValidation == "spaced_uppercase_code")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, TRUE, TRUE, FALSE);
        } elseif(!is_array($value) && $paramValidation == "spaced_code_with_dots")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, FALSE, TRUE, TRUE);
        } elseif(!is_array($value) && $paramValidation == "spaced_code")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, FALSE, TRUE, FALSE);
        }

        // Not spaced
        elseif(!is_array($value) && $paramValidation == "uppercase_code_with_dots")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, TRUE, FALSE, TRUE);
        } elseif(!is_array($value) && $paramValidation == "uppercase_code")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, TRUE, FALSE, FALSE);
        } elseif(!is_array($value) && $paramValidation == "code_with_dots")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, FALSE, FALSE, TRUE);
        } elseif(!is_array($value) && $paramValidation == "code")
        {
            $validValue = static::getValidCode($value, $paramDefaultValue, FALSE, FALSE, FALSE);
        }

        // Other
        elseif(!is_array($value) && $paramValidation == "email")
        {
            $validValue = esc_html(sanitize_email($value));
        } elseif(!is_array($value) && $paramValidation == "url")
        {
            $validValue = esc_url(sanitize_text_field($value));
        } elseif(!is_array($value) && $paramValidation == "date_format")
        {
            $tmpFormat = sanitize_text_field($value);
            // No esc_html needed here
            $validValue = static::isValidDateFormat($tmpFormat) ? $tmpFormat : $paramDefaultValue;
        } elseif(!is_array($value) && $paramValidation == "time_format")
        {
            $tmpFormat = sanitize_text_field($value);
            // No esc_html needed here
            $validValue = static::isValidTimeFormat($tmpFormat) ? $tmpFormat : $paramDefaultValue;
        } else
        {
            $validValue = $paramDefaultValue;
        }

        // Specific value validation (if needed)
        if(sizeof($paramAllowedValues) > 0)
        {
            $validValue = in_array($validValue, $paramAllowedValues) ? $validValue : $paramDefaultValue;
        }

        // Set the class member value
        return $validValue;
    }
}