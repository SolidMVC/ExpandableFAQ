<?php
/**
 * FAQ

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\FAQ;
use ExpandableFAQ\Models\AbstractStack;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\ElementInterface;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class FAQ extends AbstractStack implements StackInterface, ElementInterface
{
    private $conf 	                = NULL;
    private $lang 		            = NULL;
    private $debugMode 	            = 0;
    private $faqId                  = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramFaqId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->faqId = StaticValidator::getValidValue($paramFaqId, 'positive_integer', 0);
    }

    private function getDataFromDatabaseById($paramFaqId, $paramColumns = array('*'))
    {
        $validFaqId = StaticValidator::getValidPositiveInteger($paramFaqId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);

        $sqlQuery = "
            SELECT {$validSelect}
            FROM {$this->conf->getPrefix()}faqs
            WHERE faq_id='{$validFaqId}'
        ";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->faqId;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    /**
     * @note Do not translate title here - it is used for editing
     * @param bool $paramIncludeUnclassified - not used
     * @return mixed
     */
    public function getDetails($paramIncludeUnclassified = FALSE)
    {
        $ret = $this->getDataFromDatabaseById($this->faqId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['faq_question'] = stripslashes($ret['faq_question']);
            $ret['faq_answer'] = stripslashes($ret['faq_answer']);

            // Retrieve translation
            $ret['translated_faq_question'] = $this->lang->getTranslated("fa{$ret['faq_id']}_faq_question", $ret['faq_question']);
            $ret['translated_faq_answer'] = $this->lang->getTranslated("fa{$ret['faq_id']}_faq_answer", $ret['faq_answer']);
        }

        return $ret;
    }

    /**
     * @param array $params
     * @return false|int
     */
    public function save(array $params)
    {
        $saved = FALSE;
        $ok = TRUE;
        $validFAQ_Id = StaticValidator::getValidPositiveInteger($this->faqId, 0);
        $validFAQ_Question = isset($params['faq_question']) ? esc_sql(sanitize_text_field($params['faq_question'])) : ''; // for sql query only
        $validFAQ_Answer = isset($params['faq_answer']) ? esc_sql(implode("\n", array_map('sanitize_text_field', explode("\n", $params['faq_answer'])))) : ''; // for sql query only

        if(isset($params['faq_order']) && StaticValidator::isPositiveInteger($params['faq_order']))
        {
            $validFAQ_Order = StaticValidator::getValidPositiveInteger($params['faq_order'], 1);
        } else
        {
            // SELECT MAX
            $sqlQuery = "
                SELECT MAX(faq_order) AS max_order
                FROM {$this->conf->getPrefix()}faqs
                WHERE 1
            ";
            $maxOrderResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            $validFAQ_Order = !is_null($maxOrderResult) ? intval($maxOrderResult)+1 : 1;
        }

        // Search for existing F.A.Q. question
        $faqQuestionExistsQuery = "
            SELECT faq_id
            FROM {$this->conf->getPrefix()}faqs
            WHERE faq_id!={$validFAQ_Id} AND faq_question='{$validFAQ_Question}'
            AND blog_id='{$this->conf->getBlogId()}'
        ";
        $faqQuestionExists = $this->conf->getInternalWPDB()->get_var($faqQuestionExistsQuery);
        if(!is_null($faqQuestionExists))
        {
            $ok = FALSE;
            $this->errorMessages[] = $this->lang->getText('LANG_FAQ_QUESTION_EXISTS_ERROR_TEXT');
        }

        if($validFAQ_Id > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}faqs SET
                faq_question='{$validFAQ_Question}', faq_answer='{$validFAQ_Answer}', faq_order='{$validFAQ_Order}'
                WHERE faq_id='{$validFAQ_Id}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($saved === FALSE)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_FAQ_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_FAQ_UPDATED_TEXT');
            }
        } else if($ok)
        {
            // For admins we do not save the IP, as the source writers IP may be unknown
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}faqs
                (
                    faq_question, faq_answer, faq_order, blog_id
                ) VALUES
                (
                    '{$validFAQ_Question}', '{$validFAQ_Answer}', '{$validFAQ_Order}', '{$this->conf->getBlogId()}'
                )
            ");

            // We will process only if there one line was added to sql
            if($saved)
            {
                // Get newly inserted faq id
                $validInsertedNewFAQ_Id = $this->conf->getInternalWPDB()->insert_id;

                // Update the core faq id for future use
                $this->faqId = $validInsertedNewFAQ_Id;
            }

            if($saved === FALSE || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_FAQ_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_FAQ_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $faqDetails = $this->getDetails();
        if(!is_null($faqDetails))
        {
            $this->lang->register("fa{$this->faqId}_faq_question", $faqDetails['faq_question']);
            $this->lang->register("fa{$this->faqId}_faq_answer", $faqDetails['faq_answer']);
            $this->okayMessages[] = $this->lang->getText('LANG_FAQ_REGISTERED_TEXT');
        }
    }

    /**
     * @return false|int
     */
    public function delete()
    {
        $validFaqId = StaticValidator::getValidPositiveInteger($this->faqId, 0);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}faqs
            WHERE faq_id='{$validFaqId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === FALSE || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_FAQ_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_FAQ_DELETED_TEXT');
        }

        return $deleted;
    }
}