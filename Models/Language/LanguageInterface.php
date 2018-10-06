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
     * @param string $paramGlobalExtLangPath
     * @param string $paramLocalExtLangPath
     * @param string $paramLocale
     * @param bool $paramStrictLocale
     * @throws \Exception
     */
    public function __construct($paramTextDomain, $paramGlobalExtLangPath, $paramLocalExtLangPath, $paramLocale = "en_US", $paramStrictLocale = FALSE);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param string $paramKey
     * @return string
     */
    public function getPrint($paramKey);

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
    public function getTranslatedUrl($paramPostId);
}