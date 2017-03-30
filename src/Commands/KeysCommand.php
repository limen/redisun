<?php
namespace Limen\RedModel\Commands;

class KeysCommand extends Command
{
    public function getScript()
    {
        $script = <<<LUA
    local values = {}; 
    for i,v in ipairs(KEYS) do 
        values[#values+1] = redis.pcall('keys',v); 
    end 
    return values;
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