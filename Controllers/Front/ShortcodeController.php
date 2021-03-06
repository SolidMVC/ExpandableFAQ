<?php
/**
 * Initializer class to parse shortcodes
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Front;
use ExpandableFAQ\Controllers\Front\Shortcodes\FAQsController;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class ShortcodeController
{
    private $conf 	                            = NULL;
    private $lang 		                        = NULL;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * @param array $paramAttrArray
     * @return string
     * @throws \Exception
     */
    public function parse(array $paramAttrArray)
    {
        // Get special shortcode parameter values
        $sanitizedDisplay = isset($paramAttrArray['display']) ? sanitize_key($paramAttrArray['display']) : "";
        $paramLayout = isset($paramAttrArray['layout']) ? $paramAttrArray['layout'] : "";
        $paramStyle = isset($paramAttrArray['style']) ? $paramAttrArray['style'] : "";

        // Layout processor - sanitize early
        $layoutParts = explode("-", $paramLayout);
        $sanitizedLayout = '';
        foreach($layoutParts AS $layoutPart)
        {
            $sanitizedLayout .= ucfirst(sanitize_key($layoutPart));
        }

        // Validate style early
        $validStyle = '';
        if($paramStyle != "")
        {
            $validStyle = StaticValidator::getValidPositiveInteger($paramStyle, 0);
        }

        // Prepare the limits array - pop unnecessary array elements
        $paramArrLimits = $paramAttrArray;
        if(isset($paramArrLimits['display'])) { unset($paramArrLimits['display']); }
        if(isset($paramArrLimits['layout'])) { unset($paramArrLimits['layout']); }
        if(isset($paramArrLimits['style'])) { unset($paramArrLimits['style']); }

        // Render the page HTML to output buffer cache
        switch($sanitizedDisplay)
        {
            case "faqs":
                // Create instance and render F.A.Q.'s list
                $objFAQsController = new FAQsController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objFAQsController->getContent($sanitizedLayout, $validStyle);
                break;

            default:
                // Do nothing
                $retContent = '';
        }

        // Return page content to shortcode
        return $retContent;
    }
}