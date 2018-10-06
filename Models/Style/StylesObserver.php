<?php
/**
 * Styles observer

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Style;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\File\StaticFile;
use ExpandableFAQ\Models\ObserverInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class StylesObserver implements ObserverInterface
{
    private $conf             = NULL;
    private $lang             = NULL;
    private $settings		    = array();
    private $debugMode        = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->conf = $paramConf;
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    /**
     * Get supported styles in this plugin
     * @note - The list of supported styles is based on stylesheets files with non-empty "Style Name" in Local Front CSS folder
     * @return array
     */
    public function getSupportedStyles()
    {
        $cssFolderPath = $this->conf->getRouting()->getFrontLocalCSS_Path('', FALSE);
        $cssFiles = StaticFile::getFolderFileList($cssFolderPath, array("css"));

        $retSupportedStyles = array();
        foreach($cssFiles AS $cssFile)
        {
            // Case-insensitive check
            $cssTemplateData = get_file_data($cssFolderPath.$cssFile, array('StyleName' => 'Style Name'));
            if($cssTemplateData['StyleName'] != "")
            {
                $retSupportedStyles[] = array(
                    "style_name" => sanitize_text_field($cssTemplateData['StyleName']),
                    "file_name" => sanitize_text_field($cssFile),
                );
            }
        }

        return $retSupportedStyles;
    }

    public function getDropdownOptions($paramSelectedStyle)
    {
        $retHTML = '';
        $supportedStyles = $this->getSupportedStyles();
        foreach($supportedStyles AS $supportedStyle)
        {
            if($supportedStyle['style_name'] == $paramSelectedStyle)
            {
                $retHTML .= '<option value="'.$supportedStyle['style_name'].'" selected="selected">'.$supportedStyle['style_name'].'</option>'."\n";
            } else
            {
                $retHTML .= '<option value="'.$supportedStyle['style_name'].'">'.$supportedStyle['style_name'].'</option>'."\n";
            }
        }

        return $retHTML;
    }
}