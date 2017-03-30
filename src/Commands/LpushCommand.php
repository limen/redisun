<?php
namespace Limen\RedModel\Commands;

class LpushCommand extends Command
{
    public function getScript()
    {
        $elementsPart = $this->joinArguments();
        $luaSetTtl = $this->luaSetTtl($this->getTtl());

        $setTtl = $luaSetTtl ? 1 : 0;

        $script = <<<LUA
    local values = {}; 
    local setTtl = '$setTtl';
    for i,v in ipairs(KEYS) do
        local members = redis.pcall('lrange', v, 0, -1);
        local len = #members;
        local rs = redis.pcall('lpush',v,$elementsPart);
        if rs and len>0 then
            redis.pcall('ltrim', v, 0, -1 - len);
        end
        if setTtl=='1' then
            $luaSetTtl
        end
        values[#values+1] = rs;
    end 
    return {KEYS,values};
LUA;
        return $script;
    }

}