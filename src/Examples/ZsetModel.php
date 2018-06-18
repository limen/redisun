<?php
namespace Limen\Redisun\Examples;

class ZsetModel extends BaseModel
{
    protected $type = 'zset';

    protected $key = 'redisun:{id}:zset';
}