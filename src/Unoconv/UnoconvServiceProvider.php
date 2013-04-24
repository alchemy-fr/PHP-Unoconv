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

use Silex\Application;
use Silex\ServiceProviderInterface;

class UnoconvServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['unoconv.binary'] = null;
        $app['unoconv.logger'] = null;
        $app['unoconv.timeout'] = 0;

        $app['unoconv'] = $app->share(function(Application $app) {

            if ($app['unoconv.logger']) {
                $logger = $app['unoconv.logger'];
            } else {
                $logger = null;
            }

            if (null === $app['unoconv.binary']) {
                return Unoconv::create($logger, array('timeout' => $app['unoconv.timeout']));
            } else {
                return Unoconv::load($app['unoconv.binary'], $logger, array('timeout' => $app['unoconv.timeout']));
            }
        });
    }

    public function boot(Application $app)
    {
    }
}
