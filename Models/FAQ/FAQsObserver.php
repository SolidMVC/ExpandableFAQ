<?php
/**
 * Faqs Observer

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

    public function getAllIds($paramFaqId = -1)
    {
        $validFaqId = StaticValidator::getValidInteger($paramFaqId, -1); // -1 means 'skip'

        $sqlAdd = '';
        if($validFaqId > 0)
        {
            // FAQ id
            $sqlAdd .= " AND faq_id='{$validFaqId}'";
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

    public function getTranslatedDropdownOptions($paramSelectedFAQId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "")
    {
        return $this->getDropdownOptions($paramSelectedFAQId, $paramDefaultValue, $paramDefaultLabel, TRUE);
    }

    /**
     * @param int $paramSelectedFAQId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramTranslated
     * @return string
     */
    public function getDropdownOptions($paramSelectedFAQId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "", $paramTranslated = FALSE)
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $faqIds = $this->getAllIds();

        $retHTML = '';
        if($paramSelectedFAQId == $validDefaultValue)
        {
            $retHTML .= '<option value="'.$validDefaultValue.'" selected="selected">'.$sanitizedDefaultLabel.'</option>';
        } else
        {
            $retHTML .= '<option value="'.$validDefaultValue.'">'.$sanitizedDefaultLabel.'</option>';
        }
        foreach($faqIds AS $faqId)
        {
            $objFAQ = new FAQ($this->conf, $this->lang, $this->settings, $faqId);
            $faqDetails = $objFAQ->getDetails();
            $printQuestion = $paramTranslated ? $faqDetails['print_translated_question'] : $faqDetails['print_question'];

            if($faqDetails['faq_id'] == $paramSelectedFAQId)
            {
                $retHTML .= '<option value="'.$faqDetails['faq_id'].'" selected="selected">'.$printQuestion.'</option>';
            } else
            {
                $retHTML .= '<option value="'.$faqDetails['faq_id'].'">'.$printQuestion.'</option>';
            }
        }

        return $retHTML;
    }


    /* --------------------------------------------------------------------------- */
    /* ----------------------- METHODS FOR ADMIN ACCESS ONLY --------------------- */
    /* --------------------------------------------------------------------------- */

    public function getAdminList()
    {
        $retHTML = '';
        $faqIds = $this->getAllIds();
        foreach ($faqIds AS $faqId)
        {
            $objFAQ = new FAQ($this->conf, $this->lang, $this->settings, $faqId);
            $faqDetails = $objFAQ->getDetails();

            $printQuestion = $faqDetails['print_translated_faq_question'];
            if($this->lang->canTranslateSQL())
            {
                $printQuestion .= '<br />-------------------------------<br />';
                $printQuestion .= '<span class="not-translated" title="'.$this->lang->getPrint('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$faqDetails['print_faq_question'].')</span>';
            }

            $printAnswer = $faqDetails['print_translated_faq_answer'];
            if($this->lang->canTranslateSQL())
            {
                $printAnswer .= '<br />-------------------------------<br />';
                $printAnswer .= '<span class="not-translated" title="'.$this->lang->getPrint('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$faqDetails['print_faq_answer'].')</span>';
            }

            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$faqId.'</td>';
            $retHTML .= '<td>'.$printQuestion.'</td>';
            $retHTML .= '<td>'.$printAnswer.'</td>';
            $retHTML .= '<td style="text-align: center">'.$faqDetails['faq_order'].'</td>';
            $retHTML .= '<td align="right">';
            if(current_user_can('manage_'.$this->conf->getPluginPrefix().'all_faqs'))
            {
                $retHTML .= '<a href="'.admin_url('admin.php?page='.$this->conf->getPluginURL_Prefix().'add-edit-faq&amp;faq_id='.$faqId).'">'.$this->lang->getPrint('LANG_EDIT_TEXT').'</a>';
                $retHTML .= ' - ';
                $retHTML .= '<a href="javascript:;" onclick="javascript:'.$this->conf->getPluginJS_ClassPrefix().'Admin.deleteFAQ(\''.$faqId.'\')">'.$this->lang->getPrint('LANG_DELETE_TEXT').'</a>';
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