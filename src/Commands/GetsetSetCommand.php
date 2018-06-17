<?php
namespace Limen\RedModel\Commands;

class GetsetSetCommand extends Command
{
    public function getScript()
    {
        $elementsPart = $this->joinArguments();
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;

        $script = <<<LUA
    local values = {}; 
    local setTtl = $setTtl;
    for i,v in ipairs(KEYS) do 
        local ttl = redis.pcall('ttl', v);
        values[#values+1] = redis.pcall('smembers',v); 
        redis.pcall('del',v);
        redis.pcall('sadd',v,$elementsPart);
        if setTtl == 1 then
            $luaSetTtl
        elseif ttl >= 0 then
            redis.pcall('expire',v,ttl)
        end
    end 
    return {KEYS,values};
LUA;
        return $script;
    }
}