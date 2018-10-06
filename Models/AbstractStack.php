<?php
/**
 * Message stack
 * Abstract class must be inherited later.
 * Note: Is abstract, because message stacks can be filled by the child class only
 * @package ExpandableFAQ
 * @author KestutisIT
 * @copyright KestutisIT
 * @license MIT License. See Legal/License.txt for details.
 */
namespace ExpandableFAQ\Models;

abstract class AbstractStack
{
    protected $debugMessages = array();
    protected $okayMessages = array();
    protected $errorMessages = array();

    public function flushMessages()
    {
        $this->debugMessages = array();
        $this->okayMessages = array();
        $this->errorMessages = array();
    }

    public function getAllMessages()
    {
        return array(
            'debug' => $this->debugMessages,
            'okay' => $this->okayMessages,
            'error' => $this->errorMessages,
        );
    }

    public function getDebugMessages()
    {
        return $this->debugMessages;
    }

    public function getOkayMessages()
    {
        return $this->okayMessages;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }
}