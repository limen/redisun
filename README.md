# Make redis manipulations easy. Unify commands for all data types.

[![Build Status](https://travis-ci.org/limen/redisun.svg?branch=master)](https://travis-ci.org/limen/redisun)
[![Packagist](https://img.shields.io/packagist/l/limen/redisun.svg?maxAge=2592000)](https://packagist.org/packages/limen/redisun)

[中文](https://github.com/limen/redisun/blob/master/README.cn.md)

[Wiki](https://github.com/limen/redisun/wiki)

[Python version](https://github.com/limen/redisun-py)

## Features

+ Unified commands for all data types: string, list, hash, set and zset.
+ support SQL like query
+ use "eval" to save time consumption on network.
+ "set" like commands all support to set new ttl or keep current ttl

## Unified commands

+ create: create key
+ createNotExists: create key when which not exists
+ createExists: create key when which exists
+ insert: similar to create except supporting multiple keys
+ insertNotExists: similar to createNotExists
+ insertExists: similar to createExists
+ get: get key to replace get, lrange, hgetall, smembers and zrange
+ getAndSet: get key and set new value
+ find: similar to get
+ findBatch: find batch
+ update: update keys
+ destroy: remove one key
+ destroyBatch: remove keys
+ delete: remove keys

## Installation

Recommend to install via [composer](https://getcomposer.org/ "").

```bash
composer require "limen/redisun"
```

## Usage

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
$hashModel->find(1);                    // return $person
$hashModel->where('id',1)->first();     // return $person
$hashModel->where('id',1)->get();       // return ['redisun:1:hash' => $person]
$hashModel->where('id',1)->delete();    // remove key "redisun:1:hash" from database

$nick = 'martin-walk';

$stringModel = new StringModel();
$stringModel->insert([
    'id' => 1,
    'name' => 'martin'
], $nick);
$stringModel->where('id',1)->first();   // return $nick
$stringModel->where('id',1)->get();     // return ['redisun:1:string:martin' => $nick]
```

## Concepts

#### _Key representation_

Every model has its own key representation which tells how to build query keys. For example
 
```
school:{schoolId}:class:{classId}:members
```

We can use where clauses to query the Redis.

```
$model->where('schoolId',1)->whereIn('classId',[1,2])->get();
```

The keys to query are

```
school:1:class:1:members
school:1:class:2:members
```

#### _Key field_

Key field is a dynamic part of the key representation. 

Take the key representation above, it has two fields

+ schoolId
+ classId

#### _Complete key_

When a key has no unbound field, we treat it as complete. For example

```
school:1:class:2:members
```

On the contrary, an incomplete key is similar to

```
school:1:class:{classId}:members
```

## Returned data set

The returned data set would be an associated array whose indices are the query keys.

When both keys exist on Redis database, the returned data set would be

```
[
    'school:1:class:1:members' => <item1>,
    'school:1:class:2:members' => <item2>,
]
```

If a key not exist, the equivalent index would be not set.

The returned item's data type depends on the model's type which could be string, hash, list, set or zset.

+ string: string
+ hash: associated array
+ list: array
+ set: array
+ zset: array


## Methods

### create

Can use when a model's key representation has only one dynamic field as its primary field.

The item's ttl is optional.

Hash type with key representation
```
user:{id}:info
```

```
$model->create(1, [
    'name' => 'maria',
    'age' => 22,
], 10);   // the item "user:1:info" would expire after 10 seconds
```

zset type with key representation
```
shop:{id}:customers
```

```
// key -> member, value -> score
$model->create(1, [
    'maria' => 1,
    'martin' => 2,
]);   // the item "shop:1:customers" would not expire
```

### createExists
Similar to "setxx" but supports more data types: string, hash, set, zset and list.

### createNotExists
Similar to "setnx" but supports more data types.

### insert

An optional parameter make it possible to insert like "setnx" and "setxx".
String type with key representation. 

```
user:{id}:code
```

```
$model->insert([
    'id' => 1,
], 10010, 20); // the item "user:1:code" would expire after 20 seconds 
```

### insertExists

Similar to createExists

### insertNotExists

Similar to createNotExists

### find
Can use when a model's key representation has only one dynamic field as its primary field.

```
$model->find(1);
```

### findBatch

Similar to find. The returned data set are indexed by ids.
```
$model->findBatch([1,2,3]);
// [
//     1 => <item1>,
//     2 => <item2>,
//     3 => <item3>,
// ]
```

### updateBatch

Similar to findBatch.

The key would be created if not exist. The key's ttl would not be modified if the ttl parameter not set.

```
$model->updateBatch([1,2,3], $value);
```

### all

key representation

```
user:{id}:code
```

```
$model->all();      // return all keys which match pattern "user:*:code"
```


### where

Similar to SQL

```
$model->where('id', 1)->where('name', 'maria');
```

### whereIn

Similar to where

```
$model->whereIn('id', [1,2,3]);
```

### first

Get first exist item from query keys. Return null when all query keys not exist.

```
$model->whereIn('id', [1,2,3])->first();    // return string|array|null
```

### update

The key would be created if not exist. The key's ttl would not be modified if the ttl parameter not set.

```
$model->where('id',1)->update($value);
```

### delete

Delete query keys.

```
$model->where('id',1)->delete();
```

### orderBy, sort

string type with key representation

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

Count the exist query keys.

```
$model->where('id', 1)->count();    // return an integer
```

### max

Get the maximum item in the returned data set.

```
$model->where('id', 1)->max();
```

### min

Get the minimum item in the returned data set.

```
$model->where('id', 1)->min();
```

### sum

Get the sum of the returned data set.

```
$model->where('id', 1)->sum();
```

## Predis native methods

Predis native methods such as "sadd", "hset" can use when the query contains only one complete query key.
    
    // string model
    $model->where('id', 1)->set('maria');
    
    // hash model
    $model->where('id', 1)->update([
        'name' => 'Maria',
        'age' => '22',
    ]);
    // equals to
    $model->where('id', 1)->hmset([
        'name' => 'Maria',
        'age' => '22',
    ]);

## Query builder

Taking the job to build query keys for model.

key representation

```
user:{id}:{name}
```

```php
$queryBuilder->whereIn('id', [1,2])->whereIn('name', ['maria', 'cat']);
// built keys
// user:1:maria
// user:1:cat
// user:2:maria
// user:2:cat

$queryBuilder->refresh()->whereIn('id', [1,2]);
// built keys
// user:1:{name}
// user:2:{name}
```

## Development

### Test

```bash
$ phpunit --bootstrap tests/bootstrap.php tests/
```


