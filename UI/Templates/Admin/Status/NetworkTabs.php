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
<div class="expandable-faq-status-admin expandable-faq-tabbed-admin expandable-faq-tabbed-admin-medium bg-cyan">
	<?php if ($errorMessage != ""): ?>
		<div class="admin-info-message admin-standard-width-message admin-error-message"><?=esc_br_html($errorMessage);?></div>
	<?php elseif ($okayMessage != ""): ?>
		<div class="admin-info-message admin-standard-width-message admin-okay-message"><?=esc_br_html($okayMessage);?></div>
	<?php endif; ?>
    <?php if ($ksesedDebugHTML != ""): ?>
        <div class="admin-info-message admin-standard-width-message admin-debug-html"><?=$ksesedDebugHTML;?></div>
    <?php endif; ?>
	<div class="body">
		<!-- tabs -->
		<div class="modern-tabs modern-tabs-pos-top-left modern-tabs-anim-flip modern-tabs-response-to-icons">
			<input type="radio" name="modern-tabs"<?=(!empty($tabs['status']) ? ' checked="checked"' : '');?> id="modern-tab1" class="modern-tab-content-1">
			<label for="modern-tab1"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_STATUS_NETWORK_TEXT']);?></span></span></label>

            <ul>
				<li class="modern-tab-content-1">
					<div class="typography">
						<?php include 'Shared/NetworkStatusPartial.php'; ?>
					</div>
				</li>
			</ul>
		</div>
		<!--/ tabs -->
	</div>
</div>