<?php


class Db_object
{
    public static function find_all(){
        return static::find_this_query("SELECT * FROM " . static::$db_table);
        //return static::find_this_query("SELECT * FROM users");
    }

    public static function find_by_id($id){
        $result = static::find_this_query("SELECT * FROM " . static::$db_table . " WHERE id=$id LIMIT 1");
        //$result = static::find_this_query("SELECT * FROM user WHERE id=$users_id");
        /*if(!empty($result)){
            return array_shift($result);
        }else{
            return false;
        }*/
        return !empty($result) ? array_shift($result) : false;
    }

    public static function find_this_query($sql){
        global $database;
        $result = $database->query($sql);
        $the_object_array = array();
        while ($row = mysqli_fetch_array($result)){
            $the_object_array[] = static::instantie($row);
        }
        return $the_object_array;
    }

    public static function instantie($result){
        $calling_class = get_called_class(); //static late binding
        $the_object = new $calling_class();
        /*$the_object->id = $result['id'];
        $the_object->username = $result['username'];
        $the_object->password = $result['password'];
        $the_object->first_name = $result['first_name'];
        $the_object->last_name = $result['last_name'];*/

        foreach ($result as $the_attribute => $value){
            if($the_object->has_the_attribute($the_attribute)){
                $the_object->$the_attribute = $value;
            }
        }
        return $the_object;
    }

    private function has_the_attribute($the_attribute){
        $object_properties = get_object_vars($this);

        return array_key_exists($the_attribute, $object_properties);
    }

    public function save(){
        return isset($this->id) ? $this->update() : $this->create();
    }

    public function create(){
        global $database;
        $properties = $this->clean_properties();

        $sql = "INSERT INTO " . static::$db_table . " (" . implode(",",array_keys($properties)) . ")";
        $sql .= " VALUES ('" . implode("','", array_values($properties)) . "')";
        /* $sql .= $database->escape_string($this->username) . "', '";
         $sql .= $database->escape_string($this->password) . "', '";
         $sql .= $database->escape_string($this->first_name) . "', '";
         $sql .= $database->escape_string($this->last_name) . "')";*/
// INSERT INTO user (username, password, first_name, last_name) VALUES ('gebruiker', '1478252', 'voornaam', 'familienaam')
        var_dump($sql);
        if($database->query($sql)){
            $this->id = $database->the_insert_id();
            return true;
        }else{
            return false;
        }
    }

    public function update(){
        global $database;
        $properties = $this->clean_properties();
        $properties_assoc = array();

        foreach ($properties as $key => $value){
            $properties_assoc[] = "{$key}='{$value}'";
        }
        //UPDATE user SET username = 'gebruiker', password = '7852', first_name = 'voornaam', last_name = 'familienaam'
        $sql = "UPDATE " . static::$db_table . " SET ";
        $sql .= implode(", ", $properties_assoc);
        /*$sql .= "username = '" . $database->escape_string($this->username) . "', ";
        $sql .= "password = '" . $database->escape_string($this->password) . "', ";
        $sql .= "first_name = '" . $database->escape_string($this->first_name) . "', ";
        $sql .= "last_name = '" . $database->escape_string($this->last_name) . "' ";*/
        $sql .= "WHERE id= " . $database->escape_string($this->id);

        $database->query($sql);
        return (mysqli_affected_rows($database->connection) == 1) ? true : false;
    }

    public function delete(){
        global $database;

        $sql = "DELETE FROM " . static::$db_table;
        $sql .= " WHERE id= " . $database->escape_string($this->id);
        $sql .= " LIMIT 1";

        $database->query($sql);
        return (mysqli_affected_rows($database->connection) == 1) ? true : false;
    }

    protected function properties(){
        //return get_object_vars($this);
        $properties = array();
        foreach (static::$db_table_fields as $db_field){
            if(property_exists($this, $db_field)){
                $properties[$db_field] = $this->$db_field;
            }
        }
        return $properties;
    }

    protected function clean_properties(){
        global $database;
        $clean_properties = array();
        foreach ($this->properties() as $key => $value){
            $clean_properties[$key] = $database->escape_string($value);
        }
        return $clean_properties;
    }
}