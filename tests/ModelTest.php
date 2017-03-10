<?php

use PHPUnit\Framework\TestCase;
use Limen\RedModel\Examples\HashModel;
use Limen\RedModel\Examples\ListModel;
use Limen\RedModel\Examples\StringModel;
use Limen\RedModel\Examples\ZsetModel;

/**
 * Class ModelTest
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 */
class ModelTest extends TestCase
{
    public function testHashModel()
    {
        $hash = [
            'name' => 'martin',
            'age' => 22,
            'height' => 175,
            'nation' => 'China',
        ];
        $model = new HashModel();
        $model->create(1, $hash);
        $model->create(2, $hash);
        $this->assertEquals($model->find(1), $hash);

        $value = $model->where('id', 1)->first();
        $this->assertEquals($hash, $value);

        $hash2 = $hash;
        $hash2['age'] = 23;
        $hash2['hobby'] = 'soccer';
        $model->create(2, $hash2);
        $this->assertEquals([$hash, $hash2], $model->whereIn('id', [1,2])->get());


        $model->where('id', 1)->update([
            'age' => 23,
            'hobby' => 'soccer',
        ]);
        $value = $model->where('id', 1)->first();
        $this->assertEquals($hash2, $value);

        $value = $model->find(1);
        $this->assertEquals($value, $hash2);

        $model->destroy(1);
        $this->assertEquals($model->find(1), []);
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

        $value = ucfirst($value);
        $model->where('id', 1)->where('name', 'martin')->update($value);
        $this->assertEquals($value, $model->where('id', 1)->where('name', 'martin')->first(1));

        $model->where('id', 1)->where('name', 'martin')->delete();
        $this->assertEquals($model->where('id', 1)->where('name', 'martin')->first(), null);
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
    }
}