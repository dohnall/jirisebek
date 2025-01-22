<?php
$record_id = isset($_GET['id']) ? $_GET['id'] : 0;

$query = "SELECT * FROM ".Config::db_table_newsletter()." WHERE newsletter_id='".$record_id."'";
$data = $this->db->select($query, true);

$query = "SELECT ngroup_id FROM ".Config::db_table_newsletter_ngroup()." WHERE newsletter_id='".$record_id."'";
$groups = $this->db->select($query);
$data['ngroup'] = array();
foreach($groups as $row) {
	$data['ngroup'][] = $row['ngroup_id'];
}

if(isset($_POST['save'])) {
//d($_POST);
    $v = new Validator($_POST);
    $v->addRule('name', 'required');
	$v->addRule('subject', 'required');
    $error = $v->getErrors($v->validate(), $this->dictionary);
    if($error) {
        $this->session->alert = implode('<br />', $error);
        $this->session->alert_css_class = 'error';
        $this->session->data = $_POST;
        Common::redirect();
    } else {
		$data = $_POST;
	    $data['prepared'] = isset($data['prepared']) ? $data['prepared'] : 0;
        if($record_id) {
            $query = "UPDATE ".Config::db_table_newsletter()." SET name='".$data['name']."', subject='".stripslashes($data['subject'])."', content='".stripslashes($data['content'])."', prepared='".$data['prepared']."' WHERE newsletter_id='".$record_id."'";
            $this->db->update($query);
        } else {
            $query = "INSERT INTO ".Config::db_table_newsletter()." (`name`, `subject`, `content`, `prepared`, `inserted`) VALUES ('".$data['name']."', '".stripslashes($data['subject'])."', '".stripslashes($data['content'])."', '".$data['prepared']."', NOW())";
            $record_id = $this->db->insert($query);
        }

		//vlozeni do skupin
		$this->db->delete("DELETE FROM ".Config::db_table_newsletter_ngroup()." WHERE newsletter_id = ".$record_id);
		if(isset($data['ngroup']) && is_array($data['ngroup'])) {
			foreach($data['ngroup'] as $ngroup_id) {
				if($ngroup_id > 0) {
		            $query = "INSERT INTO ".Config::db_table_newsletter_ngroup()." (newsletter_id, ngroup_id) VALUES (".$record_id.", ".$ngroup_id.")";
		            $this->db->insert($query);
				}
	        }
		}

		//vytvoreni front prijemcu
        if($data['prepared'] == 1) {
			$query = "SELECT ngroup_id
					  FROM ".Config::db_table_newsletter_ngroup()."
					  WHERE newsletter_id = ".$record_id;
			$result = $this->db->select($query);
			$groups = array();
			if($result) {
				foreach($result as $row) {
					$groups[] = $row['ngroup_id'];
				}
			}

			if($groups) {
	            $query = "SELECT ug.nuser_id
						  FROM ".Config::db_table_nuser_ngroup()." ug
						  LEFT JOIN ".Config::db_table_nuser()." u ON (ug.nuser_id = u.nuser_id)
						  WHERE ug.ngroup_id IN (".implode(', ', $groups).") AND
						  		u.status = '1'";
	            $nusers = $this->db->select($query);
			} else {
	            $query = "SELECT `nuser_id` FROM ".Config::db_table_nuser()." WHERE `status`='1'";
	            $nusers = $this->db->select($query);
			}

            foreach($nusers as $nuser) {
                $query = "INSERT INTO ".Config::db_table_nqueue()." (`newsletter_id`, `nuser_id`) VALUE ('".$record_id."', '".$nuser['nuser_id']."')";
                $this->db->insert($query);
            }
        }

        $this->session->alert = $this->dictionary['item_saved'];
        $this->session->alert_css_class = 'success left-icon';
        Common::redirect(CMSROOT."?module=".$this->module);
    }
    Common::redirect();
} elseif(isset($_POST['sendTest'])) {
    $mail = new PHPMailer();
    $mail->IsMail();
    $mail->IsHTML(true);
    $mail->CharSet  = "utf-8";
    $mail->FromName = "Divadlo Zbraslav";
    $mail->From     = "kultura@mc-zbraslav.cz";
    $mail->WordWrap = 50;
    $mail->Subject  = $data['subject'];
    $mail->AddReplyTo("kultura@mc-zbraslav.cz");

	$this->smarty->assign(array(
		'subject' => $data['subject'],
		'content' => $data['content'],
		'DESIGN' => DESIGN,
		'SERVICE' => ROOT.'lib/service/',
	));
	$content = $this->smarty->fetch('newsletter-email.html');

    preg_match_all('/\<img.*src=\"(.*)\".*\/?\>/Ui', $content, $matches);
    $matches[1] = array_unique($matches[1]);

    foreach($matches[1] as $cid => $image) {
        $cid = "image".(++$cid);
        $newimage = str_replace(ROOT, LOCAL, $image);

        switch(substr($image, -3)) {
            case "jpg": $type = "image/jpeg"; break;
            case "gif": $type = "image/gif"; break;
            case "png": $type = "image/png"; break;
            default: $type = ""; break;
        }
        
        if(!$type) {
            continue;
        }

        $mail->AddEmbeddedImage($newimage, $cid, "", "base64", $type);
        $content = str_replace($image, "cid:".$cid, $content);
    }

    $mail->AddAddress($_POST['testEmail']);

    $mail->Body = $content;
    $mail->AltBody  =  var_export($content, true);

    $mail->Send();
    $mail->ClearAddresses();

	$this->session->alert = $this->dictionary['test_email_sent'];//"Test email was sent.";
	$this->session->alert_css_class = 'success left-icon';
    Common::redirect();
}

$this->data = $this->session->data ? $this->session->data : $data;

$query = "SELECT * FROM ".Config::db_table_ngroup()." ORDER BY `name` ASC";
$groups = $this->db->select($query);

$this->smarty->assign(array(
    'data' => $this->data,
    'groups' => $groups,
));
