<?php
namespace Joselfonseca\LighthouseGraphQLPassport\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Joselfonseca\LighthouseGraphQLPassport\Providers\LighthouseGraphQLPassportServiceProvider'];
    }

    public function test_assert_true()
    {
        $this->assertTrue(true);
    }
}