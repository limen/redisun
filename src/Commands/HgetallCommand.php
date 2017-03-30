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

/**
 * Command for "hgetall"
 * Class HgetallCommand
 * @package Limen\RedModel\Commands
 */
class HgetallCommand extends Command
{
    public function getScript()
    {
        $script = <<<LUA
    local values = {}; 
    for i,v in ipairs(KEYS) do 
        values[#values+1] = redis.pcall('hgetall',v); 
    end 
    return {KEYS,values};
LUA;
        return $script;
    }
}