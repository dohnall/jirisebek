<?php
/**
 * General class for manipulation of eshop basket
 * @author Lukas Dohnal <dohnal@pharos.cz>
 * @version 1.0  
 */
class Basket {

    /**
     * Session variable differential
     * @access protected
     */
    protected $identifier = 'basket';

    /**
     * Singleton class instance holder
     * @access private static
     */
    private static $instance;

    /**
     * database instance variable
     * @access private
     */
    private $db;

    /**
     * session instance variable
     * @access private
     */
    private $session;

    /**
     * Standard contructor for new instance of basket
     * @access public
     */
    public function __construct() {
        $this->session = Session::getInstance($this->identifier);
    }
    
    /**
     * Singleton design pattern method
     * @access public
     * @return Basket
     */
    public static function getInstance() {
        if(!isset(self::$instance)) {
            $classname = __CLASS__;
            self::$instance = new $classname;
        }
        return self::$instance;
    }

    /**
     * Adds product into basket
     * @access public
     * @param int $product_id identifier of product
     * @param int $amount amount of product added to basket
     * @param boolean $increment accumulates amount of current product in basket when true, otherwise fixed amount
     */
    public function addProduct($product_id, $amount, $increment=false) {
        if($increment == true) {
            $product = $this->session->getAssigned($this->identifier, $product_id);
            $this->session->assign($this->identifier, $product_id, $amount + $product);
        } else {
            $this->session->assign($this->identifier, $product_id, $amount);
        }
    }

    /**
     * Removes product from basket
     * @access public
     * @param int $product_id identifier of product
     * @param boolean $increment accumulates amount of current product in basket when true, otherwise fixed amount
     */
    public function removeProduct($product_id) {
        $this->session->remove($this->identifier, $product_id);
    }

    /**
     * Removes all products from basket
     * @access public
     */
    public function emptyBasket() {
        unset($this->session->{$this->identifier});
    }

    /**
     * Gets current basket state information
     * @access public
     * @return array keys of the array are product identifiers, values amounts
     */
    public function getBasket() {
        return $this->session->{$this->identifier};
    }

}
