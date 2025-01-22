<?php

class Validator {

    private $data = array();
    private $rules = array();
    private $error = array();
    private $increment = 0;

    public function __construct($data) {
        $this->data = $data;
    }

    public function addRule($col, $type, $min = 0, $max = 0, $re = "") {
        $this->rules[] = array(
            'col' => $col,
            'type' => $type,
            'min' => $min,
            'max' => $max,
            're' => $re,
        );
    }

    public function validate() {
        foreach($this->rules as $rule) {
            $this->check_length($rule['col'], $rule['min'], $rule['max']);
            if(method_exists($this, "check_".$rule['type']) && (!isset($rule['re']) || !$rule['re'])) {
                $this->{"check_".$rule['type']}($rule['col']);
            } elseif($rule['re']) {
                $this->check_special($rule['col'], $rule['re']);
            }
        }
        return $this->error;
    }

    public function getErrors($errors, $dictionary) {
        $return = array();
        foreach($errors as $k => $error) {
            $keys = array_keys($error);
            $key = $keys[0];
            $return[$k] = sprintf($dictionary[$error[$key]], $dictionary[$key]);
        }
        return $return;
    }

    private function setError($col, $code) {
        $this->error[$this->increment++][$col] = $code;
    }

    private function check_length($col, $min = 0, $max = 0) {
        if($min > 0 && strlen($this->data[$col]) < $min) {
            $this->setError($col, "error_length_min");
        }

        if($max > 0 && strlen($this->data[$col]) > $max) {
            $this->setError($col, "error_length_max");
        }
    }

    private function check_required($col) {
        if(!isset($this->data[$col]) || empty($this->data[$col])) {
            $this->setError($col, "error_required");
        }
    }

    private function check_number($col) {
        if(!isset($this->data[$col]) || !preg_match("/[\d]+/", $this->data[$col])) {
            $this->setError($col, "error_number");
        }
    }

    private function check_email($col) {
        if(!isset($this->data[$col]) || !preg_match("/[\w\d\.\-\_]+@[\w\d\.\-\_]+\.[\w]{2,6}/", $this->data[$col])) {
            $this->setError($col, "error_email");
        }
    }

    private function check_password($col) {
        if(!isset($this->data[$col]) || !isset($this->data["re".$col]) || $this->data[$col] != $this->data["re".$col]) {
            $this->setError($col, "error_password");
        }
    }

    private function check_reemail($col) {
        if(!isset($this->data[$col]) || !isset($this->data["re".$col]) || $this->data[$col] != $this->data["re".$col]) {
            $this->setError($col, "error_reemail");
        }
    }

    private function check_phone($col) {
        if(!isset($this->data[$col]) || !preg_match("/[\+]?[\d\ ]{9,}/", $this->data[$col])) {
            $this->setError($col, "error_phone");
        }
    }

    private function check_special($col, $re) {
        if(!isset($this->data[$col]) || !preg_match($re, $this->data[$col])) {
            $this->setError($col, "error_special");
        }
    }

}
