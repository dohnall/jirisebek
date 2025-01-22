<?php

class Database {

    public static function connect($host = "", $user = "", $pass = "", $name = "", $charset = "") {
        $host = !$host ? DBHOST : $host;
        $user = !$user ? DBUSER : $user;
        $pass = !$pass ? DBPASS : $pass;
        $name = !$name ? DBNAME : $name;
        $charset = !$charset ? DBCSET : $charset;

        if(DBTYPE === "mysql") {
            return MySQL::getInstance($host, $user, $pass, $name, $charset);
        }
    }

}
