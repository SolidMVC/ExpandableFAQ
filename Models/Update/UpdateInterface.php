<?php
/**
 * Element must-have interface - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * NOTE: Updating must not have any patch. For patching we have a separate interface. If after update we saw,
 *      that we missed something, i.e. we need to fix a bug, rename database field,
 *      or add an add new index to the database table, then it is a patch, and should be performed via patch interface.
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;

interface UpdateInterface
{
    public function alterDatabaseEarlyStructure();
    public function updateDatabaseData();
    public function alterDatabaseLateStructure();
    // NOTE: Expandable FAQ does not have any custom roles
    public function updateCustomCapabilities();
    public function updateDatabaseSemver();
}