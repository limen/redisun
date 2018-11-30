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
use \Exception;
use Limen\Redisun\Commands\Traits\Existence;
use Limen\Redisun\Model;
use Predis\Command\ScriptCommand;

/**
 * Lua script command
 *
 * Class Command
 * @package Limen\Redisun\Commands
 */
abstract class Command extends ScriptCommand
{
    use Existence;

    /**
     * Keys to manipulate
     * @var array
     */
    protected $keys;

    /**
     * Additional arguments
     * @var array
     */
    protected $arguments;

    /**
     * Lua script
     * @var string
     */
    protected $script;

    /**
     * Keys ttl in second
     * @var
     */
    protected $ttl;

    /**
     * Command constructor.
     * @param array $keys
     * @param array $args
     */
    public function __construct($keys = [], $args = [])
    {
        $this->keys = $keys;
        $this->arguments = $args;
    }

    public function getArguments()
    {
        return array_merge($this->keys, $this->arguments);
    }

    public function getKeysCount()
    {
        return count($this->keys);
    }

    /**
     * Set keys ttl
     * @param int $seconds
     * @return $this
     */
    public function setTtl($seconds)
    {
        $this->ttl = $seconds;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Resolve data returned from "eval"
     *
     * @param $data
     * @return mixed
     * @throws Exception
     */
    function parseResponse($data)
    {
        if (empty($data)) {
            return [];
        }

        if (isset($data[0]) && count($data[0]) === $this->getKeysCount()) {
            $items = array_combine($data[0], $data[1]);

            return array_filter($items, [$this, 'notNil']);
        }

        throw new Exception('Error when evaluate lua script. Response is: ' . json_encode($data));
    }

    /**
     * @param $item
     * @return bool
     */
    protected function notNil($item)
    {
        return $item !== [] && $item !== null;
    }

    /**
     * @return string
     */
    protected function joinArguments()
    {
        $joined = '';

        for ($i = 1; $i <= count($this->arguments); $i++) {
            $joined .= "ARGV[$i],";
        }

        return rtrim($joined, ',');
    }

    protected function getTmpKey()
    {
        return uniqid('__limen__redisun__' . time() . '__' . rand(1, 1000) . '__');
    }

    protected function luaSetTtl($ttl)
    {
        if (!$ttl) {
            $script = '';
        } elseif ($ttl == Model::TTL_PERSIST) {
            $script = <<<LUA
redis.call('persist', v);
LUA;
        } else {
            $script = <<<LUA
redis.call('expire', v, $ttl);
LUA;
        }

        return $script;
    }
}
