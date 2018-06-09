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
        $hash->destroy(1);
    }
}