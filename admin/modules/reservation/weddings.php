<?php
if(isset($_POST['search'])) {
    $this->session->weddings_search = $_POST['search'];
    Common::redirect();
} elseif(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $query = "DELETE FROM `weddings` WHERE id=".$_GET['delete'];
    $this->db->delete($query);
    Common::redirect();
}

$search = $this->session->weddings_search;
$where = "";
if($search) {
    $where.= " WHERE (
                 `fname` LIKE '%".mysqli_escape_string(MySQL::$conn, $search)."%' OR
                 `lname` LIKE '%".mysqli_escape_string(MySQL::$conn, $search)."%' OR
                 `place` LIKE '%".mysqli_escape_string(MySQL::$conn, $search)."%' OR
                 `email` LIKE '%".mysqli_escape_string(MySQL::$conn, $search)."%' OR
                 `phone` LIKE '%".mysqli_escape_string(MySQL::$conn, $search)."%'
               )";
}

$perpage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$cnt = $this->db->select("SELECT COUNT(*) AS cnt FROM `weddings`".$where, true, 'cnt');

$pager = new Pager($cnt, $perpage, $page);
$pager->process();
$pagerResult = $pager->getPager();

$query = "SELECT *
		  FROM `weddings`
		  ".$where."
		  ORDER BY `datetime` DESC
		  LIMIT ".$pagerResult['from'].", ".$pagerResult['perpage'];
$items = $this->db->select($query);

$this->smarty->assign(array(
	'items' => $items,
    'pager' => $pagerResult,
    'search' => $search,
));
