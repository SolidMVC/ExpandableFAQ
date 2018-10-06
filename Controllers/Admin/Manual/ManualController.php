<?php
/**
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Controllers\Admin\Manual;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Formatting\StaticFormatter;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Controllers\Admin\AbstractController;

final class ManualController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Get the tab values
        $tabs = StaticFormatter::getTabParams(array(
            'instructions', 'shortcodes', 'shortcode-parameters', 'url-parameters-hashtags', 'ui-overriding'
        ), 'instructions', isset($_GET['tab']) ? $_GET['tab'] : '');

        // 1. Set the view variables - Tab settings
        $this->view->instructionsTabChecked = !empty($tabs['instructions']) ? ' checked="checked"' : '';
        $this->view->shortcodesTabChecked = !empty($tabs['shortcodes']) ? ' checked="checked"' : '';
        $this->view->shortcodeParametersTabChecked = !empty($tabs['shortcode-parameters']) ? ' checked="checked"' : '';
        $this->view->urlParametersHastagsTabChecked = !empty($tabs['url-parameters-hashtags']) ? ' checked="checked"' : '';
        $this->view->uiOverridingTabChecked = !empty($tabs['ui-overriding']) ? ' checked="checked"' : '';

        // Print the template
        $templateRelPathAndFileName = 'Manual'.DIRECTORY_SEPARATOR.'Tabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
