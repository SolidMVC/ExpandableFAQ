<?php
/**
 * Element must-have interface - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

interface UpdateInterface
{
    public function alterDatabaseEarlyStructure();
    public function updateDatabaseData();
    public function alterDatabaseLateStructure();
    public function updateDatabaseVersion();

    // Base methods
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId);
    public function inDebug();
    public function getId();
}