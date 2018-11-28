<?php
/**
 * Modern semver validator
 * Note 1: This model does not depend on any other class
 *
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 *
 * Example:
 *   $good = array(
        "1.0.8",
        "1.23.7",
        "2.0.0-alpha.123.abc",
        "2.0.0-alpha.123.abc+build.acebfde1284",
        "1.0.0-alpha",
        "1.0.0-alpha.1",
        "1.0.0-0.3.7",
        "1.0.0-x.7.z.92",
        "1.0.0-alpha",
        "1.0.0-alpha.1",
        "1.0.0-beta.2",
        "1.0.0-beta.11",
        "1.0.0-rc.1",
        "1.0.0-rc.1+build.1",
        "1.0.0-rc.1+build.1-b",
        "1.0.0",
        "1.0.0+0.3.7",
        "1.3.7+build",
        "1.3.7+build.2.b8f12d7",
        "1.3.7+build.11.e0f985a",
        "1.3.7+build.11.e0f9-85a",
        "1.0.0+build-acbe",
        "2.0.0+build.acebfde1284-alpha.123.abc",
    );
 *
 *  $bad = array(
        "v1.0.0",
        "a.b.c",
        "1",
        "1.0.0b",
        "1.0",
        "1.0.0+b[\\]^_`uild", // [,\,],^,_,` are between A-z, but not A-Za-z
        "1.0.0+build-acbe.", // trailing period
        "1.0.0+build.!@#$%",
    );
 */
namespace ExpandableFAQ\Models\Semver;

final class Semver implements SemverInterface
{
    private $versionWildcardsAllowed    = FALSE;
	private $semver                     = '0.0.0-alpha.1+build.0';
	private $version                    = '0.0.0'; // '*.*.*' is also supported, if enabled
    private $major                      = 0;
    private $minor                      = 0;
    private $patch                      = 0;
    private $buildMetadata              = '';
    private $release                    = '';

    /**
     * Parse the raw semver
     * @param string $paramSemver
     * @param bool $paramVersionWildcardsAllowed - for 'any major', 'any minor', 'any patch' support
     */
	public function __construct($paramSemver, $paramVersionWildcardsAllowed = FALSE)
	{
	    // Set version wildcards allowance
        $this->versionWildcardsAllowed = $paramVersionWildcardsAllowed === TRUE ? TRUE : FALSE;

        // Set semver
        $this->setSemver($paramSemver);
    }

    /**
     * NOTE: Negative version's major, minor or patch will be converted to '0'
     * @param string $paramSemver
     */
    public function setSemver($paramSemver)
    {
        $findRelease = strpos('-', $paramSemver); // For '1.0.0-beta.1+build.1' (LENGTH=20) it's pos will be INDEX=5
        $findBuildMetadata = strpos('+', $paramSemver); // For '1.0.0-beta.1+build.1' (LENGTH=20) it's pos will be INDEX=12

        // 1. GET LENGTHS & START POSITIONS
        if($findRelease !== FALSE)
        {
            $lengthOfVersionPart = $findRelease; // For '1.0.0-beta.1+build.1' it will be = 5 (sub-string = '1.0.0')
        } else if($findBuildMetadata !== FALSE && $findRelease === FALSE)
        {
            $lengthOfVersionPart = $findBuildMetadata; // For '1.0.0+build.1' it will be = 5 (sub-string = '1.0.0')
        } else
        {
            $lengthOfVersionPart = strlen($paramSemver); // For '1.0.0' it is = 5
        }

        // Get release start position & length
        $startPosOfRelease = FALSE;
        $lengthOfReleasePart = 0;
        if($findRelease !== FALSE)
        {
            $startPosOfRelease = $findRelease + 1; // For '1.0.0-beta.1+build.1' it will be = 6
            $lengthOfReleasePart = $findBuildMetadata > $findRelease ? ($findBuildMetadata + 1) - $lengthOfVersionPart : strlen($paramSemver) - $lengthOfVersionPart;
        }

        // Get build metadata start position & length
        $startPosOfBuildMetadata = FALSE;
        $lengthOfBuildMetadataPart = 0;
        if($findBuildMetadata !== FALSE && ($findRelease === FALSE || $findRelease < $findBuildMetadata))
        {
            $startPosOfBuildMetadata = $findBuildMetadata + 1; // For '1.0.0-beta.1+build.1' it will be = 12
            $lengthOfBuildMetadataPart = $startPosOfRelease !== FALSE ? strlen($paramSemver) - $lengthOfReleasePart - $lengthOfVersionPart : strlen($paramSemver)-$lengthOfVersionPart;
        }


        // 2. GET PARAMS
        $paramVersion = substr($paramSemver, 0, $lengthOfVersionPart);
        $paramRelease = substr($paramSemver, $startPosOfRelease, $lengthOfReleasePart);
        $paramBuildMetadata = substr($paramSemver, $startPosOfBuildMetadata, $lengthOfBuildMetadataPart);


        // 3. PARSE VERSION & GET VALID VERSION
        // NOTE #1: Only ASCII alphanumerics chars and dots [0-9\.] and wildcards (if allowed). Negative numbers will be set as 0.
        // NOTE #2: We allow temporary the negative numbers, but later they will be converted to '0'
        $versionRegex = $this->versionWildcardsAllowed ? '[^-0-9\.*]' : '[^-0-9\.]';
        $parsedVersion = preg_replace($versionRegex, '', $paramVersion); // Remove anything that is not a digit or dot
        $parsedVersionParts = explode('.', $parsedVersion);

        // Set defaults
        $validMajor = 0;
        $validMinor = 0;
        $validPatch = 0;

        // Normalize the version format, is some part(-s) are missing (major, minor or patch)
        if (sizeof($parsedVersionParts) == 0)
        {
            // Add default major '0'
            $validMajor = 0;
            // Add default minor '0'
            $validMinor = 0;
            // Add default patch '0'
            $validPatch = 0;
        } else if (sizeof($parsedVersionParts) == 1)
        {
            // Get valid major
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[0] == "*")
            {
                $validMajor = "*";
            } else
            {
                $validMajor = is_numeric($parsedVersionParts[0]) && $parsedVersionParts[0] > 0 ? intval($parsedVersionParts[0]) : 0;
            }

            // Add default minor '0'
            $validMinor = 0;
            // Add default patch '0'
            $validPatch = 0;
        } else if (sizeof($parsedVersionParts) == 2)
        {
            // Get valid major
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[0] == "*")
            {
                $validMajor = "*";
            } else
            {
                $validMajor = is_numeric($parsedVersionParts[0]) && $parsedVersionParts[0] > 0 ? intval($parsedVersionParts[0]) : 0;
            }

            // Get valid minor
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[1] == "*")
            {
                $validMinor = "*";
            } else
            {
                $validMinor = is_numeric($parsedVersionParts[1]) && $parsedVersionParts[1] > 0 ? intval($parsedVersionParts[1]) : 0;
            }

            // Add default patch '0'
            $validPatch = 0;
        } else if (sizeof($parsedVersionParts) == 3)
        {
            // Get valid major
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[0] == "*")
            {
                $validMajor = "*";
            } else
            {
                $validMajor = is_numeric($parsedVersionParts[0]) && $parsedVersionParts[0] > 0 ? intval($parsedVersionParts[0]) : 0;
            }

            // Get valid minor
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[1] == "*")
            {
                $validMinor = "*";
            } else
            {
                $validMinor = is_numeric($parsedVersionParts[1]) && $parsedVersionParts[1] > 0 ? intval($parsedVersionParts[1]) : 0;

            }
            // Get valid patch
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[2] == "*")
            {
                $validPatch = "*";
            } else
            {
                $validPatch = is_numeric($parsedVersionParts[2]) && $parsedVersionParts[2] > 0 ? intval($parsedVersionParts[2]) : 0;
            }
        }
        $validVersion = "{$validMajor}.{$validMinor}.{$validPatch}";


        // 3. GET VALID RELEASE
        // Note:    A pre-release version MAY be denoted by appending a hyphen and a series of dot separated identifiers immediately following the patch version.
        //          Identifiers MUST comprise only ASCII alphanumerics and hyphen [0-9A-Za-z-].
        //          Identifiers MUST NOT be empty.
        //          Numeric identifiers MUST NOT include leading zeroes.
        //          Pre-release versions have a lower precedence than the associated normal version.
        //          A pre-release version indicates that the version is unstable
        //          and might not satisfy the intended compatibility requirements as denoted by its associated normal version.
        //          Examples: 1.0.0-alpha, 1.0.0-alpha.1, 1.0.0-0.3.7, 1.0.0-x.7.z.92.
        $validRelease = preg_replace('[^0-9A-Za-z-\.]', '', $paramRelease); // Remove anything that is not a digit or dot


        // 4. GET VALID BUILD METADATA
        // Note:    Build metadata MAY be denoted by appending a plus sign and a series of dot separated identifiers immediately following the patch or pre-release version.
        //          Identifiers MUST comprise only ASCII alphanumerics and hyphen [0-9A-Za-z-].
        //          Identifiers MUST NOT be empty.
        //          Build metadata SHOULD be ignored when determining version precedence.
        //          Thus two versions that differ only in the build metadata, have the same precedence.
        //          Examples: 1.0.0-alpha+001, 1.0.0+20130313144700, 1.0.0-beta+exp.sha.5114f85.
        $validBuildMetadata = preg_replace('[^0-9A-Za-z-\.]', '', $paramBuildMetadata); // Remove anything that is not a digit or dot


        // 5. GET VALID SEMVER
        $validSemver = $validVersion;
        if($validRelease != "")
        {
            $validSemver .= "-{$validRelease}";
        }

        if($validBuildMetadata != "")
        {
            $validSemver .= "+{$validBuildMetadata}";
        }

        // Set object variables
        $this->semver = $validSemver;
        $this->version = $validVersion;
        $this->major = $validMajor;
        $this->minor = $validMinor;
        $this->patch = $validPatch;
        $this->release = $validRelease;
        $this->buildMetadata = $validBuildMetadata;
    }

    /**
     * NOTE: Negative major, minor or patch will be converted to '0'
     * @param string $paramVersion
     */
    public function setVersion($paramVersion)
    {
        // PARSE VERSION & GET VALID VERSION
        // Notes:    Only ASCII alphanumerics chars and dots [0-9\.] and wildcards (if allowed)
        $versionRegex = $this->versionWildcardsAllowed ? '[^0-9\.*]' : '[^0-9\.]';
        $parsedVersion = preg_replace($versionRegex, '', $paramVersion); // Remove anything that is not a digit or dot
        $parsedVersionParts = explode('.', $parsedVersion);

        // Set defaults
        $validMajor = 0;
        $validMinor = 0;
        $validPatch = 0;

        // Normalize the version format, is some part(-s) are missing (major, minor or patch)
        if (sizeof($parsedVersionParts) == 0)
        {
            // Add default major '0'
            $validMajor = 0;
            // Add default minor '0'
            $validMinor = 0;
            // Add default patch '0'
            $validPatch = 0;
        } else if (sizeof($parsedVersionParts) == 1)
        {
            // Get valid major
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[0] == "*")
            {
                $validMajor = "*";
            } else
            {
                $validMajor = is_numeric($parsedVersionParts[0]) && $parsedVersionParts[0] > 0 ? intval($parsedVersionParts[0]) : 0;
            }

            // Add default minor '0'
            $validMinor = 0;
            // Add default patch '0'
            $validPatch = 0;
        } else if (sizeof($parsedVersionParts) == 2)
        {
            // Get valid major
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[0] == "*")
            {
                $validMajor = "*";
            } else
            {
                $validMajor = is_numeric($parsedVersionParts[0]) && $parsedVersionParts[0] > 0 ? intval($parsedVersionParts[0]) : 0;
            }

            // Get valid minor
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[1] == "*")
            {
                $validMinor = "*";
            } else
            {
                $validMinor = is_numeric($parsedVersionParts[1]) && $parsedVersionParts[1] > 0 ? intval($parsedVersionParts[1]) : 0;
            }

            // Add default patch '0'
            $validPatch = 0;
        } else if (sizeof($parsedVersionParts) == 3)
        {
            // Get valid major
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[0] == "*")
            {
                $validMajor = "*";
            } else
            {
                $validMajor = is_numeric($parsedVersionParts[0]) && $parsedVersionParts[0] > 0 ? intval($parsedVersionParts[0]) : 0;
            }

            // Get valid minor
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[1] == "*")
            {
                $validMinor = "*";
            } else
            {
                $validMinor = is_numeric($parsedVersionParts[1]) && $parsedVersionParts[1] > 0 ? intval($parsedVersionParts[1]) : 0;

            }
            // Get valid patch
            if($this->versionWildcardsAllowed === TRUE && $parsedVersionParts[2] == "*")
            {
                $validPatch = "*";
            } else
            {
                $validPatch = is_numeric($parsedVersionParts[2]) && $parsedVersionParts[2] > 0 ? intval($parsedVersionParts[2]) : 0;
            }
        }
        $validVersion = "{$validMajor}.{$validMinor}.{$validPatch}";

        // GET VALID SEMVER
        $validSemver = $validVersion;
        if($this->release != "")
        {
            $validSemver .= "-{$this->release}";
        }

        if($this->buildMetadata != "")
        {
            $validSemver .= "+{$this->buildMetadata}";
        }

        // Set updatable object variables
        $this->semver = $validSemver;
        $this->version = $validVersion;
        $this->major = $validMajor;
        $this->minor = $validMinor;
        $this->patch = $validPatch;
    }

    /**
     * NOTE: Negative major will be converted to '0'
     * @param int $paramMajor
     */
    public function setMajor($paramMajor)
    {
        if($this->versionWildcardsAllowed === TRUE && $paramMajor == "*")
        {
            $validMajor = "*";
        } else
        {
            $validMajor = is_numeric($paramMajor) && $paramMajor > 0 ? intval($paramMajor) : 0;
        }
        $validVersion = "{$validMajor}.{$this->minor}.{$this->patch}";

        // GET VALID SEMVER
        $validSemver = $validVersion;
        if($this->release != "")
        {
            $validSemver .= "-{$this->release}";
        }

        if($this->buildMetadata != "")
        {
            $validSemver .= "+{$this->buildMetadata}";
        }

        // Set updatable object variables
        $this->semver = $validSemver;
        $this->version = $validVersion;
        $this->major = $validMajor;
    }

    /**
     * NOTE: Negative minor will be converted to '0'
     * @param int $paramMinor
     */
    public function setMinor($paramMinor)
    {
        if($this->versionWildcardsAllowed === TRUE && $paramMinor == "*")
        {
            $validMinor = "*";
        } else
        {
            $validMinor = is_numeric($paramMinor) && $paramMinor > 0  ? intval($paramMinor) : 0;
        }
        $validVersion = "{$this->major}.{$validMinor}.{$this->patch}";

        // GET VALID SEMVER
        $validSemver = $validVersion;
        if($this->release != "")
        {
            $validSemver .= "-{$this->release}";
        }

        if($this->buildMetadata != "")
        {
            $validSemver .= "+{$this->buildMetadata}";
        }

        // Set updatable object variables
        $this->semver = $validSemver;
        $this->version = $validVersion;
        $this->minor = $validMinor;
    }

    /**
     * NOTE: Negative patch will be converted to '0'
     * @param int $paramPatch
     */
    public function setPatch($paramPatch)
    {
        if($this->versionWildcardsAllowed === TRUE && $paramPatch == "*")
        {
            $validPatch = "*";
        } else
        {
            $validPatch = is_numeric($paramPatch) && $paramPatch > 0 ? intval($paramPatch) : 0;
        }
        $validVersion = "{$this->major}.{$this->minor}.{$validPatch}";

        // GET VALID SEMVER
        $validSemver = $validVersion;
        if($this->release != "")
        {
            $validSemver .= "-{$this->release}";
        }

        if($this->buildMetadata != "")
        {
            $validSemver .= "+{$this->buildMetadata}";
        }

        // Set updatable object variables
        $this->semver = $validSemver;
        $this->version = $validVersion;
        $this->patch = $validPatch;
    }

    /**
     * @param int $paramAdjustment - i.e. -1, 0, +1
     */
    public function adjustMajor($paramAdjustment)
    {
        $validAdjustment = is_numeric($paramAdjustment) ? intval($paramAdjustment) : 0;
        $this->setMajor($this->major + $validAdjustment);
    }

    /**
     * @param int $paramAdjustment - i.e. -1, 0, +1
     */
    public function adjustMinor($paramAdjustment)
    {
        $validAdjustment = is_numeric($paramAdjustment) ? intval($paramAdjustment) : 0;
        $this->setMinor($this->minor + $validAdjustment);
    }

    /**
     * @param int $paramAdjustment - i.e. -1, 0, +1
     */
    public function adjustPatch($paramAdjustment)
    {
        $validAdjustment = is_numeric($paramAdjustment) ? intval($paramAdjustment) : 0;
        $this->setPatch($this->patch + $validAdjustment);
    }

    /**
     * @param string $paramRelease
     */
    public function setRelease($paramRelease)
    {
        // Note:    A pre-release version MAY be denoted by appending a hyphen and a series of dot separated identifiers immediately following the patch version.
        //          Identifiers MUST comprise only ASCII alphanumerics and hyphen [0-9A-Za-z-].
        //          Identifiers MUST NOT be empty.
        //          Numeric identifiers MUST NOT include leading zeroes.
        //          Pre-release versions have a lower precedence than the associated normal version.
        //          A pre-release version indicates that the version is unstable
        //          and might not satisfy the intended compatibility requirements as denoted by its associated normal version.
        //          Examples: 1.0.0-alpha, 1.0.0-alpha.1, 1.0.0-0.3.7, 1.0.0-x.7.z.92.
        $validRelease = preg_replace('[^0-9A-Za-z-\.]', '', $paramRelease); // Remove anything that is not a digit or dot

        // GET VALID SEMVER
        $validSemver = $this->version;
        if($validRelease != "")
        {
            $validSemver .= "-{$validRelease}";
        }

        if($this->buildMetadata != "")
        {
            $validSemver .= "+{$this->buildMetadata}";
        }

        // Set updatable object variables
        $this->semver = $validSemver;
        $this->release = $validRelease;
    }

    /**
     * @param string $paramBuildMetadata
     */
    public function setBuildMetadata($paramBuildMetadata)
    {
        // Note:    Build metadata MAY be denoted by appending a plus sign and a series of dot separated identifiers immediately following the patch or pre-release version.
        //          Identifiers MUST comprise only ASCII alphanumerics and hyphen [0-9A-Za-z-].
        //          Identifiers MUST NOT be empty.
        //          Build metadata SHOULD be ignored when determining version precedence.
        //          Thus two versions that differ only in the build metadata, have the same precedence.
        //          Examples: 1.0.0-alpha+001, 1.0.0+20130313144700, 1.0.0-beta+exp.sha.5114f85.
        $validBuildMetadata = preg_replace('[^0-9A-Za-z-\.]', '', $paramBuildMetadata); // Remove anything that is not a digit or dot

        // GET VALID SEMVER
        $validSemver = $this->version;
        if($this->release != "")
        {
            $validSemver .= "-{$this->release}";
        }

        if($validBuildMetadata != "")
        {
            $validSemver .= "+{$validBuildMetadata}";
        }

        // Set updatable object variables
        $this->semver = $validSemver;
        $this->buildMetadata = $validBuildMetadata;
    }

    /**
     * Get semver
     * @return string
     */
    public function getSemver()
    {
        return $this->semver;
    }

    /**
     * Get version
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

	/**
	 * get major version
	 * @return int
	 */
	public function getMajor()
	{
		return $this->major;
	}

	/**
	 * get minor version
	 * @return int
	 */
	public function getMinor()
	{
		return $this->minor;
	}

	/**
	 * get patch 
	 * @return int
	 */
	public function getPatch()
	{
		return $this->patch;
	}

	/**
	 * get release name,
     * i.e. for version '1.3.0-beta+exp.sha.5114f85' the result would be 'beta'
     *
	 * @return string
	 */
	public function getRelease()
	{
		return $this->release;
	}

    /**
     * get build meta,
     * i.e. for version '1.3.0-beta+exp.sha.5114f85' the result would be 'exp.sha.5114f85'
     *
     * @return string
     */
    public function getBuildMetadata()
    {
        return $this->buildMetadata;
    }
}
