# CURD model for redis in laravel style

[![Build Status](https://travis-ci.org/limen/redmodel.svg?branch=master)](https://travis-ci.org/limen/redmodel)
[![Packagist](https://img.shields.io/packagist/l/limen/redmodel.svg?maxAge=2592000)](https://packagist.org/packages/limen/redmodel)

## Features

+ support operations: create, insert, find, destroy and so on 
+ fluent query builder
+ use "multi" and "exec" for batch operation

## Installation

Recommend to install via [composer](https://getcomposer.org/ "").

```bash
composer require "limen/redmodel"
```

## Usage

```php
use Limen\RedModel\Examples\HashModel;

// constructing parameters are passed transparently to Predis client's constructor 
$hashModel = new HashModel([
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379,
]);

$maria = [
    'name' => 'Maria',
    'age' => '22',
    'nation' => 'USA',
    'state' => 'New York',
];
$cat = [
    'name' => 'Catherine',
    'age' => '23',
    'nation' => 'UK',
    'city' => 'London',
];

// insert
$hashModel->insert(['id' => 1], $maria);
$hashModel->insert(['id' => 2], $cat);
// find by primary key
$user = $hashModel->find(1);                // return $maria

// find by query
$hashModel->where('id', 1)->get();          // return [$maria]
$hashModel->where('id', 1)->first();        // return $maria
$hashModel->whereIn('id', [1,2])->get();    // return [$maria, $cat]

// find batch by primary keys
$users = $hashModel->findBatch([1,2]);      // return [$maria, $cat]

// update by query
$hashModel->where('id', 1)->update([
    'age' => '23',
]);
$hashModel->find(1);        // return [
                            //    'name' => 'Maria',
                            //    'age' => '23',
                            //    'nation' => 'USA',
                            //    'state' => 'New York',
                            // ];

// remove item
$hashModel->destroy(1);
$user = $hashModel->find(1);                // return []
```

## Operation notices

### create

Can use when a model's key representation has only one dynamic field.

The default choice is "forced", which would replace the same key if exists.  

### insert

The default choice is "forced", which would replace the same key if exists.  

### Redis native methods

Redis native methods such as "sadd", "hset" can use when the query builder contains only one valid query key.
    
    // string model
    $model->where('id', 1)->set('maria');
    
    // hash model
    $model->where('id', 1)->update([
        'name' => 'Maria',
        'age' => '22',
    ]);
    // equal to
    $model->where('id', 1)->hmset([
        'name' => 'Maria',
        'age' => '22',
    ]);

## Query builder

Taking the job to build query keys for model.

```php
// model's key representation user:{id}:{name}
$queryBuilder->whereIn('id', [1,2])->whereIn('name', ['maria', 'cat']);
// built keys
// user:1:maria
// user:1:cat
// user:2:maria
// user:2:cat
```
    
The built query keys which contain unbound fields would be ignored. For example

```
user:1:{name}
```

## Development

### Test

```bash
$ phpunit --bootstrap tests/bootstrap.php tests/
PHPUnit 5.7.15 by Sebastian Bergmann and contributors.

Runtime:       PHP 5.6.24
Configuration: /usr/local/app/deployments/lmx/mobile-api-new/vendor/limen/redmodel/phpunit.xml.dist

.....                                                               5 / 5 (100%)

Time: 162 ms, Memory: 13.25MB

OK (5 tests, 20 assertions)
```


