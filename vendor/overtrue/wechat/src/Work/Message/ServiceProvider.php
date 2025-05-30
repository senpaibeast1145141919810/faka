<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Work\Message;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['message'] = function ($app) {
            return new Client($app);
        };

        $app['messenger'] = function ($app) {
            $messenger = new Messenger($app['message']);

            if (is_numeric($app['config']['agent_id'])) {
                $messenger->ofAgent($app['config']['agent_id']);
            }

            return $messenger;
        };
    }
}
