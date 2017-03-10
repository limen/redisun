<?php

use PHPUnit\Framework\TestCase;
use Limen\RedModel\QueryBuilder;

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

        $builder = new QueryBuilder($key, '{?}');

        $keys = $builder->where('schoolId', 1)->where('classId', 2)->getBuiltKeys();
        $this->assertEquals($keys, [
            'school:1:class:2:students'
        ]);

        $key = $builder->where('schoolId', 1)->where('classId', 2)->firstBuiltKey();
        $this->assertEquals($key, 'school:1:class:2:students');

        $keys = $builder->where('schoolId', 1)->getBuiltKeys();
        $this->assertEquals($keys, []);

        $keys = $builder->whereIn('schoolId', [1,2])->getRawKeys();
        $this->assertEquals($keys, [
            'school:1:class:{classId}:students',
            'school:2:class:{classId}:students',
        ]);

        $builder->flushBuiltKeys();

        $keys = $builder->whereIn('schoolId', [1,2])->whereIn('classId', [2,3])->getBuiltKeys();
        $this->assertEquals($keys, [
            'school:1:class:2:students',
            'school:1:class:3:students',
            'school:2:class:2:students',
            'school:2:class:3:students',
        ]);
    }

}