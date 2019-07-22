<?php
/**
 * Static cache must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Cache;

interface StaticCacheInterface
{
    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramHTMLs
     */
    public static function cacheHTML_Array($paramKey, array $paramHTMLs);

    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramValues
     */
    public static function cacheValueArray($paramKey, array $paramValues);

    /**
     * @param string $paramHTML
     * @param string $paramKey
     */
    public static function cacheHTML($paramHTML, $paramKey);

    /**
     * @param string $paramKey
     * @param string $paramValue
     */
    public static function cacheValue($paramKey, $paramValue);

    /**
     * @param string $paramKey
     * @return string
     */
    public static function getKsesedHTML_Once($paramKey);

    /**
     * NOTE: Unescaped
     * @param string $paramKey
     * @return string
     */
    public static function getValueOnce($paramKey);

    /**
     * @param string $paramKey
     * @return void
     */
    public static function unsetKey($paramKey);
}