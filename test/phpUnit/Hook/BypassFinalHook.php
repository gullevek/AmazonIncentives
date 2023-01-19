<?php

// strip the final name from a to be mocked class

declare(strict_types=1);

namespace test\phpUnit\Hook;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeFirstTestHook;

// only works if it is the FIRST load and not before EACH test
final class BypassFinalHook implements BeforeFirstTestHook
{
    public function executeBeforeFirstTest(): void
    {
        BypassFinals::enable();
    }
}

// __END__
