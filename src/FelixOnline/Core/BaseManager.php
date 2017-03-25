<?php
namespace FelixOnline\Core;

use \FelixOnline\Exceptions\InternalException;
use \FelixOnline\Exceptions\SQLException;

/**
 * Base manager
 */
class BaseManager
{
    /**
     * database table
     */
    public $table;

    /**
     * object class name
     */
    public $class;

    /**
     * primary key
     */
    public $pk = 'id';

    /**
     * Array of query filters
     */
    public $filters = array();

    /**
     * Order statement
     */
    protected $order;

    /**
     * Limit
     */
    protected $limit;

    /**
     * Group
     */
    protected $group;

    /**
     * Joins
     */
    protected $joins = array();

    /**
     * Unions
     */
    protected $unions = array();

    /**
     * Cache flag
     */
    protected $cache = false;

    /**
     * Random flag
     */
    protected $random = false;

    /**
     * Distinct select flag
     */
    protected $distinct = false;

    /**
     * Cache expiry
     */
    protected $cacheExpiry = null;

    /**
     * Allow fetch of deleted records
     */
    protected $allowDeleted = false;

    public static function build($class, $table = null, $pk = null) {
        $manager = new self();
        $manager->class = $class;

        if (!is_null($table)) {
            $manager->table = $table;
        } else {
            $temp = new $class;
            $manager->table = $temp->dbtable;
        }

        if (!is_null($pk)) {
            $manager->pk = $pk;
        } else {
            $temp = new $class;
            $manager->pk = $temp->pk;
        }

        return $manager;
    }

    public function allowDeleted() {
        $this->allowDeleted = true;

        return $this;
    }

    /**
     * Get all objects
     */
    public function all() {
        $_filters = $this->filters; // store filters

        if(!$this->allowDeleted) {
            $this->filters = array("`" . $this->table . "`.deleted = 0"); // reset them
        } else {
            $this->filters = array();
        }

        $values = $this->values();

        $this->filters = $_filters; // restore filters

        return $values;
    }

    /**
     * Filter objects
     */
    public function filter($filter, $values = array(), $or = array()) {
        return $this->filterOnSpecifiedTable($this->table, $filter, $values, $or);
    }

    public function filterOnSpecifiedTable($table, $filter, $values = array(), $or = array()) {
        $app = \FelixOnline\Core\App::getInstance();

        if (!is_array($values)) {
            throw new InternalException('Values is not an array');
        }

        $filter = trim($filter);

        $string = '';

        if(count($or) > 0) {
            $string .= '(';
        }

        $string .= "`" . $table . "`." . $app['safesql']->query($filter, $values);

        if(count($or) > 0) {
            foreach($or as $orStatement) {
                $string .= " OR `" . $table . "`." . $app['safesql']->query($orStatement[0], $orStatement[1]);
            }

            $string .= ')';
        }

        $this->filters[] = $string;

        return $this;
    }

    /**
     * Order objects
     */
    public function order($columns, $order) {
        $colArray = array();

        if(is_array($columns)) {
            foreach($columns as $column) {
                $colArray[] = array($column, $order);
            }
        } else {
            $colArray[] = array($columns, $order);
        }

        $this->multiOrder($colArray);
        return $this;
    }

    /**
     * Order objects - multiple columns with different sort orders
     */
    public function multiOrder($columns) {
        $this->order = $columns;
        return $this;
    }

    /**
     * Add limit to query
     */
    public function limit($offset, $number) {
        $this->limit = array($offset, $number);
        return $this;
    }

    /**
     * Make the select result appear in random order
     */
    public function randomise($switch = true) {
        $this->random = $switch;
        return $this;
    }

    /**
     * Add UNION
     */
    public function union($q2) {
        $this->unions[] = $q2;
        return $this;
    }

    /**
     * Add grouping to query
     */
    public function group($group) {
        if(!is_array($group)) {
            $this->group = array($group);
        } else {
            $this->group = $group;
        }

        return $this;
    }

    /**
     * Get count
     */
    public function count() {
        $sql = $this->getCountSql();
        $results = $this->query("SELECT COUNT(*) AS count FROM (".$sql.") AS result");

        return (int) $results[0]->count;
    }

    /**
     * Get SQL for Count
     */
    public function getCountSQL() {
        $statement = [];

        if($this->distinct) {
            $distinct = 'DISTINCT ';
        } else {
            $distinct = '';
        }

        $statement[] = "(SELECT $distinct`" . $this->table . "`.*";
        $statement[] = $this->getFrom();
        $statement[] = $this->getJoin();
        $statement[] = $this->getWhere();
        $statement[] = $this->getOrder();
        $statement[] = ")";

        foreach($this->unions as $union) {
            $statement[] = "UNION ".$distinct;
            $statement[] = $union->getCountSQL();
        }

        // Remove null values
        $statement = array_filter($statement);

        $sql = implode("\n", $statement);

        return $sql;
    }

    /**
     * Get values
     */
    public function values($distinct = false) {
        $this->distinct = $distinct;

        $sql = $this->getSQL();

        $results = $this->query($sql);

        if (is_null($results)) {
            return null;
        }

        $models = $this->resultToModels($results);

        return $models;
    }

    /**
     * Get sql
     */
    public function getSQL() {
        $statement = [];

        if($this->distinct) {
            $distinct = 'DISTINCT ';
        } else {
            $distinct = '';
        }

        $statement[] = "(SELECT $distinct`" . $this->table . "`.*";
        $statement[] = $this->getFrom();
        $statement[] = $this->getJoin();
        $statement[] = $this->getWhere();

        $statement[] = ")";

        foreach($this->unions as $union) {
            $statement[] = "UNION ".$distinct;
            $statement[] = $union->getSQL();
        }

        if($this->unions) {
            $statement[] = $this->getGroup(true);
            $statement[] = $this->getOrder(true);
        } else {
            $statement[] = $this->getGroup(false);
            $statement[] = $this->getOrder(false);
        }

        $statement[] = $this->getRandom();
        $statement[] = $this->getLimit();

        // Remove null values
        $statement = array_filter($statement);

        return implode("\n", $statement);
    }

    /**
     * Get one
     */
    public function one() {
        $_limit = $this->limit;
        $this->limit = null;

        $values = $this->values();

        if (is_null($values)) {
            throw new InternalException('No results');
        }

        if (count($values) > 1) {
            throw new InternalException('More than one result');
        }

        $this->limit = $_limit;
        return $values[0];
    }

    /**
     * Join managers together
     */
    public function join(BaseManager $manager, $type = null, $column = null, $column_right = null) {
        $this->joins[$manager->table] = array(
            'manager' => $manager,
            'type' => $type,
            'column' => $column,
            'column_right' => $column_right
        );
        return $this;
    }

    /**
     * Set cache status
     */
    public function cache($flag, $expiry = null) {
        $this->cache = (boolean) $flag;

        if (!is_null($expiry)) {
            $this->cacheExpiry = $expiry;
        }
        return $this;
    }

    /**
     * From
     */
    protected function getFrom() {
        return "FROM `" . $this->table . "`";
    }

    /**
     * Get Join
     */
    protected function getJoin() {
        if (!empty($this->joins)) {
            $joins = array();
            foreach ($this->joins as $join) {
                $manager = $join['manager'];
                $st = array();

                if ($join['type']) {
                    $st[] = $join['type'];
                }

                if ($join['column']) {
                    $column = $join['column'];
                } else {
                    $column = $this->pk;
                }

                if ($join['column_right']) {
                    $column_right = $join['column_right'];
                } else {
                    $column_right = $manager->pk;
                }

                $st[] = "JOIN `" . $manager->table . "`";

                $st[] = "ON (";
                $st[] = "`" . $this->table . "`.`" . $column . "`";
                $st[] = "=";
                $st[] = "`" . $manager->table . "`.`" . $column_right . "`";
                $st[] = ")";
                $joins[] = implode(' ', $st);
                $joins[] = $manager->getJoin();
            }
            return implode("\n", $joins);
        }
        return null;
    }

    /**
     * Where
     */
    protected function getWhere() {
        $filters = $this->getWhereAsArray();

        $string = '';

        foreach($filters as $filter) {
            if($string == '') {
                $string .= 'WHERE ';
            } else {
                $string .= "\nAND ";
            }

            $string .= $filter;
        }

        return $string;
    }

    /**
     * Where as array for recursive joins
     */
    protected function getWhereAsArray() {
        $filters = [];

        if (!empty($this->filters)) {
            $filters = $this->filters;
        }

        if(!$this->allowDeleted) {
            $filters[] = "(`" . $this->table . "`.deleted = 0 OR `" . $this->table . "`.deleted IS NULL)";
        }

        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $manager = $join['manager'];
                $filters = array_merge($filters, $manager->getWhereAsArray());
            }
        }

        return $filters;
    }

    /**
     * Order
     */
    protected function getOrder($tableless = false) {
        if ($this->order) {
            $order = "ORDER BY ";

            $first = true;

            foreach($this->order as $orderItem) {
                if(!$first) {
                    $order .= ", ";
                }

                $order .= $this->getColumnReference($orderItem[0], $tableless);
                $order .= " ";
                $order .= $orderItem[1];

                $first = false;
            }

            return $order;
        }
        return null;
    }

    /**
     * Random Order
     */
    protected function getRandom() {
        if ($this->random) {
            $random = "ORDER BY RAND() ASC";

            return $random;
        }
        return null;
    }

    /**
     * Group
     */
    protected function getGroup($tableless = false) {
        if ($this->group) {
            $group = "GROUP BY ";

            $first = true;

            foreach($this->group as $groupCol) {
                if(!$first) {
                    $group .= ", ";
                }

                $group .= $this->getColumnReference($groupCol, $tableless);

                $first = false;
            }

            return $group;
        }
        return null;
    }

    /**
     * Get column reference
     */
    protected function getColumnReference($column, $tableless) {
        if($tableless) {
            return $column;
        }

        // check if table is already defined
        if (count(explode(".", $column)) > 1) {
            return $column;
        }

        return "`" . $this->table . "`.`" . $column . "`";
    }

    /**
     * Limit
     */
    protected function getLimit() {
        if ($this->limit) {
            return "LIMIT " . implode(", ", $this->limit);
        }
        return null;
    }

    /**
     * Query sql
     */
    protected function query($sql) {
        $GLOBALS['current_sql'] = $sql;

        $app = \FelixOnline\Core\App::getInstance();

        $item = null;
        if ($this->cache == true) {
            $item = $app['cache']->getItem($this->table.'/'.md5($sql));
            $results = $item->get(\Stash\Invalidation::PRECOMPUTE, 300);
        }

        if ($item && !$item->isMiss()) {
            return $results;
        }

        set_error_handler(function($errno, $errstr) {
            $sql = $GLOBALS['current_sql']; // $sql in query function not in scope here - this is a nasty hack
            unset($GLOBALS['current_sql']);

            throw new SQLException($errstr, $sql);
        });
        $results = $app['db']->get_results($sql);
        restore_error_handler(); // restore old error handler

        if ($app['db']->last_error) {
            unset($GLOBALS['current_sql']);
            throw new SQLException($app['db']->last_error, $app['db']->captured_errors);
        }

        if ($item) {
            if ($this->cacheExpiry) {
                $item->expiresAfter($this->cacheExpiry);
            }
            $app['cache']->save($item->set($results));
        }

        unset($GLOBALS['current_sql']);

        return $results;
    }

    /**
     * Map result to models
     */
    protected function resultToModels($result) {
        $models = array();
        foreach ($result as $r) {
            $pk = $r->{$this->pk};

            try {
                // It is inefficient to fetch every record from the DB here
                // Instead, pass in the result data
                $models[] = new $this->class($pk, $r);
            } catch(\Exception $e) { }
        }
        return $models;
    }
}
