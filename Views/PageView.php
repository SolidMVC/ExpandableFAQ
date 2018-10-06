<?php
/**
 * Template View
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Views;
use ExpandableFAQ\Models\Language\LanguageInterface;

final class PageView
{
    private $vars = array();
    private $debugMode = 0;

    public function __get($name)
    {
        return $this->vars[$name];
    }

    /**
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if($name == 'templateFile')
        {
            if(isset($this->vars['lang']) && $this->vars['lang'] instanceof LanguageInterface)
            {
                $objLang = $this->vars['lang'];
                $exceptionText = $objLang->getText('LANG_TEMPLATE_CANNOT_BIND_VARIABLE_ERROR_TEXT');
            } else
            {
                $exceptionText = 'Cannot bind variable named &#39;templateFile&#39;';
            }
            // NOTE: We do not translate the system messages
            throw new \Exception($exceptionText);
        }
        $this->vars[$name] = $value;
    }

    /**
     * @param $templateFile
     * @return string
     * @throws \Exception
     */
    public function render($templateFile)
    {
        // DEBUG
        if($this->debugMode >= 1)
        {
            echo '<br />Trying to render template:'. $templateFile;
        }

        if(is_readable($templateFile))
        {
            // Extra class variables to use in the template
            extract($this->vars);

            // Start output buffering
            ob_start();

            // Include the template file content
            include $templateFile;

            // Get the output buffer cache content to variable
            $retContent = ob_get_contents();

            // Then clean and disabled the output buffer
            ob_end_clean();


            // DEBUG
            if($this->debugMode >= 1)
            {
                echo '<br />Template file is readable and the template rendered successfully.';
            }
            if($this->debugMode >= 2)
            {
                echo '<br />Template content:<br />'.$retContent;
            }

            return $retContent;
        } else
        {
            if(isset($this->vars['lang']) && $this->vars['lang'] instanceof LanguageInterface)
            {
                $objLang = $this->vars['lang'];
                $exceptionText = $objLang->getText('LANG_TEMPLATE_NOT_EXIST_ERROR_TEXT');
            } else
            {
                $exceptionText = sprintf('Template file %s does not exist.', $templateFile);
            }
            // NOTE: We do not translate the system messages
            throw new \Exception($exceptionText);
        }
    }
}