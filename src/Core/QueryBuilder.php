<?php

namespace App\Core;

class QueryBuilder
{
    private $db;
    private $table;
    private $select = ['*'];
    private $where = [];
    private $orderBy = [];
    private $limit = null;
    private $offset = null;
    private $joins = [];
    private $groupBy = [];
    private $having = [];
    private $cache = null;
    private $cacheTime = 300; // 5분
    private $queryLog = [];
    private $explain = false;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($columns)
    {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (is_callable($column)) {
            $this->where[] = ['type' => 'group', 'callback' => $column];
        } else {
            if ($value === null) {
                $value = $operator;
                $operator = '=';
            }
            $this->where[] = ['type' => 'basic', 'column' => $column, 'operator' => $operator, 'value' => $value];
        }
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = ['column' => $column, 'direction' => strtoupper($direction)];
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        if (is_callable($first)) {
            $this->joins[] = ['table' => $table, 'type' => $type, 'callback' => $first];
        } else {
            $this->joins[] = [
                'table' => $table,
                'type' => $type,
                'first' => $first,
                'operator' => $operator,
                'second' => $second
            ];
        }
        return $this;
    }

    public function groupBy($columns)
    {
        $this->groupBy = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function having($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->having[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    public function cache($time = null)
    {
        $this->cache = true;
        if ($time !== null) {
            $this->cacheTime = $time;
        }
        return $this;
    }

    public function explain()
    {
        $this->explain = true;
        return $this;
    }

    private function buildWhere()
    {
        if (empty($this->where)) {
            return '';
        }

        $conditions = [];
        foreach ($this->where as $where) {
            if ($where['type'] === 'basic') {
                $conditions[] = sprintf(
                    "%s %s ?",
                    $where['column'],
                    $where['operator']
                );
            } elseif ($where['type'] === 'group') {
                $builder = new self($this->db);
                $where['callback']($builder);
                $conditions[] = '(' . $builder->buildWhere() . ')';
            }
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }

    private function buildJoins()
    {
        if (empty($this->joins)) {
            return '';
        }

        $joins = [];
        foreach ($this->joins as $join) {
            if (isset($join['callback'])) {
                $builder = new self($this->db);
                $join['callback']($builder);
                $joins[] = sprintf(
                    "%s JOIN %s ON %s",
                    $join['type'],
                    $join['table'],
                    $builder->buildWhere()
                );
            } else {
                $joins[] = sprintf(
                    "%s JOIN %s ON %s %s %s",
                    $join['type'],
                    $join['table'],
                    $join['first'],
                    $join['operator'],
                    $join['second']
                );
            }
        }

        return implode(' ', $joins);
    }

    private function buildQuery()
    {
        $sql = sprintf(
            "SELECT %s FROM %s %s %s %s %s %s %s",
            implode(', ', $this->select),
            $this->table,
            $this->buildJoins(),
            $this->buildWhere(),
            !empty($this->groupBy) ? 'GROUP BY ' . implode(', ', $this->groupBy) : '',
            !empty($this->having) ? 'HAVING ' . implode(' AND ', array_map(function($having) {
                return sprintf("%s %s ?", $having['column'], $having['operator']);
            }, $this->having)) : '',
            !empty($this->orderBy) ? 'ORDER BY ' . implode(', ', array_map(function($order) {
                return sprintf("%s %s", $order['column'], $order['direction']);
            }, $this->orderBy)) : '',
            $this->limit !== null ? "LIMIT {$this->limit}" : '',
            $this->offset !== null ? "OFFSET {$this->offset}" : ''
        );

        if ($this->explain) {
            $sql = "EXPLAIN " . $sql;
        }

        return $sql;
    }

    private function getParameters()
    {
        $parameters = [];
        foreach ($this->where as $where) {
            if ($where['type'] === 'basic') {
                $parameters[] = $where['value'];
            }
        }
        foreach ($this->having as $having) {
            $parameters[] = $having['value'];
        }
        return $parameters;
    }

    public function get()
    {
        $sql = $this->buildQuery();
        $parameters = $this->getParameters();

        if ($this->cache) {
            $cacheKey = md5($sql . serialize($parameters));
            $cached = $this->db->getCache($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $startTime = microtime(true);
        $result = $this->db->query($sql, $parameters)->fetchAll();
        $executionTime = microtime(true) - $startTime;

        $this->logQuery($sql, $parameters, $executionTime);

        if ($this->cache) {
            $this->db->setCache($cacheKey, $result, $this->cacheTime);
        }

        return $result;
    }

    public function first()
    {
        $this->limit(1);
        $result = $this->get();
        return $result ? $result[0] : null;
    }

    private function logQuery($sql, $parameters, $executionTime)
    {
        $this->queryLog[] = [
            'sql' => $sql,
            'parameters' => $parameters,
            'time' => $executionTime,
            'timestamp' => microtime(true)
        ];
    }

    public function getQueryLog()
    {
        return $this->queryLog;
    }
} 