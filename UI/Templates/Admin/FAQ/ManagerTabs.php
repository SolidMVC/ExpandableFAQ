<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('datatables-jqueryui');
wp_enqueue_script('datatables-responsive-datatables');
wp_enqueue_script('datatables-responsive-jqueryui');
wp_enqueue_script('expandable-faq-admin');

// Styles
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');
wp_enqueue_style('jquery-ui-theme'); // NOTE: We need it for datatables
wp_enqueue_style('datatables-jqueryui');
wp_enqueue_style('datatables-responsive-jqueryui');
wp_enqueue_style('expandable-faq-admin');
?>
<div class="car-expandable-faq-list-admin expandable-faq-tabbed-admin expandable-faq-tabbed-admin-wide bg-cyan">
    <?php if ($errorMessage != ""): ?>
        <div class="admin-info-message admin-wide-message admin-error-message"><?=esc_br_html($errorMessage);?></div>
    <?php elseif ($okayMessage != ""): ?>
        <div class="admin-info-message admin-wide-message admin-okay-message"><?=esc_br_html($okayMessage);?></div>
    <?php endif; ?>
    <?php if ($ksesedDebugHTML != ""): ?>
        <div class="admin-info-message admin-wide-message admin-debug-html"><?=$ksesedDebugHTML;?></div>
    <?php endif; ?>
    <div class="body">
        <!-- tabs -->
        <div class="modern-tabs modern-tabs-pos-top-left modern-tabs-anim-flip modern-tabs-response-to-icons">
            <input type="radio" name="modern-tabs"<?=(!empty($tabs['faqs']) ? ' checked="checked"' : '');?> id="modern-tab1" class="modern-tab-content-1">
            <label for="modern-tab1"><span><span><i class="fa fa-question-circle" aria-hidden="true"></i><?=esc_html($lang['LANG_FAQS_TEXT']);?></span></span></label>

            <ul>
                <li class="modern-tab-content-1">
                    <div class="typography">
                        <?php include 'Shared/FAQsPartial.php'; ?>
                    </div>
                </li>
            </ul>
        </div>
        <!--/ tabs -->
    </div>
</div>