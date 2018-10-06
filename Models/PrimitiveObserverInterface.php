<?php
/**
 * Observer  must-have interface (without settings array)
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

interface PrimitiveObserverInterface
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang);
    public function inDebug();
}