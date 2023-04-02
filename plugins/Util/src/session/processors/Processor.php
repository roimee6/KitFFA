<?php

namespace Util\session\processors;

use pocketmine\event\Event;
use Util\session\Session;

abstract class Processor
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public abstract function process(Event $ev);
}