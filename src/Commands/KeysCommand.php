<?php
namespace Limen\Redisun\Commands;

class KeysCommand extends Command
{
    public function getScript()
    {
        $script = <<<LUA
    local values={} 
    for i,v in ipairs(KEYS) do 
        if string.find(v,'?')~=nil or string.find(v,'*')~=nil then
            values[#values+1]=redis.call('keys',v) 
        else if redis.call('exists',v)==1 then 
            values[#values+1] = {v}
        end
        end
    end 
    return values
LUA;
        return $script;
    }

    /**
     * @param array $data
     * @return array
     */
    public function parseResponse($data)
    {
        $keys = [];

        if ($data) {
            foreach ($data as $value) {
                $keys = array_merge($keys, $value);
            }
        }

        return array_values($keys);
    }
}
