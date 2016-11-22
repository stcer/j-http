<?php

namespace j\network\message\command;

use j\network\http\AbstractAction;

/**
 * Class Base
 * @package j\network\message\command
 */
class Base extends AbstractAction {
    protected $accessAllow = '*';
}