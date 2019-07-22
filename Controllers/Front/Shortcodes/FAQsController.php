<?php
/**
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Front\Shortcodes;
use ExpandableFAQ\Controllers\Front\AbstractController;
use ExpandableFAQ\Models\FAQ\FAQ;
use ExpandableFAQ\Models\FAQ\FAQsObserver;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class FAQsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "List", $paramStyle = "")
    {
        // Create mandatory instances
        $objFAQsObserver = new FAQsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        $faqIds = $objFAQsObserver->getAllIds();
        $faqs = array();
        foreach($faqIds AS $faqId)
        {
            $objFAQ = new FAQ($this->conf, $this->lang, $this->dbSets->getAll(), $faqId);
            $faqDetails = $objFAQ->getDetails();
            $faqDetails['expanded'] = $faqId == $this->expandedFAQ ? TRUE : FALSE;
            $faqs[] = $faqDetails;
        }

        // Get the template
        $this->view->faqs = $faqs;
        $retContent = $this->getTemplate('', 'FAQs', $paramLayout, $paramStyle);

        return $retContent;
    }
}