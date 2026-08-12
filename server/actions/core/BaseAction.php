<?php

use Framework\Core\Logger;

class BaseAction
{
    protected $name;

    public function __construct()
    {
        $this->name = get_class($this);
    }
}
