<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('expandable-faq-admin');

// Styles
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');
wp_enqueue_style('expandable-faq-admin');
?>
<div class="expandable-faq-manual-admin expandable-faq-tabbed-admin expandable-faq-tabbed-admin-medium bg-cyan">
	<?php if ($errorMessage != ""): ?>
		<div class="admin-info-message admin-standard-width-message admin-error-message"><?=$errorMessage;?></div>
	<?php elseif ($okayMessage != ""): ?>
		<div class="admin-info-message admin-standard-width-message admin-okay-message"><?=$okayMessage;?></div>
	<?php endif; ?>
    <?php if ($debugMessage != ""): ?>
        <div class="admin-info-message admin-standard-width-message admin-debug-message"><?=$debugMessage;?></div>
    <?php endif; ?>
	<div class="body">
		<!-- tabs -->
		<div class="modern-tabs modern-tabs-pos-top-left modern-tabs-anim-flip modern-tabs-response-to-icons">
			<input type="radio" name="modern-tabs"<?=$instructionsTabChecked;?> id="modern-tab1" class="modern-tab-content-1">
			<label for="modern-tab1"><span><span><i class="fa fa-info-circle" aria-hidden="true"></i>Instructions</span></span></label>

            <input type="radio" name="modern-tabs"<?=$shortcodesTabChecked;?> id="modern-tab2" class="modern-tab-content-2">
            <label for="modern-tab2"><span><span><i class="fa fa-tasks" aria-hidden="true"></i>Shortcodes</span></span></label>

            <input type="radio" name="modern-tabs"<?=$shortcodeParametersTabChecked;?> id="modern-tab3" class="modern-tab-content-3">
            <label for="modern-tab3"><span><span><i class="fa fa-code" aria-hidden="true"></i>Shortcode Parameters</span></span></label>

            <input type="radio" name="modern-tabs"<?=$urlParametersHastagsTabChecked;?> id="modern-tab4" class="modern-tab-content-4">
            <label for="modern-tab4"><span><span><i class="fa fa-link" aria-hidden="true"></i>URL Parameters &amp; Hashtags</span></span></label>

            <input type="radio" name="modern-tabs"<?=$uiOverridingTabChecked;?> id="modern-tab5" class="modern-tab-content-5">
            <label for="modern-tab5"><span><span><i class="fa fa-crop" aria-hidden="true"></i>UI Overriding</span></span></label>

			<ul>
				<li class="modern-tab-content-1">
					<div class="typography">
						<?php include 'Shared/InstructionsPartial.php'; ?>
					</div>
				</li>
	            <li class="modern-tab-content-2">
                    <div class="typography">
                        <?php include 'Shared/ShortcodesPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-3">
                    <div class="typography">
                        <?php include 'Shared/ShortcodeParametersPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-4">
                    <div class="typography">
                        <?php include 'Shared/URL_ParametersHashtagsPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-5">
                    <div class="typography">
                        <?php include 'Shared/UI_OverridingPartial.php'; ?>
                    </div>
                </li>
            </ul>
		</div>
		<!--/ tabs -->
	</div>
</div>