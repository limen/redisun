<?php
namespace Limen\RedModel\Examples;

class ZsetModel extends BaseModel
{
    protected $type = 'zset';

    protected $key = 'redmodel:{id}:zset';
}