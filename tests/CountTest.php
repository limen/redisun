<?php

use Limen\Redisun\Examples\StringModel;
use PHPUnit\Framework\TestCase;

class CountTest extends TestCase
{
    public function testCount()
    {
        $model = new StringModel();
        $model->insert(['id' => 1, 'name' => 'hello'], 'world1', 60);
        $model->insert(['id' => 2, 'name' => 'hello'], 'world2', 70);
        $model->insert(['id' => 3, 'name' => 'hello'], 'world3', 80);
        $cnt1 = $model->whereIn('id', ['1','2','3'])->count();
        $cnt2 = $model->where('name', 'hello')->count();
        $this->assertEquals($cnt1, 3);
        $this->assertEquals($cnt2, 3);
    }
}