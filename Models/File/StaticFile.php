<?php
/**
 * File Static methods
 * Note 1: This model does not depend on any other class
 * Note 2: This model must be used in static context only
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\File;

final class StaticFile
{
    protected static $debugMode = 0;
    protected static $maxImageFileSize = 4096000 ; // max image size in bytes, default 4 MiB
    protected static $thumbnailsEnabled = TRUE; // max image size in bytes, default 4 MiB

    public static function createWritableDirectory($paramNewFolderPathAndNameWithoutEndSlash)
    {
        // Create an upload dir if not exist
        $newDirectoryExists = TRUE;
        if(!file_exists($paramNewFolderPathAndNameWithoutEndSlash))
        {
            // The mkdir doesn't work in WordPress setup on regular servers (DirectAdmin+Ubuntu based etc.)
            //$uploadDirectoryExists = mkdir($paramToFolderWithoutEndSlash, 0777, TRUE);
            $newDirectoryExists = wp_mkdir_p($paramNewFolderPathAndNameWithoutEndSlash);
        }

        // Check if folder is writable
        $newDirectoryWritable = FALSE;
        if($newDirectoryExists)
        {
            if(is_writable($paramNewFolderPathAndNameWithoutEndSlash) === FALSE)
            {
                chmod($paramNewFolderPathAndNameWithoutEndSlash, 0777);
                if(is_writable($paramNewFolderPathAndNameWithoutEndSlash))
                {
                    $newDirectoryWritable = TRUE;
                }
            } else
            {
                $newDirectoryWritable = TRUE;
            }
        }

        return $newDirectoryWritable;
    }

    /**
     * @param $uploadedImageFile
     * @param string $paramUploadsPathWithoutEndSlash
     * @param string $paramFilePrefix
     * @return string
     */
    public static function uploadImageFile(&$uploadedImageFile, $paramUploadsPathWithoutEndSlash, $paramFilePrefix = "")
    {
        // Create writable uploads directory if it do not exist yet
        $uploadDirectoryWritable = StaticFile::createWritableDirectory($paramUploadsPathWithoutEndSlash);

        $sanitizedUploadsPathWithoutEndSlash = sanitize_text_field($paramUploadsPathWithoutEndSlash);
        $sanitizedFilePrefix = sanitize_text_field($paramFilePrefix);
        $sanitizedNewFileName = $sanitizedFilePrefix.sanitize_file_name($uploadedImageFile['name']);
        if(file_exists($sanitizedUploadsPathWithoutEndSlash.'/'.$sanitizedNewFileName))
        {
            $sanitizedNewFileName = $sanitizedFilePrefix.time().'_'.sanitize_file_name($uploadedImageFile['name']);
        }
        $allowedToUpload = FALSE;
        $movedToUploadsFolder = FALSE;
        $imageTypeFileSize = $uploadedImageFile['tmp_name'] != '' ? getimagesize($uploadedImageFile['tmp_name']) : FALSE;
        $isImageFileType = preg_match("#^image/#i", $uploadedImageFile['type']);
        $isValidImageSize = $uploadedImageFile['size'] <= static::$maxImageFileSize;
        if($imageTypeFileSize !== FALSE && $uploadDirectoryWritable && $uploadedImageFile['error'] == 0 && $isImageFileType && $isValidImageSize)
        {
            $allowedToUpload = TRUE;
            // Actions to perform, if new image been updated
            $movedToUploadsFolder = move_uploaded_file($uploadedImageFile['tmp_name'], $sanitizedUploadsPathWithoutEndSlash.'/'.$sanitizedNewFileName);
        }

        if(static::$debugMode)
        {
            echo "<br />Uploaded File Info: "; print_r($uploadedImageFile);
            echo "<br />Uploads path: ".$sanitizedUploadsPathWithoutEndSlash.'/';
            echo "<br />File prefix: ".$sanitizedFilePrefix;
            echo "<br />Image file name: ".$sanitizedNewFileName;
            echo "<br />[CHECK] \$imageTypeFileSize: ".var_export($imageTypeFileSize, TRUE);
            echo "<br />[CHECK] Upload directory writable: ".($uploadDirectoryWritable ? "Yes" : "No");
            echo "<br />[CHECK] \$uploadedImageFile[&#39;error&#39;]: "; var_export($uploadedImageFile['error'], TRUE);
            echo "<br />[CHECK] \$isImageFileType: "; var_export($isImageFileType, TRUE);
            echo "<br />[CHECK] \$isValidImageSize: ".($isValidImageSize ? "Yes" : "No")." (".$uploadedImageFile['size']." &lt;= ".static::$maxImageFileSize.")";
            echo "<br />[RESULT] Allowed to upload: ".($allowedToUpload ? "Yes" : "No");
            echo "<br />Moved to uploads folder: ".($movedToUploadsFolder ? "Yes" : "No");
        }

        return $movedToUploadsFolder ? $sanitizedNewFileName : '';
    }


    public static function makeThumbnail($paramUploadDirectory, $paramImageName, $paramThumbnailWidth, $paramThumbnailHeight, $paramThumbnailNamePrefix = "thumb_")
    {
        $thumbnailCreated = FALSE;

        $sanitizedUploadDirectory = sanitize_text_field($paramUploadDirectory);
        $sanitizedImageName = sanitize_text_field($paramImageName);
        $validThumbnailWidth = intval($paramThumbnailWidth);
        $validThumbnailHeight = intval($paramThumbnailHeight);
        $sanitizedThumbnailNamePrefix = sanitize_text_field($paramThumbnailNamePrefix);

        if(static::$thumbnailsEnabled && $sanitizedImageName != '')
        {
            $image = wp_get_image_editor($sanitizedUploadDirectory.$sanitizedImageName); // Return an implementation that extends <tt>WP_Image_Editor</tt>

            if(!is_wp_error($image) && $validThumbnailWidth > 0 && $validThumbnailHeight > 0)
            {
                $image->resize($validThumbnailWidth, $validThumbnailHeight, true ); // height, width and crop
                $saved = $image->save($sanitizedUploadDirectory.$sanitizedThumbnailNamePrefix.$sanitizedImageName);
                if(!is_wp_error($saved))
                {
                    $thumbnailCreated = TRUE;
                }
            }
        }

        if(static::$debugMode)
        {
            echo "<br />Thumbnails enabled: ".(static::$thumbnailsEnabled ? "Yes" : "No");
            echo "<br />Thumbnail created: ".($thumbnailCreated ? "Yes" : "No");
        }

        return $thumbnailCreated;
    }


    /**
     * Because copy($moveFolderAndAllFilesInsideFrom, $paramToFolder) - does NOT work in this WordPress setup (because of CHMOD rights),
     * so we need a workaround function - and this is the main reason why we have a function bellow, which DOES WORK!
     * @param string $paramMoveFolderAndAllFilesInsideFrom
     * @param string $paramToFolderWithoutEndSlash
     */
    public static function recurseCopy($paramMoveFolderAndAllFilesInsideFrom, $paramToFolderWithoutEndSlash)
    {
        $sourceDirectory = opendir($paramMoveFolderAndAllFilesInsideFrom);
        while (FALSE !== ( $file = readdir($sourceDirectory)) )
        {
            if(( $file != '.' ) && ( $file != '..' ))
            {
                if( is_dir($paramMoveFolderAndAllFilesInsideFrom.'/'.$file))
                {
                    static::recurseCopy($paramMoveFolderAndAllFilesInsideFrom.'/'.$file, $paramToFolderWithoutEndSlash.'/'.$file);
                } else
                {
                    copy($paramMoveFolderAndAllFilesInsideFrom.'/'.$file, $paramToFolderWithoutEndSlash.'/'.$file);
                }
            }
        }
        closedir($sourceDirectory);
    }

    /**
     * Copy folder and all it's files from it's old location to new location
     * @param string $paramCopyAllFilesFromFolderWithoutEndSlash
     * @param string $paramToFolderWithoutEndSlash
     * @return bool
     */
    public static function copyFolder($paramCopyAllFilesFromFolderWithoutEndSlash, $paramToFolderWithoutEndSlash)
    {
        $copied = FALSE;
        if(file_exists($paramCopyAllFilesFromFolderWithoutEndSlash))
        {
            $toDirectoryIsWritable = static::createWritableDirectory($paramToFolderWithoutEndSlash);
            if($toDirectoryIsWritable)
            {
                // NOTE: copy() does NOT work in this WordPress setup (because of CHMOD rights)
                //$copied = copy($paramCopyAllFilesFromFolderWithoutEndSlash, $paramToFolderWithoutEndSlash);
                static::recurseCopy($paramCopyAllFilesFromFolderWithoutEndSlash, $paramToFolderWithoutEndSlash);
                $copied = TRUE;
            }

            if(static::$debugMode == 2)
            {
                echo "<br />[{$paramCopyAllFilesFromFolderWithoutEndSlash}] SOURCE FOLDER (TO MOVE FILES FROM IT) DO EXISTS, ";
                echo "destination folder is writable: ".var_export($toDirectoryIsWritable, TRUE);
            }
        } else
        {
            if(static::$debugMode == 2)
            {
                echo "<br />[{$paramCopyAllFilesFromFolderWithoutEndSlash}] SOURCE FOLDER (TO MOVE FILES FROM IT) DO NOT EXISTS";
            }
        }

        //die();
        return $copied;
    }


    /**
     * Because rename($moveFolderAndAllFilesInsideFrom, $paramToFolder) - does NOT work in this WordPress setup (because of CHMOD rights),
     * so we need a workaround function - and this is the main reason why we have a function bellow, which DOES WORK!
     * @param string $paramMoveFolderAndAllFilesInsideFrom
     * @param string $paramToFolderWithoutEndSlash
     */
    public static function recurseRename($paramMoveFolderAndAllFilesInsideFrom, $paramToFolderWithoutEndSlash)
    {
        $sourceDirectory = opendir($paramMoveFolderAndAllFilesInsideFrom);
        while (FALSE !== ( $file = readdir($sourceDirectory)) )
        {
            if(( $file != '.' ) && ( $file != '..' ))
            {
                if( is_dir($paramMoveFolderAndAllFilesInsideFrom.'/'.$file))
                {
                    static::recurseRename($paramMoveFolderAndAllFilesInsideFrom.'/'.$file, $paramToFolderWithoutEndSlash.'/'.$file);
                } else
                {
                    rename($paramMoveFolderAndAllFilesInsideFrom.'/'.$file, $paramToFolderWithoutEndSlash.'/'.$file);
                }
            }
        }
        closedir($sourceDirectory);
    }

    /**
     * Rename folder and all it's files from it's old location to new location
     * @param $paramFromFolderWithoutEndSlash
     * @param $paramToFolderWithoutEndSlash
     * @return bool
     */
    public static function renameFolder($paramFromFolderWithoutEndSlash, $paramToFolderWithoutEndSlash)
    {
        $renamed = FALSE;
        if(file_exists($paramFromFolderWithoutEndSlash))
        {
            $toDirectoryIsWritable = static::createWritableDirectory($paramToFolderWithoutEndSlash);
            if($toDirectoryIsWritable)
            {
                // NOTE: rename() does NOT work in this WordPress setup (because of CHMOD rights)
                //$renamed = rename($paramFromFolderWithoutEndSlash, $paramToFolderWithoutEndSlash);
                static::recurseRename($paramFromFolderWithoutEndSlash, $paramToFolderWithoutEndSlash);
                $renamed = TRUE;

                // Remove old folder
                rmdir($paramFromFolderWithoutEndSlash);
            }

            if(static::$debugMode == 2)
            {
                echo "<br />[{$paramFromFolderWithoutEndSlash}] SOURCE FOLDER (TO MOVE FILES FROM IT) DO EXISTS, ";
                echo "destination folder is writable: ".var_export($toDirectoryIsWritable, TRUE);
            }
        } else
        {
            if(static::$debugMode == 2)
            {
                echo "<br />[{$paramFromFolderWithoutEndSlash}] SOURCE FOLDER (TO MOVE FILES FROM IT) DO NOT EXISTS";
            }
        }

        //die();
        return $renamed;
    }

    /**
     * Get files list for specific extension in specified directory
     * @param string $paramPath
     * @param array $paramAllowedFileExtensions
     * @return array
     */
    public static function getFolderFileList($paramPath, array $paramAllowedFileExtensions)
    {
        $retFiles = array();
        $validPath = sanitize_text_field($paramPath);
        $sanitizedAllowedFileExtensions = array_map('sanitize_text_field', $paramAllowedFileExtensions);
        $dirFiles = array_diff(scandir($validPath), array('..', '.'));

        foreach($dirFiles AS $dirFile)
        {
            $ext = strtolower(pathinfo($validPath.$dirFile, PATHINFO_EXTENSION));
            // Case-insensitive check
            if(sizeof($sanitizedAllowedFileExtensions) == 0 || in_array($ext, $sanitizedAllowedFileExtensions))
            {
                $retFiles[] = $dirFile;
            }
        }

        return $retFiles;
    }

    /**
     * Get folder list in specified directory
     * @param $paramPath
     * @return array
     */
    public static function getFolderList($paramPath)
    {
        $retFolders = array();
        $validPath = sanitize_text_field($paramPath);
        $dirFolders = array_diff(scandir($validPath), array('..', '.'));

        foreach($dirFolders AS $dirFolder)
        {
            if(is_dir($paramPath.$dirFolder))
            {
                $retFolders[] = $dirFolder;
            }
        }

        return $retFolders;
    }

    /**
     * Get supported classes
     * @param string $paramIdentifier
     * @param string $paramFilePath
     * @param array $paramPhpFileList
     * @return array
     */
    public static function getSupportedClassesFromPhpFileList($paramIdentifier, $paramFilePath, array $paramPhpFileList)
    {
        $retSupportedClasses = array();
        $sanitizedFilePath = sanitize_text_field($paramFilePath);
        $sanitizedIdentifier = sanitize_text_field($paramIdentifier);
        foreach($paramPhpFileList AS $paramPhpFile)
        {
            $sanitizedPhpFile = sanitize_file_name($paramPhpFile);
            $className = pathinfo($sanitizedPhpFile, PATHINFO_FILENAME); // returns file name (without extension)
            if(is_readable($sanitizedFilePath.$sanitizedPhpFile))
            {
                include_once ($sanitizedFilePath.$sanitizedPhpFile);
                // Add to supported class list only if that class has a constant, which names matches the identifier and that constant is set to true
                if(class_exists($className) &&
                    defined($className.'::'.$sanitizedIdentifier) && constant($className.'::'.$sanitizedIdentifier) === TRUE
                ) {
                    $retSupportedClasses[] = array(
                        "file_path" => $sanitizedFilePath,
                        "file_name" => $sanitizedPhpFile,
                        "class_name" => $className,
                    );
                }
            }
        }

        return $retSupportedClasses;
    }
}