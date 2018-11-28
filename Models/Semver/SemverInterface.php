<?php
/**
 * Semver must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package ExpandableFAQ
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Semver;

interface SemverInterface
{
    /**
     * Parse the raw semver
     * @param string $paramSemver
     * @param bool $paramVersionWildcardsAllowed - for 'any major', 'any minor', 'any patch' support
     */
    public function __construct($paramSemver, $paramVersionWildcardsAllowed = FALSE);

    /**
     * NOTE: Negative version's major, minor or patch will be converted to '0'
     * @param string $paramSemver
     */
    public function setSemver($paramSemver);

    /**
     * NOTE: Negative major, minor or patch will be converted to '0'
     * @param string $paramVersion
     */
    public function setVersion($paramVersion);

    /**
     * NOTE: Negative major will be converted to '0'
     * @param int $paramMajor - supports wildcard
     */
    public function setMajor($paramMajor);

    /**
     * NOTE: Negative minor will be converted to '0'
     * @param int $paramMinor - supports wildcard
     */
    public function setMinor($paramMinor);

    /**
     * NOTE: Negative patch will be converted to '0'
     * @param int $paramPatch - supports wildcard
     */
    public function setPatch($paramPatch);

    /**
     * @param int $paramAdjustment - i.e. -1, 0, +1
     */
    public function adjustMajor($paramAdjustment);

    /**
     * @param int $paramAdjustment - i.e. -1, 0, +1
     */
    public function adjustMinor($paramAdjustment);

    /**
     * @param int $paramAdjustment - i.e. -1, 0, +1
     */
    public function adjustPatch($paramAdjustment);

    /**
     * @param string $paramRelease
     */
    public function setRelease($paramRelease);

    /**
     * @param string $paramBuildMetadata
     */
    public function setBuildMetadata($paramBuildMetadata);

    /**
     * @return string
     */
    public function getSemver();

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @return int
     */
    public function getMajor();

    /**
     * @return int
     */
    public function getMinor();

    /**
     * @return int
     */
    public function getPatch();

    /**
     * @return string
     */
    public function getRelease();

    /**
     * @return string
     */
    public function getBuildMetadata();
}
