<?php
/**
 * Status must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Status;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

interface SingleStatusInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId);
    public function getId();
    public function inDebug();

    /**
     * Get additional links to show in local plugins manager
     * @return array
     */
    public function getActionLinks();

    /**
     * Additional links to show in next to local plugin description
     * @return array
     */
    public function getInfoLinks();

    /**
     * @note1 - This function maintains backwards compatibility to NS V6.0 and newer
     * @note2 - This function says if there are plugin struct
     * @param float $paramRequiredPluginVersion
     * @return bool
     */
    public function checkPluginDBStructExists($paramRequiredPluginVersion);

    /**
     * @note1 - This function maintains backwards compatibility to NS V6.0 and newer
     * @note2 - This function says if there data exists for at least one extension
     * @param float $paramRequiredPluginVersion
     * @return bool
     */
    public function checkPluginDataExists($paramRequiredPluginVersion);

    /**
     * @note - This function maintains backwards compatibility to NS V6.0 and newer
     * @return float
     */
    public function getPluginVersionInDatabase();

    /**
     * Is the NS database version is newer or same as code version. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isPluginDataUpToDateInDatabase();

    /**
     * @return bool
     */
    public function canUpdatePluginDataInDatabase();

    /**
     * Can we do a major upgrade, i.e. from V1 to V2 etc., not V1 to V1.1
     * @return bool
     */
    public function canMajorlyUpgradePluginDataInDatabase();
}