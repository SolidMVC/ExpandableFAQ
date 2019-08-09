<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html(sprintf($lang['LANG_STATUS_S_PLUGIN_TEXT'], $lang['PLUGIN_NAME']));?></span>
</h1>
<div style="padding-bottom: 20px;" class="big-text">
    <strong><?=esc_html($lang['LANG_STATUS_NETWORK_ENABLED_TEXT']);?>:</strong> <?=esc_html($lang[$networkEnabled ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT']);?><br />
    <br />
    <strong><?=esc_html($lang['LANG_STATUS_INSTALLED_VERSION_TEXT']);?>:</strong> <?=esc_html($databaseSemver);?>
</div>
<div>
    <?=esc_html(sprintf($lang['LANG_STATUS_GO_TO_S_NETWORK_ADMIN_TEXT'], $lang['PLUGIN_NAME']));?><br />
    <br />
    <?=esc_html($lang['LANG_STATUS_NOTE_FOR_POSSIBLY_NOT_IMPORTED_DATA_TEXT']);?>
</div>
