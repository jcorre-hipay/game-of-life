<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;

class FeatureContext implements Context
{
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }
}
