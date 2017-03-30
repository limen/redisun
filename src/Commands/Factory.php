<?php
/*
 * This file is part of the Redmodel package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\RedModel\Commands;

class Factory
{
    /**
     * @param string $command redis command in lower case
     * @param array $keys
     * @param array $args
     * @return Command
     * @throws \Exception
     */
    public static function getCommand($command, $keys = [], $args = [])
    {
        $instance = null;

        $className = __NAMESPACE__ . '\\' . ucfirst($command) . 'Command';

        if (class_exists($className)) {
            $instance = new $className($keys, $args);

            if (! $instance instanceof Command) {
                throw new \Exception("$className is not subclass of " . __NAMESPACE__ . '\\Command');
            }
        } else {
            throw new \Exception("$className not exists");
        }

        return $instance;
    }
}