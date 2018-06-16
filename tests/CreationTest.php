<?php

use PHPUnit\Framework\TestCase;
use Limen\RedModel\Examples\HashModel;
use Limen\RedModel\Examples\SetModel;

class CreationTest extends TestCase
{
    public function testCreate()
    {
        $hash = new HashModel();
        $girl = [
            'name' => 'maria',
            'age' => 18,
        ];
        $hash->create(1, $girl, 100);
        $this->assertFalse($hash->createNotExists(1, $girl, 100));
        $this->assertTrue($hash->createExists(1, $girl, 100));
        $hash->destroy(1);

        $set = new SetModel();
        $girls = [
            'maria',
            'lily',
        ];
        $set->create(1, $girls, 100);
        $this->assertTrue($set->createExists(1, $girl, 100));
        $this->assertFalse($set->createNotExists(1, $girl, 100));
        $set->destroy(1);
    }

    public function testInsert()
    {
        $string = new \Limen\RedModel\Examples\StringModel();

        $bindings = [
            'id' => 1,
            'name' => 'demo',
        ];
        $string->newQuery()->insert($bindings, 'hello world!', 120);
        $this->assertFalse($string->newQuery()->insert($bindings, 'hello world!', 120, false));
        $this->assertTrue($string->newQuery()->insert($bindings, 'hello world!', 120, true));
        $string->newQuery()->where('id', 1)->where('name', 'demo')->delete();

        $list = new \Limen\RedModel\Examples\ListModel();
        $list->newQuery()->insert(['id' => 1], [1,2,3], 120);
        $this->assertFalse($list->newQuery()->insertNotExists(['id' => 1], [1,2,3], 120));
        $this->assertTrue($list->newQuery()->insertExists(['id' => 1], [1,2,3], 120));
        $list->destroy(1);
    }
}