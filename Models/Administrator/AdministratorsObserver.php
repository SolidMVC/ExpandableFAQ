<?php
/**
 * Partners Observer

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Administrator;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\ObserverInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class AdministratorsObserver implements ObserverInterface
{
    private $conf           = NULL;
    private $lang 		    = NULL;
    private $settings 	    = array();
    private $debugMode 	    = 0;

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
		// Set class settings
		$this->conf = $paramConf;
		// Already sanitized before in it's constructor. Too much sanitization will kill the system speed
		$this->lang = $paramLang;
        $this->settings = $paramSettings;
	}

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    /**
     * @param int $paramSelectedWPUserId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @return string
     */
    public function getDropdownOptions($paramSelectedWPUserId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "")
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $validDefaultLabel = esc_html(sanitize_text_field($paramDefaultLabel));

        $retHTML = '';
        if($paramSelectedWPUserId == $validDefaultValue)
        {
            $retHTML .= '<option value="'.$validDefaultValue.'" selected="selected">'.$validDefaultLabel.'</option>';
        } else
        {
            $retHTML .= '<option value="'.$validDefaultValue.'">'.$validDefaultLabel.'</option>';
        }

        $roleName = (new AdministratorRole($this->conf, $this->lang))->getRoleName();
        $arrOjbWPUsers = get_users(array('role' => $roleName));
        // Array of WP_User objects.
        foreach($arrOjbWPUsers AS $objWPUser)
        {
            $validWPUserId = StaticValidator::getValidPositiveInteger($objWPUser->ID, 0);
            $printWPUserDisplayName = esc_html($objWPUser->display_name);
            if($validWPUserId == $paramSelectedWPUserId)
            {
                $retHTML .= '<option value="'.$validWPUserId.'" selected="selected">'.$printWPUserDisplayName.'</option>';
            } else
            {
                $retHTML .= '<option value="'.$validWPUserId.'">'.$printWPUserDisplayName.'</option>';
            }
        }
        return $retHTML;
    }
}