<?php
if(isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] > 0) {
	$query = "SELECT orders_id
			  FROM ".Config::db_table_orders()."
			  WHERE user_id=".$_GET['delete'];
	$orders = $this->db->select($query);

	foreach($orders as $row) {
		$query = "DELETE FROM ".Config::db_table_orders()."
				  WHERE orders_id=".$row['orders_id'];
		$this->db->delete($query);
		$query = "DELETE FROM ".Config::db_table_orders_items()."
				  WHERE orders_id=".$row['orders_id'];
		$this->db->delete($query);
	}

	$this->session->alert = $this->dictionary['item_deleted'];
	$this->session->alert_css_class = 'success left-icon';

	Common::redirect();
}

$order = isset($_GET['order']) && in_array($_GET['order'], array('fname', 'lname', 'email', 'status', 'inserted')) ? $_GET['order'] : "inserted";
$sort = isset($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')) ? $_GET['sort'] : "desc";

$query = "SELECT u.user_id, u.fname, u.lname, u.email, u.status, u.inserted, uv.int_val AS newsletter
		  FROM ".Config::db_table_orders()." o
		  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = o.user_id)
		  LEFT JOIN ".Config::db_table_user_value()." uv ON (uv.user_id = o.user_id AND uv.code='newsletter')
		  WHERE o.user_id > 0 AND
		  		u.deleted = '0'
		  GROUP BY u.user_id
		  ORDER BY u.".$order." ".$sort;
$items = $this->db->select($query);

$this->smarty->assign(array(
    'items' => $items,
    'order' => $order,
    'sort' => $sort,
));
