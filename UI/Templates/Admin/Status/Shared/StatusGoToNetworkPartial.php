<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=$lang['LANG_STATUS_SYSTEM_TEXT'];?></span>
</h1>
<div style="padding-bottom: 20px;" class="big-text">
    <strong><?=$lang['LANG_STATUS_NETWORK_ENABLED_TEXT'];?>:</strong> <?=$networkEnabled;?><br />
    <br />
    <strong><?=$lang['LANG_STATUS_INSTALLED_VERSION_TEXT'];?>:</strong> <?=$installedPluginSemver;?>
</div>
<div>
    <?=$lang['LANG_STATUS_GO_TO_NETWORK_ADMIN_TEXT'];?>
</div>
