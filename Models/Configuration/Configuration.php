<?php
/**
 * Configuration class dependant on template
 * Note: This is a root class and do not depend on any other plugin classes
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Configuration;
use ExpandableFAQ\Models\Routing\RoutingInterface;

final class Configuration implements ConfigurationInterface
{
    private $routing                            = NULL; // Dependency injection for routing interface

    private $internalWPDB                       = NULL;
    private $blogId                             = 1;
    private $debugMode                          = 0;

    private $requiredPHP_Version                = '5.4.0';
    private $currentPHP_Version                 = '0.0.0';
    private $requiredWP_Version                 = 4.6;
    private $currentWP_Version                  = 0.0;
    private $oldestCompatiblePluginSemver       = '0.0.0';
    private $pluginSemver                       = '0.0.0';
    private $pluginPrefix                       = "";
    private $pluginHandlePrefix                 = "";
    private $pluginURL_Prefix                   = "";
    private $pluginCSS_Prefix                   = "";
    private $galleryFolderName                  = "";
    private $globalGalleryPath                  = "";
    private $globalGalleryPathWithoutEndSlash   = "";
    private $globalGalleryURL                   = "";
    private $themeUI_FolderName                 = "";
    private $pluginName                         = "";
    private $blogPrefix                         = "";
    private $wpPrefix                           = "";
    private $prefix                             = "";
    private $networkEnabled                     = FALSE;
    private $shortcode                          = "";
    private $textDomain                         = "";

    // Paths
    private $wpPluginsPath                      = "";
    private $pluginPathWithFilename             = "";
    private $pluginPath                         = "";
    private $pluginBasename                     = "";
    private $pluginFolderName                   = "";
    private $librariesPath                      = "";
    private $localLangPath                      = "";
    private $globalLangPath                     = "";
    private $globalPluginLangPath               = "";
    private $localLangRelPath                   = "";

    // URLs
    private $pluginURL                          = "";

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
    ) {
        // Makes sure the plugin is defined before trying to use it, because by default it is available only for admin section
        if(!function_exists('is_plugin_active_for_network'))
        {
            require_once ABSPATH.'/wp-admin/includes/plugin.php';
        }

        $this->internalWPDB = $paramWPDB;
        $this->blogId = absint($paramBlogId);

        $this->requiredPHP_Version              = !is_array($paramRequiredPHP_Version) ? preg_replace('[^0-9\.,]', '', $paramRequiredPHP_Version) : '5.4.0';
        $this->currentPHP_Version               = !is_array($paramCurrentPHP_Version) ? preg_replace('[^0-9\.,]', '', $paramCurrentPHP_Version) : '0.0.0';
        $this->requiredWP_Version               = !is_array($paramRequiredWP_Version) ? preg_replace('[^0-9\.,]', '', $paramRequiredWP_Version) : 4.6;
        $this->currentWP_Version                = !is_array($paramCurrentWP_Version) ? preg_replace('[^0-9\.,]', '', $paramCurrentWP_Version) : 0.0;
        $this->oldestCompatiblePluginSemver     = !is_array($paramOldestCompatiblePluginSemver) ? preg_replace('[^0-9A-Za-z-\+\.]', '', $paramOldestCompatiblePluginSemver) : '0.0.0';
        $this->pluginSemver                     = !is_array($paramPluginSemver) ? preg_replace('[^0-9A-Za-z-\+\.]', '', $paramPluginSemver) : '0.0.0';

        // We must use plugin_basename here, despite that we used full path for activation hook, because in database the plugin is still saved UNIX like:
        // network_db_prefix_options:
        //      Row: active_plugins
        //      Value (in JSON): <..>;i:0;s:32:"ExpandableFAQ/ExpandableFAQ.php";<..>
        $this->networkEnabled                   = is_plugin_active_for_network(plugin_basename($paramPluginPathWithFilename));
        $this->pluginPrefix                     = isset($params['plugin_prefix']) ? sanitize_key($params['plugin_prefix']) : '';
        $this->pluginHandlePrefix               = isset($params['plugin_handle_prefix']) ? sanitize_key($params['plugin_handle_prefix']) : '';
        $this->pluginURL_Prefix                 = isset($params['plugin_url_prefix']) ? sanitize_key($params['plugin_url_prefix']) : '';
        $this->pluginCSS_Prefix                 = isset($params['plugin_css_prefix']) ? sanitize_key($params['plugin_css_prefix']) : '';

        if(isset($params['gallery_folder_name']) && !is_array($params['gallery_folder_name']))
        {
            // No sanitization, uppercase needed
            $this->galleryFolderName            = preg_replace('[^-_0-9a-zA-Z]', '', $params['gallery_folder_name']);
        } else
        {
            $this->galleryFolderName            = '';
        }

        // Extension gallery is always in one place, so it is static, and can be defined in the class constructor to safe resources later
        $uploadsDir = wp_upload_dir();
        if($this->galleryFolderName != "")
        {
            // This plugin has its own gallery folder
            $this->globalGalleryPath = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$this->galleryFolderName.DIRECTORY_SEPARATOR;
            $this->globalGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$this->galleryFolderName;
            $this->globalGalleryURL = $uploadsDir['baseurl'].'/'.$this->galleryFolderName.'/';
        } else
        {
            // Otherwise - either gallery is not needed, or we should use global gallery folder
            $this->globalGalleryPath = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR;
            $this->globalGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']);
            $this->globalGalleryURL = $uploadsDir['baseurl'].'/';
        }

        if(isset($params['theme_ui_folder_name']) && !is_array($params['theme_ui_folder_name']))
        {
            // No sanitization, uppercase chars needed
            $this->themeUI_FolderName           = preg_replace('[^-_0-9a-zA-Z]', '', $params['theme_ui_folder_name']);

        } else
        {
            // No sanitization, uppercase chars needed
            $this->themeUI_FolderName           = 'UI';
        }

        if(isset($params['plugin_name']) && !is_array($params['plugin_name']))
        {
            // No sanitization, uppercase chars and spaces needed
            $this->pluginName                   = preg_replace('[^-_0-9a-zA-Z ]', '', $params['plugin_name']);
        } else
        {
            $this->pluginName                   = '';
        }

        // We need this for multisite data for regular WordPress tables, i.e. 'posts'.
        $this->blogPrefix                       = $this->internalWPDB->get_blog_prefix($paramBlogId);
        // We don't use unique blog prefix here, as we want to all multisite to work, this means that all sites data should be under same blog id
        // So use internalWPDB->prefix here instead, as it automatically figures out for every site
        // NOTE: Appears that WordPress internalWPDB->prefix cannot figure out himself, so need to do that on our own
        if($this->networkEnabled)
        {
            // Plugin is network-enabled, so we use same blog id for all sites
            // NOTE: 'BLOG_ID_CURRENT_SITE' should be always defined in multisite mode, but we do this check here for 'just in case'
            $networkBlogId                      = defined('BLOG_ID_CURRENT_SITE') ? BLOG_ID_CURRENT_SITE : 1;
            $this->prefix                       = $this->internalWPDB->get_blog_prefix($networkBlogId).$this->pluginPrefix;
            $this->wpPrefix                     = $this->internalWPDB->get_blog_prefix($networkBlogId);
        } else
        {
            // Plugin is locally-enabled, so we use same blog id of current site
            $this->prefix                       = $this->internalWPDB->prefix.$this->pluginPrefix;
            $this->wpPrefix                     = $this->internalWPDB->prefix;
        }

        $this->shortcode                        = isset($params['shortcode']) ? sanitize_key($params['shortcode']) : '';
        $this->textDomain                       = isset($params['text_domain']) ? sanitize_key($params['text_domain']) : '';


        /* ------------------------------------------------------------------------------------------------------- */
        /* Paths                                                                                                   */
        /* ------------------------------------------------------------------------------------------------------- */

        // Global Settings
        // Note 1: It's ok to use 'sanitize_text_field' function here,
        //       because this function does not escape or remove the '/' char in path.
        // Note 2: We use __FILE__ to make sure that we are not dependant on plugin folder name
        // Note 3: WordPress constants overview - http://wpengineer.com/2382/wordpress-constants-overview/
        // Demo examples (__FILE__ = $this->pluginFolderAndFile):
        // 1. __FILE__ => /GitHub/<REPOSITORY_NAME>/wp-content/plugins/ExpandableFAQ/ExpandableFAQ.php
        // 2. plugin_dir_path(__FILE__) => /GitHub/<REPOSITORY_NAME>/wp-content/plugins/ExpandableFAQ/ (with trailing slash at the end)
        // 3. plugin_basename(__FILE__) => ExpandableFAQ/ExpandableFAQ.php (used for active plugins list in WP database)
        // 4. dirname(plugin_basename((__FILE__)) => ExpandableFAQ
        // 5. basename($this->pluginPath) => ExpandableFAQ
        // 6. localLangRelPath used for load_textdomain, i.e. ExpandableFAQ/Languages/ (the correct example is WITH the ending trailing slash)
        $this->pluginPathWithFilename = sanitize_text_field($paramPluginPathWithFilename); // Leave directory separator UNIX like here, used in WP hooks

        // NOTE #1: The functions bellow must go after '$this->pluginPathWithFilename' retrieval
        // NOTE #2: WordPress 'wp_normalize_path(plugin_dir_path($this->pluginPathWithFilename))' would do the same as below,
        //       just it would always forward-slash the path (even in Windows Environment),
        //       and do not use DIRECTORY_SEPARATOR constant, that is always recommended to use
        // @see - https://stackoverflow.com/questions/26881333/when-to-use-directory-separator-in-php-code
        if(version_compare($this->currentPHP_Version, '7.0.0', '>='))
        {
            $this->pluginPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(dirname($this->pluginPathWithFilename, 1), '/\\').DIRECTORY_SEPARATOR);
            $this->wpPluginsPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(dirname($this->pluginPathWithFilename, 2), '/\\').DIRECTORY_SEPARATOR);
        } else
        {
            // PHP 5.6 backwards compatibility
            $this->pluginPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(php7_dirname($this->pluginPathWithFilename, 1), '/\\').DIRECTORY_SEPARATOR);
            $this->wpPluginsPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(php7_dirname($this->pluginPathWithFilename, 2), '/\\').DIRECTORY_SEPARATOR);
        }

        // Leave directory separator UNIX like here, used in WP database
        // Note: It is mostly used for add_filter calls and comparisons of plugin basename saved in WP options db table
        $this->pluginBasename = plugin_basename($this->pluginPathWithFilename);

        // Basename - Returns only the folder name of the path (or filename, if the filename is given)
        $this->pluginFolderName = basename($this->pluginPath);
        $this->librariesPath = $this->pluginPath.'Libraries'.DIRECTORY_SEPARATOR;
        $this->localLangPath = $this->pluginPath.'Languages'.DIRECTORY_SEPARATOR;
        $this->localLangRelPath = $this->pluginFolderName.'/Languages'; // No slash at the end (!)
        $wpLangDir = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),WP_LANG_DIR);
        $this->globalLangPath = $wpLangDir.DIRECTORY_SEPARATOR;
        $this->globalPluginLangPath = $wpLangDir.DIRECTORY_SEPARATOR.$this->pluginFolderName.DIRECTORY_SEPARATOR;


        /* ------------------------------------------------------------------------------------------------------- */
        /* URLs                                                                                                    */
        /* ------------------------------------------------------------------------------------------------------- */

        // esc_url replaces ' and & chars with &#39; and &amp; - but because we know that exact path,
        // we know it does not contains them, so we don't need to have two versions esc_url and esc_url_raw
        // Demo examples (__FILE__ = $this->pluginFolderAndFile):
        // 1. plugin_dir_url(__FILE__) => https://nativerental.com/wp-content/plugins/ExpandableFAQ/
        $this->pluginURL = esc_url(plugin_dir_url($this->pluginPathWithFilename));


        // DEBUG
        if($this->debugMode == 1)
        {
            echo "<br />[Configuration] Plugin Namespace: ".static::PLUGIN_NAMESPACE."\n";
            echo "<br />[Configuration] Blog Id: {$this->blogId}\n";
            echo "<br />[Configuration] Required PHP Version: {$this->requiredPHP_Version}\n";
            echo "<br />[Configuration] Current PHP Version: {$this->currentPHP_Version}\n";
            echo "<br />[Configuration] Required WP Version: {$this->requiredWP_Version}\n";
            echo "<br />[Configuration] Current WP Version: {$this->currentWP_Version}\n";
            echo "<br />[Configuration] Plugin Semver: {$this->pluginSemver}\n";
            echo "<br />[Configuration] Network Enabled: ".var_export($this->networkEnabled, TRUE)."\n";
            echo "<br />[Configuration] Plugin Path With Filename: {$this->pluginPathWithFilename}\n";
            echo "<br />[Configuration] Plugin Path: {$this->pluginPath}\n";
            echo "<br />[Configuration] Plugin Basename: {$this->pluginBasename}\n";
            echo "<br />[Configuration] Plugin Folder Name: {$this->pluginFolderName}\n";
            echo "<br />[Configuration] Local Lang Path: {$this->localLangPath}\n";
            echo "<br />[Configuration] Global Lang Path: {$this->globalLangPath}\n";
            echo "<br />[Configuration] Global Plugin Lang Path: {$this->globalPluginLangPath}\n";
            echo "<br />[Configuration] Local Lang Rel Path: {$this->localLangRelPath}\n";
            echo "<br />[Configuration] Plugin URL: {$this->pluginURL}\n";
        }
    }

    /**
     * This is late state (setter) dependency injection. We need to make sure that routing is always set
     * We can use for that either Dependency injection container
     * Or to use try{} catch{} statements for NULL. Because we need only one variable here,
     * we choose to go with exception handling scenario
     * @see #1 https://codeinphp.github.io/post/dependency-injection-in-php/
     * @see #2 http://krasimirtsonev.com/blog/article/Dependency-Injection-in-PHP-example-how-to-DI-create-your-own-dependency-injection-container
     * @param RoutingInterface $routing
     * @return void
     */
    public function setRouting(RoutingInterface $routing)
    {
        $this->routing = $routing;
    }

    /**
     * @return null|RoutingInterface
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * @return \wpdb
     */
    public function getInternalWPDB()
    {
        return $this->internalWPDB;
    }

    public function getBlogId()
    {
        return $this->blogId;
    }

    /**
     * Get's blog locale for early calls AS get_locale() is not allowed to process in install process
     *
     * @param int $paramBlogId
     * @return string
     */
    public function getBlogLocale($paramBlogId = -1)
    {
        if($paramBlogId == -1)
        {
            // Skip blog id overriding
            $validBlogPrefix = $this->blogPrefix;
        } else
        {
            $validBlogPrefix = $this->internalWPDB->get_blog_prefix($paramBlogId);
        }
        // A workaround, that does a direct call to WP table
        $sqlQuery = "SELECT option_value FROM `{$validBlogPrefix}options` WHERE option_name='WPLANG'";
        $blogLocaleResult = $this->internalWPDB->get_var($sqlQuery);
        $blogLocale = !is_null($blogLocaleResult) && $blogLocaleResult != '' ? $blogLocaleResult : 'en_US';

        // DEBUG
        if($this->debugMode == 1)
        {
            echo "<br />[Configuration] BLOG ID:".intval($paramBlogId).", LOCALE (STATIC FOR INSTALL): ".get_locale().", locale via WPLANG from DB: ".$blogLocale."\n";
        }

        return $blogLocale;
    }

    public function getRequiredPHP_Version()
    {
        return $this->requiredPHP_Version;
    }

    public function getCurrentPHP_Version()
    {
        return $this->currentPHP_Version;
    }

    public function getRequiredWP_Version()
    {
        return $this->requiredWP_Version;
    }

    public function getCurrentWP_Version()
    {
        return $this->currentWP_Version;
    }

    public function getOldestCompatiblePluginSemver()
    {
        return $this->oldestCompatiblePluginSemver;
    }

    public function getPluginSemver()
    {
        return $this->pluginSemver;
    }

    public function isNetworkEnabled()
    {
        return $this->networkEnabled;
    }

    public function getPluginPrefix()
    {
        return $this->pluginPrefix;
    }

    public function getPluginHandlePrefix()
    {
        return $this->pluginHandlePrefix;
    }

    public function getPluginURL_Prefix()
    {
        return $this->pluginURL_Prefix;
    }

    public function getPluginCSS_Prefix()
    {
        return $this->pluginCSS_Prefix;
    }

    public function getGalleryFolderName()
    {
        return $this->galleryFolderName;
    }

    public function getGlobalGalleryPath()
    {
        return $this->globalGalleryPath;
    }

    public function getGlobalGalleryPathWithoutEndSlash()
    {
        return $this->globalGalleryPathWithoutEndSlash;
    }

    public function getGlobalGalleryURL()
    {
        return $this->globalGalleryURL;
    }

    public function getThemeUI_FolderName()
    {
        return $this->themeUI_FolderName;
    }

    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * @note - Differently to plugin full prefix, the blog prefix may be different for sites, as pages can be inserted in different _posts tables
     * @param int $paramBlogId
     * @return string
     */
    public function getBlogPrefix($paramBlogId = -1)
    {
        if($paramBlogId == -1)
        {
            // Skip blog id overriding
            return $this->blogPrefix;
        } else
        {
            return $this->internalWPDB->get_blog_prefix($paramBlogId);
        }
    }

    /**
     * @note - we never use blog_id param here, as the prefix for the site is always the same - despite even if it is multisite and plugin is network enabled
     * @return string
     */
    public function getWP_Prefix()
    {
        return $this->wpPrefix;
    }

    /**
     * @note - we never use blog_id param here, as the prefix for the site is always the same - despite even if it is multisite and plugin is network enabled
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getShortcode()
    {
        return $this->shortcode;
    }

    public function getTextDomain()
    {
        return $this->textDomain;
    }


    /* ------------------------------------------------------------------------------------------------------- */
    /* Path methods                                                                                            */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getWP_PluginsPath()
    {
        return $this->wpPluginsPath;
    }

    public function getPluginPathWithFilename()
    {
        return $this->pluginPathWithFilename;
    }

    public function getPluginPath()
    {
        return $this->pluginPath;
    }

    public function getPluginBasename()
    {
        return $this->pluginBasename;
    }

    public function getPluginFolderName()
    {
        return $this->pluginFolderName;
    }

    public function getLibrariesPath()
    {
        return $this->librariesPath;
    }

    public function getLocalLangPath()
    {
        return $this->localLangPath;
    }

    public function getGlobalLangPath()
    {
        return $this->globalLangPath;
    }

    public function getGlobalPluginLangPath()
    {
        return $this->globalPluginLangPath;
    }

    /**
     * localLangRelPath used for load_textdomain (without slash at the end), i.e. ExpandableFAQ/Languages/Common
     * @note - Do not use DIRECTORY_SEPARATOR for this file, as it used for WP-TEXT-DOMAIN definition and always should be the same
     * @return string
     */
    public function getLocalLangRelPath()
    {
        return $this->localLangRelPath;
    }


    /* ------------------------------------------------------------------------------------------------------- */
    /* URL methods                                                                                             */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getPluginURL()
    {
        return $this->pluginURL;
    }
}