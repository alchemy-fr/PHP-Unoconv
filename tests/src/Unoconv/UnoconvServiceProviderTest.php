<?php

namespace Unoconv;

use Silex\Application;

class UnoconvServiceProvoderTest extends \PHPUnit_Framework_TestCase
{

    private function getApplication()
    {
        return new Application();
    }


    public function testInit()
    {
        $app = $this->getApplication();
        $app->register(new UnoconvServiceProvider());

        $this->assertInstanceOf('\Unoconv\Unoconv', $app['unoconv']);
    }

}

