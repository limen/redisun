<?php
namespace Limen\Redisun\Examples;

class StringModel extends BaseModel
{
    protected $key = 'redisun:{id}:string:{name}';

    protected $type = 'string';

    protected $sortable = true;

    protected function compare($a, $b)
    {
        if ($a > $b) {
            return 1;
        } elseif ($a < $b) {
            return -1;
        } else {
            return 0;
        }
    }
}