<?php

App::import('Lib', 'QueryBuilder.QueryOptions');
Mock::generate('Set');

class __ActsAsQueryBuilder extends Object {
}
Mock::generate('__ActsAsQueryBuilder');

class __MockBasicModel extends Mock__ActsAsQueryBuilder {
    function once($method, $args, $return) {
        $this->expectOnce($method, $args);
        $this->setReturnValue($method, $return, $args);
    }
    
}

class QueryMethodTestCase extends CakeTestCase {
    var $model;
    var $query, $method, $args;

    function startTest() {
        $this->model = new __MockBasicModel();
        $this->method = 'find';
        $this->args = array('all', 'custom', 'foo');
        $this->query = new QueryMethod($this->model, $this->method, $this->args);
    }

    function endTest() {
        ClassRegistry::flush();
    }

    function defaultObj() {
        return array($this->query, $this->method, $this->args);
    }

    function testInit() {
        $this->assertIdentical($this->method, $this->query->getMethod());
        $this->assertIdentical($this->model, $this->query->getTarget());
        $this->assertIdentical($this->args, $this->query->args);
    }

    function testArgs() {
        $q = new QueryMethod($this->model, $this->method);
        $this->assertIdentical(array(), $q->args);

        $this->assertIdentical($q, $q->args(1, 2, array('k' => 'v')));
        $this->assertIdentical(array(1, 2, array('k' => 'v')), $q->args);

        $q->args('a', 'b');
        $this->assertIdentical(array('a', 'b'), $q->args);

        $q->args();
        $this->assertIdentical(array(), $q->args);
    }

    function testGetAllArguments() {
        list($a, $method, $args) = $this->defaultObj();
        $this->assertEqual($args, $a->getAllArguments());

        $options = array('conditions' => 'deleted is null',
                         'order' => array('id asc', 'created desc'));
        $allArgs = am($args, array($options));

        $a->setOption('conditions', $options['conditions']);
        $a->setOption('order', $options['order']);
        $this->assertEqual($allArgs, $a->getAllArguments());
    }

    function testPrintArgs() {
        list($a, $method, $args) = $this->defaultObj();
        $options = array('conditions' => 'deleted is null',
                         'order' => array('id asc', 'created desc'));
        $allArgs = am($args, array($options));


        // setup mock
        $klass = '__MockDummyFunc';
        $func = 'pr';
        Mock::generate('Object', $klass, array($func));
        $mock = new $klass;
        $cb = array($mock, $func);

        $mock->expectCallCount($func, 2);
        $mock->expectAt(0, $func, array($args));
        $mock->expectAt(1, $func, array($allArgs));
        //

        $this->assertIdentical($a, $a->printArgs($cb));
        
        $a->setOption('conditions', $options['conditions']);
        $a->setOption('order', $options['order']);
        $a->printArgs($cb);
    }

    function testInvoke() {
        list($a, $method, $args) = $this->defaultObj();

        $options = array('conditions' => 'deleted is null',
                         'order' => array('id asc', 'created desc'));
        $allArgs = am($args, array($options));


        $this->model->setReturnValueAt(0, 'dispatchMethod', array(1,2,3));
        $this->model->setReturnValueAt(1, 'dispatchMethod', true);
        $this->model->expectCallCount('dispatchMethod', 2);
        $this->model->expectAt(0, 'dispatchMethod', array($method, $args));
        $this->model->expectAt(1, 'dispatchMethod', array($method, $allArgs));

        $this->assertIdentical(array(1,2,3), $a->invoke());

        $a->setOption('conditions', $options['conditions']);
        $a->setOption('order', $options['order']);
        $this->assertTrue($a->invoke());
    }


    function testInvoke_Set() {
        list($a, $method, $args) = $this->defaultObj();

        $set = new MockSet();
        $a->receiverSet = $set;

        $result = array(1,2,3);

        $extractParams = array('/User/id');
        $combineParams = array('{n}.Post.user_id', '{n}.0.posts_count');

        $set->expectOnce('extract', array($result, $extractParams[0]));
        $set->expectOnce('combine', array($result, $combineParams[0], $combineParams[1]));

        $set->setReturnValue('extract', 1);
        $set->setReturnValue('combine', 2);

        $this->model->setReturnValue('dispatchMethod', $result);
        $this->model->expectCallCount('dispatchMethod', 2);
        $this->model->expectAt(0, 'dispatchMethod', array($method, $args));
        $this->model->expectAt(1, 'dispatchMethod', array($method, $args));

        $this->assertIdentical(1, $a->invoke('extract', $extractParams[0]));
        $this->assertIdentical(2, $a->invoke('combine', $combineParams[0], $combineParams[1]));
    }

}
