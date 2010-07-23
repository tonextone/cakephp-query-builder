<?php

App::import('Behavior', 'QueryBuilder.QueryBuilder');
Mock::generate('Model', 'MockActsAsQueryBuilder', array('createQueryMethod'));
Mock::generate('QueryMethod');

class TestModelForQueryBuilderTestCase extends Model {
    var $useTable = false;
    var $actsAs = array('QueryBuilder.QueryBuilder');
    var $queryOptions
        = array('default' => array('limit' => 10),
                'more' => array('limit' => 100),
                'normal_order' => array('order' => 'created ASC'),
                'approved' => array('conditions'
                                    => array('status' => 'approved')));
}

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

    function testGetQueryOptions() {
        $model = new stdClass;
        $model->queryOptions
            = array('default' => array('fields' => a('id', 'title'),
                                       'limit' => 50),
                    'approved' => array('conditions'
                                        => array('status' => 'approved')));

        foreach($model->queryOptions as $name => $arr) {
            $this->assertEqual($arr,
                               $this->f->getQueryOptions($model, $name));
        }
    }

    function testGetQueryOptions_Error() {
        $key = 'no_such_key';

        $expectedError = $this->f->_missingOptionsError($key);

        $model = new MockActsAsQueryBuilder;
        $model->expectCallCount('cakeError', 3);
        $model->expect('cakeError', array('error', $expectedError));

        $this->assertNull($this->f->getQueryOptions($model, $key));

        $model->queryOptions = array('foo' => 'bar');
        $this->assertNull($this->f->getQueryOptions($model, $key));

        // found but not an array
        $model->queryOptions = array($key => null);
        $this->assertNull($this->f->getQueryOptions($model, $key));
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
        $type = 'all';
        $returnObj = new stdClass;

        $model = new MockActsAsQueryBuilder;
        $model->expectOnce('createQueryMethod',
                           array('find', array($type)));
        $model->setReturnValue('createQueryMethod', $returnObj);

        $finder = $this->f->finder($model, $type);
        $this->assertIdentical($returnObj, $finder);
    }

    function testFinder_QueryOptions() {
        $type = 'all';

        $returnObj = new MockQueryMethod;
        $model = new MockActsAsQueryBuilder;

        // setup Mocks
        $model->queryOptions
            = array('common' => array('limit' => 50,
                                      'order' => 'id DESC',
                                      'conditions' => 'id NOT NULL'),
                    'approved' => array('conditions' => array('status' => 'approved'),
                                        'limit' => 100));
        $model->expectOnce('createQueryMethod',
                           array('find', array($type)));
        $model->setReturnValue('createQueryMethod', $returnObj);

        $returnObj->expectOnce('import', array(array_keys($model->queryOptions)));


        $finder = $this->f->finder($model, $type, 'common', 'approved');
        $this->assertIdentical($returnObj, $finder);
    }

    function testFinder_Attached() {
        $model = new TestModelForQueryBuilderTestCase();

        $f = $model->finder('all')
            ->fields('id', 'title')
            ->order('id ASC')
            ->User_id(3)
            ->User_created('>', '2010-01-01')
            ->limit(20);

        $this->assertIsA($f, 'QueryMethod');
        $this->assertIdentical(array('all'), $f->args);
        $this->assertIdentical(array('id', 'title'), $f->fields);
        $this->assertIdentical('id ASC', $f->order);
        $this->assertIdentical(array('User.id' => 3,
                                     'User.created >' => '2010-01-01'),
                               $f->conditions);
        $this->assertIdentical(20, $f->limit);

        $f2 = $model->finder('first', 'more', 'approved')
            ->conditions('title IS NOT NULL');
        $this->assertIsA($f2, 'QueryMethod');
        $this->assertIdentical(array('status' => 'approved',
                                     'title IS NOT NULL'),
                               $f2->conditions);
        $this->assertIdentical(100, $f2->limit);
        
    }

}
