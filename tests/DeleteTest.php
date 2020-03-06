<?php

use Limen\Redisun\Examples\StringModel;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
    public function testDelete()
    {
        $model = new StringModel();
        $model->insert(['id' => 1, 'name' => 'hello'], 'world1', 60);
        $model->insert(['id' => 2, 'name' => 'hello'], 'world2', 70);
        $model->insert(['id' => 3, 'name' => 'hello'], 'world3', 80);
        $cnt1 = $model->newQuery()->where('name', 'hello')->count();
        $cnt2 = $model->newQuery()->where('name', 'hello')->delete();
        $this->assertEquals($cnt1, 3);
        $this->assertEquals($cnt2, 3);
    }
}