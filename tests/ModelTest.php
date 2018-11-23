<?php

use PHPUnit\Framework\TestCase;
use Limen\Redisun\Examples\HashModel;
use Limen\Redisun\Examples\ListModel;
use Limen\Redisun\Examples\StringModel;
use Limen\Redisun\Examples\ZsetModel;
use Limen\Redisun\Examples\SetModel;

/**
 * Class ModelTest
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 */
class ModelTest extends TestCase
{
    public function testQueryKeys()
    {
        try {
            $model = new StringModel();
            $range = range(1,20);
            $keys = [];
            foreach ($range as $i) {
                $model->insert([
                    'id' => $i,
                    'name' => 'martin',
                ],'22');
                $keys[] = "redisun:$i:string:martin";
            }
            $value = $model->newQuery()->where('id', 1)->getKeys();
            $this->assertEquals([
                "redisun:1:string:martin",
            ], $value);
            $value = $model->newQuery()->whereIn('id', [1,2])->getKeys();
            $this->assertEquals([
                "redisun:1:string:martin",
                "redisun:2:string:martin",
            ], $value);
            $value = $model->newQuery()
                ->whereIn('id', [1,2,3,4,5,6])
                ->orderBy('id')
                ->take(5)
                ->getKeys();
            $this->assertEquals([
                "redisun:1:string:martin",
                "redisun:2:string:martin",
                "redisun:3:string:martin",
                "redisun:4:string:martin",
                "redisun:5:string:martin",
            ], $value);
            $model->newQuery()->whereIn('id', $range)->delete();
            $this->assertEquals([], $model->all());
        } catch (Exception $e) {
            throw $e;
        } finally {
            $model->newQuery()->whereIn('id', $range)->delete();
        }
    }

    public function testHashModel()
    {
        $a = [
            'name' => 'martin',
            'age' => '22',
            'height' => '175',
            'nation' => 'China',
        ];
        $b = [
            'name' => 'nathan',
            'age' => '23',
            'height' => '176',
            'nation' => 'China',
        ];
        $model = new HashModel();
        $model->create(1, $a);
        $model->create(2, $b);

        $value = $model->newQuery()->where('id', 1)->first();
        $this->assertEquals($a, $value);

        $this->assertEquals($model->find(1), $a);

        $values = $model->findBatch([1,2]);
        $this->assertEquals(2, count($values));
        $this->assertEquals($a, $values[1]);
        $this->assertEquals($b, $values[2]);

        $values = $model->all();
        $this->assertTrue(in_array($a, $values));
        $this->assertTrue(in_array($b, $values));

        $data = $model->newQuery()->whereIn('id', [1,2])->orderBy('id', 'desc')->get();
        $this->assertEquals([$b, $a], array_values($data));

        $data = $model->newQuery()->whereIn('id', [1,2])->orderBy('id', 'desc')->take(1)->get();
        $this->assertEquals(1, count($data));
        $this->assertEquals($b, $data['redisun:2:hash']);

        $updated = [];
        $updated['age'] = '24';
        $model->newQuery()->where('id', 1)->update($updated);
        $value = $model->newQuery()->where('id', 1)->first();
        $this->assertEquals($updated, $value);

        $model->destroy(1);
        $this->assertEquals($model->find(1), []);

        $model->updateBatch([1,2], $a);
        $this->assertEquals($model->find(1), $a);
        $this->assertEquals($model->find(2), $a);

        $model->destroyBatch([1,2]);
        $this->assertEquals($model->find(2), []);

        $model->newQuery()->whereIn('id', [1,2])->delete();

        $this->assertEquals($model->all(), []);
    }

    public function testListModel()
    {
        $list = [1,2,3];

        $model = new ListModel();
        $model->create(1, [1,2,3]);
        $this->assertEquals($model->find(1), $list);

        $list[] = 4;
        $model->where('id', 1)->update($list);
        $this->assertEquals($model->find(1), $list);

        $model->where('id', 1)->delete();
        $this->assertEquals($model->find(1), []);

        $this->assertEquals($model->all(), []);
    }

    public function testStringModel()
    {
        $value = 'martin-walk';
        $model = new StringModel();
        $model->insert([
            'id' => 1,
            'name' => 'martin'
        ], $value);
        $this->assertEquals($value, $model->where('id', 1)->where('name', 'martin')->first());

        $this->assertEquals($value, $model->where('id', 1)->first());

        $this->assertEquals($value, $model->where('name', 'martin')->first());

        $value = ucfirst($value);
        $model->where('id', 1)->update($value);
        $this->assertEquals($value, $model->where('id', 1)->first(1));

        $model->where('id', 1)->delete();
        $this->assertEquals($model->where('id', 1)->first(), null);

        $this->assertEquals($model->all(), []);
    }

    public function testZsetModel()
    {
        $zset = [
            'google' => 10000,
            'amazon' => 8000,
            'apple' => 20000,
            'alibaba' => 2000,
        ];
        $model = new ZsetModel();
        $model->create(1, $zset);
        asort($zset);
        $this->assertEquals($model->find(1), array_keys($zset));

        unset($zset['alibaba']);
        $model->where('id', 1)->update($zset);
        $this->assertEquals($model->find(1), array_keys($zset));

        $model->destroy(1);
        $this->assertEquals($model->find(1), []);

        $this->assertEquals($model->all(), []);
    }

    public function testSetModel()
    {
        $set = [
            'alibaba',
            'google',
            'amazon',
            'apple',
        ];
        sort($set);
        $model = new SetModel();
        $model->create(1, $set);
        $value = $model->find(1);
        sort($value);
        $this->assertEquals($value, $set);

        array_pop($set);
        $model->where('id', 1)->update($set);
        $value = $model->find(1);
        sort($value);
        $this->assertEquals($value, $set);
        $model->destroy(1);
        $this->assertEquals($model->find(1), []);
        $this->assertEquals($model->all(), []);
    }

    public function testAggregation()
    {
        $model = new StringModel();
        $model->insert([
            'id' => 1,
            'name' => 'martin',
        ],10);
        $model->insert([
            'id' => 2,
            'name' => 'martin',
        ],20);
        $model->insert([
            'id' => 3,
            'name' => 'martin',
        ],30);

        $this->assertEquals(60, $model->newQuery()->sum());
        $this->assertEquals(10, $model->newQuery()->min());
        $this->assertEquals(30, $model->newQuery()->max());
        $this->assertEquals(3, $model->newQuery()->count());
        $this->assertEquals(1, $model->newQuery()->where('id',1)->count());
        $this->assertEquals(3, $model->newQuery()->where('name', 'martin')->count());
        $this->assertEquals(0, $model->newQuery()->where('name', 'maria')->count());
        $model->newQuery()->whereIn('id', [1,2,3])->where('name', 'martin')->delete();
        $this->assertEquals($model->all(), []);
    }

    public function testSort()
    {
        $model = new StringModel();

        $array = [
            '10','20','30',
        ];

        $model->insert([
            'id' => 1,
            'name' => 'maria',
        ],$array[0]);
        $model->insert([
            'id' => 2,
            'name' => 'maria',
        ],$array[1]);
        $model->insert([
            'id' => 3,
            'name' => 'maria',
        ],$array[2]);

        $this->assertEquals($array, $model->newQuery()->sort('asc'));

        $this->assertEquals(array_reverse($array), $model->newQuery()->sort('desc'));

        $model->newQuery()->whereIn('id', [1,2,3])->where('name', 'maria')->delete();

        $this->assertEquals($model->all(), []);
    }

    public function testTtl()
    {
        $ttl = 2;

        // StringModel
        $model = new StringModel();

        $model->insert([
            'id' => 1,
            'name' => 'maria',
        ], 'maria', $ttl);

        $this->assertEquals($ttl, $model->newQuery()->where('id',1)->where('name','maria')->ttl());

        $model->newQuery()->where('id',1)->where('name','maria')->update('mary');
        $this->assertGreaterThanOrEqual(0, $model->newQuery()->where('id',1)->where('name','maria')->ttl());
        $this->assertLessThanOrEqual($ttl, $model->newQuery()->where('id',1)->where('name','maria')->ttl());

        sleep($ttl + 1);
        $this->assertEquals([], $model->newQuery()->where('id',1)->where('name','maria')->get());
        $this->assertNull($model->newQuery()->where('id',1)->where('name','maria')->first());

        // HashModel
        $model = new HashModel();
        $model->create(1, [
            'name' => 'maria',
            'age' => 25,
        ], $ttl);
        $model->create(2, [
            'name' => 'maria',
            'age' => 25,
        ], $ttl + 1);

        $this->assertEquals($ttl, $model->newQuery()->where('id',1)->ttl());
        $model->where('id', 1)->update([
            'age' => 26,
        ]);
        $this->assertEquals($ttl, $model->newQuery()->where('id',1)->ttl());

        $model->updateBatch([1,2], [
            'age' => 27
        ]);
        $this->assertEquals($ttl, $model->newQuery()->where('id',1)->ttl());
        $this->assertEquals($ttl + 1, $model->newQuery()->where('id',2)->ttl());

        sleep($ttl + 1);
        $this->assertEquals([], $model->newQuery()->where('id',1)->get());

        // SetModel
        $model = new SetModel();
        $model->create(1, [
            'martin',
            'maria'
        ], $ttl);

        $this->assertEquals($ttl, $model->newQuery()->where('id',1)->ttl());
        $model->where('id', 1)->update([
            'martin',
            'maria',
            'cathrine',
        ]);
        $this->assertGreaterThanOrEqual(0, $model->newQuery()->where('id',1)->ttl());
        $this->assertLessThanOrEqual($ttl, $model->newQuery()->where('id',1)->ttl());

        sleep($ttl + 1);
        $this->assertEquals([], $model->newQuery()->where('id',1)->get());
        $this->assertEquals([], $model->find(1));

        // ZsetModel
        $model = new ZsetModel();
        $model->create(1, [
            'martin' => 1,
            'maria' => 2,
        ], $ttl);

        $this->assertequals($ttl, $model->newQuery()->where('id',1)->ttl());
        $model->where('id', 1)->update([
            'martin' => 2,
            'maria' => 3,
            'cathrine' => 1,
        ]);
        $this->assertGreaterThanOrEqual(0, $model->newQuery()->where('id',1)->ttl());
        $this->assertLessThanOrEqual($ttl, $model->newQuery()->where('id',1)->ttl());

        sleep($ttl + 1);
        $this->assertEquals([], $model->newQuery()->where('id',1)->get());

        // ListModel
        $model = new ListModel();
        $model->create(1, [
            'martin',
            'maria',
        ], $ttl);

        $this->assertequals($ttl, $model->newQuery()->where('id',1)->ttl());
        $model->where('id', 1)->update([
            'martin',
            'maria',
            'cathrine',
        ]);
        $this->assertGreaterThanOrEqual(0, $model->newQuery()->where('id',1)->ttl());
        $this->assertLessThanOrEqual($ttl, $model->newQuery()->where('id',1)->ttl());

        sleep($ttl + 1);
        $this->assertEquals([], $model->newQuery()->where('id',1)->get());

        $model->create(1, [
            'martin',
            'maria',
        ], $ttl);
        $ttl++;
        $model->newQuery()->where('id', 1)->update(['maria', 'martin'], $ttl);
        $this->assertequals($ttl, $model->newQuery()->where('id',1)->ttl());

        $model->newQuery()->where('id',1)->delete();
        $this->assertEquals([], $model->all());
    }

    public function testNativeMethods()
    {
        $numbers = [1,2,3,4,5,6,100,200,300];
        $listModel = new ListModel();
        $listModel->newQuery()->where('id', 1)->rpush($numbers);
        $this->assertEquals($listModel->newQuery()->where('id', 1)->first(), $numbers);

        $listModel->newQuery()->where('id', 2)->lpush($numbers);
        $this->assertEquals($listModel->newQuery()->where('id', 2)->first(), array_reverse($numbers));

        // clean up
        $listModel->newQuery()->whereIn('id', [1,2])->delete();

        $this->assertEquals($listModel->newQuery()->whereIn('id', [1,2])->count(), 0);

        $set = ['alibaba', 'google', 'amazon', 'apple',];
        $model = new SetModel();
        $model->newQuery()->where('id', 1)->sadd($set);
        $value = $model->find(1);
        $this->assertTrue($this->compareSet($value, $set));

        $model->newQuery()->where('id', 1)->srem($set);
        $this->assertFalse((bool)$model->find(1));
    }

    protected function compareSet($a, $b)
    {
        if (count($a) !== count($b)) {
            return false;
        }

        foreach ($a as $v) {
            if (!in_array($v, $b)) {
                return false;
            }
        }

        return true;
    }
}