<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('expandable-faq-admin');

// Styles
wp_enqueue_style('jquery-validate');
wp_enqueue_style('expandable-faq-admin');
?>
<p>&nbsp;</p>
<div id="container-inside" style="width:1000px;">
   <span style="font-size:16px; font-weight:bold"><?=esc_html($lang['LANG_FAQ_ADD_EDIT_TEXT']);?></span>
   <input type="button" value="<?=esc_attr($lang['LANG_FAQ_BACK_TO_LIST_TEXT']);?>" onClick="window.location.href='<?=esc_url($backToListURL);?>'" style="background: #EFEFEF; float:right; cursor:pointer;"/>
   <hr style="margin-top:10px;"/>
   <form action="<?=esc_url($formAction);?>" method="POST" class="expandable-faq-add-edit-faq-form">
        <table cellpadding="5" cellspacing="2" border="0">
            <input type="hidden" name="faq_id" value="<?=esc_attr($faqId);?>"/>
            <tr>
                <td width="95px"><strong><?=esc_html($lang['LANG_FAQ_QUESTION_TEXT']);?>:</strong></td>
                <td>
                    <input type="text" name="faq_question" maxlength="255" value="<?=esc_attr($faqQuestion);?>" class="required faq-question" style="width:350px;" title="<?=esc_attr($lang['LANG_FAQ_QUESTION_TEXT']);?>" />
                </td>
            </tr>
            <tr>
                <td align="left">
                    <strong><?=esc_html($lang['LANG_FAQ_ANSWER_TEXT']);?>:</strong><br />
                </td>
                <td>
                    <textarea name="faq_answer" rows="8" cols="50" class="required faq-answer" title="<?=esc_attr($lang['LANG_FAQ_ANSWER_TEXT']);?>"><?=esc_textarea($faqAnswer);?></textarea>
                </td>
            </tr>
            <tr>
                <td><strong><?=esc_html($lang['LANG_FAQ_ORDER_TEXT']);?>:</strong></td>
                <td>
                    <input type="text" name="faq_order" maxlength="11" value="<?=esc_attr($faqOrder);?>" class="faq-order" style="width:40px;" title="<?=esc_attr($lang['LANG_FAQ_ORDER_TEXT']);?>" />
                </td>
                <td>
                    <em><?=($faqId > 0 ? '' : '('.esc_html($lang['LANG_FAQ_ORDER_OPTIONAL_TEXT']).')');?></em>
                </td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="<?=esc_attr($lang['LANG_FAQ_SAVE_TEXT']);?>" name="save_faq" style="cursor:pointer;"/></td>
            </tr>
        </table>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        // Validator
        jQuery('.expandable-faq-add-edit-faq-form').validate();
    });
</script>
