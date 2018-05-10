<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[SetupDatabase::class])) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->setUpDatabase();
        }
    }
}
