<?php
/**
 * Element must-have interface - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * NOTE: Patching must not impact the roles or capabilities. It it does have to impact that, then it is an update, not a patch.
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Update;

interface PatchInterface
{
    public function patchDatabaseEarlyStructure();
    public function patchData();
    public function patchDatabaseLateStructure();
    public function updateDatabaseSemver();
}