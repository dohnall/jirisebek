<?php
if(isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] > 0) {
	$query = "DELETE FROM ".Config::db_table_orders()."
			  WHERE orders_id=".$_GET['delete'];
	$this->db->delete($query);
	$query = "DELETE FROM ".Config::db_table_orders_items()."
			  WHERE orders_id=".$_GET['delete'];
	$this->db->delete($query);

	$this->session->alert = $this->dictionary['item_deleted'];
	$this->session->alert_css_class = 'success left-icon';

	Common::redirect();
}

$order = isset($_GET['order']) && in_array($_GET['order'], array('orders_id', 'lname', 'email', 'price', 'status', 'inserted')) ? $_GET['order'] : "inserted";
$sort = isset($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')) ? $_GET['sort'] : "desc";

$query = "SELECT o.orders_id, o.lname, o.email, o.price, o.status, o.inserted
		  FROM ".Config::db_table_orders()." o
		  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = o.user_id)
		  WHERE u.deleted = '0' OR
		  		o.user_id = 0
		  ORDER BY ".$order." ".$sort;
$items = $this->db->select($query);

$this->smarty->assign(array(
    'items' => $items,
    'order' => $order,
    'sort' => $sort,
));
