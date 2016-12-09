<?php
/**
 * @author LI Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/12/6 15:54
 */

namespace Limen\RedModel;

use Predis\Client as RedisClient;

/**
 * CRUD model for redis
 * Class Model
 * @package Limen\RedModel
 */
abstract class Model
{
    const TYPE_STRING = 'string';
    const TYPE_SET = 'set';
    const TYPE_SORTED_SET = 'zset';
    const TYPE_LIST = 'list';
    const TYPE_HASH = 'hash';

    /**
     * Redis data type
     * @var string
     * Could be string, list, set, zset, hash
     */
    protected $type;

    /**
     * Redis key representation.
     * users:{id}:phone e.g.
     * @var string
     */
    protected $key;

    /**
     * Primary key name like database
     * @var string
     */
    protected $primaryKeyName = 'id';

    /**
     * @var string
     */
    protected $bindingWrapper = '{?}';

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Push method for list type
     * @var string
     */
    protected $listPushMethod = 'rpush';

    /**
     * @var RedisClient
     */
    protected $redClient;

    private $queryBuilderMethods = [
        'getBuiltKeys',
        'firstBuiltKey',
        'lastBuiltKey',
    ];

    public function __construct($parameters = null, $options = null)
    {
        $this->initRedisClient($parameters, $options);
        $this->initQueryBuilder($this->key, $this->bindingWrapper);
    }

    protected function initRedisClient($parameters, $options)
    {
        $this->redClient = new RedisClient($parameters, $options);
    }

    protected function initQueryBuilder($key, $bindingWrapper)
    {
        $this->queryBuilder = new QueryBuilder($key, $bindingWrapper);
    }

    /**
     * Refresh query builder
     * @return $this
     */
    public function newQuery()
    {
        $this->freshQueryBuilder();

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @return string
     */
    public function geyPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }

    /**
     * Query like database
     * The {$bindingKey} part in the key representation would be replace by $value
     * @param $bindingKey string
     * @param $value string
     * @return $this
     */
    public function where($bindingKey, $value)
    {
        $this->queryBuilder->where($bindingKey, $value);

        return $this;
    }

    /**
     * @param $bindingKey
     * @param array $values
     * @return $this
     */
    public function whereIn($bindingKey, array $values)
    {
        $this->queryBuilder->whereIn($bindingKey, $values);

        return $this;
    }

    /**
     * Retrieve items
     * @return array
     */
    public function get()
    {
        $queryKeys = $this->queryBuilder->getBuiltKeys();

        if ($queryKeys) {
            return $this->getProxy($queryKeys);
        }

        return [];
    }

    /**
     * Retrieve first item
     * @return mixed|null
     */
    public function first()
    {
        $queryKey = $this->queryBuilder->firstBuiltKey();

        if ($queryKey) {
            list($method, $params) = $this->getFindMethodAndParameters();
            array_unshift($params, $queryKey);
            return call_user_func_array([$this->redClient, $method], $params);
        }

        return null;
    }

    /**
     * Create an item
     * @param $id int|string Primary key
     * @param $value mixed
     * @param bool $force if true the exists item would be replaced
     * @return bool
     */
    public function create($id, $value, $force = true)
    {
        $queryKey = $this->where($this->primaryKeyName, $id)->firstBuiltKey();

        if ($queryKey === null) {
            return false;
        }

        if ($force === false && $this->redClient->exists($queryKey)) {
            return false;
        }

        return $this->insertProxy($queryKey, $value);
    }

    /**
     * @param array $bindings
     * @param $value
     * @param bool $force
     * @return mixed
     */
    public function insert(array $bindings, $value, $force = true)
    {
        $this->freshQueryBuilder();

        foreach ($bindings as $k => $v) {
            $this->where($k, $v);
        }
        $queryKey = $this->queryBuilder->firstBuiltKey();

        if ($queryKey === null) {
            return false;
        }

        if ($force === false && $this->redClient->exists($queryKey)) {
            return false;
        }

        return $this->insertProxy($queryKey, $value);
    }

    /**
     * find an item
     * @param $id int|string Primary key
     * @return mixed
     */
    public function find($id)
    {
        $this->freshQueryBuilder()->where($this->primaryKeyName, $id);

        $queryKey = $this->queryBuilder->firstBuiltKey();

        if ($queryKey === null) {
            return null;
        }

        list($method, $parameters) = $this->getFindMethodAndParameters();

        array_unshift($parameters, $queryKey);
        $value = call_user_func_array([$this->redClient, $method], $parameters);

        return $value;
    }

    /**
     * Update items, need to use where() first
     * @param $value
     * @return mixed
     */
    public function update($value)
    {
        $queryKeys = $this->queryBuilder->getBuiltKeys();

        if (count($queryKeys) === 1) {
            return $this->updateProxy($queryKeys[0], $value);
        } elseif (count($queryKeys) > 1) {
            return $this->updateBatchProxy($queryKeys, $value);
        }

        return false;
    }

    /**
     * Delete items
     * @return bool|int
     */
    public function delete()
    {
        $queryKeys = $this->queryBuilder->getBuiltKeys();

        if (count($queryKeys) > 0) {
            return $this->redClient->del($queryKeys);
        }

        return false;
    }

    /**
     * Destroy item
     * @param string $id primary key
     * @return bool
     */
    public function destroy($id)
    {
        $queryKey = $this->freshQueryBuilder()->where($this->primaryKeyName, $id)->firstBuiltKey();

        if ($queryKey === null) {
            return false;
        }

        return (bool)$this->redClient->del([$queryKey]);
    }

    /**
     * @param array $ids primary keys
     * @return array
     */
    public function findBatch(array $ids)
    {
        $queryKeys = $this->freshQueryBuilder()->whereIn($this->primaryKeyName, $ids)->getBuiltKeys();

        if (count($queryKeys) === 0) {
            return [];
        }

        return $this->getProxy($queryKeys);
    }

    /**
     * @param array $ids primary keys
     * @return int
     */
    public function destroyBatch(array $ids)
    {
        $queryKeys = $this->freshQueryBuilder()->whereIn($this->primaryKeyName, $ids)->getBuiltKeys();

        if ($queryKeys) {
            return $this->redClient->del($queryKeys);
        }

        return false;
    }

    /**
     * @param array $ids primary keys
     * @param $value
     * @return mixed
     */
    public function updateBatch(array $ids, $value)
    {
        $queryKeys = $this->freshQueryBuilder()->whereIn($this->primaryKeyName, $ids)->getBuiltKeys();

        if (count($queryKeys) === 0) {
            return false;
        }

        return $this->updateBatchProxy($queryKeys, $value);
    }

    public function __call($method, $parameters = [])
    {
        if (in_array($method, $this->queryBuilderMethods)) {
            return call_user_func_array([$this->queryBuilder, $method], $parameters);
        }

        $keys = $this->queryBuilder->getBuiltKeys();

        if (count($keys) > 1) {
            throw new \Exception('More than one key had been built and redis built-in method "' . $method . '" dont support batch operation.');
        } elseif (count($keys) === 0) {
            throw new \Exception('No query keys had been built, need to use where() first.');
        }

        array_unshift($parameters, $keys[0]);
        return call_user_func_array([$this->redClient, $method], $parameters);
    }

    /**
     * Update proxy
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function updateProxy($key, $value)
    {
        return $this->updateBatchProxy([$key], $value);
    }

    protected function insertProxy($key, $value)
    {
        $method = $this->getUpdateMethod();

        if (!$method) {
            return false;
        }

        $value = $this->castValueForUpdate($value);

        $this->redClient->multi();
        $this->redClient->del($key);

        call_user_func_array([$this->redClient, $method], [$key, $value]);

        $execData = $this->redClient->exec();

        return (bool)$execData[count($execData) - 1];

    }

    /**
     * @param $keys
     * @param $value
     * @return bool
     */
    protected function updateBatchProxy($keys, $value)
    {
        $method = $this->getUpdateMethod();

        if (empty($method)) {
            return false;
        }

        $value = $this->castValueForUpdate($value);

        $this->redClient->multi();

        if ($this->type == static::TYPE_LIST
            || $this->type == static::TYPE_SET
            || $this->type == static::TYPE_SORTED_SET
        ) {
            $this->redClient->del($keys);
        }

        foreach ($keys as $key) {
            $this->redClient->$method($key, $value);
        }

        $execData = $this->redClient->exec();

        return $execData[count($execData) - 1];
    }

    /**
     * @param $keys
     * @return array
     */
    protected function getProxy($keys)
    {
        list($method, $params) = $this->getFindMethodAndParameters();

        $this->redClient->multi();

        foreach ($keys as $key) {
            array_unshift($params, $key);
            call_user_func_array([$this->redClient, $method], $params);
            array_shift($params);
        }

        $rawData = $this->redClient->exec();

        if ($this->type == static::TYPE_HASH) {
            $rawData = $this->rawHashToAssocArray($rawData);
        }

        return $rawData;
    }

    /**
     * @return $this
     */
    protected function freshQueryBuilder()
    {
        $this->queryBuilder = new QueryBuilder($this->key, $this->bindingWrapper);

        return $this;
    }

    protected function getUpdateMethod()
    {
        $method = '';
        switch ($this->type) {
            case 'string':
                $method = 'set';
                break;
            case 'list':
                $method = $this->listPushMethod;
                break;
            case 'set':
                $method = 'sadd';
                break;
            case 'zset':
                $method = 'zadd';
                break;
            case 'hash':
                $method = 'hmset';
                break;
            default:
                break;
        }

        return $method;
    }

    protected function castValueForUpdate($value)
    {
        switch ($this->type) {
            case 'string':
                $value = (string)$value;
                break;
            case 'list':
            case 'set':
            case 'zset':
            case 'hash':
                $value = (array)$value;
                break;
            default:
                break;
        }

        return $value;
    }

    /**
     * Get find method and default parameters according to redis data type.
     * @return array
     */
    protected function getFindMethodAndParameters()
    {
        $method = '';
        $parameters = [];

        switch ($this->type) {
            case 'string':
                $method = 'get';
                break;
            case 'list':
                $method = 'lrange';
                $parameters = [0, -1];
                break;
            case 'set':
                $method = 'smembers';
                break;
            case 'zset':
                $method = 'zrange';
                $parameters = [0, -1];
                break;
            case 'hash':
                $method = 'hgetall';
                break;
            default:
                break;
        }

        return [$method, $parameters];
    }

    /**
     * raw hash data to associate array
     * @param array $hashes
     * @return array
     */
    private function rawHashToAssocArray(array $hashes)
    {
        $assoc = [];

        foreach ($hashes as $hash) {
            $item = [];
            for ($i = 0; $i < count($hash); $i = $i + 2) {
                $item[$hash[$i]] = $hash[$i + 1];
            }
            if ($item) {
                $assoc[] = $item;
            }
        }

        return $assoc;
    }
}