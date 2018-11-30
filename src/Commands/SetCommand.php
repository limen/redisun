<?php
namespace Limen\Redisun\Commands;

class SetCommand extends Command
{
    public function getScript()
    {
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? '1' : '0';
        $checkScript = $this->existenceScript;

        $script = <<<LUA
$checkScript
local values = {}; 
for i,v in ipairs(KEYS) do
    local ttl = redis.call('ttl', v);
    local setTtl = $setTtl;
    values[#values+1] = redis.call('set',v,ARGV[1]);
    if setTtl == 1 then
        $luaSetTtl
    elseif ttl >= 0 then
        redis.call('expire', v, ttl);
    end
end 
return {KEYS,values};
LUA;
        return $script;
    }

}
