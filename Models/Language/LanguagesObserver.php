<?php
/**
 * Languages Observer (no setup for single location)

 * @note - this class is a root observer (with $settings) on purpose - for registration
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Language;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\FAQ\FAQ;
use ExpandableFAQ\Models\FAQ\FAQsObserver;
use ExpandableFAQ\Models\PrimitiveObserverInterface;
use ExpandableFAQ\Models\Settings\Setting;
use ExpandableFAQ\Models\Settings\SettingsObserver;

final class LanguagesObserver implements PrimitiveObserverInterface
{
    private $conf 	                = NULL;
    private $lang 		            = NULL;
    private $debugMode 	            = 0;

    /**
     * DecisionMakersObserver constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    /**
     * @note - we use array() here instead of all settings, just because we know that we
     * are not going to use that data for registration. It will be fine with default data there
     */
    public function registerAllForTranslation()
    {
        // FAQs Manager
        $objFAQsObserver = new FAQsObserver($this->conf, $this->lang, array());

        // Settings
        $objSettingsObserver = new SettingsObserver($this->conf, $this->lang);


        // ----------------------------------------------------------------------
        // ----------------------------------------------------------------------
        // ----------------------------------------------------------------------

        // FAQs Manager
        $faqIds = $objFAQsObserver->getAllIds();
        foreach($faqIds AS $faqId)
        {
            $objFAQ = new FAQ($this->conf, $this->lang, array(), $faqId);
            $objFAQ->registerForTranslation();
        }

        // Settings
        $settings = $objSettingsObserver->getAll();
        foreach($settings AS $key => $value)
        {
            if(isset($settings[$key.'_translatable']) && $settings[$key.'_translatable'] == 1)
            {
                $objSetting = new Setting($this->conf, $this->lang, $key);
                $objSetting->registerForTranslation();
            }
        }
    }
}