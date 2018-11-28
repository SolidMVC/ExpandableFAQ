<?php
/**
 * Configuration class dependant on template
 * Note 1: This is a root class and do not depend on any other plugin classes

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Configuration;
use ExpandableFAQ\Models\Routing\RoutingInterface;

interface ConfigurationInterface
{
    // Used mostly for an autoloader
    const PLUGIN_NAMESPACE                      = "ExpandableFAQ";

    /**
     * @param \wpdb $paramWPDB
     * @param int $paramBlogId
     * @param string $paramRequiredPHP_Version
     * @param string $paramCurrentPHP_Version
     * @param float $paramRequiredWP_Version
     * @param float $paramCurrentWP_Version
     * @param string $paramOldestCompatiblePluginSemver
     * @param string $paramPluginSemver
     * @param string $paramPluginPathWithFilename
     * @param array $params
     */
    public function __construct(
        \wpdb &$paramWPDB, $paramBlogId, $paramRequiredPHP_Version, $paramCurrentPHP_Version, $paramRequiredWP_Version,
        $paramCurrentWP_Version, $paramOldestCompatiblePluginSemver, $paramPluginSemver, $paramPluginPathWithFilename, array $params
    );

    // Dependency Injection Methods

    /**
     * @param RoutingInterface $routing
     * @return void
     */
    public function setRouting(RoutingInterface $routing);
    /**
     * @return null|RoutingInterface
     */
    public function getRouting();

    // Core methods
    public function getPluginNamespace();
    /**
     * @return \wpdb
     */
    public function getInternalWPDB();
    public function getBlogId();
    public function getBlogLocale($paramBlogId = -1);
    public function getRequiredPHP_Version();
    public function getCurrentPHP_Version();
    public function getRequiredWP_Version();
    public function getCurrentWP_Version();
    public function getOldestCompatiblePluginSemver();
    public function getPluginSemver();
    public function getEditPluginSemver();
    public function getPrintPluginSemver();
    public function isNetworkEnabled();
    public function getPluginPrefix();
    public function getPluginHandlePrefix();
    public function getPluginURL_Prefix();
    public function getPluginCSS_Prefix();
    public function getPluginJS_ClassPrefix();
    public function getPluginJS_VariablePrefix();
    public function getThemeUI_FolderName();
    public function getPluginName();
    public function getBlogPrefix($paramBlogId = -1);
    public function getWP_Prefix();
    public function getPrefix();
    public function getShortcode();
    public function getTextDomain();

    // Path methods
    public function getWP_PluginsPath();
    public function getPluginPathWithFilename();
    public function getPluginPath();
    public function getPluginBasename();
    public function getPluginFolderName();
    public function getLibrariesPath();
    public function getLocalLangPath();
    public function getGlobalLangPath();
    public function getLocalLangRelPath();

    // URL methods
    public function getPluginURL();
}