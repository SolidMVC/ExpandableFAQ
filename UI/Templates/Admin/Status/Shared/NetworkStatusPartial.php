<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=$lang['LANG_STATUS_SYSTEM_TEXT'];?></span>
</h1>
<form name="status_form" action="<?=$statusTabFormAction;?>" method="POST" class="status-form">
    <div style="padding-bottom: 20px;" class="big-text">
        <strong><?=$lang['LANG_STATUS_NETWORK_ENABLED_TEXT'];?>:</strong> <?=$lang['LANG_YES_TEXT'];?><br />
        <br />
        <strong><?=$lang['LANG_STATUS_DATABASE_MIN_VERSION_TEXT'];?>:</strong> <?=$minDatabaseVersion;?><br />
        <br />
        <?php if($updateAvailable): ?>
            <?php if($majorUpgradeAvailable): ?>
                <span style="color: red; font-weight: bold"><?=$lang['LANG_STATUS_MAJOR_UPGRADE_AVAILABLE_TEXT'];?></span><br />
            <?php else: ?>
                <span style="color: #2da5da; font-weight: bold"><?=$lang['LANG_STATUS_MINOR_UPDATE_AVAILABLE_TEXT'];?></span><br />
            <?php endif; ?>
            <br />
            <strong><?=$lang['LANG_STATUS_NEWEST_VERSION_AVAILABLE_TEXT'];?>:</strong> <?=$newestVersionAvailable;?><br />
            <br />
            <?=$lang['LANG_STATUS_UPDATE_FOLLOW_STEPS_TEXT'];?>:
            <ol>
                <li><?=$lang['LANG_STATUS_UPDATE_STEP_MAKE_A_COPY_TEXT'];?>,</li>
                <li><?=$lang['LANG_STATUS_UPDATE_STEP_DOWNLOAD_NEW_VERSION_TEXT'];?>,</li>
                <li><?=$lang['LANG_STATUS_UPDATE_STEP_UPLOAD_VIA_FTP_TEXT'];?>,</li>
                <li><?=$lang['LANG_STATUS_UPDATE_STEP_UPLOAD_NEW_VERSION_TEXT'];?>,</li>
                <li><?=$lang['LANG_STATUS_UPDATE_STEP_ACTIVATE_NEW_VERSION_TEXT'];?>,</li>
                <li><?=$lang['LANG_STATUS_UPDATE_STEP_CLICK_UPDATE_TEXT'];?>,</li>
                <li><?=$lang['LANG_STATUS_UPDATE_STEP_DONE_TEXT'];?>.</li>
            </ol>
        <?php else: ?>
            <?php printf($lang['LANG_STATUS_YOU_HAVE_S_NO_UPDATE_AVAILABLE_TEXT'], '<span style="color: green; font-weight: bold">'.$lang['LANG_STATUS_THE_NEWEST_VERSION_TEXT'].'</span>'); ?>
        <?php endif; ?>
    </div>
    <?php if($databaseMatchesCodeVersion === FALSE): ?>
        <?php if($canMajorlyUpgrade): ?>
            <div style="padding-bottom: 20px;" class="big-text">
                <strong><?=$lang['LANG_STATUS_SYSTEM_READY_FOR_UPGRADE_TEXT'];?></strong>
            </div>
            <div style="text-align: center;" class="big-text">
                <input type="submit" value="<?=$lang['LANG_STATUS_UPGRADE_TO_NEXT_VERSION_TEXT'];?>" name="update" style="cursor: pointer;" />
            </div>
        <?php elseif($canUpdate): ?>
            <div style="padding-bottom: 20px;" class="big-text">
                <strong><?=$lang['LANG_STATUS_SYSTEM_READY_FOR_UPDATE_TEXT'];?></strong>
            </div>
            <div style="text-align: center" class="big-text">
                  <input type="submit" value="<?=$lang['LANG_STATUS_UPDATE_TO_NEXT_VERSION_TEXT'];?>" name="update" style="cursor: pointer;" />
            </div>
        <?php else: ?>
            <?=$lang['LANG_STATUS_UPDATE_NOT_ALLOWED_ERROR_TEXT'];?>
        <?php endif; ?>
    <?php endif; ?>
</form>