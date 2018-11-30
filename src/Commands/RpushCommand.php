<?php
/*
 * This file is part of the Redisun package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\Redisun\Commands;

class RpushCommand extends Command
{
    public function getScript()
    {
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;
        $checkScript = $this->existenceScript;
        $delScript = $this->deleteScript;

        $script = <<<LUA
$checkScript
local values = {}; 
local setTtl = '$setTtl';
for i,v in ipairs(KEYS) do
    local ttl = redis.call('ttl', v)
    $delScript
    local rs
    for j=1,#ARGV do
        rs=redis.call('rpush',v,ARGV[j]);
    end
    if setTtl=='1' then
        $luaSetTtl
    elseif ttl > 0 then
        redis.call('expire', v, ttl)
    end
    values[#values+1]=rs;
end 
return {KEYS,values};
LUA;
        return $script;
    }

}
