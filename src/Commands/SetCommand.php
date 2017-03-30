<?php
namespace Limen\RedModel\Commands;

class SetCommand extends Command
{
    public function getScript()
    {
        $luaSetTtl = $this->luaSetTtl($this->getTtl());

        $script = <<<LUA
    local values = {}; 
    for i,v in ipairs(KEYS) do
        values[#values+1] = redis.pcall('set',v,ARGV[1]);
        $luaSetTtl
    end 
    return {KEYS,values};
LUA;
        return $script;
    }

}