<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span><?=esc_html($lang['LANG_FAQ_LIST_TEXT']);?></span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="<?=esc_attr($lang['LANG_FAQ_ADD_NEW_TEXT']);?>" onClick="window.location.href='<?=esc_url($addNewFAQ_URL);?>'" />
</h1>
<table id="faqs-datatable" class="display faqs-datatable" border="0" style="width:100%">
	<thead>
        <tr>
            <th><?=esc_html($lang['LANG_ID_TEXT']);?></th>
            <th><?=esc_html($lang['LANG_FAQ_QUESTION_TEXT']);?></th>
            <th><?=esc_html($lang['LANG_FAQ_ANSWER_TEXT']);?></th>
            <th style="text-align: center"><?=esc_html($lang['LANG_LIST_ORDER_TEXT']);?></th>
            <th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
        </tr>
	</thead>
	<tbody>
	    <?=$trustedAdminFAQ_ListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#faqs-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[3,'asc'],[0,'asc']],
        "aoColumns": [
            { "width": "1%" },
            { "width": "30%" },
            { "width": "55%" },
            { "width": "4%" },
            { "width": "10%" }
        ],
        "bAutoWidth": true,
		"bInfo": true,
		"sScrollY": "100%",
		"sScrollX": "100%",
		"bScrollCollapse": true,
		"sPaginationType": "full_numbers",
		"bRetrieve": true,
        "language": {
            "url": ExpandableFAQ_Vars['DATATABLES_LANG_URL']
        }
	});
});
</script>