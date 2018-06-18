<?php

use PHPUnit\Framework\TestCase;
use Limen\Redisun\Examples\HashModel;
use Limen\Redisun\Examples\SetModel;

class CreationTest extends TestCase
{
    public function testCreate()
    {
        try {
            $hash = new HashModel();
            $girl = [
                'name' => 'maria',
                'age' => 18,
            ];
            $hash->create(1, $girl, 100);
            $this->assertFalse($hash->createNotExists(1, $girl, 100));
            $this->assertTrue($hash->createExists(1, $girl, 100));
        } catch (Exception $e) {
            throw $e;
        } finally {
            $hash->destroy(1);
        }

        try {
            $set = new SetModel();
            $girls = [
                'maria',
                'lily',
            ];
            $set->create(1, $girls, 100);
            $this->assertTrue($set->createExists(1, $girl, 100));
            $this->assertFalse($set->createNotExists(1, $girl, 100));
        } catch (Exception $e) {
            throw $e;
        } finally {
            $set->destroy(1);
        }
    }

    public function testInsert()
    {
        try {
            $string = new \Limen\Redisun\Examples\StringModel();
            $bindings = [
                'id' => 1,
                'name' => 'demo',
            ];
            $string->newQuery()->insert($bindings, 'hello world!', 120);
            $this->assertFalse($string->newQuery()->insert($bindings, 'hello world!', 120, false));
            $this->assertTrue($string->newQuery()->insert($bindings, 'hello world!', 120, true));
        } catch (Exception $e) {
            throw $e;
        } finally {
            $string->newQuery()->where('id', 1)->where('name', 'demo')->delete();
        }

        try {
            $list = new \Limen\Redisun\Examples\ListModel();
            $list->newQuery()->insert(['id' => 1], [1,2,3], 120);
            $this->assertFalse($list->newQuery()->insertNotExists(['id' => 1], [1,2,3], 120));
            $this->assertTrue($list->newQuery()->insertExists(['id' => 1], [1,2,3], 120));
        } catch (Exception $e) {
            throw $e;
        } finally {
            $list->destroy(1);
        }
    }
}