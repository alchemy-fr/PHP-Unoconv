<?php

/*
 * This file is part of PHP-Unoconv.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unoconv;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;

class UnoconvServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['unoconv.binary'] = null;
        $app['unoconv.logger'] = null;

        if (isset($app['monolog'])) {
            $app['unoconv.logger'] = function() use ($app) {
                return $app['monolog'];
            };
        }

        $app['unoconv'] = $app->share(function(Application $app) {

            if ($app['unoconv.logger']) {
                $logger = $app['unoconv.logger'];
            } elseif (isset($app['monolog'])) {
                $logger = $app['monolog'];
            } else {
                $logger = new Logger('unoconv');
                $logger->pushHandler(new NullHandler());
            }

            if (!$app['unoconv.binary']) {
                return Unoconv::load($logger);
            } else {
                return new Unoconv($app['unoconv.binary'], $logger);
            }
        });
    }

    public function boot(Application $app)
    {

    }
}
