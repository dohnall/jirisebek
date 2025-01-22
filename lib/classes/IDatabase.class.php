<?php
interface IDatabase {

    public function connect($host, $user, $pass, $name);
    public function execute($query);
	public function insert($query);
    public function update($query);
    public function replace($query);
    public function select($query, $one=false, $col="");
    public function delete($query);
    public function affected();
    public function lastId();
    public function begin();
    public function commit();
    public function rollback();
    public function log($action, $query);
    public function free($result);
    public function error($query);

}
