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

interface NetworkStatusInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang);
    public function inDebug();
    /**
     * Additional links to show in network plugins manager
     * @return array
     */
    public function getAdditionalActionLinks();

    /**
     * Additional links to show in next to network plugin description
     * @return array
     */
    public function getInfoLinks();

    /**
     * @note1 - This function maintains backwards compatibility to NS V4.3 and newer
     * @note2 - This function says if there are plugin struct
     * @param float $paramRequiredPluginVersion
     * @return bool
     */
    public function checkPluginDBStructExists($paramRequiredPluginVersion);

    /**
     * @note1 - This function maintains backwards compatibility to NS V4.3 and newer
     * @note2 - This function says if there data exists for at least one extension
     * @param float $paramRequiredPluginVersion
     * @return bool
     */
    public function checkPluginDataExistsInSomeBlog($paramRequiredPluginVersion);

    /**
     * @note - This function maintains backwards compatibility to NS V4.3 and newer
     * @return float
     */
    public function getAllPluginVersionsInDatabase();

    /**
     * @note - This function maintains backwards compatibility to NS V4.3 and newer
     * @return float
     */
    public function getMinPluginVersionInDatabase();

    /**
     * Is the NS database version is newer or same as code version. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isAllBlogsWithPluginDataUpToDate();

    /**
     * @return bool
     */
    public function canUpdatePluginDataInSomeBlog();

    /**
     * Can we do a major upgrade in some blog, i.e. from V1 to V2 etc., not V1 to V1.1
     * @return bool
     */
    public function canMajorlyUpgradePluginDataInSomeBlog();
}