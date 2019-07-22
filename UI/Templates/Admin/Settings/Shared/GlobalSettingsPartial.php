<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_SETTINGS_GLOBAL_TEXT']);?></span>
</h1>
<form name="global_settings_form" action="<?=esc_url($globalSettingsTabFormAction);?>" method="POST" class="global-settings-form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%" class="global-settings">
        <tr>
            <td width="20%"><strong><?=esc_html($lang['LANG_SETTINGS_USE_SESSIONS_TEXT']);?>:</strong></td>
            <td width="80%">
                <select name="conf_use_sessions" title="<?=esc_attr($lang['LANG_SETTINGS_USE_SESSIONS_TEXT']);?>">
                    <?=$arrGlobalSettings['trusted_use_sessions_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTINGS_FRONTEND_STYLE_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_system_style" title="<?=esc_attr($lang['LANG_SETTINGS_FRONTEND_STYLE_TEXT']);?>">
                    <?=$trustedSystemStylesDropdownOptionsHTML;?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTINGS_FONT_AWESOME_ICONS_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_load_font_awesome_from_plugin" title="<?=esc_attr($lang['LANG_SETTINGS_FONT_AWESOME_ICONS_TEXT']);?>">
                    <?=$arrGlobalSettings['trusted_load_font_awesome_from_plugin_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="<?=esc_attr($lang['LANG_SETTINGS_UPDATE_GLOBAL_SETTINGS_TEXT']);?>" name="update_global_settings" style="cursor:pointer;"/>
            </td>
        </tr>
    </table>
</form>
<p><?=esc_html($lang['LANG_PLEASE_KEEP_IN_MIND_THAT_TEXT']);?>:</p>
<ol>
    <li><?=esc_html($lang['LANG_SETTINGS_NOTE_FOR_SESSIONS_USAGE_TEXT']);?></li>
    <li><?=esc_html($lang['LANG_SETTINGS_NOTE_FOR_ASSETS_LOADING_PLACE_TEXT']);?></li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('.global-settings-form').validate();
});
</script>