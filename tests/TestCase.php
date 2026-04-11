<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        if (! extension_loaded('mongodb')) {
            $this->markTestSkipped('MongoDB PHP extension is required for Laravel feature tests.');
        }

        /** @var Application $app */
        $app = parent::createApplication();

        return $app;
    }
}
