<?php
/*
 * This file is part of the Redisun package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\Redisun\Commands;

/**
 * Command for "hgetall"
 * Class HgetallCommand
 * @package Limen\Redisun\Commands
 */
class HgetallCommand extends Command
{
    public function getScript()
    {
        $script = <<<LUA
    local values = {}; 
    for i,v in ipairs(KEYS) do 
        values[#values+1] = redis.call('hgetall',v); 
    end 
    return {KEYS,values};
LUA;
        return $script;
    }
}
