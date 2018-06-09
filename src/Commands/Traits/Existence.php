<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/6/9
 */

namespace Limen\RedModel\Commands\Traits;

/**
 * Check key exists or not before create
 *
 * Trait Existence
 * @package Limen\RedModel\Commands\Traits
 */
trait Existence
{
    protected $existenceScript = '';

    public function pleaseExists()
    {
        $this->existenceScript = <<<LUA
for i,v in ipairs(KEYS) do
    local ex = redis.pcall('exists', v);
    if ex==0 then
        return nil
    end
end 
LUA;
        return $this;
    }

    public function pleaseNotExists()
    {
        $this->existenceScript = <<<LUA
for i,v in ipairs(KEYS) do
    local ex = redis.pcall('exists', v);
    if ex==1 then
        return nil
    end
end 
LUA;
        return $this;
    }
}