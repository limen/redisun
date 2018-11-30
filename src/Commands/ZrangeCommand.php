<?php
namespace Limen\Redisun\Commands;

class ZrangeCommand extends Command
{
    public function getScript()
    {
        return <<<LUA
local values = {}; 
for i,v in ipairs(KEYS) do 
    values[#values+1] = redis.call('zrange', v, 0, -1); 
end 
return {KEYS,values};
LUA;
    }
}
