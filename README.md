# QueryBuilder Behavior

This behavior provides a step-by-step interface to build find options.

## Requirements

- CakePHP 1.3
- PHP 5.1 or later

## Installation 

    cd app/plugins
    git clone git://github.com/tkyk/cakephp-query-builder.git query_builder

## Usage

QueryBuilderBehavior provides `finder` method to build and invoke a find operation.

    // method-chain style
    $results = $Model->finder('all')
      ->fields('id', 'title')
      ->Model_title('like', '%abc')
      ->order('id ASC', 'created ASC')
      ->limit(10)
      ->invoke();
    
    // property style
    $f = $Model->finder('all');
    $f->fields = array('id', 'title');
    $f->conditions = array('Model.title like' => '%abc');
    $f->order = array('id ASC', 'created ASC');
    $f->limit = 10;
    $results = $f->invoke();

The similar methods `paginator` and `subquery` are for pagination and subqueries, respectively.

    // pagination
    $results = $Model->paginator($Controller)
      ->fields('id', 'title')
      ->Model_title('like', '%abc')
      ->order('id ASC', 'created ASC')
      ->invoke();
    
     // subquery
     $subqueryStr = $Model->subquery('users', 'User2')
       ->fields('User2.id')
       ->User2_deleted(null)
       ->__toString();


