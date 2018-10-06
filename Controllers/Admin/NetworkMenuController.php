<?php
/**
 * Initializer class to load admin section
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin;
use ExpandableFAQ\Controllers\Admin\Status\NetworkController;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class NetworkMenuController
{
    private $conf 	                = NULL;
    private $lang 		            = NULL;
    private $errorMessages                 = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitation will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * @param int $paramMenuPosition
     */
    public function addMenu($paramMenuPosition = 97)
    {
        $validMenuPosition = intval($paramMenuPosition);
        $iconURL = $this->conf->getRouting()->getAdminImagesURL('Plugin.png');
        $URLPrefix = $this->conf->getPluginURL_Prefix();

        // For admins only - update_plugins are official WordPress role for updates
        add_menu_page(
            $this->lang->getPrint('LANG_MENU_ACCORDION_FAQ_TEXT'), $this->lang->getPrint('LANG_MENU_ACCORDION_FAQ_TEXT'),
            "update_plugins", "{$URLPrefix}network-menu", array(&$this, "printNetworkStatus"), $iconURL, $validMenuPosition
        );
            add_submenu_page(
                "{$URLPrefix}network-menu", $this->lang->getPrint('LANG_STATUS_NETWORK_TEXT'), $this->lang->getPrint('LANG_STATUS_NETWORK_TEXT'),
                "update_plugins", "{$URLPrefix}network-status", array(&$this, "printNetworkStatus")
            );
        remove_submenu_page("{$URLPrefix}network-menu", "{$URLPrefix}network-menu");
    }

    // Network Status
    public function printNetworkStatus()
    {
        try
        {
            $objStatusController = new NetworkController($this->conf, $this->lang);
            $objStatusController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

	/******************************************************************************************/
	/* Other methods                                                                          */
	/******************************************************************************************/
    /**
     * @param $paramName
     * @param $paramErrorMessage
     */
    private function processError($paramName, $paramErrorMessage)
    {
        if(StaticValidator::inWPDebug())
        {
            $sanitizedName = sanitize_text_field($paramName);
            $sanitizedErrorMessage = sanitize_text_field($paramErrorMessage);
            // Load errors only in local or global debug mode
            $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_ERROR_IN_METHOD_TEXT'), $sanitizedName, $sanitizedErrorMessage);

            // 'add_action('admin_notices', ...)' doesn't work here (maybe due to fact, that 'admin_notices' has to be registered not later than X point in code)

            // Works
            $sanitizedErrorMessage = '<div id="message" class="error"><p>'.$sanitizedErrorMessage.'</p></div>';
            _doing_it_wrong($sanitizedName, $sanitizedErrorMessage, $this->conf->getPluginVersion());
        }
    }
}