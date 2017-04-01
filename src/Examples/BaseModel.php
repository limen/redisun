<?php
/*
 * This file is part of the Redmodel package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\RedModel\Examples;

use Limen\RedModel\Model;

/**
 * Class BaseModel
 * @package Limen\RedModel\Examples
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
            $parameters['database'] = 4;
        }

        parent::initRedisClient($parameters, $options);
    }
}