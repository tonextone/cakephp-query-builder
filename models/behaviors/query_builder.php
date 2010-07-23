<?php
/**
 * QueryBuilder Behavior
 * 
 * <code>
 * $Model->finder('all')
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
     * @var array
     */
    public $errorTemplate = array('code' => 500,
                                  'name' => 'Missing Query Options');

    /**
     * This method is public but not available through the Models.
     * 
     * @param string  config name
     */
    public function _missingOptionsError($name) {
        return $this->errorTemplate + array('message' => $name);
    }

    /**
     * Returns a query options array named $name
     * 
     * @param string  option name
     * @return array
     */
    public function getQueryOptions($model, $name) {
        if(!isset($model->queryOptions) ||
           !isset($model->queryOptions[$name]) ||
           !is_array($model->queryOptions[$name])) {
            $model->cakeError('error',
                              $this->_missingOptionsError($name));
            return;
        }
        return $model->queryOptions[$name];
    }

    /**
     * <code>
     * $finder = $Model->finder('all')->fields(...)->conditions(...);
     * 
     * // using query options
     * $finder = $Model->finder('all', 'approved', 'normal_order')->limit(...);
     * </code>
     * 
     * @param object  Model
     * @param string  find type
     * @return QueryMethod
     */
    public function finder($model, $type) {
        $finder = $model->createQueryMethod('find', array($type));
        if(func_num_args() > 2) {
            $args = func_get_args();
            $finder->import(array_slice($args, 2));
        }
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

