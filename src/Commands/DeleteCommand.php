<?php

namespace Limen\Redisun\Commands;

/**
 * Delete keys on the server side.
 * Initially for improving the performance of `keys` then `del` operations,
 * by getting rid of the time consumption on network to return keys to the client.
 *
 * When the key to delete is fuzzy, it would call `keys` command first to get matched keys
 * and then call `del` command to delete them. Otherwise it would call `del` command directly.
 *
 * Class DeleteCommand
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 *
 * @package Limen\Redisun\Commands
 */
class DeleteCommand extends Command
{
    /**
     * Gets the body of a Lua script.
     *
     * @return string
     */
    public function getScript()
    {
        $script = <<<LUA
local cnt = 0; 
for i,v in ipairs(KEYS) do 
    if string.find(v, '*') ~= nil then
        local ks = redis.call('keys', v);
        if #ks > 0 then
            cnt = cnt + redis.call('del', unpack(ks)); 
        end
    else
        cnt = cnt + redis.call('del', v);
    end
end 
return cnt;
LUA;
        return $script;
    }

    public function parseResponse($data)
    {
        return (int)$data;
    }
}