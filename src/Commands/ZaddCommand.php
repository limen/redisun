<?php
namespace Limen\RedModel\Commands;

class ZaddCommand extends Command
{
    public function getScript()
    {
        $elementsPart = $this->joinArguments();
        $tmpKey = $this->getTmpKey();
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;
        $checkScript = $this->existenceScript;

        $script = <<<LUA
$checkScript
local values = {};
local setTtl = '$setTtl';
local rs1 = redis.pcall('zadd', '$tmpKey', $elementsPart);
for i,v in ipairs(KEYS) do
    if rs1 then
        local ttl = redis.pcall('ttl', v);
        local rs2 = redis.pcall('zunionstore', v, 2, '$tmpKey', '$tmpKey', 'weights', 0, 1)
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