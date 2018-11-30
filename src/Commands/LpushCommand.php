<?php
namespace Limen\Redisun\Commands;

class LpushCommand extends Command
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
    local rs
    for j=1,#ARGV do
        rs=redis.call('lpush',v,ARGV[j]);
    end
    if setTtl=='1' then
        $luaSetTtl
    elseif ttl > 0 then
        redis.call('expire', v, ttl)
    end
    values[#values+1]=rs;
end 
return {KEYS,values};
LUA;
        return $script;
    }

}
