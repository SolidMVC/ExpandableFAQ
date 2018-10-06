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
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Node\LinkedNode;
use ExpandableFAQ\Models\Node\LinkedNodesObserver;
use ExpandableFAQ\Models\Node\LinkedNodesTable;

final class AssetController
{
    private $conf 	                = NULL;
    private $lang 		            = NULL;
    private static $mandatoryPlainJSInitialized = FALSE;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitation will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * We use this method, because WP_LOCALIZE_SCRIPT does not do the great job,
     * and even the 'l10n_print_after' param is a backward-compatible feature, that has issues of initializing first or second count
     * NOTE: About dynamic properties:
     *       https://stackoverflow.com/questions/11040472/how-to-check-if-object-property-exists-with-a-variable-holding-the-property-name/30148756
     */
    public function enqueueMandatoryPlainJS()
    {
        $dataTablesRelPath = 'DataTables'.DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR;
        $dataTablesRelURL = 'DataTables/Plugins/i18n/';
        $dataTablesLangFilename = $this->lang->getPrint('DATATABLES_LANG').'.json';
        if(is_readable($this->conf->getRouting()->get3rdPartyAssetsPath($dataTablesRelPath.$dataTablesLangFilename)) === FALSE)
        {
            $dataTablesLangFilename = 'English.json';
        }

        $extVars = array(
            'DATATABLES_LANG_URL' => $this->conf->getRouting()->get3rdPartyAssetsURL($dataTablesRelURL.$dataTablesLangFilename, TRUE),
        );
        $extLang = array(
            'LANG_FAQ_DELETING_DIALOG_TEXT' => $this->lang->getPrint('LANG_FAQ_DELETING_DIALOG_TEXT'),
        );

        if(static::$mandatoryPlainJSInitialized === FALSE)
        {
            static::$mandatoryPlainJSInitialized = TRUE;
            ?>
            <script type="text/javascript">var <?=$this->conf->getPluginJS_ClassPrefix();?>Vars;</script>
            <script type="text/javascript">var <?=$this->conf->getPluginJS_ClassPrefix();?>Lang;</script>
            <?php
        }
        ?>
        <script type="text/javascript"><?=$this->conf->getPluginJS_ClassPrefix();?>Vars = <?=json_encode($extVars, JSON_FORCE_OBJECT);?>;</script>
        <script type="text/javascript"><?=$this->conf->getPluginJS_ClassPrefix();?>Lang = <?=json_encode($extLang, JSON_FORCE_OBJECT);?>;</script>
        <?php
    }

    public function registerScripts()
    {
        if(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        {
            // Debug scripts

            // 1. Datatables with Responsive support
            wp_register_script('datatables-jquery-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/jquery.dataTables.js'));
            wp_register_script('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/dataTables.jqueryui.js'));
            wp_register_script('datatables-responsive-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/dataTables.responsive.js'));
            wp_register_script('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/responsive.jqueryui.js'));
        } else
        {
            // Regular scripts

            // 1. Datatables with Responsive support
            wp_register_script('datatables-jquery-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js'));
            wp_register_script('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/dataTables.jqueryui.min.js'));
            wp_register_script('datatables-responsive-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js'));
            wp_register_script('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/responsive.jqueryui.min.js'));
        }

        // 2. jQuery validate
        wp_register_script('jquery-validate', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-validation/jquery.validate.js'));

        // 3. NS Admin script
        $filename = $this->conf->getPluginJS_ClassPrefix().'Admin.js';
        wp_register_script($this->conf->getPluginHandlePrefix().'admin', $this->conf->getRouting()->getAdminJS_URL($filename), array(), '1.0', TRUE);
    }

    public function registerStyles()
    {
        // Register 3rd party styles for further use (register even it the file is '' - WordPress will process that as needed)
        if(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        {
            // Debug style

            // 1. Font-Awesome styles
            wp_register_style('font-awesome', $this->conf->getRouting()->get3rdPartyAssetsURL('font-awesome/css/font-awesome.css'));

            // 2. Modern tabs styles
            wp_register_style('modern-tabs', $this->conf->getRouting()->get3rdPartyAssetsURL('ModernTabs/ModernTabs.css'));

            // 3. jQuery UI theme (currently used for DataTables)
            wp_register_style('jquery-ui-theme', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-ui/themes/custom/jquery-ui.css'));

            // 4. Datatables with Responsive support
            wp_register_style('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/css/dataTables.jqueryui.css'));
            wp_register_style('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/css/responsive.jqueryui.css'));
        } else
        {
            // Regular style

            // 1. Font-Awesome styles
            wp_register_style('font-awesome', $this->conf->getRouting()->get3rdPartyAssetsURL('font-awesome/css/font-awesome.min.css'));

            // 2. Modern tabs styles
            wp_register_style('modern-tabs', $this->conf->getRouting()->get3rdPartyAssetsURL('ModernTabs/ModernTabs.css'));

            // 3. jQuery UI theme (currently used for DataTables)
            wp_register_style('jquery-ui-theme', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-ui/themes/custom/jquery-ui.min.css'));

            // 4. Datatables with Responsive support
            wp_register_style('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/css/dataTables.jqueryui.min.css'));
            wp_register_style('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/css/responsive.jqueryui.min.css'));
        }

        // 5. jQuery Validate
        wp_register_style('jquery-validate', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-validation/jquery.validate.css'));

        // 6. Plugin style
        wp_register_style($this->conf->getPluginURL_Prefix().'admin', $this->conf->getRouting()->getAdminCSS_URL('Admin.css'));
    }
}