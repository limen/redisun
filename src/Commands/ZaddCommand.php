<?php
namespace Limen\Redisun\Commands;

class ZaddCommand extends Command
{
    public function getScript()
    {
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;
        $checkScript = $this->existenceScript;
        $delScript = $this->deleteScript;

        $script = <<<LUA
$checkScript
local values = {};
local setTtl = '$setTtl';
for i,v in ipairs(KEYS) do
    local ttl = redis.call('ttl', v)
    $delScript
    local rs1
    local j=1
    while j<#ARGV do
        rs1=redis.call('zadd',v,ARGV[j],ARGV[j+1]);
        j=j+2
    end
    if rs1 then
        if setTtl=='1' then
            $luaSetTtl
        elseif ttl > 0 then
            redis.call('expire', v, ttl)
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
