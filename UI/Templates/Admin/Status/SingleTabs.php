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
<div class="car-expandable-faq-status-admin expandable-faq-tabbed-admin expandable-faq-tabbed-admin-medium bg-cyan">
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
			<input type="radio" name="modern-tabs"<?=$statusTabChecked;?> id="modern-tab1" class="modern-tab-content-1">
			<label for="modern-tab1"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=$lang['LANG_STATUS_TEXT'];?></span></span></label>

			<ul>
				<li class="modern-tab-content-1">
					<div class="typography">
                        <?php
                        if($goToNetworkAdmin):
                            include 'Shared/StatusGoToNetworkPartial.php';
                        else:
                            include 'Shared/SingleStatusPartial.php';
                        endif;
                        ?>
					</div>
				</li>
			</ul>
		</div>
		<!--/ tabs -->
	</div>
</div>