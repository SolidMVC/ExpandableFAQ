<?php
/**
 * Demo import manager

 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models\Import;
use ExpandableFAQ\Models\Configuration\ConfigurationInterface;
use ExpandableFAQ\Models\File\StaticFile;
use ExpandableFAQ\Models\PrimitiveObserverInterface;
use ExpandableFAQ\Models\Validation\StaticValidator;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class DemosObserver implements PrimitiveObserverInterface
{
    private $conf             = NULL;
    private $lang             = NULL;
    private $debugMode        = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->conf = $paramConf;
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? TRUE : FALSE);
    }

    /**
     * Get importable demos in this plugin
     * @return array
     */
    private function getAll()
    {
        $extDemosPath = $this->conf->getRouting()->getSQLsPath('', FALSE);
        $phpFiles = array();
        if(is_dir($extDemosPath))
        {
            // Get PHP folder file list
            $tmpPhpFiles = StaticFile::getFolderFileList($extDemosPath, array("php"));
            $tmpFiles = array();
            foreach ($tmpPhpFiles AS $tmpPhpFile)
            {
                if(!in_array($tmpPhpFile, $tmpFiles))
                {
                    $tmpFiles[] = $tmpPhpFile;
                    $phpFiles[] = array(
                        "file_path" => $extDemosPath,
                        "file_name" => $tmpPhpFile,
                    );
                }
            }
        }

        $retDemos = array();
        foreach ($phpFiles AS $phpFile)
        {
            // Case-insensitive check - Find the position of the last occurrence of a case-insensitive substring in a string
            $firstPhpDemoPos = stripos($phpFile['file_name'], "DemoSQL");
            $lastPhpPos = strripos($phpFile['file_name'], ".php");
            $requiredPhpPos = strlen($phpFile['file_name']) - strlen(".php");
            $phpDemoData = array();
            if($firstPhpDemoPos !== FALSE && $lastPhpPos === $requiredPhpPos)
            {
                $phpDemoData = get_file_data($phpFile['file_path'].$phpFile['file_name'], array('DemoUID' => 'Demo UID', 'DemoName' => 'Demo Name', 'DemoEnabled' => 'Demo Enabled'));

                // Format data
                $validDemoId = intval($phpDemoData['DemoUID']);
                $validDemoName = sanitize_text_field($phpDemoData['DemoName']);
                $validDemoEnabled = intval($phpDemoData['DemoEnabled']);
                $validFilePath = sanitize_text_field($phpFile['file_path']);
                $validFileName = sanitize_file_name($phpFile['file_name']);
                $validFileNameWithPath = $validFilePath . $validFileName;

                $retDemos[] = array(
                    "demo_id" => $validDemoId,
                    "demo_name" => $validDemoName,
                    "demo_enabled" => $validDemoEnabled,
                    "file_path" => $validFilePath,
                    "file_name" => $validFileName,
                    "file_name_with_path" => $validFileNameWithPath,
                );
            }

            // DEBUG
            if($this->debugMode == 2)
            {
                echo "<br /><br />\$phpDemoData: " . nl2br(print_r($phpDemoData, TRUE));
                echo "<br /><br />File: {$phpFile['file_name']}";
                echo "<br />\$firstPhpDemoPos: {$firstPhpDemoPos} === 0";
                echo "<br />\$lastPhpPos: {$lastPhpPos} === \$requiredPhpPos: {$requiredPhpPos}";
            }
        }

        // DEBUG
        if($this->debugMode == 1)
        {
            echo "<br />Php demo files: ".nl2br(print_r($phpFiles, TRUE));
            echo "<br />Demos: ".nl2br(print_r($retDemos, TRUE));
        }

        return $retDemos;
    }

    public function getDropdownOptions($paramSelectedDemoId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "")
    {
        $validDefaultValue = StaticValidator::getValidPositiveInteger($paramDefaultValue, 0);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);

        $retHTML = '';
        if($paramSelectedDemoId == $validDefaultValue)
        {
            $retHTML .= '<option value="'.$validDefaultValue.'" selected="selected">'.$sanitizedDefaultLabel.'</option>';
        } else
        {
            $retHTML .= '<option value="'.$validDefaultValue.'">'.$sanitizedDefaultLabel.'</option>';
        }
        $allDemos = $this->getAll();
        foreach ($allDemos AS $demo)
        {
            if($demo['demo_enabled'] == 1)
            {
                if($demo['demo_id'] == $paramSelectedDemoId)
                {
                    $retHTML .= '<option value="'.$demo['demo_id'].'" selected="selected">'.$demo['demo_name'].'</option>';
                } else
                {
                    $retHTML .= '<option value="'.$demo['demo_id'].'">'.$demo['demo_name'].'</option>';
                }
            }
        }

        return $retHTML;
    }
}