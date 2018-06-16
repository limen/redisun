<?php
namespace Limen\RedModel\Commands;

class LpushCommand extends Command
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
    $delScript
    local rs = redis.pcall('lpush',v,$elementsPart);
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