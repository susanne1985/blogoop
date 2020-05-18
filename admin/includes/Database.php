<?php

require_once ('config.php');
class Database
{
    public $connection;
    function __construct()
    {
        $this->open_db_connection();
    }

    public function open_db_connection(){
        $this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        //var_dump(mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME));
        if(mysqli_connect_errno()){
            die("Database connection mislukt" . mysqli_error());
        }
    }

    public function query($sql){
        $result = $this->connection->query($sql);
        $this->confirm_query($result);
        return $result;
    }

    public function confirm_query($result){
        if(!$result){
            die("query kon niet worden uitgevoerd" . $this->connection->error);
        }
    }

    public function escape_string($string){
        $escaped_string = $this->connection->real_escape_string($string);
        return $escaped_string;
    }

    public function the_insert_id(){
        return mysqli_insert_id($this->connection);
    }
}
$database = new Database();
?>