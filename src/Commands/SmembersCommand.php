<?php
namespace Limen\Redisun\Commands;

class SmembersCommand extends Command
{
    /**
     * @return string
     */
    public function getScript()
    {
        return <<<LUA
local values = {}; 
for i,v in ipairs(KEYS) do 
    values[#values+1] = redis.call('smembers',v); 
end 
return {KEYS,values};
LUA;
    }
}
