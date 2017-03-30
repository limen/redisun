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

class SaddCommand extends Command
{
    public function getScript()
    {
        $elementsPart = $this->joinArguments();

        $tmpKey = $this->getTmpKey();

        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;

        $script = <<<LUA
    local values = {};
    local setTtl = '$setTtl';
    local rs1 = redis.pcall('sadd', '$tmpKey', $elementsPart);
    for i,v in ipairs(KEYS) do
        if rs1 then
            local ttl = redis.pcall('ttl', v);
            local rs2 = redis.pcall('sunionstore', v, '$tmpKey', '$tmpKey')
            if setTtl=='1' then
                $luaSetTtl
            elseif ttl >= 0 then
                redis.pcall('expire', v, ttl);
            end
            values[#values+1] = rs2;
        else
            values[#values+1] = false;
        end
    end 
    redis.pcall('del', '$tmpKey')
    return {KEYS,values};
LUA;
        return $script;
    }

}
