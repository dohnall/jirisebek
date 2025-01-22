<?php
Common::redirect(CMSROOT.'?module=newsletter&submodule=nuser');
if(isset($_POST['action']) && $_POST['action'] == "deleteItem") {
    $this->db->update("DELETE FROM ".Config::db_table_newsletter()." WHERE `newsletter_id`='".$_POST['item_id']."'");
    $this->db->delete("DELETE FROM ".Config::db_table_nqueue()." WHERE `newsletter_id`='".$_POST['item_id']."'");
    Common::redirect();
}

$query = "SELECT * FROM ".Config::db_table_newsletter()." ORDER BY inserted DESC";
$records = $this->db->select($query);

$this->smarty->assign(array(
    'records' => $records,
));
