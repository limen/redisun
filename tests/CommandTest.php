<?php

use PHPUnit\Framework\TestCase;
use Limen\Redisun\Commands\Factory;

class CommandTest extends TestCase
{
    public function testRpushCommand()
    {
        $arguments = [
            'list1','list2',1,2,3,4,
        ];

        $factory = new Factory();

        $command = $factory->getCommand('rpush', [$arguments[0], $arguments[1]], array_slice($arguments, 2));

        $this->assertEquals(2, $command->getKeysCount());

        $this->assertEquals($arguments, $command->getArguments());
    }

}