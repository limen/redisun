<?php
namespace Limen\RedModel\Examples;

class HashModel extends BaseModel
{
    protected $key = 'redmodel:{id}:hash';

    protected $type = 'hash';
}