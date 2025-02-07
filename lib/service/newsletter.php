<?php
define('MODE', 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

error_reporting(E_ALL);

Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$db = Database::connect();

$session->domain_id = DOMAINID;
$langList = new LangList();
$domain = new Domain(DOMAINID);
$domain->load();
$session->default_lang_id = $domain->getDefaultLang();
if(!isset($session->lang_id)) {
    $session->lang_id = $session->default_lang_id;
}

$helper = new Helper();

$smarty = Smarty::getInstance();
$smarty->template_dir = TEMPLATES;
$smarty->compile_dir = TEMPLATESC;

$query = "SELECT st.section_id
          FROM ".Config::db_table_section_text()." st
          LEFT JOIN ".Config::db_table_section()." s ON (st.section_id = s.section_id)
          LEFT JOIN ".Config::db_table_section_text_value()." stv1 ON (st.section_text_id = stv1.section_text_id AND stv1.code = 'public_from')
		  LEFT JOIN ".Config::db_table_section_text_value()." stv2 ON (st.section_text_id = stv2.section_text_id AND stv2.code = 'public_to')
          WHERE st.inserted >= NOW() - INTERVAL 1 DAY AND
                st.status = '1' AND
                s.template IN ('news-detail', 'desk-detail', 'default') AND
                IF(s.template = 'desk-detail', stv1.datetime_val <= CURDATE(), true) AND
				IF(s.template = 'desk-detail', stv2.datetime_val >= CURDATE(), true)
		  ORDER BY st.name ASC";
$pages = $db->select($query);
if(!$pages) {
    exit;
}

$query = "SELECT DISTINCT(email), MD5(CONCAT_WS('.', nuser_id, email)) AS hash, SUBSTR(email, INSTR(email, '@') + 1) AS domain_name
          FROM ".Config::db_table_nuser()."
          WHERE status = '1'
          ORDER BY domain_name ASC, email ASC";
$emails = $db->select($query);
/*
$emails = [
    0 => [
        'email' => 'dohnal@gramonet.com',
        'hash' => '12345678901234567890123456789012',
    ],
];
*/
$links = [
    'news-detail' => [],
    'desk-detail' => [],
    'default' => [],
];
foreach($pages as $row) {
    $section = Section::getInstance($row['section_id']);
    $links[$section->get('section', 'template')][] = $section;
}

$smarty->assign([
    'links' => $links,
]);

$mail = new PHPMailer();

$mail->Mailer = 'smtp';
/*
$mail->Host = 'hosting01.victoriatech.cz';
$mail->Port = 587;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->SMTPAuth = true;
$mail->Username = 'nais@victoriatechmail.com';
$mail->Password = 'n!YDu5FYoHmt';
*/

$mail->Host = 'smtp.office365.com';
$mail->Port = 587;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->SMTPAuth = true;
$mail->Username = 'novinky@meucaslav.cz';
$mail->Password = 'Zpravadne595*';

$mail->isHTML(true);
$mail->CharSet  = 'utf-8';
$mail->FromName = 'Newsletter MěÚ Čáslav';
$mail->From = 'novinky@meucaslav.cz';
$mail->Subject = 'Newsletter MěÚ Čáslav - '.date('d. m. Y');

$mail->AddEmbeddedImage(LOCAL."app".DS."web1".DS."design".DS."images".DS."logo.png", "image1", "", "base64", "image/png");
$mail->AddEmbeddedImage(LOCAL."app".DS."web1".DS."design".DS."images".DS."radnice_newsletter.jpg", "image2", "", "base64", "image/jpeg");
$mail->AddEmbeddedImage(LOCAL."app".DS."web1".DS."design".DS."images".DS."fb-8.png", "image3", "", "base64", "image/png");
$mail->AddEmbeddedImage(LOCAL."app".DS."web1".DS."design".DS."images".DS."ig-8.png", "image4", "", "base64", "image/png");
$mail->AddEmbeddedImage(LOCAL."app".DS."web1".DS."design".DS."images".DS."yt-8.png", "image5", "", "base64", "image/png");
$mail->AddEmbeddedImage(LOCAL."app".DS."web1".DS."design".DS."images".DS."in-8.png", "image6", "", "base64", "image/png");
$mail->AddEmbeddedImage(LOCAL."app".DS."web1".DS."design".DS."images".DS."mr-8.png", "image7", "", "base64", "image/png");

foreach($emails as $row) {
    $smarty->assign([
        'logout' => $helper->section(777)->get('url').'?hash='.$row['hash'],
    ]);
    $html = $smarty->fetch('email-newsletter.html');
    $mail->Body = $html;

    $mail->ClearAddresses();
    $mail->AddAddress($row['email']);

    if(!$mail->Send()) {
        echo 'Message could not be sent.<br>';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        $query = "INSERT INTO ".Config::db_table_nlog()."
                  (newsletter_id, email, inserted)
                  VALUES
                  (1, '".mysqli_real_escape_string(MySQL::$conn, $row['email'])."', NOW())";
        $db->insert($query);
    }
    //usleep(200000);
}
//d('ok');