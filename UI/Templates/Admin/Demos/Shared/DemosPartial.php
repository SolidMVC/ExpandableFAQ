<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_DEMO_IMPORT_TEXT']);?></span>
</h1>
<form name="import_demo_form" action="<?=esc_url($importDemoTabFormAction);?>" method="POST" id="import_demo_form">
    <div class="big-labels">
        <select name="demo_id" class="required">
            <?=$trustedDemosDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="submit" value="<?=esc_html($lang['LANG_DEMO_IMPORT_SHORT_TEXT']);?>" name="import_demo"
               onclick="return confirm('<?=esc_js($lang['LANG_DEMO_IMPORTING_DIALOG_TEXT']);?>');"
               style="cursor:pointer;"
            />
    </div>
    <p><?=esc_html($lang['LANG_PLEASE_KEEP_IN_MIND_THAT_TEXT']);?>:</p>
    <ol>
        <li><?=esc_html($lang['LANG_DEMO_NOTE_ON_DATA_FLUSHING_TEXT']);?></li>
        <li><?=esc_html($lang['LANG_DEMO_NOTE_ON_NO_AFFECT_TO_OTHER_CONTENT_TEXT']);?></li>
        <li><?=esc_html($lang['LANG_DEMO_NOTE_TO_HAVE_DATABASE_BACKUP_TEXT']);?></li>
    </ol>
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery("#import_demo_form").validate();
});
</script>