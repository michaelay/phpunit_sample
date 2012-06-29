<?php

include('pear/db/db.class.php');

/**
 * User model 
 * store things on DB
 */

class User { 

    static public function create($name) { 
        // put DB info in config in real env 
        $db = new DBConnection('localhost', 'root', '', 'sample'); 

        $query = "INSERT INTO users SET name='{$name}'";
        $res = $db->rq($query);
        $affected_rows = $db->affected_rows($res);
        return $affected_rows;
    } 

    public function getFirstFriend() { 
        $friends = $this->getFriends(); 
        return $friends[0];
    } 

    private function getFriends() { 
        // some webservice to get friends, will be stub in the test cases
        return array('friend1', 'friend2', 'friend3');
    } 

} 
