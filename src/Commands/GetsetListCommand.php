<?php
namespace Limen\Redisun\Commands;

class GetsetListCommand extends Command
{
    public function getScript()
    {
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;

        $script = <<<LUA
    local values = {}; 
    local setTtl = $setTtl;
    for i,v in ipairs(KEYS) do 
        local ttl = redis.call('ttl', v);
        values[#values+1] = redis.call('lrange',v,0,-1); 
        redis.call('del',v);
        for j=1,#ARGV do
            redis.call('rpush',v,ARGV[j]);
        end
        if setTtl == 1 then
            $luaSetTtl
        elseif ttl >= 0 then
            redis.call('expire',v,ttl)
        end
    end 
    return {KEYS,values};
LUA;
        return $script;
    }
}
