<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/6/17
 */
use PHPUnit\Framework\TestCase;
use Limen\Redisun\Examples\StringModel;

class GetsetTest extends TestCase
{
    public function testZset()
    {
        try {
            $model = new \Limen\Redisun\Examples\ZsetModel();
            $model->create(1, ['google' => 18]);
            $oldValue = $model->where('id', 1)->getAndSet(['ms' => 19]);
            $this->assertEquals($oldValue, ['google']);
            $value = $model->where('id', 1)->first();
            $this->assertEquals($value, ['ms']);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $model->destroy(1);
        }
    }

    public function testSet()
    {
        try {
            $model = new \Limen\Redisun\Examples\SetModel();
            $model->create(1, [1,2,3]);
            $oldValue = $model->where('id', 1)->getAndSet([1,2,3,4]);
            $this->assertEquals($oldValue, [1,2,3]);
            $value = $model->where('id', 1)->first();
            $this->assertEquals($value, [1,2,3,4]);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $model->destroy(1);
        }
    }

    public function testList()
    {
        try {
            $model = new \Limen\Redisun\Examples\ListModel();
            $model->create(1, [1,2,3]);
            $oldValue = $model->where('id', 1)->getAndSet([1,2,3,4]);
            $this->assertEquals($oldValue, [1,2,3]);
            $value = $model->where('id', 1)->first();
            $this->assertEquals($value, [1,2,3,4]);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $model->destroy(1);
        }
    }

    public function testHash()
    {
        try {
            $person = [
                'name' => 'maria',
                'age' => 22,
            ];
            $model = new \Limen\Redisun\Examples\HashModel();
            $model->create(1, $person);
            $oldValue = $model->where('id', 1)->getAndSet(['name' => 'maria']);
            $this->assertEquals($oldValue, $person);
            $value = $model->find(1);
            $this->assertEquals($value, ['name' => 'maria']);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $model->destroy(1);
        }
    }

    public function testString()
    {
        try {
            $model = new StringModel();
            $model->insert(
                [
                    'id' => 1,
                    'name' => 'maria',
                ],
                'mymaria',
                120
            );
            $oldValue = $model->newQuery()->where('id', 1)
                ->where('name', 'maria')
                ->getAndSet('mymaria1', 130);
            $this->assertEquals($oldValue, 'mymaria');
            $ttl = $model->newQuery()->where('id', 1)
                ->where('name', 'maria')
                ->ttl();
            $this->assertEquals($ttl, 130);
            $value = $model->newQuery()->where('id', 1)
                ->where('name', 'maria')
                ->first();
            $this->assertEquals($value, 'mymaria1');
        } catch (Exception $e) {
            throw $e;
        } finally {
            $model->newQuery()->where('id', 1)
                ->where('name', 'maria')
                ->delete();
        }
    }
}