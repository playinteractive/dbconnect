<?php
/**
 * @author    Salvador Baqués <salva@email5.com>
 * @link      https://stage.work
 * @copyright 2017 Stage Framework
 * @package   https://github.com/playinteractive
 */

namespace Stage;

class DBConnect
{
    public $mysqli = NULL;

    private $options = ['host' => 'localhost', 'port' => 3306, 'persistent' => FALSE, 'charset' => 'utf8mb4', 'offset' => '+00:00'];

    private $results = [];

    function __construct($username, $password, $database, array $options = array())
    {
        $this->options = array_merge($this->options, $options);
        $this->mysqli = new \mysqli($this->options['persistent'] ? 'p:' . $this->options['host'] : $this->options['host'], $username, $password, $database, $this->options['port']);
        $this->mysqli->set_charset($this->options['charset']);
        $this->mysqli->query("SET time_zone = '{$this->options['offset']}'");
    }

    function insert_id()
    {
        return $this->mysqli->insert_id;
    }

    function query($query, $free = true)
    {
        $result = $this->mysqli->query($query);

        if ($result === false) throw new \Exception($this->mysqli->error);

        if ($result instanceof \mysqli_result) {

            if ($free) {

                $this->results[] = $result;

                return $result;

            } else {

                return $result;
            }
        }

        return $result;
    }

    function multi_query($query)
    {
        $results = [];

        if ($this->mysqli->multi_query($query)) {

            do {

                if ($result = $this->mysqli->store_result()) {

                    $results[] = $result;

                    $result->free();
                }

            } while ($this->mysqli->more_results() && $this->mysqli->next_result());

        } else {

            throw new \Exception($this->mysqli->error, $this->mysqli->errno);
        }

        return $results;
    }

    function real_escape_string($str)
    {
        return $this->mysqli->real_escape_string($str);
    }

    function free_results()
    {
        foreach ($this->results as $result) if ($result instanceof \mysqli_result) $result->free();

        $this->results = [];
    }

    function __destruct()
    {
        $this->free_results();

        if ($this->mysqli) $this->mysqli->close();
    }
}
