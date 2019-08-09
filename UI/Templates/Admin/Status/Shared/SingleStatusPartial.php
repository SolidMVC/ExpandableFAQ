<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html(sprintf($lang['LANG_STATUS_S_PLUGIN_TEXT'], $lang['PLUGIN_NAME']));?></span>
</h1>
<form name="status_form" action="<?=esc_url($statusTabFormAction);?>" method="POST" class="status-form">
    <div style="padding-bottom: 20px;" class="big-text">
        <strong><?=esc_html($lang['LANG_STATUS_NETWORK_ENABLED_TEXT']);?>:</strong> <?=esc_html($lang[$networkEnabled ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT']);?><br />
        <br />
        <strong><?=esc_html($lang['LANG_STATUS_DATABASE_VERSION_TEXT']);?>:</strong> <?=esc_html($databaseSemver);?><br />
        <br />
        <?php if($updateAvailable): ?>
            <?php if($majorUpgradeAvailable): ?>
                <span style="color: red; font-weight: bold"><?=esc_html($lang['LANG_STATUS_MAJOR_UPGRADE_AVAILABLE_TEXT']);?></span><br />
            <?php else: ?>
                <span style="color: #2da5da; font-weight: bold"><?=esc_html($lang['LANG_STATUS_MINOR_UPDATE_AVAILABLE_TEXT']);?></span><br />
            <?php endif; ?>
            <br />
            <strong><?=esc_html($lang['LANG_STATUS_NEWEST_VERSION_AVAILABLE_TEXT']);?>:</strong> <?=esc_html($newestSemverAvailable);?><br />
            <br />
            <?=esc_html($lang['LANG_STATUS_UPDATE_FOLLOW_STEPS_TEXT']);?>:
            <ol>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_MAKE_A_COPY_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_DOWNLOAD_NEW_VERSION_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_UPLOAD_VIA_FTP_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_UPLOAD_NEW_VERSION_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_ACTIVATE_NEW_VERSION_TEXT']);?>,</li>
                <li><?=esc_html(sprintf($lang['LANG_STATUS_UPDATE_STEP_S_CLICK_UPDATE_TEXT'], $lang['PLUGIN_NAME']));?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_DONE_TEXT']);?>.</li>
            </ol>
        <?php elseif($updateAvailable === FALSE && $updateExists === FALSE): ?>
            <?php printf($lang['LANG_STATUS_YOU_HAVE_S_NO_UPDATE_AVAILABLE_TEXT'], '<span style="color: green; font-weight: bold">'.esc_html($lang['LANG_STATUS_THE_NEWEST_VERSION_TEXT']).'</span>'); ?>
        <?php elseif($updateAvailable === FALSE && $updateExists): ?>
            <!-- Update exists, but system is not compatible to update -->
            <strong><?=esc_html($lang['LANG_STATUS_NEWEST_EXISTING_VERSION_TEXT']);?>:</strong> <?=esc_html($newestExistingSemver);?>
        <?php endif; ?>
    </div>
    <?php if($databaseMatchesCodeSemver === FALSE): ?>
        <?php if($canMajorlyUpgrade): ?>
            <div style="padding-bottom: 20px;" class="big-text">
                <strong><?=esc_html(sprintf($lang['LANG_STATUS_S_READY_FOR_UPGRADE_TEXT'], $lang['PLUGIN_NAME']));?></strong>
            </div>
            <div style="text-align: center;" class="big-text">
                <input type="submit" value="<?=esc_attr($lang['LANG_STATUS_UPGRADE_TO_NEXT_VERSION_TEXT']);?>" name="update" style="cursor: pointer;" />
            </div>
        <?php elseif($canUpdate): ?>
            <div style="padding-bottom: 20px;" class="big-text">
                <strong><?=esc_html(sprintf($lang['LANG_STATUS_S_READY_FOR_UPDATE_TEXT'], $lang['PLUGIN_NAME']));?></strong>
            </div>
            <div style="text-align: center" class="big-text">
                  <input type="submit" value="<?=esc_attr($lang['LANG_STATUS_UPDATE_TO_NEXT_VERSION_TEXT']);?>" name="update" style="cursor: pointer;" />
            </div>
        <?php else: ?>
            <?=esc_html($lang['LANG_STATUS_UPDATE_NOT_ALLOWED_ERROR_TEXT']);?>
        <?php endif; ?>
    <?php endif; ?>
</form>