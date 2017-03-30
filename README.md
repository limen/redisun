# CURD model for redis in laravel style

[![Build Status](https://travis-ci.org/limen/redmodel.svg?branch=master)](https://travis-ci.org/limen/redmodel)
[![Packagist](https://img.shields.io/packagist/l/limen/redmodel.svg?maxAge=2592000)](https://packagist.org/packages/limen/redmodel)

Make Redis manipulations easy.

This package is based on [predis](https://github.com/nrk/predis "")

## Features

+ supports most of queries usually used in relational database such as MySql. 
+ fluent query builder
+ use "eval" to save time consumption on network.

## Installation

Recommend to install via [composer](https://getcomposer.org/ "").

```bash
composer require "limen/redmodel"
```

## Usage

```
use Limen\RedModel\Examples\HashModel;
use Limen\RedModel\Examples\StringModel;

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
$hashModel->where('id',1)->get();       // return ['redmodel:1:hash' => $person]
$hashModel->where('id',1)->delete();    // remove key "redmodel:1:hash" from database

$nick = 'martin-walk';

$stringModel = new StringModel();
$stringModel->insert([
    'id' => 1,
    'name' => 'martin'
], $nick);
$stringModel->where('id',1)->first();   // return $nick
$stringModel->where('id',1)->get();     // return ['redmodel:1:string:martin' => $nick]
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


### insert

string type with key representation

```
user:{id}:code
```

```
$model->insert([
    'id' => 1,
], 10010, 20); // the item "user:1:code" would expire after 20 seconds 
```

### find
Can use when a model's key representation has only one dynamic field as its primary field.

```
$model->find(1);
```

### findBatch

Similar to find
```
$model->findBatch([1,2,3]);
```

### updateBatch

Similar to findBatch.
```
$model->updateBatch([1,2,3], $value);
```

The key wouldn't be created if not exist.

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

### update

The key would not be created if not exist.

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


