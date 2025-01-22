<?php
/**
 * General class for paging on sites
 * @author Lukas Dohnal <dohnal@pharos.cz>
 * @version 1.0  
 */
class Pager {

    /**
     * Main class variable which holds the data information about pager
     * Contains keys: count, perpage, page, from, first, previous, next, last, pages
     * @access private
     */
    private $pager = array();

    /**
     * Variable for holding information about number of items to paging
     * @access private
     */
    private $count = 0;

    /**
     * Number of items per one page
     * @access private
     */
    private $perpage = 1;

    /**
     * Number of current page
     * @access private
     */
    private $page = 1;

    /**
     * Standard contructor for new instance of Pager
     * @access public
     * @param int $count count of all items to paging
     * @param int $perpage number of items per one page
     * @param int $page number of current page
     */
    public function __construct($count, $perpage, $page) {
        $this->count = $count;
        $this->perpage = $perpage;
        $this->page = $page;
    }

    /**
     * Main processing method
     * @access public
     */
    public function process() {
        $this->setLast();        
        $this->pager['first'] = 1;
        $this->setPrevious();
        $this->setNext();
        $this->setPages();
        $this->setFrom();
        $this->pager['count'] = $this->count;
        $this->pager['perpage'] = $this->perpage;
        $this->pager['page'] = $this->page;
    }

    /**
     * Returns current pager
     * @access public
     * @return array of pager information
     */
    public function getPager() {
        return $this->pager;
    }

    /**
     * Sets last possible page of pager
     * @access private
     */
    private function setLast() {
        $this->pager['last'] = ceil($this->count/$this->perpage);
    }

    /**
     * Sets current page in case if out of possible range of pager
     * @access private
     */
    private function setPage() {
        if($this->page < 1) {
            $this->page = 1;
        } elseif($this->page > $this->pager['last']) {
            $this->page = $this->pager['last'];
        }
    }

    /**
     * Sets previous possible page of pager
     * @access private
     */
    private function setPrevious() {
        $this->pager['previous'] = $this->page - 1;
        if($this->pager['previous'] < 1) {
            $this->pager['previous'] = 1;
        }
    }

    /**
     * Sets next possible page of pager
     * @access private
     */
    private function setNext() {
        $this->pager['next'] = $this->page + 1;
        if($this->pager['next'] > $this->pager['last']) {
            $this->pager['next'] = $this->pager['last'];
        }
    }

    /**
     * Sets possible numeric links of pager pages
     * @access private
     */
    private function setPages() {
        for($i=1; $i <= $this->pager['last']; $i++) {
            $this->pager['pages'][] = $i;
        }
    }

    /**
     * Sets from which item current page beggins
     * @access private
     */
    private function setFrom() {
        $this->pager['from'] = ($this->page - 1) * $this->perpage;
    }

}
