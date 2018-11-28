<?php
/**
 * AutoLoader to load classes for NS plugin
 * @note: Do not use static:: in this class, as it is maximum backwards compatible class for version check,
 *   and should work on Php 5.2, or even 5.0. All other classes can support Php 5.3+ or so.
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Load;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;

final class AutoLoad
{
    // Because loading of language text is not allowed in the abstract controller level, we use constants to simulate language text behavior, just the text is English
    const LANG_ERROR_UNABLE_TO_LOAD_CLASS_TEXT = 'Unable to load \'%s\' class/interface/trait from plugin root folder with \'%s\' path provided.';
    // NOTE: This is stage 2 class, so the NULL won't be passed here, as that error would be caught in main controller
    private $confWithoutRouting = NULL;
    private $debugMode = 0; // 0 - off, 1 - regular, 2 - deep
    // NOTE: Make sure that debug is set to '1' or '2' and 'debug.log' file is writable in plugin's folder
    private $debugLog = FALSE; // TRUE / FALSE

    public function __construct(ConfigurationInterface &$paramConfWithoutRouting)
    {
        $this->confWithoutRouting = $paramConfWithoutRouting;

        // DEBUG
        if($this->debugMode == 2)
        {
            $output = "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": AutoLoader] Class created for \'".ConfigurationInterface::PLUGIN_NAMESPACE."\' namespace\n";
            echo $output;
            if($this->debugLog === TRUE)
            {
                $this->logToFile($output);
            }
        }
    }

    /**
     * Load the model, view or controller from plugin folder (normal or test)
     * @param $paramClassInterfaceOrTrait
     * @return bool
     * @throws \Exception
     */
    public function includeClassFile($paramClassInterfaceOrTrait)
    {
        // DEBUG
        if($this->debugMode == 2)
        {
            $output = "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": AutoLoader] Call for {$paramClassInterfaceOrTrait}\n";
            echo $output;
            if($this->debugLog === TRUE)
            {
                $this->logToFile($output);
            }
        }

        $backslashed = ConfigurationInterface::PLUGIN_NAMESPACE.'\\';
        if (substr($paramClassInterfaceOrTrait, 0, strlen($backslashed)) !== $backslashed)
        {
            /* If the class does not lie under the "<PLUGIN_NAMESPACE>" namespace,
             * then we can exit immediately.
             */
            return FALSE;
        }

        // Otherwise - process further
        // IMPORTANT NOTE: WordPress constants like:
        //      'ABSPATH', 'UPLOADS', 'WP_PLUGIN_URL', 'WP_PLUGIN_DIR', 'WP_CONTENT_URL', 'WP_CONTENT_DIR'
        //      should not be used directly by the plugin or theme,
        //      because in situation when i.e. we use 'wordpress-develop' as external library, the path will be wrong
        $wpPluginsPath = $this->confWithoutRouting->getWP_PluginsPath();
        $relativeFolderPathAndFileName = $this->getLocalPathAndFileNameFromNamespaceAndClass($paramClassInterfaceOrTrait);

        // DEBUG
        if($this->debugMode == 1)
        {
            $output = "<br />\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] '{$paramClassInterfaceOrTrait}' class/interface/trait is called.\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] It's relative path and file name: '{$relativeFolderPathAndFileName}'\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] Full path with file name: '{$wpPluginsPath}{$relativeFolderPathAndFileName}'\n";
            echo $output;
            if($this->debugLog === TRUE)
            {
                $this->logToFile($output);
            }
        }

        // Get a path if class exist, or raise and exception otherwise
        if(is_readable($wpPluginsPath.$relativeFolderPathAndFileName))
        {
            // Check for main folder in local plugin folder
            // It's a regular class / interface
            require_once $wpPluginsPath.$relativeFolderPathAndFileName;
            return TRUE;
        } else
        {
            // File do not exist or is not readable
            $validClassOrInterface = sanitize_text_field($paramClassInterfaceOrTrait);
            throw new \Exception(sprintf(
                static::LANG_ERROR_UNABLE_TO_LOAD_CLASS_TEXT,
                $validClassOrInterface, $wpPluginsPath.$relativeFolderPathAndFileName
            ));
        }
    }

    /**
     * Example:
     *   Org class name: <PLUGIN_FOLDER>\Models\Style\StylesObserver
     *   Class name: StylesObserver
     *   File name: <PLUGIN_FOLDER>\Models\Style\StylesObserver.php
     * @param $paramClassInterfaceOrTrait - a namespace
     * @return string
     */
    private function getLocalPathAndFileNameFromNamespaceAndClass($paramClassInterfaceOrTrait)
    {
        $validClassInterfaceOrTrait = sanitize_text_field($paramClassInterfaceOrTrait);

        // Set defaults
        $localPath = "";
        $classInterfaceOrTraitName = "";
        $extractedNamespace = "";

        // Remove backslash ('\') character from the beginning of a string
        $trimmedClassInterfaceOrTrait = ltrim($validClassInterfaceOrTrait, '\\');
        // Find namespace length
        $elementStartPos = strripos($trimmedClassInterfaceOrTrait, '\\');
        // 1. If namespace is found in class / interface / trait name
        if ($elementStartPos !== FALSE)
        {
            // 2. Then separate namespace and class name

            // Get namespace from class, interface or trait
            $extractedNamespace = substr($trimmedClassInterfaceOrTrait, 0, $elementStartPos);

            // Remove the top-level plugin namespace, as it will come already from plugin folder
            // NOTE: Namespaces are always only backslashed
            $tmpPath = str_replace(
                ConfigurationInterface::PLUGIN_NAMESPACE.'\\',
                $this->confWithoutRouting->getPluginFolderName().'\\', $extractedNamespace
            );

            // NOTE: This is ok, as namespaces are always only backslashed
            $localPath = str_replace('\\', DIRECTORY_SEPARATOR, $tmpPath).DIRECTORY_SEPARATOR;

            $classInterfaceOrTraitName = substr($trimmedClassInterfaceOrTrait, $elementStartPos + 1);
        }

        // Suffix the class file name with '.php' extension
        $fileName = $classInterfaceOrTraitName.'.php';

        // DEBUG
        if($this->debugMode == 2)
        {
            $output = "<br />\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] Plugin namespace: '".ConfigurationInterface::PLUGIN_NAMESPACE."'\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] Plugin folder name: '{$this->confWithoutRouting->getPluginFolderName()}'\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] Extracted namespace: '{$extractedNamespace}'\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] Org. Class/Interface/Trait: '{$validClassInterfaceOrTrait}'\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] Class/Interface/Trait name: '{$classInterfaceOrTraitName}'\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] Local path: '{$localPath}'\n";
            $output .= "<br />[".ConfigurationInterface::PLUGIN_NAMESPACE.": Autoloader] File name: '{$fileName}'\n";
            echo $output;
            if($this->debugLog === TRUE)
            {
                $this->logToFile($output);
            }
        }

        return $localPath.$fileName;
    }

    private function logToFile($trustedOutput)
    {
        // NOTE: We do not perform 'is_writable' check as that file may not yet exist & we do want to
        //          reduce failure possibilities
        file_put_contents(
            $this->confWithoutRouting->getPluginPath().DIRECTORY_SEPARATOR.'debug.log',
            $trustedOutput, FILE_APPEND
        );
    }
}