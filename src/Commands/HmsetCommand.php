<?php
/*
 * This file is part of the Redmodel package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\RedModel\Commands;

class HmsetCommand extends Command
{
    public function getScript()
    {
        $argString = $this->joinArguments();
        $luaSetTtl = $this->luaSetTtl($this->getTtl());
        $setTtl = $luaSetTtl ? 1 : 0;
        $checkExist = $this->existenceScript;
        $delScript = $this->deleteScript;

        $script = <<<LUA
$checkExist
local values = {}; 
local setTtl = '$setTtl';
for i,v in ipairs(KEYS) do 
    $delScript
    values[#values+1] = redis.pcall('hmset',v, $argString); 
    if setTtl == '1' then
        $luaSetTtl
    end
end
return {KEYS,values};
LUA;
        return $script;
    }
}