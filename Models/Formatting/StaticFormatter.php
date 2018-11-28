<?php
/**
 * Modern data formatter
 * Note 1: This model does not depend on any other class
 * Note 2: This model must be used in static context only
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Formatting;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class StaticFormatter
{
    const WORLD_TIMEZONES_MAX_DIFFERENCE_IN_SECONDS = 43200;
    protected static $debugMode = 0;

    public static function getTruncated($paramText, $maxLength)
    {
        if (strlen($paramText) > $maxLength)
        {
            $paramText = substr($paramText, 0, $maxLength);
            $paramText = substr($paramText,0, strrpos($paramText," "));
            $etc = " ... ";
            $paramText = $paramText.$etc;
        }
        return $paramText;
    }

    public static function getIncrementalHash($paramLength = 5, $paramIncludeLowercaseLetters = FALSE, $paramIncludeUppercaseLetters = TRUE, $paramIncludeNumbers = FALSE)
    {
        $charset = '';
        if($paramIncludeLowercaseLetters)
        {
            $charset .= "0123456789";
        }
        if($paramIncludeUppercaseLetters)
        {
            $charset .= "abcdefghijklmnopqrstuvwxyz";
        }
        if($paramIncludeNumbers)
        {
            $charset .= "ABCDEFGHIJKLMNPRSTUVYZ"; // fits LT & EN, O is skipped to similarity to Zero
        }
        $charsetLength = strlen($charset);
        $result = '';

        $microTimeList = explode(' ', microtime());
        $now = $microTimeList[1];
        while ($now >= $charsetLength)
        {
            $i = $now % $charsetLength;
            $result = $charset[$i] . $result;
            $now /= $charsetLength;
        }
        return substr($result, -$paramLength);
    }

    /**
     * We do not apply validators here because of flexible data and speed
     * @param $array
     * @param $multiplier - how many times to multiply
     * @return array
     */
    public static function getMultipliedNumberArray($array, $multiplier)
    {
        $retArray = array();
        foreach($array AS $key => $number)
        {
            $retArray[$key] = $number * $multiplier;
        }

        return $retArray;
    }

    /**
     * We do not apply validators here because of flexible data and speed
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function getSumOfTwoArrays($array1, $array2)
    {
        $sumArray = array();
        foreach($array1 AS $key => $number)
        {
            $sumArray[$key] = isset($array2[$key]) ? $array2[$key] + $number : $number;
        }

        return $sumArray;
    }

    /**
     * We do not apply validators here because of flexible data and speed
     * @param $percentage
     * @param $formatType
     * @return string
     */
    public static function getFormattedPercentage($percentage, $formatType)
    {
        switch($formatType)
        {
            case "tiny":
                $formattedPercentage = number_format_i18n($percentage, 2)."%";
                break;
            case "tiny_without_fraction":
                $formattedPercentage = number_format_i18n($percentage, 0)."%";
                break;
            case "regular":
                $formattedPercentage = number_format_i18n($percentage, 2)." %";
                break;
            case "regular_without_fraction":
                $formattedPercentage = number_format_i18n($percentage, 0)." %";
                break;
            default:
                $formattedPercentage = $percentage;
        }

        return $formattedPercentage;
    }

    public static function getPrintMessage(array $paramMessages)
    {
        $messagesToAdd = array();
        foreach($paramMessages AS $paramMessage)
        {
            $messagesToAdd[] = sanitize_text_field($paramMessage);
        }

        $printMessage = implode('<br />', $messagesToAdd);

        return $printMessage;
    }

    public static function getAllDaysOfTheMonthArray($paramYear = "current", $paramMonth = "current", $paramWeekend = "SAT_SUN")
    {
        if($paramYear =="current" && $paramMonth == "current")
        {
            $startDate = date("Y-m-01");
        } else
        {
            $startDate = date("{$paramYear}-{$paramMonth}-01"); // Give in your own start date
        }
        $endDate = date("Y-m-t", strtotime($startDate." 00:00:00"));

        $numberOfDays = (strtotime($endDate." 00:00:00") - strtotime($startDate." 00:00:00"))/86400+1 ; // Add the last day also

        /*DEBUG*/ //echo "START: $startDate, END: $endDate, DIFF: $numberOfDays<br />";

        return static::getDaysArray($paramYear, $paramMonth, '01', $numberOfDays, $paramWeekend);
    }

    public static function getNext30DaysArray($paramYear = "current", $paramMonth = "current", $paramDay = "current", $paramWeekend = "SAT_SUN")
    {
        if($paramYear == "current")
        {
            $paramYear = date("Y", time());
        }
        if($paramMonth == "current")
        {
            $paramMonth = date("m", time());
        }
        if($paramDay == "current")
        {
            $paramMonth = date("d", time());
        }

        return static::getDaysArray($paramYear, $paramMonth, $paramDay, 30, $paramWeekend);
    }

    /**
     * @param string $paramYear
     * @param string $paramMonth
     * @param string $paramDay
     * @param int $paramNumberOfDays
     * @param string $paramWeekend - 'FRI', 'FRI_SAT', 'SAT_SUN'
     * @return array
     */
    private static function getDaysArray($paramYear, $paramMonth, $paramDay, $paramNumberOfDays, $paramWeekend)
    {
        $days = array();

        $sanitizedYear = sanitize_text_field($paramYear); // Do not intval here, because we use it for date format
        $sanitizedMonth = sanitize_text_field($paramMonth); // Do not intval here, because we use it for date format
        $sanitizedDay = sanitize_text_field($paramDay); // Do not intval here, because we use it for date format

        $validNumberOfDays = $paramNumberOfDays > 0 ? intval($paramNumberOfDays) : 0;

        for ($i = 0; $i < $validNumberOfDays; $i++)
        {
            $dateTimestamp = strtotime(date("Y-m-d", strtotime("{$sanitizedYear}-{$sanitizedMonth}-{$sanitizedDay} 00:00:00"))." +$i day");
            $printYear = date('Y', $dateTimestamp);
            $printMonth = date('m', $dateTimestamp);
            $printDay = date('d', $dateTimestamp);
            $printDayOfWeek = date('N', $dateTimestamp); // Get day of week - 1 (for Monday) through 7 (for Sunday)
            // Here day of week can be from 1 (for Monday) to 7 (for Sunday)
            $isWeekend = FALSE;
            if($paramWeekend == 'FRI' && (int) $printDayOfWeek == 5)
            {
                $isWeekend = TRUE;
            } else if($paramWeekend == 'FRI_SAT' && in_array((int) $printDayOfWeek, array(5, 6)))
            {
                $isWeekend = TRUE;
            } else if($paramWeekend == 'SAT_SUN' && in_array((int) $printDayOfWeek, array(6, 7)))
            {
                $isWeekend = TRUE;
            }

            $dayClass = $isWeekend ? "weekend" : "weekday";

            $printMonthName = date_i18n("F", strtotime("{$paramYear}-{$paramMonth}-01 00:00:00"), TRUE);

            $days[] = array(
                "year"                          => (int) $printYear,
                "month"                         => (int) $printMonth,
                "day"                           => (int) $printDay,
                "day_of_week"                   => (int) $printDayOfWeek,
                "is_weekend"                    => $isWeekend,
                "print_year"                    => $printYear,
                "print_month" 					=> $printMonth,
                "print_month_name" 				=> $printMonthName,
                "print_day" 					=> $printDay,
                "print_day_class"  		        => $dayClass,
                "print_day_of_week"  	        => $printDayOfWeek,
            );


            if(static::$debugMode)
            {
                echo "<br />-&gt;  Year: {$printYear} Month: {$printMonth}, Day: {$printDay}, ";
            }
        }

        return $days;
    }

    /**
     * @param string $paramTimeCeiling - 'BY_TIME_COUNT', 'BY_NOON_COUNT' or 'BY_DATE_COUNT'
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param string $paramNoonTime
     * @return array
     */
    public static function getYearsRangeTimestampArray($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime)
    {
        $validFromTimestamp = $paramFromTimestamp > 0 ? intval($paramFromTimestamp) : 0;
        $modifiedTillTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime);

        // Set defaults
        $arrYearTimestamps = array();

        // Initialize with from timestamp
        $currentTimestamp = $validFromTimestamp;
        while($currentTimestamp < $modifiedTillTimestamp)
        {
            $arrYearTimestamps[] = $currentTimestamp;
            $currentTimestamp = strtotime("+1 year", $currentTimestamp); // Add 1 year
        }

        return $arrYearTimestamps;
    }


    /**
     * @param string $paramTimeCeiling - 'BY_TIME_COUNT', 'BY_NOON_COUNT' or 'BY_DATE_COUNT'
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param string $paramNoonTime
     * @return array
     */
    public static function getYearRangeAndMonthRangeTimestampArray($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime)
    {
        $validFromTimestamp = $paramFromTimestamp > 0 ? intval($paramFromTimestamp) : 0;
        $modifiedTillTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime);
        $validLastYearFromTimestamp = $validFromTimestamp + floor(($modifiedTillTimestamp - $validFromTimestamp) / 86400) * 86400;

        // Set defaults
        $arrYearTimestamps = array();
        $arrMonthTimestamps = array();

        // Start from 'from' year
        $currentYearTimestamp = $validFromTimestamp;
        // We must use <, not <=, because if person ordered a item at 9:00:00 and drop's it at 9:00:00 next year same day, we count it as one year.
        while ($currentYearTimestamp < $validLastYearFromTimestamp)
        {
            $arrYearTimestamps[] = $currentYearTimestamp;
            $currentYearTimestamp = strtotime("+1 year", $currentYearTimestamp); // Add 1 year
        }

        // Start from last year 'from' month
        $currentMonthTimestamp = $validLastYearFromTimestamp;
        // We must use <, not <=, because if person ordered a item at 9:00:00 and drop's it at 9:00:00 next month same day, we count it as one month.
        while ($currentMonthTimestamp < $modifiedTillTimestamp)
        {
            $arrMonthTimestamps[] = $currentMonthTimestamp;
            $currentMonthTimestamp = strtotime("+1 month", $currentMonthTimestamp); // Add 1 month
        }

        $combined = array(
            "years" => $arrYearTimestamps,
            "months" => $arrMonthTimestamps,
        );
        return $combined;
    }

    /**
     * @param string $paramTimeCeiling - 'BY_TIME_COUNT', 'BY_NOON_COUNT' or 'BY_DATE_COUNT'
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param string $paramNoonTime
     * @return array
     */
    public static function getMonthsRangeTimestampArray($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime)
    {
        $validFromTimestamp = $paramFromTimestamp > 0 ? intval($paramFromTimestamp) : 0;
        $modifiedTillTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime);

        // Set defaults
        $arrMonthTimestamps = array();

        // Initialize with from timestamp
        $currentTimestamp = $validFromTimestamp;
        while($currentTimestamp < $modifiedTillTimestamp)
        {
            $arrMonthTimestamps[] = $currentTimestamp;
            $currentTimestamp = strtotime("+1 month", $currentTimestamp); // Add 1 month
        }

        return $arrMonthTimestamps;
    }

    /**
     * @param string $paramTimeCeiling - 'BY_TIME_COUNT', 'BY_NOON_COUNT' or 'BY_DATE_COUNT'
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @param string $paramNoonTime
     * @return array
     */
    public static function getDateRangeTimestampArray($paramTimeCeiling, $paramFromTimestamp, $paramTillTimestamp, $paramNoonTime)
    {
        $validFromTimestamp = $paramFromTimestamp > 0 ? intval($paramFromTimestamp) : 0;
        $validTillTimestamp = $paramTillTimestamp > 0 ? intval($paramTillTimestamp) : 0;

        // Set defaults
        $arrDateTimestamps = array();

        if($paramTimeCeiling == "BY_TIME_COUNT")
        {
            // BY TIME COUNT
            // Ex. 1st second on day 2 is 86400*1 + 1
            //     1st second on day 3 is 86400*2 + 1
            //     DIFFERENCE: 172801 - 86401 = 86400 (1 DAY EXACTLY)
            $period = intval($validTillTimestamp - $validFromTimestamp);
            $totalDates = $period > 0 ? ceil($period / 86400) : 0;

            $arrDateTimestamps = array();
            for ($counter = 0; $counter < $totalDates; $counter++)
            {
                // NOTE: At $counter = 0 the addition will be +0
                $arrDateTimestamps[] = $validFromTimestamp + (86400 * $counter);
            }
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

            $arrDateTimestamps = array();
            for ($counter = 0; $counter < $totalDates; $counter++)
            {
                // NOTE: At $counter = 0 the addition will be +0
                $arrDateTimestamps[] = $validFromTimestamp + (86400 * $counter);
            }
        } else if($paramTimeCeiling == "BY_DATE_COUNT")
        {
            // BY DATE COUNT. We add +1 day, if date difference is 0 or more, and we use 00:00:00 and 23:59:59 times
            $objDate1 = date_create(date("Y-m-d", $validFromTimestamp + get_option( 'gmt_offset' ) * 3600)." 00:00:00");
            $objDate2 = date_create(date("Y-m-d", $validTillTimestamp + get_option( 'gmt_offset' ) * 3600)." 23:59:59");
            $objInterval = date_diff($objDate1, $objDate2);

            // Add plus one here, to know actual dates
            $totalDates = $objInterval->days >= 0 ? ($objInterval->days)+1 : 0;

            $arrDateTimestamps = array();
            for ($counter = 0; $counter < $totalDates; $counter++)
            {
                // NOTE: At $counter = 0 the addition will be +0
                $arrDateTimestamps[] = $validFromTimestamp + (86400 * $counter);
            }
        }

        return $arrDateTimestamps;
    }

    /**
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @return array
     */
    public static function getDateRangeAndHourRangeTimestampArray($paramFromTimestamp, $paramTillTimestamp)
    {
        $validFromTimestamp = $paramFromTimestamp > 0 ? intval($paramFromTimestamp) : 0;
        $validTillTimestamp = $paramTillTimestamp > 0 ? intval($paramTillTimestamp) : 0;
        $validLastDayFromTimestamp = $validFromTimestamp + floor(($validTillTimestamp - $validFromTimestamp) / 86400) * 86400;
        $arrDateTimestamps = array();
        $arrHourTimestamps = array();

        // Start from day 1
        $currentDateTimestamp = $validFromTimestamp;
        // We must use <, not <=, because if person ordered a car at 9:00:00 and drop's it at 9:00:00 next day, we count it as one day.
        while ($currentDateTimestamp < $validLastDayFromTimestamp)
        {
            $arrDateTimestamps[] = $currentDateTimestamp;
            $currentDateTimestamp += 86400; // add 1 day
        }

        // Start from last days first hour
        $currentHourTimestamp = $validLastDayFromTimestamp;
        // We must use <, not <=, because if person ordered a car at 9:00:00 and drop's it at 9:00:00 next day, we count it as one day.
        while ($currentHourTimestamp < $validTillTimestamp)
        {
            $arrHourTimestamps[] = $currentHourTimestamp;
            $currentHourTimestamp += 3600; // add 1 hours
        }

        $combined = array(
            "days" => $arrDateTimestamps,
            "hours" => $arrHourTimestamps,
        );
        return $combined;
    }

    /**
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @return array
     */
    public static function getHourRangeTimestampArray($paramFromTimestamp, $paramTillTimestamp)
    {
        $validFromTimestamp = $paramFromTimestamp > 0 ? intval($paramFromTimestamp) : 0;
        $validTillTimestamp = $paramTillTimestamp > 0 ? intval($paramTillTimestamp) : 0;

        // Set defaults
        $arrHourTimestamps = array();

        // Always start from hour 1
        $currentTimestamp = $validFromTimestamp;
        // We must use <, not <=, because if person ordered a car at 9:00:00 and drop's it at 10:00:00, we count it as one hour.
        while ($currentTimestamp < $validTillTimestamp)
        {
            $arrHourTimestamps[] = $currentTimestamp;
            $currentTimestamp += 3600; // add 1 hours
        }
        return $arrHourTimestamps;
    }

    public static function generateDropdownOptions($from, $to, $selectedValue = "", $defaultValue = "", $defaultText = "", $prefixed = FALSE, $suffix = "")
    {
        $ret = "";
        $suffix = $suffix != '' ? ' '.$suffix : '';

        if($defaultText != "")
        {
            if($selectedValue == $defaultValue)
            {
                $ret .= '<option value="'.$defaultValue.'" selected="selected">'.$defaultText.'</option>';
            } else
            {
                $ret .= '<option value="'.$defaultValue.'">'.$defaultText.'</option>';
            }
        }

        for($i = $from; $i <= $to; $i++)
        {
            $prefixedValue = $prefixed ? sprintf('%0'.strlen($to).'d', $i) : $i;
            if($prefixedValue == $selectedValue)
            {
                $ret .= '<option value="'.$prefixedValue.'" selected="selected">'.$i.$suffix.'</option>';

            } else
            {
                $ret .= '<option value="'.$prefixedValue.'">'.$i.$suffix.'</option>';
            }
        }

        return $ret;
    }

    /**
     * @param array $paramValueTextPairs
     * @param string $paramSelectedValue
     * @param string $paramDefaultValue
     * @param string $paramDefaultText - if "SKIP" is used, it will skip from showing
     * @return string
     */
    public static function getKeyValueDropdownOptions(array $paramValueTextPairs, $paramSelectedValue = "", $paramDefaultValue = "", $paramDefaultText = "")
    {
        $ret = "";

        if($paramDefaultText != "SKIP")
        {
            if($paramSelectedValue == $paramDefaultValue)
            {
                $ret = '<option value="'.$paramDefaultValue.'" selected="selected">'.$paramDefaultText.'</option>';
            } else
            {
                $ret = '<option value="'.$paramDefaultValue.'">'.$paramDefaultText.'</option>';
            }
        }
        foreach ($paramValueTextPairs as $value => $text)
        {
            if($value == $paramSelectedValue)
            {
                $ret .= '<option value="'.$value.'" selected="selected">'.$text.'</option>';
            } else
            {
                $ret .= '<option value="'.$value.'">'.$text.'</option>';
            }
        }

        return $ret;
    }

    public static function removeValueFromArray($paramArray, $paramValueToDelete)
    {
        if (($key = array_search($paramValueToDelete, $paramArray)) !== FALSE)
        {
            unset($paramArray[$key]);
        }

        return $paramArray;
    }

    public static function priceCompare($a, $b)
    {
        if($a['unit']['discounted_total'] == $b['unit']['discounted_total'])
        {
            return 0;
        }

        return ($a['unit']['discounted_total'] < $b['unit']['discounted_total']) ? -1 : 1;
    }

    public static function getYearStartDatesDropdownOptions(
        $paramSelectedValue, $paramStartYear, $paramEndYear, $paramDateFormat = 'm/d/Y', $paramDefaultValue = "", $paramDefaultLabel = ""
    ) {
        $printDefaultValue = esc_html(sanitize_text_field($paramDefaultValue));
        $printDefaultLabel = esc_html(sanitize_text_field($paramDefaultLabel));
        $retHTML = '';

        $shortDateFormat = "m/d/Y";
        if($paramDateFormat == "Y-m-d")
        {
            $shortDateFormat = "Y-m-d";
        } else if($paramDateFormat == "d/m/Y")
        {
            $shortDateFormat = "d/m/Y";
        } else if($paramDateFormat == "m/d/Y")
        {
            $shortDateFormat = "m/d/Y";
        }

        if($paramDefaultValue != "" || $paramDefaultLabel != "")
        {
            if($paramDefaultValue == $paramSelectedValue)
            {
                $retHTML .= '<option value="'.$printDefaultValue.'" selected="selected">'.$printDefaultLabel.'</option>';
            } else
            {
                $retHTML .= '<option value="'.$printDefaultValue.'">'.$printDefaultLabel.'</option>';
            }
        }

        if($paramStartYear >= 0 && $paramEndYear >= 0 && $paramStartYear <= $paramEndYear)
        {
            $paramStartYear = intval($paramStartYear);
            $validEndYear = intval($paramStartYear);
        } else
        {
            $paramStartYear = date_i18n("Y");
            $validEndYear = date_i18n("Y");
        }

        for($year = $paramStartYear; $year <= $validEndYear; $year++)
        {
            $timestamp = strtotime("{$year}-01-01");
            $value = date($shortDateFormat, $timestamp);
            if($year == $paramSelectedValue)
            {
                $retHTML .= '<option value="'.$value.'" selected="selected">'.$year.'</option>';
            } else
            {
                $retHTML .= '<option value="'.$value.'">'.$year.'</option>';
            }
        }

        return $retHTML;
    }

    public static function getMonthStartDatesDropdownOptions(
        $paramSelectedValue, $paramStartTimestamp, $paramEndTimestamp, $paramDateFormat = 'm/d/Y', $paramDefaultValue = "", $paramDefaultLabel = ""
    ) {
        $printDefaultValue = esc_html(sanitize_text_field($paramDefaultValue));
        $printDefaultLabel = esc_html(sanitize_text_field($paramDefaultLabel));
        $retHTML = '';

        $monthWithYearFormat = "m, Y";
        $shortDateFormat = "m/d/Y";
        if($paramDateFormat == "Y-m-d")
        {
            $shortDateFormat = "Y-m-d";
            $monthWithYearFormat = "Y m";
        } else if($paramDateFormat == "d/m/Y")
        {
            $shortDateFormat = "d/m/Y";
            $monthWithYearFormat = "m, Y";
        } else if($paramDateFormat == "m/d/Y")
        {
            $shortDateFormat = "m/d/Y";
            $monthWithYearFormat = "m, Y";
        }

        if($paramStartTimestamp >= 0 && $paramEndTimestamp >= 0 && $paramStartTimestamp <= $paramEndTimestamp)
        {
            $validStartYear = date_i18n('Y', intval($paramStartTimestamp) + get_option('gmt_offset') * 3600, TRUE);
            $validStartMonth = date_i18n('m', intval($paramStartTimestamp) + get_option('gmt_offset') * 3600, TRUE);
            $validEndYear = date_i18n('Y', intval($paramEndTimestamp) + get_option('gmt_offset') * 3600, TRUE);
            $validEndMonth = date_i18n('m', intval($paramEndTimestamp) + get_option('gmt_offset') * 3600, TRUE);
        } else
        {
            $validStartYear = date_i18n('Y', time() + get_option('gmt_offset') * 3600, TRUE);
            $validStartMonth = date_i18n('m', time() + get_option('gmt_offset') * 3600, TRUE);
            $validEndYear = date_i18n('Y', time() + get_option('gmt_offset') * 3600, TRUE);
            $validEndMonth = date_i18n('m', time() + get_option('gmt_offset') * 3600, TRUE);
        }

        /*DEBUG*/ //echo "<br />Start Timestamp: ".intval($paramStartTimestamp).", End Timestamp: ".intval($paramEndTimestamp);
        /*DEBUG*/ //echo "<br />Start Year: {$validStartYear}, Start Month: {$validStartMonth}";
        /*DEBUG*/ //echo "<br />End Year: {$validEndYear}, End Month: {$validEndMonth}";

        if($paramDefaultValue != "" || $paramDefaultLabel != "")
        {
            if($paramDefaultValue == $paramSelectedValue)
            {
                $retHTML .= '<option value="'.$printDefaultValue.'" selected="selected">'.$printDefaultLabel.'</option>';
            } else
            {
                $retHTML .= '<option value="'.$printDefaultValue.'">'.$printDefaultLabel.'</option>';
            }
        }

        for($year = $validStartYear; $year <= $validEndYear; $year++)
        {
            $monthToStart = $year == $validStartYear ? $validStartMonth : 1;
            $monthToEnd = $year == $validEndYear ? $validEndMonth : 12;
            for($month = $monthToStart; $month <= $monthToEnd; $month++)
            {
                $timestamp = strtotime("{$year}-{$month}-01");
                $value = date($shortDateFormat, $timestamp);
                $label = date($monthWithYearFormat, $timestamp);
                if($value == $paramSelectedValue)
                {
                    $retHTML .= '<option value="'.$value.'" selected="selected">'.$label.'</option>';
                } else
                {
                    $retHTML .= '<option value="'.$value.'">'.$label.'</option>';
                }
            }
        }

        return $retHTML;
    }

    /**
     * @param int $paramCurrentInteger
     * @param array $allowedIntegers
     * @return int
     */
    public static function getClosestInteger($paramCurrentInteger, array $allowedIntegers)
    {
        $validCurrentInteger = intval($paramCurrentInteger);
        $closestInteger = isset($allowedIntegers[0]) ? $allowedIntegers[0] : 0;
        foreach ($allowedIntegers AS $allowedInteger)
        {
            if (abs($validCurrentInteger - $closestInteger) > abs($allowedInteger - $validCurrentInteger))
            {
                $closestInteger = $allowedInteger;
            }
        }
        return $closestInteger;
    }

    /**
     * @param int $paramTimeInterval
     * @param string $paramSelectedTime
     * @param string $paramISOTimeFrom
     * @param string $paramISOTimeTo
     * @param string $paramMidnightText
     * @param string $paramNoonText
     * @param array $paramExcludedTimes - times to exclude, i.e. (09:00:00)
     * @return string
     */
    public static function getTimeDropdownOptions($paramTimeInterval = 1800, $paramSelectedTime = "09:00:00", $paramISOTimeFrom = "00:00:00", $paramISOTimeTo = "23:30:00", $paramMidnightText = "00:00", $paramNoonText = "12:00", $paramExcludedTimes = array())
    {
        $UTCUnixTimeFrom = strtotime(date("Y-m-d")." ".$paramISOTimeFrom);
        $UTCUnixTimeTo = strtotime(date("Y-m-d")." ".$paramISOTimeTo);
        $retHTML = '';

        $paramSelectedTimeParts = explode(":", $paramSelectedTime);
        $validSelectedHours = isset($paramSelectedTimeParts[0]) ? intval($paramSelectedTimeParts[0]) : 0;
        $validSelectedMinutes = isset($paramSelectedTimeParts[1]) ? intval($paramSelectedTimeParts[1]) : 0;
        $validSelectedSeconds = isset($paramSelectedTimeParts[2]) ? intval($paramSelectedTimeParts[2]) : 0;

        if($validSelectedHours >= 24)
        {
            $validSelectedHours = 0;
        }

        switch($paramTimeInterval)
        {
            // 5 minutes
            case 300:
                $minutesInterval = 5;
                $allowedMinutes = array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 59);
                break;

            // 10 minutes
            case 600:
                $minutesInterval = 10;
                $allowedMinutes = array(0, 10, 20, 30, 40, 50, 59);
                break;

            // 15 minutes
            case 900:
                $minutesInterval = 15;
                $allowedMinutes = array(0, 15, 30, 45, 59);
                break;

            // 20 minutes
            case 1200:
                $minutesInterval = 20;
                $allowedMinutes = array(0, 20, 40, 59);
                break;

            // 30 minutes
            case 1800:
                $minutesInterval = 30;
                $allowedMinutes = array(0, 30, 59);
                break;

            // 60 minutes
            case 3600:
                $minutesInterval = 60;
                $allowedMinutes = array(0, 59);
                break;

            default:
                $allowedMinutes = array(0, 30, 59);
                $minutesInterval = 30;
        }

        $validSelectedMinutes = static::getClosestInteger($validSelectedMinutes, $allowedMinutes);
        if(!in_array($validSelectedSeconds, array(0, 59, 60)))
        {
            $validSelectedSeconds = 0;
        }

        $stringSelectedHours = str_pad($validSelectedHours, 2, "0", STR_PAD_LEFT);
        $stringSelectedMinutes = str_pad($validSelectedMinutes, 2, "0", STR_PAD_LEFT);
        $stringSelectedSeconds = str_pad($validSelectedSeconds, 2, "0", STR_PAD_LEFT);

        // Make selected time string
        $stringSelectedTime = "{$stringSelectedHours}:{$stringSelectedMinutes}:{$stringSelectedSeconds}";

        for($hour = 0; $hour < 24; $hour++)
        {
            for($min = 0; $min < 60; $min = $min+$minutesInterval)
            {
                $currentHour = sprintf("%02d", $hour);
                $currentMin = sprintf("%02d", $min);

                $currentTime = $currentHour.':'.$currentMin.':00';

                $UTCUnixCurrentTime = strtotime(date("Y-m-d")." ".$currentTime);
                if($currentTime == "00:00:00")
                {
                    $printCurrentTime = esc_html(sanitize_text_field($paramMidnightText));
                } else if($currentTime == "12:00:00")
                {
                    $printCurrentTime = esc_html(sanitize_text_field($paramNoonText));
                } else
                {
                    $printCurrentTime = date_i18n(get_option('time_format'), $UTCUnixCurrentTime, TRUE);
                }

                // Show time only it is is not in exclude list
                if($UTCUnixCurrentTime >= $UTCUnixTimeFrom && $UTCUnixCurrentTime <= $UTCUnixTimeTo && !in_array($currentTime, $paramExcludedTimes))
                {
                    if($currentTime == $stringSelectedTime)
                    {
                        $retHTML .= '<option value="'.$currentTime.'" selected="selected">'.$printCurrentTime.'</option>';
                    } else
                    {
                        $retHTML .= '<option value="'.$currentTime.'">'.$printCurrentTime.'</option>';
                    }
                }
            }
        }

        // Special 23:59:59
        if($paramISOTimeTo == "23:59:59" && !in_array("23:59:59", $paramExcludedTimes))
        {
            $UTCUnixCurrentTime = strtotime(date("Y-m-d")." 23:59:59");
            $printCurrentTime = date_i18n(get_option('time_format'), $UTCUnixCurrentTime, TRUE);

            if($paramSelectedTime == "23:59:59")
            {
                $retHTML .= '<option value="23:59:59" selected="selected">'.$printCurrentTime.'</option>';

            } else
            {
                $retHTML .= '<option value="23:59:59">'.$printCurrentTime.'</option>';
            }
        }

        // DEBUG
        //echo "<br />getTimeDropdownOptions(): FROM-TO TIME: {$paramISOTimeFrom} - {$paramISOTimeTo}, SELECTED: {$paramSelectedTime}";

        return $retHTML;
    }

    /**
     * Number drop-down options for any select
     * @param int $paramValueFrom
     * @param int $paramValueTill
     * @param int $paramSelectedValue
     * @param string $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param string $paramSuffix
     * @return string
     */
    public static function getNumberDropdownOptions($paramValueFrom = 0, $paramValueTill = 100, $paramSelectedValue = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramSuffix = "")
    {
        $retHTML = '';
        $validSuffix = esc_html(sanitize_text_field($paramSuffix));

        if($paramDefaultValue != "" || $paramDefaultLabel != "")
        {
            $printDefaultValue = esc_html(sanitize_text_field($paramDefaultValue));
            $printDefaultLabel = esc_html(sanitize_text_field($paramDefaultLabel));
            if($paramSelectedValue == $paramDefaultValue)
            {
                $retHTML .= '<option value="'.$printDefaultValue.'" selected="selected">'.$printDefaultLabel.'</option>';
            } else
            {
                $retHTML .= '<option value="'.$printDefaultValue.'">'.$printDefaultLabel.'</option>';
            }
        }
        $i = intval($paramValueFrom);
        while ($i <= $paramValueTill)
        {
            if($i < 100)
            {
                // 1+
                $i += 1;
            } else if($i >= 100 && $i < 1000)
            {
                // 100+
                $i += 10;
            } else if($i >= 1000 && $i < 10000)
            {
                // 1k+
                $i += 100;
            } else if($i >= 10000 && $i < 100000)
            {
                // 10k+
                $i += 1000;
            } else if($i >= 100000 && $i < 1000000)
            {
                // 100k+
                $i += 10000;
            } else if($i >= 1000000)
            {
                // 1M+
                $i += 100000;
            }
            $value = $i;
            if($i == $paramSelectedValue)
            {
                $retHTML .= '<option value="'.$value.'" selected="selected">'.$i.$validSuffix.'</option>';
            } else
            {
                $retHTML .= '<option value="'.$value.'">'.$i.$validSuffix.'</option>';
            }
        }

        return $retHTML;
    }


    public static function getTabParams($paramArrTabsToCheck = array(), $paramSelectedTabByDefault = '', $paramSelectedTab = '')
    {
        $retTabs = array();
        $validSelectedTabByDefault = sanitize_key($paramSelectedTabByDefault);

        $oneTabAlreadyChecked = FALSE;
        if(is_array($paramArrTabsToCheck))
        {
            foreach($paramArrTabsToCheck AS $paramTab)
            {
                // Allow to check only one tab
                if($oneTabAlreadyChecked === FALSE && $paramSelectedTab == $paramTab)
                {
                    $retTabs[sanitize_key($paramTab)] = TRUE;
                    $oneTabAlreadyChecked = TRUE;
                } else
                {
                    $retTabs[sanitize_key($paramTab)] = FALSE;
                }
            }

            // If no tabs are marked as checked
            if($oneTabAlreadyChecked === FALSE && isset($retTabs[$validSelectedTabByDefault]))
            {
                $retTabs[$validSelectedTabByDefault] = TRUE;
            }
        }
        //echo nl2br(print_r($_REQUEST, TRUE));

        return $retTabs;
    }

    public static function extractDomainFromURL($paramURL)
    {
        $validDomainName = parse_url($paramURL, PHP_URL_HOST);

        return $validDomainName;
    }

    public static function getCurrentURL()
    {
        $validCurrentURL = esc_url("//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

        return $validCurrentURL;
    }
}