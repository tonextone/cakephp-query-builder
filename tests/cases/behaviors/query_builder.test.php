<?php

App::import('Behavior', 'QueryBuilder.QueryBuilder');

class QueryBuilderTestCase extends CakeTestCase {
    var $f;

    function startTest() {
        $this->f = new QueryBuilderBehavior();
    }

    function endTest() {
        ClassRegistry::flush();
    }

    function testInit() {
    }

    function testCreateQueryMethod() {
        $model = new stdClass;
        $method = 'find';
        $args = array('all', 'custom');

        $finder = $this->f->createQueryMethod($model, $method, $args);
        $this->assertIsA($finder, 'QueryMethod');
        $this->assertIdentical($method, $finder->getMethod());
        $this->assertIdentical($model, $finder->getTarget());
        $this->assertIdentical($args, $finder->getAllArguments());
    }

    function testFinder() {
        $model = new stdClass;
        $type = 'all';

        $finder = $this->f->finder($model, $type);
        $this->assertIsA($finder, 'QueryMethod');
        $this->assertIdentical('find', $finder->getMethod());
        $this->assertIdentical($model, $finder->getTarget());
        $this->assertIdentical(array($type), $finder->getAllArguments());

    }

}
