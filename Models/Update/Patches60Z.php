<?php
/**
 * Patch class
 *
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Semver\Semver;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class Patches60Z extends AbstractDatabase implements StackInterface, DatabaseInterface, PatchInterface
{
    const CURRENT_MAJOR = 6; // Positive integer [X]
    const CURRENT_MINOR = 0; // Positive integer [Y]
    const LATEST_PATCH = 2; // Positive integer [Z]
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
    public function patchDatabaseEarlyStructure()
    {
        $arrSQL = array();
        $objSemver = new Semver($this->pluginSemverInDatabase, FALSE);
        $currentPatch = $objSemver->getPatch();

        if($currentPatch < 2)
        {
            // [SETTINGS] Add indexes for logic scope and for the fact that both - ext_code and blog_id is used in WHERE statement,
            //  when we pull to cache all DB settings, or when we want to drop data from specific table
            $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
                ADD INDEX ( `conf_translatable` ),
                ADD INDEX ( `blog_id` );";
        }

        $patched = $this->executeQueries($arrSQL);
        if($patched === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_PATCH_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_PATCHED_TEXT'), $this->blogId);
        }

        return $patched;
    }

    /**
     * @return bool
     */
    public function patchData()
    {
        $arrSQL = array();
        $validBlogId = StaticValidator::getValidPositiveInteger($this->blogId, 0);

        $objSemver = new Semver($this->pluginSemverInDatabase, FALSE);
        $currentPatch = $objSemver->getPatch();

        if($currentPatch < 1)
        {
            // [SETTINGS] Rename settings
            $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
                SET conf_key='conf_plugin_semver'
                WHERE conf_key='conf_plugin_version' AND blog_id='{$validBlogId}'";
        }

        // Execute queries
        $patched = $this->executeQueries($arrSQL);

        if($patched === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCH_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCHED_TEXT'), $this->blogId);
        }

        return $patched;
    }

    /**
     * SQL for late database altering
     * @return bool
     */
    public function patchDatabaseLateStructure()
    {
        // NOTHING HERE
        $patched = TRUE;

        // $arrSQL = array();
        // $patched = $this->executeQueries($arrSQL);
        //if($patched === FALSE)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_LATE_STRUCTURE_PATCH_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_LATE_STRUCTURE_PATCHED_TEXT'), $this->blogId);
        //}

        return $patched;
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

        $newSemver = static::CURRENT_MAJOR.'.'.static::CURRENT_MINOR.'.'.static::LATEST_PATCH;
        $newSemver .= static::LATEST_RELEASE != "" ? "-".static::LATEST_RELEASE : "";
        $newSemver .= static::LATEST_BUILD_METADATA != "" ? "+".static::LATEST_BUILD_METADATA : "";

        // Update plugin semver till newest
        $semverUpdated = $this->executeQuery("
            UPDATE `{$this->conf->getPrefix()}settings`
            SET `conf_value`='{$newSemver}'
            WHERE `conf_key` IN ('conf_plugin_semver', 'conf_plugin_version') AND blog_id='{$validBlogId}'
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
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATED_TEXT'), $this->blogId, $newSemver);
        }

        return $updated;
    }
}