<?php
/**
 * Network status must-have interface
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
     * @note1 - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @note2 - This function says if there are plugin struct of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDB_StructExistsOf($paramRequiredPluginSemver);

    /**
     * Differently to "Exists of semver" class method, this class method is based
     * on existence of compatible data in some blog
     *
     * @note1 - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @note2 - This function says if the data exists for at least one extension of required semver
     * @return bool
     */
    public function checkPluginCompatibleDataExistsInSomeBlog();

    /**
     * @note1 - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @note2 - This function says if the data exists for at least one extension of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDataExistsInSomeBlogOf($paramRequiredPluginSemver);

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return array
     */
    public function getAllPluginSemversInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getMinPluginSemverInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getEditMinPluginSemverInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getPrintMinPluginSemverInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getMaxPluginSemverInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getEditMaxPluginSemverInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 6.0.0 and newer
     * @return string
     */
    public function getPrintMaxPluginSemverInDatabase();

    /**
     * Is the NS database semver is newer or same as code semver. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isAllBlogsWithPluginDataUpToDate();

    /**
     * NOTE: Update may exist, but the system might be not compatible for update
     * @return bool
     */
    public function checkPluginUpdateExistsForSomeBlog();

    /**
     * @return bool
     */
    public function canUpdatePluginDataInSomeBlog();

    /**
     * Can we do a major upgrade in some blog, i.e. from 1.*.* to 2.*.* etc., not 1.0.* to 1.1.*
     * @return bool
     */
    public function canMajorlyUpgradePluginDataInSomeBlog();
}