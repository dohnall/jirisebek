<?php
$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($_POST['save'])) {
	$query = "UPDATE ".Config::db_table_orders()." SET
				user_id = ".$_POST['user_id'].",
				fname = '".$_POST['fname']."',
				lname = '".$_POST['lname']."',
				email = '".$_POST['email']."',
				phone = '".$_POST['phone']."',
				street = '".$_POST['street']."',
				city = '".$_POST['city']."',
				zip = '".$_POST['zip']."',
				company_buy = '".(isset($_POST['company_buy']) && $_POST['company_buy'] == 1 ? 1 : 0)."',
				company = '".$_POST['company']."',
				ico = '".$_POST['ico']."',
				dic = '".$_POST['dic']."',
				deliveryaddress = '".(isset($_POST['deliveryaddress']) && $_POST['deliveryaddress'] == 1 ? 1 : 0)."',
				dcompany = '".$_POST['dcompany']."',
				dfname = '".$_POST['dfname']."',
				dlname = '".$_POST['dlname']."',
				dstreet = '".$_POST['dstreet']."',
				dcity = '".$_POST['dcity']."',
				dzip = '".$_POST['dzip']."',
				delivery = '".$_POST['delivery']."',
				payment = '".$_POST['payment']."',
				delivery_price = '".$_POST['delivery_price']."',
				price = '".$_POST['price']."',
				status = '".$_POST['status']."'
			  WHERE orders_id = ".$item_id;
	$this->db->update($query);

    $this->session->alert = $this->dictionary['item_saved'];
    $this->session->alert_css_class = 'success left-icon';
    Common::redirect();
}

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
	$query = "SELECT *
			  FROM ".Config::db_table_orders()."
			  WHERE orders_id = ".$item_id;
	$data = $this->db->select($query, true);
}

$query = "SELECT * 
		  FROM ".Config::db_table_orders_items()."
		  WHERE orders_id = ".$item_id;
$data['items'] = $this->db->select($query);

$query = "SELECT u.user_id, u.fname, u.lname
		  FROM ".Config::db_table_orders()." o
		  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = o.user_id)
		  GROUP BY u.user_id
		  ORDER BY u.lname ASC, u.fname ASC";
$customers = $this->db->select($query);

$this->smarty->assign(array(
    'item_id' => $item_id,
    'data' => $data,
    'customers' => $customers,
));
