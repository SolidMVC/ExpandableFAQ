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
use ExpandableFAQ\Controllers\Front\Shortcodes\AddEditRespondentController;
use ExpandableFAQ\Controllers\Front\Shortcodes\AddEditReviewController;
use ExpandableFAQ\Controllers\Front\Shortcodes\BenefitsController;
use ExpandableFAQ\Controllers\Front\Shortcodes\OrganizationContactController;
use ExpandableFAQ\Controllers\Front\Shortcodes\OrganizationController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ContactController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ChangeOrderController;
use ExpandableFAQ\Controllers\Front\Shortcodes\DealsController;
use ExpandableFAQ\Controllers\Front\Shortcodes\FAQsController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ItemsAvailabilityController;
use ExpandableFAQ\Controllers\Front\Shortcodes\DecisionMakersController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ManufacturersController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ReviewsController;
use ExpandableFAQ\Controllers\Front\Shortcodes\DecisionMakerController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ExtrasAvailabilityController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ExtrasPricesController;
use ExpandableFAQ\Controllers\Front\Shortcodes\EntriesAvailabilityController;
use ExpandableFAQ\Controllers\Front\Shortcodes\EntriesController;
use ExpandableFAQ\Controllers\Front\Shortcodes\EntriesPriceController;
use ExpandableFAQ\Controllers\Front\Shortcodes\SearchController;
use ExpandableFAQ\Controllers\Front\Shortcodes\EntryController;
use ExpandableFAQ\Controllers\Front\Shortcodes\ServicesController;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

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

        // Layout processor
        $layoutParts = explode("-", $paramLayout);
        $sanitizedLayout = '';
        foreach($layoutParts AS $layoutPart)
        {
            $sanitizedLayout .= ucfirst(sanitize_key($layoutPart));
        }

        // Prepare the limits array - pop unnecessary array elements
        $paramArrLimits = $paramAttrArray;
        if(isset($paramArrLimits['display'])) { unset($paramArrLimits['display']); }
        if(isset($paramArrLimits['layout'])) { unset($paramArrLimits['layout']); }

        // Render the page HTML to output buffer cache
        switch($sanitizedDisplay)
        {
            case "faqs":
                // Create instance and render F.A.Q.'s list
                $objFAQsController = new FAQsController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objFAQsController->getContent($sanitizedLayout);
                break;

            default:
                // Do nothing
                $retContent = '';
        }

        // Return page content to shortcode
        return $retContent;
    }
}