<?php
/**
 * Configuration class dependant on template
 * Note 1: This is a root class and do not depend on any other plugin classes

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Routing;

interface RoutingInterface
{
    public function __construct($paramPluginPath, $paramPluginURL, $paramThemeUI_FolderName);

    //PATH METHODS: START
    public function get3rdPartyAssetsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);

    public function getFrontCompatibilityCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontCompatibilityDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontLocalCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontLocalDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontSitewideCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontSitewideDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);

    public function getAdminCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);

    public function getFrontFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getDemoGalleryPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getSQLsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);

    /**
     * NOTE: We use word 'Common' here because word 'Global' is a reserved word and cannot be used in namespaces,
     *       what creates us a confusion then to have related 'Global' controllers
     *
     * @param string $paramRelativePathAndFile
     * @param bool $paramReturnWithFileName
     * @return mixed
     */
    public function getCommonTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE);

    // URL METHODS: START
    public function get3rdPartyAssetsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontCompatibilityCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontCompatibilityDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontLocalCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontLocalDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontSitewideCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontSitewideDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFrontDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getAdminDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getDemoGalleryURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE);
    public function getFolderURLs();
}