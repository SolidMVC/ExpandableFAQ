<?php
/**
 * Update class
 * NOTE: This is a boilerplate class, so please replace DatabaseXYZ with exact major ("X") and minor ("Y"), i.e. "Database70"
 *
 * @package InventoryManagementSystem
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\Semver\Semver;
use ExpandableFAQ\Models\StackInterface;
use ExpandableFAQ\Models\Language\LanguageInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;

final class Database60Z extends AbstractUpdate implements StackInterface, UpdateInterface
{
    const NEW_MAJOR = 6; // Positive integer [X]
    const NEW_MINOR = 0; // Positive integer [Y]
    const LATEST_PATCH = 1; // Positive integer [Z]
    const LATEST_RELEASE = ''; // String
    const LATEST_BUILD_METADATA = ''; // String
    const PLUGIN_PREFIX = "expandable_faq_";

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
     * @return bool
     */
    public function patchData()
    {
        $arrSQL = array();
        $validBlogId = StaticValidator::getValidPositiveInteger($this->blogId, 0);

        $objSemver = new Semver($this->pluginSemverInDatabase, FALSE);
        $currentPatch = $objSemver->getPatch();

        // [SETTINGS] Rename settings
        if($currentPatch < 1)
        {
            $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
                SET conf_key='conf_plugin_semver'
                WHERE conf_key='conf_plugin_version' AND blog_id='{$validBlogId}'";
        }

        // Execute queries
        $patched = $this->executeQueries($arrSQL);

        if($patched === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_DATA_PATCH_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_DATA_PATCHED_TEXT'), $this->blogId);
        }

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

        $newSemver = static::NEW_MAJOR.'.'.static::NEW_MINOR.'.'.static::LATEST_PATCH;
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
            $this->errorMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getPrint('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATED_TEXT'), $this->blogId, $newSemver);
        }

        return $updated;
    }
}