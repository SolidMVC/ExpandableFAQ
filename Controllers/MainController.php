<?php
/**
 * Main controller
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @description This file is the main entry point to the plugin that will handle all requests from WordPress
 * and add actions, filters, etc. as necessary. So we simply declare the class and add a constructor.
 * @note 1: In this class we use full qualifiers (without 'use', except for Configuration, which is already included).
 *          We do this, to ensure, that nobody will try to use any of these classes before the autoloader is called.
 * @note 2: This class must not depend on any static model
 * @note 3: All Controllers and Models should have full path in the class
 * @note 4: Fatal errors on this file cannot be translated
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers;
// This class file was statically included, so that's why we can use here the keyword 'use'.
// The rest class files are loaded dynamically, and SHOULD NOT be listed bellow with keyword 'use' for code quality reasons.
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Load\AutoLoad;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class MainController
{
    // Because loading of language text is not allowed in the very early time, we use constants to simulate language text behavior, just the text is English
    const LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT = 'Error in __clone() method: Cloning instances of the class in the Rental System is forbidden.';
    const LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT = 'Error in __wakeup() method: Unserializing instances of the class in the Rental System is forbidden.';
    const LANG_ERROR_SESSIONS_ARE_DISABLED_IN_SERVER_TEXT = 'Warning: Sessions are disabled in your server configuration. Please enabled sessions. As a slower &amp; less secure workaround you can use virtual session via cookies, but that is not recommended.';
    const LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT = 'Sorry, %s requires PHP %s or higher. Your current PHP version is %s. Please upgrade your server PHP version.';
    const LANG_ERROR_PLEASE_UPGRADE_WP_TEXT = 'Sorry, %s requires WordPress %s or higher. Your current WordPress version is %s. Please upgrade your WordPress setup.';
    const LANG_ERROR_EXTENSION_NOT_EXIST_PLUGIN_CHILD_THEME_TEXT = 'Sorry, but %s extension does not exist neither in %s plugin directory, nor in %s child theme folder, nor in it&#39;s parent %s theme&#39;s folder.';
    const LANG_ERROR_EXTENSION_NOT_EXIST_PLUGIN_THEME_TEXT = 'Sorry, but %s extension does not exist neither in %s plugin directory, nor in %s theme folder.';
    const LANG_ERROR_UNKNOWN_NAME_TEXT = 'Unknown name';
    const LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT = 'Dependencies are not loaded';
    const LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT = '$confWithoutRouting is NULL';
    const LANG_ERROR_CONF_IS_NULL_TEXT = '$conf is NULL';
    const LANG_ERROR_LANG_IS_NULL_TEXT = '$lang is NULL';
    const LANG_ERROR_IN_METHOD_TEXT = 'Error in &#39;%s&#39; method: %s!';

    // Configuration object reference
    private $confWithoutRouting         = NULL;
    private $conf                       = NULL;
    private $lang                       = NULL;
    private $canProcess                 = FALSE; // Have to be in main controller, as we don't use that for abstract controller
    private static $dependenciesLoaded  = FALSE;
    // DEBUG NOTE: Make sure that WP_DEBUG is enabled and 'debug.log' file is creatable in plugin's folder

    /**
     * NOTE: Here we must NOT support passing by reference, as it comes from static object
     * @param ConfigurationInterface $paramConfWithoutRouting
     */
    public function __construct(ConfigurationInterface $paramConfWithoutRouting)
    {
        // This is very important to set it here
        $this->canProcess = TRUE;

        if(StaticValidator::wpDebugLog())
        {
            $this->logToFile(__CLASS__ ."::". __FUNCTION__ .": Constructor loaded\n");
        }

        // We assign it to variable to avoid passing by reference warning for non-variables
        $this->confWithoutRouting = $paramConfWithoutRouting;

        //
        // 1. Check plug-in requirements - if not passed, then exit
        //
        if(is_null($this->confWithoutRouting))
        {
            // $confWithoutRouting is not NULL
            add_action('admin_notices', array($this, 'displayConfWithoutRoutingIsNullNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logConfWithoutRoutingIsNullNotice(__CLASS__ ."::". __FUNCTION__);
            }
            $this->canProcess = FALSE;
        }
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentPHP_Version(), $this->confWithoutRouting->getRequiredPHP_Version(), '>=') === FALSE)
        {
            // PHP version does not meet plugin requirements
            add_action('admin_notices', array($this, 'displayPHP_VersionRequirementNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logPHP_VersionRequirementNotice(__CLASS__ ."::". __FUNCTION__);
            }
            $this->canProcess = FALSE;
        }
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentWP_Version(), $this->confWithoutRouting->getRequiredWP_Version(), '>=') === FALSE)
        {
            // WordPress version does not meet plugin requirements
            add_action('admin_notices', array($this, 'displayWPVersionRequirementNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logWPVersionRequirementNotice(__CLASS__ ."::". __FUNCTION__);
            }
            $this->canProcess = FALSE;
        }

        //
        // 2. Load dependencies. Autoloader. This must be in constructor to know the file paths.
        // Note: Singleton pattern used.
        //
        if($this->canProcess && static::$dependenciesLoaded === FALSE)
        {
            // Load dependencies
            $objAutoload = new AutoLoad($this->confWithoutRouting);
            spl_autoload_register(array($objAutoload, 'includeClassFile'));
            static::$dependenciesLoaded = TRUE;
        } else
        {
            // Dependencies are not loaded!
            add_action('admin_notices', array($this, 'displayDependenciesAreNotLoadedNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logDependenciesAreNotLoadedNotice(__CLASS__ ."::". __FUNCTION__);
            }
        }

        //
        // 3. Activation Hooks
        //
        // ATTENTION: This is *only* done during plugin activation hook!
        // NOTE #1: Initialize the two lines bellow for every extension!
        // NOTE #2: Only check here for routing definition, nothing else, or install will crash
        if(!is_null($this->confWithoutRouting))
        {
            register_activation_hook($this->confWithoutRouting->getPluginPathWithFilename(), array($this, 'networkOrSingleActivate'));
            register_deactivation_hook($this->confWithoutRouting->getPluginPathWithFilename(), array($this, 'networkDeactivate'));
            add_filter('network_admin_plugin_action_links_'.$this->confWithoutRouting->getPluginBasename(), array($this, 'modifyNetworkActionLinks'));
            add_filter('plugin_action_links_'.$this->confWithoutRouting->getPluginBasename(), array($this, 'modifyActionLinks'));
            // Add links bellow plugin description
            add_filter('plugin_row_meta', array($this, 'modifyInfoLinks'), 10, 2);
        }
    }

    /**
     * Note: Do not add try {} catch {} for this block, as this method includes WordPress hooks.
     *   For those hooks handling we have individual methods in this class bellow, where the try {} catch {} is used.
     */
    public function run()
    {
        if($this->canProcess)
        {
            //
            // 4. Admin / Network Admin page hooks
            //
            // Check whether the current request is for an administrative interface page, and check if we not doing admin ajax
            // More at: https://codex.wordpress.org/AJAX_in_Plugins
            if(is_admin() || is_network_admin())
            {
                // Add network admin menu items
                add_action('network_admin_menu', array($this, 'loadNetworkAdmin'));
                // Remove admin footer text
                add_filter('admin_footer_text', array($this, 'removeAdminFooterText'));
                // Remove network admin footer text
                add_filter('network_admin_menu', array($this, 'removeAdminFooterVersion'));
            }
            if(is_admin())
            {
                // Note! Initialize the two lines bellow for every extension!
                // ATTENTION: This is *only* done during plugin activation hook!
                // register_activation_hook($this->coreConf->getPluginPathWithFilename(), array($this, 'validate'));
                // register_deactivation_hook($this->coreConf->getPluginPathWithFilename(), array($this, 'deactivate'));

                // Add network / regular admin menu items
                add_action('admin_menu', array($this, 'loadAdmin'));
                // Remove admin footer text
                add_filter('admin_footer_text', array($this, 'removeAdminFooterText'));
                // Remove admin footer text
                add_filter('admin_menu', array($this, 'removeAdminFooterVersion'));
            }

            //
            // 5. New blog creation hook
            // 'wpmu_new_blog' is an action triggered whenever a new blog is created within a multisite network
            //
            add_action( 'wpmu_new_blog', array($this, 'newBlogAdded'), 10, 6);

            //
            // 6. Blog deletion hook (fired every time when new blog is deleted in multisite)
            // More: https://developer.wordpress.org/reference/hooks/delete_blog/
            // More: http://wordpress.stackexchange.com/questions/82961/perform-action-on-wpmu-blog-deletion
            // More: https://codex.wordpress.org/Plugin_API/Action_Reference/delete_blog
            // More: http://wordpress.stackexchange.com/questions/130462/is-there-a-hook-or-a-function-for-multisite-blog-deactivate-or-delete
            // Should be replaced by 'wpmu_delete_blog' (since WP 4.8 https://wpseek.com/function/wpmu_delete_blog/ )
            // https://developer.wordpress.org/reference/hooks/delete_blog/
            // Fires before a site is deleted.
            //
            add_action('delete_blog', array($this, 'newBlogDeleted'), 10, 6);

            //
            // 7. Run on init - internationalization, custom post type and visitor session registration
            //
            add_action('init', array($this, 'runOnInit'), 0);
        }
    }

    /**
     * Modify network links
     * @param array $paramExistingNetworkActionLinks
     * @return array
     */
    public function modifyNetworkActionLinks(array $paramExistingNetworkActionLinks)
    {
        $modifiedNetworkActionLinks = array();
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // NOTE: These IF/ELSE statements should be here to clearly separate the process from model
                $objStatus = new \ExpandableFAQ\Models\Status\NetworkStatus($conf, $lang);
                $additionalLinks = $objStatus->getAdditionalActionLinks();

                // Appends additional links to the existing network action links
                $modifiedNetworkActionLinks = array_merge($paramExistingNetworkActionLinks, $additionalLinks);
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $modifiedNetworkActionLinks;
    }

    /**
     * Modify locally-enabled plugin links
     * @param array $paramExistingActionLinks
     * @return array
     */
    public function modifyActionLinks(array $paramExistingActionLinks)
    {
        $modifiedActionLinks = array();

        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instance
                $objStatus = new \ExpandableFAQ\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());

                $additionalLinks = $objStatus->getActionLinks();

                // Appends additional links to the existing action links
                $modifiedActionLinks = array_merge($paramExistingActionLinks, $additionalLinks);
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $modifiedActionLinks;
    }

    /**
     * Modify info links next to plugin description
     * @param array $paramExistingInfoLinks
     * @param string $paramPluginBasename
     * @return array
     */
    public function modifyInfoLinks(array $paramExistingInfoLinks, $paramPluginBasename)
    {
        $modifiedInfoLinks = array();

        if($this->canProcess && $paramPluginBasename == $this->confWithoutRouting->getPluginBasename())
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Show additional info links only if the plugin is locally enabled
                if(is_network_admin())
                {
                    // Create mandatory instance
                    $objStatus = new \ExpandableFAQ\Models\Status\NetworkStatus($conf, $lang);

                    // Get additional links to be displayed next to network plugin description
                    $additionalLinks = $objStatus->getInfoLinks();
                } else
                {
                    // Create mandatory instance
                    $objStatus = new \ExpandableFAQ\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());

                    // Get additional links to be displayed next to local plugin description
                    $additionalLinks = $objStatus->getInfoLinks();
                }

                // Appends additional links to the existing info links
                $modifiedInfoLinks = array_merge($paramExistingInfoLinks, $additionalLinks);
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $modifiedInfoLinks;
    }

    /**
     * Activate (enable+install or enable only) plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     * @param bool $networkWideActivation - if the activation is 'network enabled' or 'locally enabled' (even if multisite is enabled)
     */
    public function networkOrSingleActivate($networkWideActivation)
    {
        // NOTE: Temporary workaround while #36406 WordPress bug will be fixed
        //       Read more at https://core.trac.wordpress.org/ticket/36406
        $requestComingFromNetworkAdmin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "/network") !== FALSE ? TRUE : FALSE;
        if(is_multisite() && ($networkWideActivation || $requestComingFromNetworkAdmin))
        {
            // A workaround until WP will get fixed
            // SHOULD be 'networkActivate' but WordPress does not yet support that feature,
            // so this means as long as the 'MULTISITE' constant is defined in wp-config, we use that method

            // LOCAL DEBUG
            // trigger_error('Network wide activation (referer: '.$_SERVER['HTTP_REFERER'].').', E_USER_ERROR);

            $this->multisiteActivate();
        } else
        {
            // A workaround until WP will get fixed

            // LOCAL DEBUG
            // trigger_error('Regular activation (non-multisite or multisite\'s local activation, referer: '.$_SERVER['HTTP_REFERER'].').', E_USER_ERROR);

            $this->activate();
        }
    }

    /**
     * Process the plugin activation requirements
     * @throws \Exception
     */
    private function processActivationRequirements()
    {
        // Check PHP version
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentPHP_Version(), $this->confWithoutRouting->getRequiredPHP_Version(), '>=') === FALSE)
        {
            // WordPress version does not meet plugin requirements
            $errorMessage = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                $this->confWithoutRouting->getPluginName(), $this->confWithoutRouting->getRequiredPHP_Version(), $this->confWithoutRouting->getCurrentPHP_Version()
            );
            throw new \Exception($errorMessage);
        }

        // Check WordPress version
        // Note - we don't need to check here for function 'get_sites' or class 'WP_Site_Query' as it is related to WP version check, and them were introduced in Wp 4.6+
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentWP_Version(), $this->confWithoutRouting->getRequiredWP_Version(), '>=') === FALSE)
        {
            // WordPress version does not meet plugin requirements
            $errorMessage = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                $this->confWithoutRouting->getPluginName(), $this->confWithoutRouting->getRequiredWP_Version(), $this->confWithoutRouting->getCurrentWP_Version()
            );
            throw new \Exception($errorMessage);
        }
    }

    /**
     * Activate (enable+install or enable only) plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     */
    public function multisiteActivate()
    {
        try
        {
            $this->processActivationRequirements();

            // DEBUG: FOR INSTALLATION EXCEPTION TESTING PURPOSES, LETS RAISE A CRITICAL ERROR.
            // throw new \Exception('Multisite activation started. And now we are killing it');

            // Assign routing to conf, only if it is not yet assigned
            $conf = $this->conf();

            // Note: Don't move $lang to parameter below, or WordPress will generate an installation warning
            $lang = $this->i18n();

            if(is_null($conf))
            {
                throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
            }
            if(is_null($lang))
            {
                throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
            }

            // For network-install we only create tables, the rest is done by populating the data individually for each blog
            $objInstaller = new \ExpandableFAQ\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
            $objInstaller->setTables();
        } catch (\Exception $e)
        {
            if(StaticValidator::inWP_Debug())
            {
                // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                $error = sprintf(static::LANG_ERROR_IN_METHOD_TEXT, __FUNCTION__, $e->getMessage());
                trigger_error($error, E_USER_ERROR);
            }
        }
    }

    public function activate()
    {
        try
        {
            $this->processActivationRequirements();

            // DEBUG: FOR INSTALLATION EXCEPTION TESTING PURPOSES, LETS RAISE A CRITICAL ERROR.
            // throw new \Exception('Single activation started. And now we are killing it');

            // Assign routing to conf, only if it is not yet assigned
            $conf = $this->conf();

            // Note: Don't move $lang to parameter below, or WordPress will generate an installation warning
            $lang = $this->i18n();

            if(is_null($conf))
            {
                throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
            }
            if(is_null($lang))
            {
                throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
            }

            // Install plugin for single site
            $objInstaller = new \ExpandableFAQ\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
            // Install
            $objInstaller->setTables();
            // INFO: This plugin do not use custom roles
            $objInstaller->setCustomCapabilities();
            // INFO: This plugin do not use custom post types
            $objInstaller->setContent();
            $objInstaller->replaceResettableContent();
            $objInstaller->registerAllForTranslation();
        } catch (\Exception $e)
        {
            if(StaticValidator::inWP_Debug())
            {
                // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                $error = sprintf(static::LANG_ERROR_IN_METHOD_TEXT, __FUNCTION__, $e->getMessage());
                trigger_error($error, E_USER_ERROR);
            }
        }
    }

    /**
     * Deactivate plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     */
    public function networkDeactivate()
    {
        if($this->canProcess && is_multisite() && function_exists('get_sites') && class_exists('WP_Site_Query'))
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }

                $sites = get_sites();
                foreach ($sites AS $site)
                {
                    $blogId = $site->blog_id;
                    switch_to_blog($blogId);
                    flush_rewrite_rules();
                }

                // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
                switch_to_blog($conf->getBlogId());
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_METHOD_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        } else if($this->canProcess && is_multisite() === FALSE)
        {
            // A workaround until WP will get fixed
            $this->deactivate();
        }
    }

    public function deactivate()
    {
        if($this->canProcess)
        {
            try
            {
                flush_rewrite_rules();
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_METHOD_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        }
    }

    /**
     * newBlogAdded is an action triggered whenever a new blog is created within a multisite network
     * @mote1 - https://codex.wordpress.org/Plugin_API/Action_Reference/wpmu_new_blog
     * @note2 - https://developer.wordpress.org/reference/hooks/wpmu_new_blog/
     * @param int $paramNewBlogId -  Blog ID
     * @param int $paramUserId -  User ID
     * @param string $paramDomain - Site domain
     * @param string $paramPath - Site domain
     * @param int $paramSiteId - Site ID. Only relevant on multi-network installs
     * @param array $paramMeta -  Meta data. Used to set initial site options.
     */
    public function newBlogAdded($paramNewBlogId, $paramUserId, $paramDomain, $paramPath, $paramSiteId, $paramMeta)
    {
        // Do nothing. Not used by this plugin. All data is added from each blog individually if needed
    }

    /**
     * @param int $paramBlogIdToDelete Blog ID to delete
     * @param bool $paramDropBlogTables True if blog's table should be dropped. Default is false.
     */
    public function blogDeleted($paramBlogIdToDelete, $paramDropBlogTables)
    {
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }

                if($conf->isNetworkEnabled())
                {
                    $oldBlogId = $conf->getInternalWPDB()->blogid;
                    switch_to_blog($paramBlogIdToDelete);

                    $lang = new \ExpandableFAQ\Models\Language\Language(
                        $conf->getTextDomain(), $conf->getGlobalLangPath(), $conf->getLocalLangPath(), get_locale(), FALSE
                    );

                    if(is_null($lang))
                    {
                        throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                    }

                    // Delete the plugin data for across the whole network
                    $objUninstaller = new \ExpandableFAQ\Controllers\Admin\InstallController($conf, $lang, $paramBlogIdToDelete);
                    $objUninstaller->deleteContent();
                    // INFO: This plugin does not have it's own plugin-specific roles
                    $objUninstaller->removeCustomCapabilities();

                    switch_to_blog($oldBlogId);
                }
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_METHOD_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        }
    }

    public function uninstall()
    {
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                if($conf->isNetworkEnabled())
                {
                    $objNetworkUninstaller = new \ExpandableFAQ\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
                    $sites = get_sites();
                    foreach ($sites AS $site)
                    {
                        $blogId = $site->blog_id;
                        switch_to_blog($blogId);

                        // Delete all content and uninstall for specific blog id
                        $objUninstaller = new \ExpandableFAQ\Controllers\Admin\InstallController($conf, $lang, $blogId);
                        $objUninstaller->deleteContent();
                        // INFO: This plugin does not have it's own plugin-specific roles
                        $objUninstaller->removeCustomCapabilities();
                    }
                    // Drop the tables
                    // NOTE: things like 'network roles' are not used by the plugin
                    $objNetworkUninstaller->dropTables();

                    // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
                    switch_to_blog($conf->getBlogId());
                } else
                {
                    // Delete all content and uninstall
                    $objUninstaller = new \ExpandableFAQ\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
                    $objUninstaller->deleteContent();
                    // INFO: This plugin does not have it's own plugin-specific roles
                    $objUninstaller->removeCustomCapabilities();
                    $objUninstaller->dropTables();
                }
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_METHOD_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        }
    }

    public function loadNetworkAdmin()
    {
        if($this->canProcess)
        {
            try
            {
                // Set session cookie before any headers will be sent. Start the session, because:
                // 1. Search uses session to save progress
                // 2. NS admin has ok/error messages saved in sessions
                // Note: Requires Php 5.4+
                if(session_status() !== PHP_SESSION_ACTIVE)
                {
                    session_start(); // Starts a new session or resumes an existing session
                }

                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instance
                $objAssetController = new \ExpandableFAQ\Controllers\Admin\AssetController($conf, $lang);
                $objMenuController = new \ExpandableFAQ\Controllers\Admin\NetworkMenuController($conf, $lang);

                // Enqueue global main JS
                add_action('admin_head', array($objAssetController, 'enqueueMandatoryPlainJS'));
                // First - register network admin scripts
                $objAssetController->registerScripts();
                // Second - register network admin styles
                $objAssetController->registerStyles();
                // Finally load the network admin menu and register all admin pages
                $objMenuController->addMenu(97);

                // Print a warning if sessions are not supported in the server, and suggest to use _COOKIES instead
                if(session_status() == PHP_SESSION_DISABLED)
                {
                    add_action('admin_notices', array($this, 'displaySessionsAreDisabledInServerNotice'));
                    if(StaticValidator::wpDebugLog())
                    {
                        $this->logSessionsAreDisabledInServerNotice(__CLASS__ ."::". __FUNCTION__);
                    }
                }
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    public function loadAdmin()
    {
        if($this->canProcess)
        {
            try
            {
                // Set session cookie before any headers will be sent. Start the session, because:
                // 1. Search uses session to save order progress
                // 2. NS admin has ok/error messages saved in sessions
                // Note: Requires Php 5.4+
                if(session_status() !== PHP_SESSION_ACTIVE)
                {
                    session_start(); // Starts a new session or resumes an existing session
                }

                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Set the theme and child theme to config

                // Create mandatory instance
                $objAssetController = new \ExpandableFAQ\Controllers\Admin\AssetController($conf, $lang);
                $objStatus = new \ExpandableFAQ\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());
                $objMenuController = new \ExpandableFAQ\Controllers\Admin\SingleMenuController($conf, $lang);

                // Enqueue global main JS
                add_action('admin_head', array($objAssetController, 'enqueueMandatoryPlainJS'));
                // First - register single-site admin scripts
                $objAssetController->registerScripts();
                // Second - register single-site admin styles
                $objAssetController->registerStyles();
                // Finally load the single-site admin menu and register all admin pages
                if($objStatus->isPluginDataUpToDateInDatabase())
                {
                    // Regular admin menu
                    $objMenuController->addRegularMenu(98);
                } else
                {
                    // Status menu
                    $objMenuController->addStatusMenu(98);
                }

                // Print a warning if sessions are not supported in the server, and suggest to use _COOKIE
                if(session_status() == PHP_SESSION_DISABLED)
                {
                    add_action('admin_notices', array($this, 'displaySessionsAreDisabledInServerNotice'));
                    if(StaticValidator::wpDebugLog())
                    {
                        $this->logSessionsAreDisabledInServerNotice(__CLASS__ ."::". __FUNCTION__);
                    }
                }
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    /**
     * Remove admin footer text - 'Thank you for creating with WordPress'
     * @note - this mostly helps our invoice print to look much more clean
     */
    public function removeAdminFooterText()
    {
        echo '';
    }

    /**
     * Remove admin footer WordPress version
     */
    public function removeAdminFooterVersion()
    {
        remove_filter( 'update_footer', 'core_update_footer' );
    }

    /**
     * Starts the plug-in main functionality
     */
    public function runOnInit()
    {
        if($this->canProcess)
        {
            try
            {
                // Set session cookie before any headers will be sent. Start the session, because:
                // 1. Search uses session to save order progress
                // 2. NS admin has ok/error messages saved in sessions
                // Note: Requires Php 5.4+
                if(session_status() !== PHP_SESSION_ACTIVE)
                {
                    session_start(); // Starts a new session or resumes an existing session
                }

                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instances
                $objStatus = new \ExpandableFAQ\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());
                $objAssetController = new \ExpandableFAQ\Controllers\Front\AssetController($conf, $lang);

                // Process only if the plugin is installed, is updated to the latest version and there is data for this extension on this blog
                if ($objStatus->checkPluginDataExistsOf($conf->getPluginSemver()))
                {
                    // Register post types
                    // Note: it hooks to 'init' so that the post type registration would not be necessarily executed.
                    // INFO: NOTHING for this plugin - it does not use any custom post types

                    // Enqueue global main JS
                    add_action( 'wp_head', array($objAssetController, 'enqueueMandatoryPlainJS'));
                    // Enqueue global mandatory scripts
                    $objAssetController->enqueueMandatoryScripts();
                    // Enqueue global mandatory styles
                    $objAssetController->enqueueMandatoryStyles();

                    // NOTE: We should register scripts here, if we want ensure that they will be accessible from other plugins
                    // Register front-end scripts
                    $objAssetController->registerScripts();
                    // Register front-end styles
                    $objAssetController->registerStyles();

                    // Add Shortcode hook
                    // @note - Unlike a Theme, a Plugin is run at a very early stage of the loading process thus requiring us
                    //         to postpone the adding of our shortcode until WordPress has been initialized.
                    //         So it is recommended to add it inside a hook for init action.
                    // @see -  https://developer.wordpress.org/plugins/shortcodes/basic-shortcodes/#in-a-plugin
                    add_shortcode($conf->getShortcode(), array($this, 'parseShortcode'));
                }
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    /**
     * Parses NS shortcode and returns the content.
     * @note - we do not use a 2nd parameter $content = null here, as it is not enclosing shortcode
     * @param array $attributes
     * @return string
     */
    public function parseShortcode($attributes = array())
    {
        $retContent = '';
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instances
                $objStatus = new \ExpandableFAQ\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());
                $objShortcodeController = new \ExpandableFAQ\Controllers\Front\ShortcodeController($conf, $lang);

                // Process only if the plugin is installed and there is data for this blog
                if ($objStatus->checkPluginDataExistsOf($conf->getPluginSemver()))
                {
                    // Finally - parse the shortcode
                    $retContent = $objShortcodeController->parse($attributes);
                }
                $this->throwExceptionOnFailure($objStatus->getErrorMessages(), $objStatus->getDebugMessages());
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $retContent;
    }

    /**
     * Configuration with Routing.
     * Add routing to configuration, only if it is not yet added
     *
     * @access private
     * @return null|\ExpandableFAQ\Models\Configuration\ConfigurationInterface
     * @throws \Exception
     */
    private function conf()
    {
        if(static::$dependenciesLoaded === FALSE)
        {
            throw new \Exception(static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT);
        }

        if(is_null($this->confWithoutRouting))
        {
            throw new \Exception(static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT);
        }

        // Singleton pattern - load the extension configuration, only if it is not yet loaded
        if(is_null($this->conf) && !is_null($this->confWithoutRouting) && static::$dependenciesLoaded === TRUE)
        {
            $pluginPath = $this->confWithoutRouting->getPluginPath();
            $pluginURL = $this->confWithoutRouting->getPluginURL();
            $themeUI_FolderName = $this->confWithoutRouting->getThemeUI_FolderName();
            $routing = new \ExpandableFAQ\Models\Routing\UI_Routing($pluginPath, $pluginURL, $themeUI_FolderName);
            // This is fine to clone here without cloning it's sub-objects like wpdb, because we only want to to differ by routing object
            $conf = clone $this->confWithoutRouting;
            $conf->setRouting($routing);
            $this->conf = $conf;
        }

        return $this->conf;
    }

    /**
     * Internationalization.
     * Load the language file, only if it is not yet loaded
     *
     * @access private
     * @return null|\ExpandableFAQ\Models\Language\Language
     * @throws \Exception
     */
    private function i18n()
    {
        if(static::$dependenciesLoaded === FALSE)
        {
            throw new \Exception(static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT);
        }

        // NOTE: For i18n() we do not need to assign $conf at all, as it is not used here
        if(is_null($this->confWithoutRouting))
        {
            throw new \Exception(static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT);
        }

        // Singleton pattern - load the language file, only if it is not yet loaded
        if(is_null($this->lang) && static::$dependenciesLoaded === TRUE)
        {
            // Traditional WordPress plugin locale filter
            // Note 1: We don't want to include the rows bellow to language model class, as they are a part of controller
            // Note 2: Keep in mind that, if the translation do not exist, plugin will load a default english translation file
            $locale = apply_filters('plugin_locale', get_locale(), $this->confWithoutRouting->getTextDomain());

            // Load textdomain
            // Loads MO file into the list of domains.
            // Note 1: If the domain already exists, the inclusion will fail. If the MO file is not readable, the inclusion will fail.
            // Note 2: On success, the MO file will be placed in the $l10n global by $domain and will be an gettext_reader object.

            // See 1: http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
            // See 2: https://ulrich.pogson.ch/load-theme-plugin-translations
            // wp-content/languages/<PLUGIN_FOLDER_NAME>/lt_LT.mo
            load_textdomain($this->confWithoutRouting->getTextDomain(), $this->confWithoutRouting->getGlobalLangPath().$locale.'.mo');
            // wp-content/plugins/ExpandableFAQ/Languages/<EXT_FOLDER_NAME>/lt_LT.mo
            load_plugin_textdomain($this->confWithoutRouting->getTextDomain(), FALSE, $this->confWithoutRouting->getLocalLangRelPath());

            $this->lang = new \ExpandableFAQ\Models\Language\Language(
                $this->confWithoutRouting->getTextDomain(), $this->confWithoutRouting->getGlobalLangPath(), $this->confWithoutRouting->getLocalLangPath(), $locale, FALSE
            );
        }

        return $this->lang;
    }

    /*******************************************************************************/
    /**************************** KERNEL-LEVEL METHODS *****************************/
    /*******************************************************************************/

    /**
     * Throw error on object clone.
     *
     * Cloning instances of the class is forbidden.
     *
     * @since 1.0
     * @return void
     */
    public function __clone()
    {
        add_action('admin_notices', array($this, 'displayCloningIsForbiddenNotice'));
        if(StaticValidator::wpDebugLog())
        {
            $this->logCloningIsForbiddenNotice(__CLASS__ ."::". __FUNCTION__);
        }
    }

    /**
     * Disable unserializing of the class
     *
     * Unserializing instances of the class is forbidden.
     *
     * @since 1.0
     * @return void
     */
    public function __wakeup()
    {
        add_action('admin_notices', array($this, 'displayUnserializingIsForbiddenNotice'));
        if(StaticValidator::wpDebugLog())
        {
            $this->logUnserializingIsForbiddenNotice(__CLASS__ ."::". __FUNCTION__);
        }
    }


    /**
     * Display dependencies are not loaded notice
     * NOTE: This is important to have this notice,
     *       as this would get us to long troubleshooting on error otherwise
     *
     * @access static
     */
    public function displayDependenciesAreNotLoadedNotice()
    {
        echo '<div id="message" class="error"><p><strong>';
        echo static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT;
        echo '</strong></p></div>';
    }

    /**
     * Log dependencies are not loaded notice
     * NOTE: This is important to have this notice,
     *       as this would get us to long troubleshooting on error otherwise
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logDependenciesAreNotLoadedNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display $confWithoutRouting is NULL notice
     *
     * @access static
     */
    public function displayConfWithoutRoutingIsNullNotice()
    {
        echo '<div id="message" class="error"><p><strong>';
        echo static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT;
        echo '</strong></p></div>';
    }

    /**
     * Log $confWithoutRouting is NULL notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logConfWithoutRoutingIsNullNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display PHP version requirement notice
     *
     * @access static
     */
    public function displayPHP_VersionRequirementNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                $this->confWithoutRouting->getPluginName(),
                $this->confWithoutRouting->getRequiredPHP_Version(),
                $this->confWithoutRouting->getCurrentPHP_Version()
            );
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is NULL
            echo '<div id="message" class="error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            echo '</strong></p></div>';
        }
    }

    /**
     * Log PHP version requirement notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logPHP_VersionRequirementNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        if(!is_null($this->confWithoutRouting))
        {
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                $this->confWithoutRouting->getPluginName(),
                $this->confWithoutRouting->getRequiredPHP_Version(),
                $this->confWithoutRouting->getCurrentPHP_Version()
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        } else
        {
            // $confWithoutRouting is NULL
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        }
    }

    /**
     * Display WordPress version requirement notice
     *
     * @access static
     */
    public function displayWPVersionRequirementNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                $this->confWithoutRouting->getPluginName(),
                $this->confWithoutRouting->getRequiredWP_Version(),
                $this->confWithoutRouting->getCurrentWP_Version()
            );
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is NULL
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            echo '</strong></p></div>';
        }
    }

    /**
     * Log WordPress version requirement notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logWPVersionRequirementNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        if(!is_null($this->confWithoutRouting))
        {
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                $this->confWithoutRouting->getPluginName(),
                $this->confWithoutRouting->getRequiredWP_Version(),
                $this->confWithoutRouting->getCurrentWP_Version()
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        } else
        {
            // $confWithoutRouting is NULL
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        }
    }

    /**
     * Display cloning is forbidden notice
     *
     * @access static
     */
    public function displayCloningIsForbiddenNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo static::LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is NULL
            echo '<div id="message" class="error"><p><strong>';
            echo static::LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        }
    }

    /**
     * Log cloning is forbidden notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logCloningIsForbiddenNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display unserializing is forbidden notice
     *
     * @access static
     */
    public function displayUnserializingIsForbiddenNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is NULL
            echo '<div id="message" class="error"><p><strong>';
            echo static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        }
    }

    /**
     * Log unserializing is forbidden notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logUnserializingIsForbiddenNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display sessions are disabled notice
     *
     * @access static
     */
    public function displaySessionsAreDisabledInServerNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo static::LANG_ERROR_SESSIONS_ARE_DISABLED_IN_SERVER_TEXT;
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is NULL
            echo '<div id="message" class="error"><p><strong>';
            echo static::LANG_ERROR_SESSIONS_ARE_DISABLED_IN_SERVER_TEXT;
            echo '</strong></p></div>';
        }
    }

    /**
     * Log sessions are disabled notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logSessionsAreDisabledInServerNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * @param array $paramErrorMessages
     * @param array $paramDebugMessages
     * @throws \Exception
     */
    private function throwExceptionOnFailure(array $paramErrorMessages, array $paramDebugMessages)
    {
        $errorMessagesToAdd = array();
        $debugMessagesToAdd = array();
        foreach($paramErrorMessages AS $paramErrorMessage)
        {
            $errorMessagesToAdd[] = sanitize_text_field($paramErrorMessage);
        }
        foreach($paramDebugMessages AS $paramDebugMessage)
        {
            // HTML is allowed here
            $debugMessagesToAdd[] = wp_kses_post($paramDebugMessage);
        }

        if(sizeof($errorMessagesToAdd) > 0)
        {
            $throwMessage = implode('<br />', $errorMessagesToAdd);
            if(StaticValidator::inWP_Debug() && sizeof($debugMessagesToAdd) > 0)
            {
                $throwMessage .= '<br />'.implode('<br />', $debugMessagesToAdd);
            }

            throw new \Exception($throwMessage);
        }
    }

    private function processError($paramMethodName, $paramErrorMessage)
    {
        if(StaticValidator::inWP_Debug())
        {
            // Load errors only in local or global debug mode
            $validMethodName = esc_html($paramMethodName);
            $validErrorMessage = esc_html($paramErrorMessage);

            // NOTE: add_action('admin_notices', ...); doesn't always work - maybe due to fact, that 'admin_notices'
            //       has to be registered not later than X point in code. So we use '_doing_it_wrong' instead
            // Works
            if(!is_null($this->confWithoutRouting))
            {
                $validErrorMessage = '<div class="'.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><div id="message" class="error"><p>'.$validErrorMessage.'</p></div></div>';
                _doing_it_wrong($validMethodName, $validErrorMessage, $this->confWithoutRouting->getPluginSemver());
            } else
            {
                // $confWithoutRouting is NULL
                $validErrorMessage = '<div id="message" class="error"><p>'.$validErrorMessage.'</p></div>';
                _doing_it_wrong($validMethodName, $validErrorMessage, 0.0);
            }
        }
    }

    private function logToFile($trustedOutput)
    {
        // NOTE #1: We explicitly use here 'dirname' function and '__DIR__' constant to reduce failure possibilities
        // NOTE #2: We do not perform 'is_writable' check as that file may not yet exist & we do want to
        //          reduce failure possibilities
        file_put_contents(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'debug.log',
            $trustedOutput, FILE_APPEND
        );
    }
}