<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=$lang['LANG_SETTINGS_GLOBAL_TEXT'];?></span>
</h1>
<form name="global_settings_form" action="<?=$globalSettingsTabFormAction;?>" method="POST" class="global-settings-form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%" class="global-settings">
        <tr>
            <td><strong>Use Sessions:</strong></td>
            <td>
                <select name="conf_use_sessions" title="Use Sessions">
                    <?=$arrGlobalSettings['select_use_sessions'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Front-End Style:</strong></td>
            <td>
                <select name="conf_system_style" title="Front-End Style">
                    <?=$systemStylesDropdownOptions;?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Font Awesome Assets:</strong></td>
            <td>
                <select name="conf_load_font_awesome_from_plugin" title="Font Awesome Assets">
                    <?=$arrGlobalSettings['select_load_font_awesome_from_plugin'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="<?=$lang['LANG_SETTINGS_UPDATE_GLOBAL_SETTINGS_TEXT'];?>" name="update_global_settings" style="cursor:pointer;"/>
            </td>
        </tr>
    </table>
</form>
<p><?=$lang['LANG_PLEASE_KEEP_IN_MIND_THAT_TEXT'];?>:</p>
<ol>
    <li>Use of sessions is recommended, if supported by the server - that gives better site loading speed &amp; additional security layer.</li>
    <li>Loading assets from the other place, means that scripts/style/fonts/images will be loaded from the current or parent theme (if defined there),
        or from other plugin (if defined there).</li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('.global-settings-form').validate();
});
</script>