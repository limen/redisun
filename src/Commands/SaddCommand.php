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
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;
        $checkScript = $this->existenceScript;
        $delScript = $this->deleteScript;

        $script = <<<LUA
$checkScript
local values = {};
local setTtl = '$setTtl';
for i,v in ipairs(KEYS) do
    local ttl = redis.pcall('ttl', v)
    $delScript
    local rs1 = redis.pcall('sadd', v, $elementsPart);
    if rs1 then
        if setTtl=='1' then
            $luaSetTtl
        elseif ttl > 0 then
            redis.pcall('expire', v, ttl)
        end
        values[#values+1] = rs1;
    else
        values[#values+1] = nil;
    end
end 
return {KEYS,values};
LUA;
        return $script;
    }

}
