<?php
declare(strict_types=1);

abstract class ActiveRecordModel
{
    /**
     * The attributes that belongs to the table
     * @var  Array
     */
    protected $attributes = array();
    /**
     * Table name
     * @var  String
     */
    protected $table_name;
    /**
     * Username
     * @var String
     */
    protected $username;
    /**
     * password
     * @var  String
     */
    protected $password;
    /**
     * The DBMS hostname
     * @var  String
     */
    protected $hostname;
    /**
     * The database name
     * @var  String
     */
    protected $dbname;
    /**
     * The DBMS connection port
     * @var  String
     */
    protected $port = "3306";

    protected $id_name = 'id';

    public function __construct(array $attributes = null)
    {
        $this->attributes = $attributes;
    }

//    public function __set($key, $value)
//    {
//        $this->setAttribute($key, strval($value));
//    }

    public function __get(string $attributeName): ?string
    {
        return isset($this->attributes[$attributeName]) ? $this->attributes[$attributeName] : null;
    }

    protected function newInstance(array $data)
    {
        $class_name = get_class($this);
        return new  $class_name($data);
    }

    /**
     * Save the model
     * @return bool
     */
    protected function save(): bool
    {
        try {
            if (array_key_exists($this->id_name, $this->attributes)) {
                $attributes = $this->attributes;
                unset($attributes[$this->id_name]);
                $this->update($attributes);
            } else {
                $id = $this->insert($this->attributes);
                $this->setAttribute($this->id_name, $id);
            }
        } catch (ErrorException $e) {
            return false;
        }

        return true;
    }

    protected function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Used to prepare the PDO statement
     *
     * @param $connection
     * @param $values
     * @param $type
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function prepareStatement($connection, $values, $type)
    {
        if ($type == "insert") {
            $sql = "INSERT INTO {$this->table_name} (";
            foreach ($values as $key => $value) {
                $sql .= "{$key}";
                if ($value != end($values)) {
                    $sql .= ",";
                }
            }
            $sql .= ") VALUES(";
            foreach ($values as $key => $value) {
                $sql .= ":{$key}";
                if ($value != end($values)) {
                    $sql .= ",";
                }
            }
            $sql .= ")";
        } elseif ($type == "update") {
            $sql = "UPDATE {$this->table_name} SET ";
            foreach ($values as $key => $value) {
                $sql .= "{$key} =:{$key}";
                if ($value != end($values)) {
                    $sql .= ",";
                }
            }
            $sql .= " WHERE {$this->id_name}=:{$this->id_name}";
        } else {
            throw new InvalidArgumentException("PrepareStatement need to be insert,update or delete");
        }

        return $connection->prepare($sql);
    }

    /**
     * Used to insert a new record
     * @param array $values
     * @throws ErrorException
     * @return lastInsertId
     */
    protected function insert(array $values)
    {
        $connection = $this->getConnection();
        $statement = $this->prepareStatement($connection, $values, "insert");
        foreach ($values as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }

        $success = $statement->execute($values);
        if (!$success) {
            throw new ErrorException;
        }

        $id = $connection->lastInsertId();
        $connection = null;
        return $id;
    }

    /**
     * Get the connection to the database
     *
     * @throws  PDOException
     */
    protected function getConnection()
    {
        try {
            $conn = new PDO("mysql:host={$this->hostname};dbname={$this->dbname};port=$this->port", $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch (PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }

    /**
     * Update the current row with new values based on id
     *
     * @param array $values
     * @return bool
     * @throws ErrorException
     * @throws BadMethodCallException
     */
    protected function update(array $values)
    {
        if (!isset($this->attributes[$this->id_name])) {
            throw new BadMethodCallException("Cannot call update on an object non already fetched");
        }

        $connection = $this->getConnection();
        $statement = $this->prepareStatement($connection, $values, "update");
        foreach ($values as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }
        $statement->bindValue(":{$this->id_name}", $this->attributes[$this->id_name]);
        $success = $statement->execute();

        // update the current values
        foreach ($values as $key => $value) {
            $this->setAttribute($key, $value);
        }

        if (!$success) {
            throw new ErrorException;
        }

        $conn = null;
        return true;
    }

    protected function find(string $id)
    {
        $conn = $this->getConnection();
        $query = $conn->query("SELECT * FROM {$this->table_name} WHERE  {$this->id_name}= " . $conn->quote($id));
        $obj = $query->fetch(PDO::FETCH_ASSOC);

        $conn = null;
        return ($obj) ? $this->newInstance($obj) : null;
    }

    /**
     * Find rows given a where condition
     *
     * @param $where_condition
     * @return null|PDOStatement
     */
    protected function where(string $where_condition)
    {
        $conn = $this->getConnection();
        $query = $conn->query("SELECT * FROM {$this->table_name} WHERE {$where_condition}");
        $objs = $query->fetchAll(PDO::FETCH_ASSOC);
        // the model instantiated
        $models = array();

        if (!empty($objs)) {
            foreach ($objs as $obj) {
                $models[] = $this->newInstance($obj);
            }
        }

        $conn = null;
        return $models;
    }
}
