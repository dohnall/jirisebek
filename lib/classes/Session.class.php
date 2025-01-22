<?php

class Session {

    private $data = array();
    private static $instance;

    public function __construct($section) {
        session_start();
        $this->data =& $_SESSION[$section];
    }

    public static function getInstance($section) {
        if (!isset(self::$instance)) {
            $classname = __CLASS__;
            self::$instance = new $classname($section);
        }
        return self::$instance;
    }

    public function __set($name, $val) {
        $this->data[$name] = $val;
    }

    public function __get($name) {
        if(isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function __unset($name) {
        if(isset($this->data[$name])) {
            unset($this->data[$name]);
        }
    }

    public function clear() {
        $this->data = array();
    }

    /**
     * Advanced method for setting of session variables, using arrays
     * @param string $name name of class variable
     * @param string $key array key of class variable
     * @param mixed $val value of class variable
     */
    public function assign($name, $key, $val) {
        $this->data[$name][$key] = $val;
    }

    /**
     * Advanced method for getting of class variables, using arrays
     * @access public     
     * @param string $name name of class variable
     * @param string $key array key of class variable
     * @return mixed     
     */
    public function getAssigned($name, $key) {
        if(isset($this->data[$name][$key])) {
            return $this->data[$name][$key];
        }
    }

    /**
     * Advanced method for unsetting of required session variable, using arrays
     * @access public     
     * @param string $name name of class variable
     * @param string $key array key of class variable
     */
    public function remove($name, $key) {
        if(isset($this->data[$name][$key])) {
            unset($this->data[$name][$key]);
        }
    }

}
