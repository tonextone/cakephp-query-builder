<?php
/**
 * QueryBuilder Behavior
 * 
 * <code>
 * $model->finder('all')
 *   ->fields('id', 'title')
 *   ->Model_group_id(100)
 *   ->Model_title('LIKE', 'var')
 *   ->order('created DESC')
 *   ->limit(10)
 *   ->invoke();
 * </code>
 * 
 * @package QueryBuilder
 */

App::import('Lib', 'QueryBuilder.QueryOptions');

/**
 * QueryBuilderBehavior class
 * 
 * @package QueryBuilder
 */
class QueryBuilderBehavior extends ModelBehavior {

    /**
     * 
     * @param object  Model
     * @param string  find type
     * @return QueryMethod
     */
    public function finder($model, $type) {
        $finder = $this->createQueryMethod($model, 'find', array($type));
        return $finder;
    }

    /**
     * Creates a new QueryMethod object bound to the $model.
     * 
     * @param object  Model
     * @param string  method name
     * @param array   arguments
     * @return QueryMethod
     */
    public function createQueryMethod($model, $method, $args=array()) {
        return new QueryMethod($model, $method, $args);
    }

}

