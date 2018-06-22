# 让redis操作更简单，为不同数据类型封装统一的命令

[![Build Status](https://travis-ci.org/limen/redisun.svg?branch=master)](https://travis-ci.org/limen/redisun)
[![Packagist](https://img.shields.io/packagist/l/limen/redisun.svg?maxAge=2592000)](https://packagist.org/packages/limen/redisun)

[English](https://github.com/limen/redisun/blob/master/README.cn.md)
[Wiki](https://github.com/limen/redisun/wiki)

## 特性

+ 为不同的数据类型封装了统一的命令，支持常用的5种数据类型：string, hash, list, set, zset
+ 支持类似SQL的查询方法，如where、where in等
+ 使用eval降低在网络通信上的时间消耗
+ "set"类命令均支持修改key的ttl或保留当前ttl

## 已封装的命令
+ create: 创建key
+ createNotExists: 当key不存在时创建
+ createExists: 当key存在时创建
+ insert: 类似create，支持披批量创建
+ insertNotExists: 批量创建，当所有key不存在时才创建
+ insertExists: 批量创建，当所有key存在时才创建
+ get: 批量获取key
+ getAndSet: 获取key并设置新值
+ find: 获取单个key
+ findBatch: 批量获取
+ update: 批量更新
+ destroy: 删除key
+ destroyBatch: 批量删除
+ delete: 批量删除

## 安装

推荐使用[composer](https://getcomposer.org/ "")安装

```bash
composer require "limen/redisun"
```

## 使用

```
use Limen\Redisun\Examples\HashModel;
use Limen\Redisun\Examples\StringModel;

$person = [
   'name' => 'martin',
   'age' => '22',
   'height' => '175',
   'nation' => 'China',
];
$hashModel = new HashModel();
$hashModel->create(1, $person);
$hashModel->find(1);                    // 返回 $person
$hashModel->where('id',1)->first();     // 返回 $person
$hashModel->where('id',1)->get();       // 返回 ['redisun:1:hash' => $person]
$hashModel->where('id',1)->delete();    // 从redis数据库删除"redisun:1:hash"

$nick = 'martin-walk';

$stringModel = new StringModel();
$stringModel->insert([
    'id' => 1,
    'name' => 'martin'
], $nick);
$stringModel->where('id',1)->first();   // 返回 $nick
$stringModel->where('id',1)->get();     // 返回 ['redisun:1:string:martin' => $nick]
```

## 概念

#### _Key表征_

每一个Model都有自己的key表征。查询时依据key表征来构造key。例如
 
```
school:{schoolId}:class:{classId}:members
```

对于拥有这个key表征的Model，我们可以使用where和where in来查询redis

```
$model->where('schoolId',1)->whereIn('classId',[1,2])->get();
```

最终构造出的key如下。这也是将要向redis查询的key

```
school:1:class:1:members
school:1:class:2:members
```

#### _Key域_

Key域是key表征中的动态部分。

以上面的key表征为例，它有两个域

+ schoolId
+ classId

#### _完全key_

一个构造出的key不包含未绑定的域时，这个key被认为是完全的。例如

```
school:1:class:2:members
```

相反，一个不完全的key类似于

```
school:1:class:{classId}:members
```

## 返回的数据集

批量查询时，返回的数据集是由key索引的关联数组，索引对应的值为key对应的值。

当上面构造出的两个key都存在时，返回的数据集如下

```
[
    'school:1:class:1:members' => <item1>,
    'school:1:class:2:members' => <item2>,
]
```

如果某个key不存在，数据集的索引将没有该key

数据集中元素的值的类型，根据不同的redis数据类型，可以是

+ string: 字符串
+ hash: 关联数组
+ list: 数组
+ set: 数组
+ zset: 数组


## 命令手册

### create

当一个model的key表征只有一个域时，可以使用该方法

ttl参数可选。

key表征如下的Hash类型的Model
```
user:{id}:info
```

```
$model->create(1, [
    'name' => 'maria',
    'age' => 22,
], 10);   // key "user:1:info" 将在10s后过期
```

key表征如下的Zset类型的Model
```
shop:{id}:customers
```

```
// key -> 成员, value -> 分值
$model->create(1, [
    'maria' => 1,
    'martin' => 2,
]);   // "shop:1:customers"将不会过期
```

### createNotExists

类似setnx，支持多种数据类型

### createExists

类似setxx，支持多种数据类型

### insert

可选参数用于检查key是否存在，使insert的行为类似setnx或setxx.

key表征如下的Zset类型的Model

```
user:{id}:code
```

```
$model->insert([
    'id' => 1,
], 10010, 20); 
```

### insertNotExists

类似createNotExists

### insertExists

类似createExists

### find
使用条件同create

```
$model->find(1);
```

### findBatch

类型于find，返回的数据集由"id"索引

```
$model->findBatch([1,2,3]);
// [
//     1 => <item1>,
//     2 => <item2>,
//     3 => <item3>,
// ]
```

### updateBatch

类似于findBatch.

不存在的key将被创建。

如果不传入ttl参数，key的ttl将不被改变。

```
$model->updateBatch([1,2,3], $value);
```

### all

key表征如下的model
```
user:{id}:code
```

```
$model->all();      // 返回匹配模式user:*:code（keys user:*:code）的所有key的值
```


### where

绑定一个key域

```
$model->where('id', 1)->where('name', 'maria');
```

### whereIn

类似于where，为一个key域绑定多个值

```
$model->whereIn('id', [1,2,3]);
```

### first

获取构造出的key中第一个存在的key，如果所有构造的key都不存在，返回null

```
$model->whereIn('id', [1,2,3])->first();    // return string|array|null
```

### update

如果key不存在，将被创建

如果不传入ttl参数，key的ttl将不被改变。

```
$model->where('id',1)->update($value);
```

### delete

删除构造的key

```
$model->where('id',1)->delete();
```

### orderBy, sort

用户对返回的数据集进行排序。
key表征如下的string类型的Model

```
user:{id}:code
```

```
$model->insert([
    'id' => 1,
], 10010); 
$model->insert([
    'id' => 2,
], 10011); 

$model->whereIn('id', [1,2])->orderBy('id')->get();
// returned data set
// [
//     'user:1:code' => 10010,
//     'user:2:code' => 10011,
// ]
```

```
$model->newQuery()->whereIn('id', [1,2])->orderBy('id', 'desc')->get();
// returned data set
// [
//     'user:2:code' => 10011,
//     'user:1:code' => 10010,
// ]
```

```
$model->newQuery()->whereIn('id', [1,2])->sort();
// returned data set
// [
//     'user:1:code' => 10010,
//     'user:2:code' => 10011,
// ]
```

### count

返回存在的key的数量。

```
$model->where('id', 1)->count();    // 返回整数
```

### max

返回数据集中的最大值

```
$model->where('id', 1)->max();
```

### min

返回数据集中的最小值

```
$model->where('id', 1)->min();
```

### sum

返回查询到的数据集的和

```
$model->where('id', 1)->sum();
```

## Predis的原生方法

当一个查询只构造出一个完整的key时，可以使用Predis的原生方法，例如
    
    // string model
    $model->where('id', 1)->set('maria');
    
    // hash model
    $model->where('id', 1)->update([
        'name' => 'Maria',
        'age' => '22',
    ]);
    // 等同于
    $model->where('id', 1)->hmset([
        'name' => 'Maria',
        'age' => '22',
    ]);

## 查询构造器

负责为model构造待查询的key

key表征

```
user:{id}:{name}
```

```php
$queryBuilder->whereIn('id', [1,2])->whereIn('name', ['maria', 'cat']);
// 构造出的key
// user:1:maria
// user:1:cat
// user:2:maria
// user:2:cat

$queryBuilder->refresh()->whereIn('id', [1,2]);
// 构造出的key
// user:1:{name}
// user:2:{name}
```

## 开发

### 测试

```bash
$ phpunit --bootstrap tests/bootstrap.php tests/
```
