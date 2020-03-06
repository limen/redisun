<?php

namespace Limen\Redisun\Commands;

/**
 * Count exist keys.
 * Support fuzzy and full keys.
 * For fuzzy key like '*hello*', `keys` command would be used.
 * For full key like 'hello', `exists` command would be used.
 *
 * Class CountCommand
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 *
 * @package Limen\Redisun\Commands
 */
class CountCommand extends Command
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
        cnt = cnt + #ks;
    else
        cnt = cnt + redis.call('exists', v);
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