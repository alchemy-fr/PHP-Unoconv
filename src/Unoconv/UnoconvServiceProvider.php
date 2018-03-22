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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;


class UnoconvServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['unoconv.default.configuration'] = array(
            'unoconv.binaries' => array('unoconv'),
            'timeout'          => 120,
        );
        $app['unoconv.configuration'] = array();
        $app['unoconv.logger'] = null;

        $app['unoconv'] = function(Application $app) {
            $app['unoconv.configuration'] = array_replace(
                $app['unoconv.default.configuration'], $app['unoconv.configuration']
            );

            return Unoconv::create($app['unoconv.configuration'], $app['unoconv.logger']);
        };
    }
}
