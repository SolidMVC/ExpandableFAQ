<?php
/**
 * Language must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Language;

interface LanguageInterface
{
    /**
     * @param string $paramTextDomain
     * @param string $paramGlobalPluginLangPath
     * @param string $paramLocalLangPath
     * @param string $paramLocale
     * @param bool $paramStrictLocale
     * @throws \Exception
     */
    public function __construct($paramTextDomain, $paramGlobalPluginLangPath, $paramLocalLangPath, $paramLocale = "en_US", $paramStrictLocale = FALSE);

    /**
     * @return string
     */
    public function getCoreLocale();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * NOTE #1: Supports multiline text
     * NOTE #2: Unescaped
     * @param string $paramKey
     * @return string
     */
    public function getText($paramKey);

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escSQL($paramKey);

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escAttr($paramKey);

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escBrHTML($paramKey);

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escHTML($paramKey);

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escJS($paramKey);

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escTextarea($paramKey);

    /**
     * @return array
     */
    public function getAll();
    public function isRTL();
    public function getQuantityText($quantity, $singularText, $pluralText, $pluralText2);
    public function getPositionText($position, $textST, $textND, $textRD, $textTH);
    public function getTimeText($number, $singularText, $pluralText, $pluralText2);
    public function canTranslateSQL();
    public function register($paramKey, $paramValue);
    public function getTranslated($paramKey, $paramNonTranslatedValue);
    public function getTranslatedURL($paramPostId);
}