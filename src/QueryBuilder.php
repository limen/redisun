<?php
/*
 * This file is part of the Redmodel package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\RedModel;

/**
 * Build redis keys for model
 * Class QueryBuilder
 * @package Limen\RedModel
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 */
class QueryBuilder
{
    protected $queryKey;

    protected $bindingWrapper;

    protected $builtKeys = [];

    public function __construct($key, $wrapper)
    {
        $this->queryKey = $key;
        $this->bindingWrapper = $wrapper;
    }

    /**
     * @return array
     */
    public function getRawKeys()
    {
        return $this->builtKeys;
    }

    /**
     * Get valid keys have been built
     * @return array
     */
    public function getBuiltKeys()
    {
        $keys = $this->getValidQueryKeys();
        $this->flushBuiltKeys();
        return $keys;
    }

    /**
     * Get first key
     * @return string|null
     */
    public function firstBuiltKey()
    {
        $keys = $this->getValidQueryKeys();
        $this->flushBuiltKeys();
        return $keys ? $keys[0] : null;
    }

    /**
     * @param string $bindingKey
     * @param string $value
     * @return QueryBuilder
     */
    public function where($bindingKey, $value)
    {
        return $this->whereIn($bindingKey, [$value]);
    }

    /**
     * @param $bindingKey string
     * @param string[] $values
     * @return $this
     */
    public function whereIn($bindingKey, array $values)
    {
        if ($this->builtKeys === []) {
            foreach ($values as $value) {
                $this->builtKeys[] = $this->bindValue($this->queryKey, $bindingKey, $value);
            }
        } else {
            $builtKeys = $this->builtKeys;
            $this->builtKeys = [];
            foreach ($builtKeys as $key) {
                foreach ($values as $value) {
                    $this->builtKeys[] = $this->bindValue($key, $bindingKey, $value);
                }
            }
        }

        $this->builtKeys = array_unique(array_values($this->builtKeys));

        return $this;
    }

    /**
     * Flush keys have been built
     */
    public function flushBuiltKeys()
    {
        $this->builtKeys = [];
    }

    /**
     * Get valid query keys
     * @return array
     */
    protected function getValidQueryKeys()
    {
        $invalidPattern = '/' . str_replace('?', '\S+', $this->bindingWrapper) . '/';

        $builtKeys = array_filter($this->builtKeys, function ($key) use ($invalidPattern) {
            return !empty($key) && !preg_match($invalidPattern, $key);
        });

        return $builtKeys;
    }

    /**
     * @param string $queryKey
     * @param string $bindingKey
     * @param string $value
     * @return string
     */
    protected function bindValue($queryKey, $bindingKey, $value)
    {
        $bindingNeedle = $this->getBindingNeedle($bindingKey);

        return str_replace($bindingNeedle, $value, $queryKey);
    }

    /**
     * Get binding needle to replace
     * @param $bindingKey
     * @return mixed
     */
    protected function getBindingNeedle($bindingKey)
    {
        return str_replace('?', $bindingKey, $this->bindingWrapper);
    }

}