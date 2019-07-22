<?php
/**
 * Manager element (account)

 * @note - It does not have settings param in constructor on purpose!
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Administrator;
use ExpandableFAQ\Models\AbstractStack;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Models\WPUserInterface;

final class Administrator extends AbstractStack implements StackInterface, WPUserInterface
{
    private $conf           = NULL;
    private $lang 		    = NULL;
    private $debugMode 	    = 0;
    private $roleName       = '';
    private $wpUserId       = 0;

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramWPUserId
     */
	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramWPUserId)
	{
		// Set class settings
		$this->conf = $paramConf;
		// Already sanitized before in it's constructor. Too much sanitization will kill the system speed
		$this->lang = $paramLang;

		$this->roleName = 'administrator'; // No prefix for role here, as it is official WordPress administrator role name
		$this->wpUserId = StaticValidator::getValidPositiveInteger($paramWPUserId, 0);
	}

    /**
     * @return bool
     */
    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->wpUserId;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        $objWPUser = get_user_by( 'ID', $this->wpUserId);
        $displayName = '';
        if($objWPUser !== FALSE)
        {
            $displayName = $objWPUser->display_name;
        }

        return $displayName;
    }
}