<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');

// Styles
if($settings['conf_load_font_awesome_from_plugin'] == 1):
    wp_enqueue_style('font-awesome');
endif;
wp_enqueue_style('expandable-faq-main');
?>
<div class="expandable-faq-wrapper expandable-faq-faqs-list">
    <?php if(sizeof($faqs) > 0): ?>
        <dl class="toggle-list">
            <?php foreach($faqs as $faq): ?>
                <dt id="faq-<?=$faq['faq_id'];?>" class="question-row <?=($faq['expanded'] === TRUE ? 'expanded' : 'collapsed');?>">
                    <i class="fa <?=($faq['expanded'] === TRUE ? 'fa-minus' : 'fa-plus');?>" aria-hidden="true"></i>
                    <?=$faq['print_translated_faq_question'];?>
                </dt>
                <dd class="answer-row">
                    <?=$faq['print_translated_faq_answer'];?>
                </dd>
            <?php endforeach; ?>
        </dl>
    <?php else:?>
        <div class="no-faqs-available"><?=$lang['LANG_FAQS_NONE_AVAILABLE_TEXT'];?></div>
    <?php endif; ?>
</div>
<script type="text/javascript">
jQuery().ready(function() {
    jQuery(".expandable-faq-faqs-list .toggle-list dt.collapsed").next().hide();
    jQuery(".expandable-faq-faqs-list .toggle-list dt").click(function () {
        jQuery(this).next(".expandable-faq-faqs-list .toggle-list dd").slideToggle(200);
        jQuery(this).toggleClass("expanded");
        jQuery(this).toggleClass("collapsed");
        jQuery(this).find('i.fa').toggleClass("fa-plus");
        jQuery(this).find('i.fa').toggleClass("fa-minus");
    });
});
</script>