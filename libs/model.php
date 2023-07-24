<?php

include_once 'database.php';

class Model {

    function __construct(){
        $this->db = new Database();
    }

    function query($query){
        return $this->db->connect()->query($query);
    }

    function prepare($query){
        return $this->db->connect()->prepare($query);
    }

    function getDB(){
        return $this->db->connect();
    }
}

?>
