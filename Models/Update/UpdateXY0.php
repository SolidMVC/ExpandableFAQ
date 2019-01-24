<?php
/**
 * Update class
 * NOTE: This is a boilerplate class, so please replace UpdateXY0 with exact major ("X") and minor ("Y"), i.e. "Update700"
 *
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class UpdateXY0 extends AbstractDatabase implements StackInterface, UpdateInterface
{
    const NEW_MAJOR = 7; // Positive integer [X]
    const NEW_MINOR = 0; // Positive integer [Y]
    // NOTE: No patch here for updates. For updates the patch is always '0'
    const LATEST_RELEASE = ''; // String
    const LATEST_BUILD_METADATA = ''; // String
    const PLUGIN_PREFIX = "expandable_faq_";

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramBlogId);
    }

    /**
     * SQL for early database altering
     * @return bool
     */
    public function alterDatabaseEarlyStructure()
    {
        // NOTHING HERE
        $altered = TRUE;

        // $arrSQL = array();
        // $altered = $this->executeQueries($arrSQL);
        //if($altered === FALSE)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_ALTER_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_ALTERED_TEXT'), $this->blogId);
        //}

        return $altered;
    }

    /**
     * SQL for updating database data
     * @return bool
     */
    public function updateDatabaseData()
    {
        // NOTHING HERE

        // Update main data
        $updated = TRUE;

        // $arrSQL = array();
        // $updated = $this->executeQueries($arrSQL);
        //if($updated === FALSE)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_DATA_UPDATE_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_DATA_UPDATED_TEXT'), $this->blogId);
        //}

        return $updated;
    }

    /**
     * SQL for late database altering
     * @return bool
     */
    public function alterDatabaseLateStructure()
    {
        // NOTHING HERE
        $altered = TRUE;

        // $arrSQL = array();
        // $altered = $this->executeQueries($arrSQL);
        //if($altered === FALSE)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_LATE_STRUCTURE_ALTER_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_LATE_STRUCTURE_ALTERED_TEXT'), $this->blogId);
        //}

        return $altered;
    }

    public function updateCustomRoles()
    {
        // NOTHING HERE
        $rolesUpdated = TRUE;

        //if($rolesUpdated === FALSE)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_ROLES_UPDATE_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_ROLES_UPDATED_TEXT'), $this->blogId);
        //}

        return $rolesUpdated;
    }

    public function updateCustomCapabilities()
    {
        // NOTHING HERE
        $rolesUpdated = TRUE;

        //if($rolesUpdated === FALSE)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_CAPABILITIES_UPDATE_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_CAPABILITIES_UPDATED_TEXT'), $this->blogId);
        //}

        return $rolesUpdated;
    }

    /**
     * NOTE: This method has to be in update class of specific update, because settings table itself,
     *       and it's columns can change over a time as well
     * @return bool
     */
    public function updateDatabaseSemver()
    {
        $updated = FALSE;
        $validBlogId = StaticValidator::getValidPositiveInteger($this->blogId, 0);

        // NOTE: For updates the patch is always 0
        $newSemver = static::NEW_MAJOR.'.'.static::NEW_MINOR.'.0';
        $newSemver .= static::LATEST_RELEASE != "" ? "-".static::LATEST_RELEASE : "";
        $newSemver .= static::LATEST_BUILD_METADATA != "" ? "+".static::LATEST_BUILD_METADATA : "";

        // Update plugin semver till newest
        $semverUpdated = $this->executeQuery("
            UPDATE `{$this->conf->getPrefix()}settings`
            SET `conf_value`='{$newSemver}'
            WHERE `conf_key`='conf_plugin_semver' AND blog_id='{$validBlogId}'
        ");
        // Reset counter back to 0 to say that the new update can start from the first update class query. That will be used in future updates
        $counterReset = $this->executeQuery("
            UPDATE `{$this->conf->getPrefix()}settings`
            SET `conf_value`='0'
            WHERE `conf_key`='conf_updated' AND blog_id='{$validBlogId}'
        ");
        if($semverUpdated !== FALSE && $counterReset !== FALSE)
        {
            $updated = TRUE;
        }

        if($updated === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATED_TEXT'), $this->blogId, $newSemver);
        }

        return $updated;
    }
}