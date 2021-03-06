<?php
/**
 * FAQs Observer

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\FAQ;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\ObserverInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class FAQsObserver implements ObserverInterface
{
    private $conf 	                = NULL;
    private $lang 		            = NULL;
    private $settings		        = array();
    private $debugMode 	            = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    public function getAllIds($paramFAQ_Id = -1)
    {
        $validFAQ_Id = StaticValidator::getValidInteger($paramFAQ_Id, -1); // -1 means 'skip'

        $sqlAdd = '';
        if($validFAQ_Id > 0)
        {
            // FAQ id
            $sqlAdd .= " AND faq_id='{$validFAQ_Id}'";
        }

        $searchSQL = "
            SELECT faq_id
            FROM {$this->conf->getPrefix()}faqs
            WHERE blog_id='{$this->conf->getBlogId()}'{$sqlAdd}
            ORDER BY faq_order ASC, faq_id ASC
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResult;
    }

    public function getTranslatedDropdownOptionsHTML($paramSelectedFAQ_Id = -1, $paramDefaultValue = -1, $paramDefaultLabel = "")
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedFAQ_Id, $paramDefaultValue, $paramDefaultLabel, TRUE);
    }

    /**
     * @param int $paramSelectedFAQ_Id
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramTranslated
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedFAQ_Id = -1, $paramDefaultValue = -1, $paramDefaultLabel = "", $paramTranslated = FALSE)
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $faqIds = $this->getAllIds();

        $retHTML = '';
        if($paramSelectedFAQ_Id == $validDefaultValue)
        {
            $retHTML .= '<option value="'.esc_attr($validDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
        } else
        {
            $retHTML .= '<option value="'.esc_attr($validDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
        }
        foreach($faqIds AS $faqId)
        {
            $objFAQ = new FAQ($this->conf, $this->lang, $this->settings, $faqId);
            $faqDetails = $objFAQ->getDetails();
            $faqQuestion = $paramTranslated ? $faqDetails['translated_faq_question'] : $faqDetails['faq_question'];

            if($faqDetails['faq_id'] == $paramSelectedFAQ_Id)
            {
                $retHTML .= '<option value="'.esc_attr($faqDetails['faq_id']).'" selected="selected">'.esc_html($faqQuestion).'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($faqDetails['faq_id']).'">'.esc_html($faqQuestion).'</option>';
            }
        }

        return $retHTML;
    }


    /* --------------------------------------------------------------------------- */
    /* ----------------------- METHODS FOR ADMIN ACCESS ONLY --------------------- */
    /* --------------------------------------------------------------------------- */

    public function getTrustedAdminListHTML()
    {
        $retHTML = '';
        $faqIds = $this->getAllIds();
        foreach ($faqIds AS $faqId)
        {
            $objFAQ = new FAQ($this->conf, $this->lang, $this->settings, $faqId);
            $faqDetails = $objFAQ->getDetails();

            $faqQuestionHMTL = esc_html($faqDetails['translated_faq_question']);
            if($this->lang->canTranslateSQL())
            {
                $faqQuestionHMTL .= '<br />-------------------------------<br />';
                $faqQuestionHMTL .= '<span class="not-translated" title="'.$this->lang->escAttr('LANG_WITHOUT_TRANSLATION_TEXT').'">('.esc_html($faqDetails['faq_question']).')</span>';
            }

            $faqAnswerHTML = esc_br_html($faqDetails['translated_faq_answer']);
            if($this->lang->canTranslateSQL())
            {
                $faqAnswerHTML .= '<br />-------------------------------<br />';
                $faqAnswerHTML .= '<span class="not-translated" title="'.$this->lang->escAttr('LANG_WITHOUT_TRANSLATION_TEXT').'">('.esc_br_html($faqDetails['faq_answer']).')</span>';
            }

            $retHTML .= '<tr>';
            $retHTML .= '<td>'.esc_html($faqId).'</td>';
            $retHTML .= '<td>'.$faqQuestionHMTL.'</td>';
            $retHTML .= '<td>'.$faqAnswerHTML.'</td>';
            $retHTML .= '<td style="text-align: center">'.esc_html($faqDetails['faq_order']).'</td>';
            $retHTML .= '<td align="right">';
            if(current_user_can('manage_'.$this->conf->getPluginPrefix().'all_faqs'))
            {
                $retHTML .= '<a href="'.admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'add-edit-faq&amp;faq_id='.$faqId).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a>';
                $retHTML .= ' - ';
                $retHTML .= '<a href="javascript:;" onclick="javascript:ExpandableFAQ_Admin.deleteFAQ(\''.$faqId.'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $retHTML .= '--';
            }
            $retHTML .= '</td>';
            $retHTML .= '</tr>';
        }

        return  $retHTML;
    }
}