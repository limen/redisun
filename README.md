# CURD model for redis in laravel style

## Features

+ support operations: create, insert, find, destroy and so on 
+ fluent query builder
+ use "multi" and "exec" for batch operation

## Installation

Recommend to install via composer. See [php composer](https://getcomposer.org/ "").
```
composer require "limen/redmodel:dev-master"
```

## Usage

```php
use Limen\RedModel\Examples\HashModel;


// constructing parameters are passed transparently to Predis client's constructor 
$hasModel = new HashModel([
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

$tested = [];

// insert
$hasModel->insert(['id' => 1], $maria);
$hasModel->insert(['id' => 2], $cat);
// find by primary key
$user = $hasModel->find(1);

if ($user === $maria) {
    $tested[] = 'Find OK';
}

// find by query
$users = $hasModel->where('id', 1)->get();
if ($users === [$maria]) {
    $tested[] = 'Where then get OK';
}

$user = $hasModel->where('id', 1)->first();
if ($user === $maria) {
    $tested[] = 'Where then first OK';
}

$users = $hasModel->whereIn('id', [1,2])->get();
if ($users === [$maria, $cat]) {
    $tested[] = 'Where in then get OK';
}

// find batch by primary keys
$users = $hasModel->findBatch([1,2]);
if ($users === [$maria, $cat]) {
    $tested[] = 'find batch OK';
}

// update by query
$hasModel->where('id', 1)->update([
    'age' => '23',
]);
$user = $hasModel->find(1);
if ($user['age'] === '23') {
    $tested[] = 'Update OK';
}

// remove item
$hasModel->destroy(1);

$user = $hasModel->find(1);
if (!$user) {
    $tested[] = 'Destroy OK';
}

var_dump($tested);
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
$queryBuilder->whereIn('id', [1,2])->where('name', 'maria');
// built keys
// user:1:maria
// user:2:maria
```
    
The built query keys which contain unbound fields would be ignored. For example

```
user:1:{name}
```



