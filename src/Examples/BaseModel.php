<?php
/*
 * This file is part of the Redisun package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\Redisun\Examples;

use Limen\Redisun\Model;

/**
 * Class BaseModel
 * @package Limen\Redisun\Examples
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 */
class BaseModel extends Model
{
    protected function initRedisClient($parameters, $options)
    {
        if (!isset($parameters['host'])) {
            $parameters['host'] = 'localhost';
        }

        if (!isset($parameters['port'])) {
            $parameters['port'] = 6379;
        }

        if (!isset($parameters['database'])) {
            $parameters['database'] = 0;
        }

        parent::initRedisClient($parameters, $options);
    }
}