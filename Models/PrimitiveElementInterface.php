<?php
/**
 * Element must-have interface (without settings array) - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

interface PrimitiveElementInterface
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramElementId);
    public function getId();
    public function inDebug();
    public function getDetails($paramIncludeUnclassified = FALSE);
    /**
     * @param array $params
     * @return false|int
     */
    public function save(array $params);
    /**
     * @return void
     */
    public function registerForTranslation();
    /**
     * @return false|int
     */
    public function delete();
}