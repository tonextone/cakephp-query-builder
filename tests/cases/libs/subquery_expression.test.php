<?php

App::import('Lib', 'QueryBuilder.QueryOptions');
App::import('Datasource', 'DboSource');
Mock::generate('Model');
Mock::generate('DboSource');

class SubqueryExpressionTestCase extends CakeTestCase {
    var $model, $dbo;
    var $q;

    function startTest() {
        $this->dbo = new MockDboSource();
        $this->model = new MockModel();
        $this->model->setReturnReference('getDataSource', $this->dbo);
        $this->q = new SubqueryExpression($this->model);
    }

    function endTest() {
        ClassRegistry::flush();
    }

    function testInit() {
        $this->assertIsA($this->q, 'QueryOptions');
        $this->assertTrue(isset($this->q->type));
        $this->assertEqual('expression', $this->q->type);
    }

    function testToString() {
        $options = array('table' => 'users',
                         'alias' => 'User2',
                         'fields' => 'User2.id',
                         'limit' => 10,
                         'conditions' => array('User2.status' => 'A'));
        $expectedOptions = am($this->q->subqueryDefaults,
                              $options,
                              array('fields' => array($options['fields'])));

        $sql = 'SELECT User2.id FROM users2 AS User2 ....';

        $this->model->expectCallCount('getDataSource', 2);
        $this->dbo->expectCallCount('buildStatement', 2);
        $this->dbo->setReturnValue('buildStatement', $sql);
        
        $this->dbo->expectAt(0, 'buildStatement',
                             array($expectedOptions,
                                   '*' /* This wildcard is required to avoid recursive comparison */));
        $this->dbo->expectAt(1, 'buildStatement',
                             array($expectedOptions,
                                   '*' /* This wildcard is required to avoid recursive comparison */));

        $this->q
            ->table('users')
            ->alias('User2')
            ->fields('User2.id')
            ->limit(10)
            ->User2_status('A');

        $this->assertEqual($sql, $this->q->__toString());

        // value property calls __toString
        $this->assertEqual("(". $sql .")", $this->q->value);
    }

    function testTableOrAlias() {
        $q = $this->q;

        $this->assertNull($q->table);
        $this->assertNull($q->alias);

        $this->assertIdentical($q, $q->tableOrAlias('users'));
        $this->assertIdentical('users', $q->table);
        $this->assertNull($q->alias);

        $this->assertIdentical($q, $q->tableOrAlias('groups_users'));
        $this->assertIdentical('groups_users', $q->table);
        $this->assertNull($q->alias);

        $this->assertIdentical($q, $q->tableOrAlias('GroupsUser'));
        $this->assertIdentical('groups_users', $q->table);
        $this->assertIdentical('GroupsUser', $q->alias);

        
    }

}
