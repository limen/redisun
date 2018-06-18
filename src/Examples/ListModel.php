<?php
namespace Limen\Redisun\Examples;

class ListModel extends BaseModel
{
    protected $type = 'list';

    protected $key = 'redisun:{id}:list';
}