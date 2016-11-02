<?php
namespace Sentegrity\BusinessBundle\Services\Support\Database;


use Sentegrity\BusinessBundle\Handlers;

/**
 * MySQL Query builder
 *
 * @author: abraovic@gmail.com
 */

class MySQLQuery
{
    const SINGLE_ROW = 1;
    const MULTI_ROWS = 2;

    const CONN_MASTER = 1000;
    const CONN_SLAVE = 1001;

    ## LOGIC
    const _OR = "OR";
    const _AND = "AND";

    const _INCR = "INCR";
    const _DECR = "DECR";

    const _DESC = "DESC";

    /** @var \PDO $masterDbh */
    private $masterDbh;
    /** @var \PDO $slaveDbh */
    private $slaveDbh;

    private $rowCount;
    private $lastInsertId;
    private $dbh;
    private $oldDbh;
    private $slaveOn = 0;

    public $query = "";

    function __construct(\PDO $masterDbh, PDOSlave $slaveDbh)
    {
        $this->masterDbh = $masterDbh;
        // if there is no slave, use master connection
        $this->slaveDbh = ($slaveDbh->validSlave) ? $slaveDbh : $masterDbh;
        $this->dbh = $this->masterDbh;
        $this->oldDbh = $this->masterDbh;
    }

    /**
     * Manually select database on which you would like to perform operations for
     * current jos
     *
     * @deprecated
     * @param $db -> CONN_MASTER|CONN_SLAVE
     * @throws \Sentegrity\BusinessBundle\Exceptions\QueryFailedException
     */
    public function setDbh($db)
    {
        switch ($db) {
            case self::CONN_MASTER:
                $this->dbh = $this->masterDbh;
                break;
            case self::CONN_SLAVE:
                $this->dbh = $this->slaveDbh;
                break;
            default:
                throw new \Sentegrity\BusinessBundle\Exceptions\QueryFailedException("Invalid db selector use CONN_MASTER|CONN_SLAVE");
        }
    }

    /**
     * Executes query on slave connection
     */
    public function slave()
    {
        $this->oldDbh = $this->dbh;
        $this->dbh = $this->slaveDbh;
        $this->slaveOn = 1;
        return $this;
    }

    /**
     * Performs select query
     * @param $table -> name of table from which you want to select
     * @param $columns -> php array of columns names that you want to select
     * @param $where -> php array
     *                      There are few versions depend on where clause
     *                      1. null -> if you do not have where but you want to select all
     *                      2. array(
     *                          "column_name" =>
     *                              array(
     *                                  "value" => [actual_value],
     *                                  (optional)"logic" => [AND|OR],
     *                                  (optional)"in" => [0|1]
     *                              ),
     *                              ...
     *                          )
     *                         In case you have "in" than actual_value needs to be comma separated string
     *                         First column in where must never have logic key
     * @param $order -> php array
     *                      array("column" => [column_name], "type" => [DESC|ASC])
     * @param $limit -> php array
     *                      array("limit" => [0|...], "offset" => [0|...])
     * @param $special -> string -> this is a simple string that you can put at the end of the query
     *                      eg HAVING, ...
     * @return array $result
     * @return \stdClass $result
     *         It depends if there are multiple rows or a single
     * @throws \Sentegrity\BusinessBundle\Exceptions\QueryFailedException
     */
    public function select(
        $table,
        $columns,
        $where = [],
        $order = [],
        $limit = [],
        $special = "",
        $resultType = self::SINGLE_ROW,
        $fetchType = \PDO::FETCH_OBJ
    )
    {
        $query = "SELECT :columns FROM :table";
        if (!empty($where)) {
            $query .= " WHERE :where";
        } else {
            $where = null;
        }
        if ($special) {
            $query .= " " . $special;
        }
        if (!empty($order)) {
            $query .= " ORDER BY ";
            if (isset($order['column'])) {
                $query .= $order['column'] . " " . $order['type'];
            } else {
                $ordCnt = 0;
                foreach ($order as $rule) {
                    $query .= $rule['column'] . " " . $rule['type'];
                    if ($ordCnt < count($order) - 1) {
                        $query .= ", ";
                    }
                    $ordCnt++;
                }
            }
        }
        if (!empty($limit)) {
            $query .= " LIMIT " . $limit['offset'] . ", " . $limit['limit'];
        }

        $query = $this->buildQuery($query, $table, Handlers\JString::array2CSString($columns), $where);
        $result = $this->execute($query, $where)->fetchAll($fetchType);
        $this->setOldDbh();

        switch ($resultType) {
            case self::SINGLE_ROW:
            default:
                if (!empty($result)) {
                    return $result[0];
                }
                return null;
                break;
            case self::MULTI_ROWS:
                return $result;
                break;
        }
    }

    /**
     * Performs delete query
     * @param $table -> name of table from which you want to delete
     * @param $where -> php array
     *                      There are few versions depend on where clause
     *                      1. null -> if you do not have where but you want to delete all
     *                      2. array(
     *                          "column_name" =>
     *                              array(
     *                                  "value" => [actual_value],
     *                                  (optional)"logic" => [AND|OR],
     *                                  (optional)"in" => [0|1]
     *                              ),
     *                              ...
     *                          )
     *                         In case you have "in" than actual_value needs to be comma separated string
     *                         First column in where must never have logic key
     * @return bool
     * @throws \Sentegrity\BusinessBundle\Exceptions\QueryFailedException
     */
    public function delete($table, $where)
    {
        $query = "DELETE FROM :table WHERE :where";
        $query = $this->buildQuery($query, $table, null, $where);
        $result = $this->execute($query, $where);
        $this->setOldDbh();
        return ($result) ? true : false;
    }

    /**
     * Performs insert query
     * @param $table -> name of table into which you want to insert
     * @param $columns -> php array of columns names that you want to insert
     * @param $values -> php array
     *                      There are few versions depend on where clause
     *                      1. array(
     *                          "column_name" =>
     *                              array(
     *                                  "value" => [actual_value]
     *                              ),
     *                              ...
     *                          )
     *                      2. array(
     *                              array('value_for_column_one', ...),
     *                              ...
     *                         )
     *                         Number of items in an inner array must be same as number of columns
     * @param $multi -> Determines if you are inserting multiple rows in a single query
     * @return bool
     * @throws \Sentegrity\BusinessBundle\Exceptions\QueryFailedException
     */
    public function insert(
        $table,
        $columns,
        $values,
        $multi = false
    )
    {
        $query = "INSERT INTO :table (:columns) VALUES (:values)";

        $insertFields = "";
        $counter = 0;

        if ($multi) {
            $query = "INSERT INTO :table (:columns) VALUES :values";

            foreach ($values as $items) {
                $insertFields .= "(";
                $innerCounter = 0;
                foreach ($items as $item) {
                    $insertFields .= "'" . $item . "'";
                    if ($innerCounter < count($items) - 1) {
                        $insertFields .= ", ";
                    }
                    $innerCounter++;
                }
                $insertFields .= ")";

                if ($counter < count($values) - 1) {
                    $insertFields .= ", ";
                }
                $counter++;
            }

            $values = null;
        } else {
            foreach ($columns as $record) {
                $insertFields .= ":" . $record;
                if ($counter < count($columns) - 1) {
                    $insertFields .= ", ";
                }
                $counter++;
            }
        }

        $query = $this->buildQuery(
            $query,
            $table,
            Handlers\JString::array2CSString($columns),
            null,
            $insertFields
        );

        $result = $this->execute($query, $values);
        $this->lastInsertId = $this->dbh->lastInsertId();
        $this->setOldDbh();
        return ($result) ? true : false;
    }

    /**
     * Performs update query
     * @param $table -> name of table into which you want to update
     * @param $columns -> php array of columns names that you want to update
     *                     array("colum_name" => [value|INCR|DECR], ...)
     * @param $where -> php array
     *                      There are few versions depend on where clause
     *                      1. null -> if you do not have where but you want to select all
     *                      2. array(
     *                          "column_name" =>
     *                              array(
     *                                  "value" => [actual_value],
     *                                  (optional)"logic" => [AND|OR],
     *                                  (optional)"in" => [0|1]
     *                              ),
     *                              ...
     *                          )
     *                         In case you have "in" than actual_value needs to be comma separated string
     *                         First column in where must never have logic key
     * @param $custom -> custom string as part of where, do not forged add logic operator before string
     * @return bool
     * @throws \Sentegrity\BusinessBundle\Exceptions\QueryFailedException
     */
    public function update(
        $table,
        $columns,
        $where,
        $custom = ""
    )
    {
        $query = "UPDATE :table SET :columns WHERE :where";

        $updateFields = "";
        $counter = 0;
        foreach ($columns as $column => $record) {
            if (!is_string($record)) {
                $record = (string)$record;
            }
            if ($record != self::_INCR && $record != self::_DECR) {
                $updateFields .= $column . " = :" . $column;
            } else {
                $updateFields .= $column . " = " . $column . (($record == self::_INCR) ? "+" : "-") . "1";
            }
            if ($counter < count($columns) - 1) {
                $updateFields .= ", ";
            }
            $counter++;
        }

        $query = $this->buildQuery($query, $table, $updateFields, $where);
        if ($custom) {
            $query .= " " . $custom;
        }
        $result = $this->execute($query, $where, $columns);
        $this->setOldDbh();

        return ($result) ? true : false;
    }

    /**
     * Performs a raw query
     * @param $query
     * @param $fetch -> set is as true if query must return a result
     * @param $fetchType
     * @return array $result
     * @return \stdClass $result
     *         It depends if there are multiple rows or a single
     * @return bool
     * @throws \Sentegrity\BusinessBundle\Exceptions\QueryFailedException
     */
    public function raw($query, $fetch = false, $fetchType = \PDO::FETCH_OBJ)
    {
        $result = $this->execute($query);

        if ($fetch) {
            return $result->fetchAll($fetchType);
        }

        return $result;
    }

    /**
     * Gets a number of affected rows by last executed query
     * @return int
     */
    public function affectedRowCount()
    {
        return $this->rowCount;
    }

    /**
     * Gets a last insert id
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    private function execute($query, $where = null, $columns = null)
    {
        $qh = $this->dbh->prepare($query);
        if ($where) {
            foreach ($where as $key => $item) {
                if (isset($item['in'])) {
                    if ($item['in'] == 1) {
                        continue;
                    }
                }

                $binder = $key;
                if (isset($item['binder'])) {
                    $binder = $item['binder'];
                }

                if (is_int($item['value'])) {
                    $qh->bindValue(":" . $binder, (int)$item['value'], \PDO::PARAM_INT);
                } else {
                    $qh->bindValue(":" . $binder, $item['value']);
                }
            }
        }

        if ($columns) {
            foreach ($columns as $column => $record) {
                if (!is_string($record)) {
                    $record = (string)$record;
                }
                if ($record != self::_INCR && $record != self::_DECR) {
                    $qh->bindValue(":" . $column, $record);
                }
            }
        }

        $this->query = $query;

        try {
            $qh->execute();
            $this->rowCount = $qh->rowCount();
        } catch (\PDOException $e) {
            throw new \Sentegrity\BusinessBundle\Exceptions\QueryFailedException();
        }

        return $qh;
    }

    private function buildQuery(
        $query,
        $table,
        $columns = null,
        $where = null,
        $values = null
    )
    {
        $query = str_replace(":table", $table, $query);
        if ($columns) {
            $query = str_replace(":columns", $columns, $query);
        }
        if ($where) {
            $whereString = "";
            $counter = 0;
            foreach ($where as $key => $item) {
                $binder = $key;
                if (isset($item['binder'])) {
                    $binder = $item['binder'];
                }

                if (isset($item['r_key'])) {
                    $key = $item['r_key'];
                }

                if (isset($item['group_open'])) {
                    $whereString .= "(";
                }
                if ($counter < count($where) - 1) {
                    if (isset($item['logic'])) {
                        switch ($item['logic']) {
                            case self:: _AND:
                                $whereString .= " AND ";
                                break;
                            case self:: _OR:
                                $whereString .= " OR ";
                                break;
                        }
                    }
                }
                if (isset($item['in'])) {
                    if ($item['in'] == 1) {
                        $whereString .= $key . " IN (" . $item['value'] . ")";
                    } else {
                        $whereString .= $key . " " . (isset($item['operator']) ? $item['operator'] : "=") . " :" . $binder;
                    }
                } else if (isset($item['like'])) {
                    $whereString .= $key . " LIKE :" . $binder;
                } else {
                    $whereString .= $key . " " . (isset($item['operator']) ? $item['operator'] : "=") . " :" . $binder;
                }

                if (isset($item['group_close'])) {
                    $whereString .= ")";
                }
            }
            $query = str_replace(":where", $whereString, $query);
        }
        if ($values) {
            $query = str_replace(":values", $values, $query);
        }
        return $query;
    }

    private function setOldDbh()
    {
        if ($this->slaveOn) {
            $this->slaveOn = 0;
            $this->dbh = $this->masterDbh;
        }
    }
}
