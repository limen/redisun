<?php
namespace Limen\Redisun\Examples;

class HashModel extends BaseModel
{
    protected $key = 'redisun:{id}:hash';

    protected $type = 'hash';
}