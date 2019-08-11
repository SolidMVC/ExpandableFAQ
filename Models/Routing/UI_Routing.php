<?php
/**
 * UI routing class dependant on template
 * Note: This is a root class and do not depend on any other plugin classes
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Routing;

final class UI_Routing implements RoutingInterface
{
    private $pluginPath                         = "";
    private $pluginURL                          = "";
    private $themeUI_FolderName                 = "";
    private $debugMode                          = 0;
    private $arrPathsCache                      = array();
    private $arrURLsCache                       = array();

    public function __construct($paramPluginPath, $paramPluginURL, $paramThemeUI_FolderName)
    {
        // Set class settings
        $this->pluginPath = sanitize_text_field($paramPluginPath);
        $this->pluginURL = sanitize_text_field($paramPluginURL);
        $this->themeUI_FolderName = sanitize_text_field($paramThemeUI_FolderName);
    }

    /**
     * @note - file_exist and is_readable are server time and resources consuming, so we cache the paths
     * @param string $paramRelativePathAndFile
     * @param bool $paramReturnWithFileName
     * @return string
     */
    private function getPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $ret = DIRECTORY_SEPARATOR;
        $validRelativePathAndFile = sanitize_text_field($paramRelativePathAndFile);

        // If the file with path is not yet cached
        if(!isset($this->arrPathsCache[$validRelativePathAndFile]))
        {
            // NOTE #1: If the folder is not in the plugin folder, then the folder name has a 'Rental' prefix
            // NOTE #2: Common path check should always go after extension path check,
            //          because otherwise we would not be able to create a common template in a child theme, that would override the rest designs
            $uiPathInCurrentTheme = get_stylesheet_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR;
            $uiPathInParentTheme = get_template_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR;
            $uiPathInPluginFolder = $this->pluginPath.'UI'.DIRECTORY_SEPARATOR;

            if(is_readable($uiPathInCurrentTheme.$validRelativePathAndFile))
            {
                // First - check for <THEME_UI_FOLDER_NAME>/ folder in current theme's folder
                $ret = $uiPathInCurrentTheme;
            } else if($uiPathInCurrentTheme != $uiPathInParentTheme && is_readable($uiPathInParentTheme.$validRelativePathAndFile))
            {
                // Third - check for <THEME_UI_FOLDER_NAME>/folder in parent theme's folder
                $ret = $uiPathInParentTheme;
            } else if(is_readable($uiPathInPluginFolder.$validRelativePathAndFile))
            {
                // Fifth - check for UI/ folder in local plugin folder
                $ret = $uiPathInPluginFolder;
            }

            // Save path to cache for future use
            $this->arrPathsCache[$validRelativePathAndFile] = $ret;

            if($this->debugMode == 2)
            {
                echo "<br /><br /><strong>[Routing] Checking getPath(&#39;".$validRelativePathAndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Target UI path &amp; file in current theme: ".$uiPathInCurrentTheme.$validRelativePathAndFile;
                echo "<br />[Routing] Target UI path &amp; file in parent theme: ".$uiPathInParentTheme.$validRelativePathAndFile;
                echo "<br />[Routing] Target UI path &amp; file in plugin folder: ".$uiPathInPluginFolder.$validRelativePathAndFile;
                echo "<br />[Routing] Returned path: ".$ret;
            }
        } else
        {
            // Return path from cache
            $ret = $this->arrPathsCache[$validRelativePathAndFile];

            if($this->debugMode == 2)
            {
                echo "<br /><br /><strong>[Routing] Checking getPath(&#39;".$validRelativePathAndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Returned path from cache: ".$ret;
            }
        }

        return $ret.($paramReturnWithFileName === TRUE ? $validRelativePathAndFile : '');
    }

    /**
     * @note - file_exist and is_readable are server time and resources consuming, so we cache the paths
     * @param string $paramRelativeURL_AndFile
     * @param bool $paramReturnWithFileName
     * @return string
     */
    private function getURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $ret = '/';
        $validRelativeURL_AndFile = sanitize_text_field($paramRelativeURL_AndFile);
        $validRelativePathAndFile = str_replace('/', DIRECTORY_SEPARATOR, $paramRelativeURL_AndFile);

        // If the file with path is not yet cached
        if(!isset($this->arrURLsCache[$validRelativeURL_AndFile]))
        {
            // NOTE #1: If the folder is not in the plugin folder, then the folder name has a 'Rental' prefix
            // NOTE #2: Common path check should always go after extension path check,
            //          because otherwise we would not be able to create a common template in a child theme, that would override the rest designs
            $uiPathInCurrentTheme = get_stylesheet_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR;
            $uiPathInParentTheme = get_template_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR;
            $uiPathInPluginFolder = $this->pluginPath.'UI'.DIRECTORY_SEPARATOR;

            // NOTE #1: If the folder is not in the plugin folder, then the folder name has a 'Rental' prefix
            // NOTE #2: Common URL check should always go after extension URL check,
            //          because otherwise we would not be able to create a common template in a child theme, that would override the rest designs
            $uiURL_InCurrentTheme = get_stylesheet_directory_uri().'/'.$this->themeUI_FolderName.'/';
            $uiURL_InParentTheme = get_template_directory_uri().'/'.$this->themeUI_FolderName.'/';
            $uiURL_InPluginFolder = $this->pluginURL.'UI/';

            if(is_readable($uiPathInCurrentTheme.$validRelativePathAndFile))
            {
                // First - check for <THEME_UI_FOLDER_NAME>/<folder in current theme's folder
                $ret = $uiURL_InCurrentTheme; // URL
            } else if($uiPathInCurrentTheme != $uiPathInParentTheme && is_readable($uiPathInParentTheme.$validRelativePathAndFile))
            {
                // Third - check for <THEME_UI_FOLDER_NAME>/ folder in parent theme's folder
                $ret = $uiURL_InParentTheme; // URL
            } else if(is_readable($uiPathInPluginFolder.$validRelativePathAndFile))
            {
                // Fifth - check for UI/ folder in local plugin folder
                $ret = $uiURL_InPluginFolder; // URL
            }

            // Save URL to cache for future use
            $this->arrURLsCache[$validRelativeURL_AndFile] = $ret;

            if($this->debugMode == 1)
            {
                echo "<br /><br /><strong>[Routing] Checking getExtURL(&#39;".$validRelativeURL_AndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Target UI URL in current theme: ".$uiURL_InCurrentTheme.$validRelativeURL_AndFile;
                echo "<br />[Routing] Target UI URL in parent theme: ".$uiURL_InParentTheme.$validRelativeURL_AndFile;
                echo "<br />[Routing] Target UI URL in plugin folder: ".$uiURL_InPluginFolder.$validRelativeURL_AndFile;
                echo "<br />[Routing] Returned URL: ".$ret;
            }
        } else
        {
            // Return URL from cache
            $ret = $this->arrURLsCache[$validRelativeURL_AndFile];

            if($this->debugMode == 1)
            {
                echo "<br /><br /><strong>[Routing] Checking getURL(&#39;".$validRelativeURL_AndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Returned URL from cache: ".$ret;
            }
        }

        return $ret.($paramReturnWithFileName === TRUE ? $validRelativeURL_AndFile : '');
    }


    /****************************************************************************************/
    /* ------------------------------- PATH METHODS: START -------------------------------- */
    /****************************************************************************************/

    public function get3rdPartyAssetsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'3rdParty'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'3rdParty'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontCompatibilityCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontCompatibilityDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontLocalCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontLocalDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontSitewideCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontSitewideDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getAdminCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getAdminDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getAdminFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getAdminImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getAdminJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getAdminDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getDemoGalleryPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('DemoGallery'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'DemoGallery'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getSQLsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('SQLs'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'SQLs'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getFrontTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Templates'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Templates'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    public function getAdminTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Templates'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Templates'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }

    /**
     * NOTE: We use word 'Common' here because word 'Global' is a reserved word and cannot be used in namespaces,
     *       what creates us a confusion then to have related 'Global' controllers
     *
     * @param string $paramRelativePathAndFile
     * @param bool $paramReturnWithFileName
     * @return string
     */
    public function getCommonTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderPath = $this->getPath('Templates'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, FALSE)
            .'Templates'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === TRUE ? $paramRelativePathAndFile : '');
    }


    /****************************************************************************************/
    /* ---------------------------- URL METHODS: START ------------------------------------ */
    /****************************************************************************************/

    public function get3rdPartyAssetsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/3rdParty/'.$paramRelativeURL_AndFile, FALSE).'Assets/3rdParty/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontCompatibilityCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/CSS/Compatibility/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/CSS/Compatibility/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontCompatibilityDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/DevCSS/Compatibility/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/DevCSS/Compatibility/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontLocalCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/CSS/Local/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/CSS/Local/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontLocalDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/DevCSS/Local/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/DevCSS/Local/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontSitewideCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/CSS/Sitewide/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/CSS/Sitewide/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontSitewideDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/DevCSS/Sitewide/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/DevCSS/Sitewide/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Admin/CSS/'.$paramRelativeURL_AndFile, FALSE).'Assets/Admin/CSS/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Admin/DevCSS/'.$paramRelativeURL_AndFile, FALSE).'Assets/Admin/DevCSS/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/Fonts/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/Fonts/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Admin/Fonts/'.$paramRelativeURL_AndFile, FALSE).'Assets/Admin/Fonts/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/Images/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/Images/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Admin/Images/'.$paramRelativeURL_AndFile, FALSE).'Assets/Admin/Images/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/JS/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/JS/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Front/DevJS/'.$paramRelativeURL_AndFile, FALSE).'Assets/Front/DevJS/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Admin/JS/'.$paramRelativeURL_AndFile, FALSE).'Assets/Admin/JS/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('Assets/Admin/DevJS/'.$paramRelativeURL_AndFile, FALSE).'Assets/Admin/DevJS/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getDemoGalleryURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = TRUE)
    {
        $folderURL = $this->getURL('DemoGallery/'.$paramRelativeURL_AndFile, FALSE).'DemoGallery/';

        return $folderURL.($paramReturnWithFileName === TRUE ? $paramRelativeURL_AndFile : '');
    }

    public function getFolderURLs()
    {
        $urlRoots = array(
            "CURRENT_UI" => get_stylesheet_directory_uri().'/'.$this->themeUI_FolderName.'/',
            "PARENT_UI" => get_template_directory_uri().'/'.$this->themeUI_FolderName.'/',
            "PLUGIN_UI" => $this->pluginURL.'UI/',
        );

        $relativeURLs = array(
            "3RD_PARTY_ASSETS" => "Assets/3rdParty/",
            "FRONT_COMPATIBILITY_CSS" => "Assets/Front/CSS/Compatibility/",
            "FRONT_COMPATIBILITY_DEV_CSS" => "Assets/Front/DevCSS/Compatibility/",
            "FRONT_LOCAL_CSS" => "Assets/Front/CSS/Local/",
            "FRONT_LOCAL_DEV_CSS" => "Assets/Front/DevCSS/Local/",
            "FRONT_SITEWIDE_CSS" => "Assets/Front/CSS/Sitewide/",
            "FRONT_SITEWIDE_DEV_CSS" => "Assets/Front/DevCSS/Sitewide/",
            "ADMIN_CSS" => "Assets/Admin/CSS/",
            "ADMIN_DEV_CSS" => "Assets/Admin/DevCSS/",
            "FRONT_FONTS" => "Assets/Front/Fonts/",
            "ADMIN_FONTS" => "Assets/Admin/Fonts/",
            "FRONT_IMAGES" => "Assets/Front/Images/",
            "ADMIN_IMAGES" => "Assets/Admin/Images/",
            "FRONT_JS" => "Assets/Front/JS/",
            "FRONT_DEV_JS" => "Assets/Front/DevJS/",
            "ADMIN_JS" => "Assets/Admin/JS/",
            "ADMIN_DEV_JS" => "Assets/Admin/DevJS/",
            "DEMO_GALLERY" => "DemoGallery/",
        );

        $urls = array();
        foreach($urlRoots AS $urlRootKey => $urlRootValue)
        {
            foreach($relativeURLs AS $relativeURL_Key => $relativeURL_Value)
            {
                $urls[$urlRootKey][$relativeURL_Key] = $urlRootValue.$relativeURL_Value;
            }
        }

        return $urls;
    }
}