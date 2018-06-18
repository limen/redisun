<?php

use PHPUnit\Framework\TestCase;
use Limen\Redisun\QueryBuilder;

/**
 * Class QueryBuilderTest
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 */
class QueryBuilderTest extends TestCase
{
    public function testBuilder()
    {
        $key = 'school:{schoolId}:class:{classId}:students';

        $builder = new QueryBuilder($key);
        $builder->setFieldNeedle('schoolId', '{schoolId}');
        $builder->setFieldNeedle('classId', '{classId}');

        $keys = $builder->whereEqual('schoolId', 1)->whereEqual('classId', 2)->getQueryKeys();
        $this->assertEquals($keys, [
            'school:1:class:2:students',
        ]);

        $key = $builder->refresh()->whereEqual('schoolId', 1)->whereEqual('classId', 2)->firstQueryKey();
        $this->assertEquals($key, 'school:1:class:2:students');

        $keys = $builder->refresh()->whereIn('schoolId', [1,2])->getQueryKeys();
        $this->assertEquals($keys, [
            'school:1:class:{classId}:students',
            'school:2:class:{classId}:students',
        ]);

        $keys = $builder->refresh()->whereBetween('schoolId', [1,5])->getQueryKeys();
        $this->assertEquals($keys, [
            'school:1:class:{classId}:students',
            'school:2:class:{classId}:students',
            'school:3:class:{classId}:students',
            'school:4:class:{classId}:students',
            'school:5:class:{classId}:students',
        ]);

        $keys = $builder->refresh()->whereIn('schoolId', [1,2])->whereIn('classId', [2,3])->getQueryKeys();
        $this->assertEquals($keys, [
            'school:1:class:2:students',
            'school:1:class:3:students',
            'school:2:class:2:students',
            'school:2:class:3:students',
        ]);

        $keys = $builder->refresh()->getQueryKeys();
        $this->assertEquals($keys, []);
    }

}