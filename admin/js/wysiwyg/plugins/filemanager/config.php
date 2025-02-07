<?php
	require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))."/lib/config/config.php";
	$session = Session::getInstance(MODE);
	$user = new User($session->user_id);
	$user->load();

	require_once(MCMANAGER_ABSPATH . "FileManager/FileManagerPlugin.php");
	require_once(MCMANAGER_ABSPATH . "FileSystems/LocalFileImpl.php");

	// * * * * FileManager config

	// General options
	$mcFileManagerConfig['general.theme'] = "fm";
	$mcFileManagerConfig['general.user_friendly_paths'] = true;
	//CFMOD $mcFileManagerConfig['general.tools'] = "createdir,createdoc,refresh,zip,upload,edit,rename,cut,copy,paste,delete,selectall,unselectall,view,download,insert,addfavorite,removefavorite";
	$mcFileManagerConfig['general.tools'] = "createdir,refresh,upload,edit,rename,cut,copy,paste,delete,selectall,unselectall,view,download,insert"; //,addfavorite,removefavorite
	$mcFileManagerConfig['general.disabled_tools'] = "createdir";
	$mcFileManagerConfig['general.error_log'] = ""; // see at the bottom

	$mcFileManagerConfig['general.language'] = $user->cmslang == 'cz' ? 'cs' : $user->cmslang;
	$mcFileManagerConfig['general.plugins'] = "History"; //,Favorites comma seperated
	$mcFileManagerConfig['general.demo'] = false;
	$mcFileManagerConfig['general.debug'] = false;
	$mcFileManagerConfig['general.encrypt_paths'] = true;
	$mcFileManagerConfig['general.remember_last_path'] = true;
	$mcFileManagerConfig['general.allow_override'] = "*";
	$mcFileManagerConfig['general.allow_export'] = "demo,tools,disabled_tools,debug";

	// Preview options
	$mcFileManagerConfig['preview.wwwroot'] = ''; // absolute or relative from this script path (c:/Inetpub/wwwroot).
	$mcFileManagerConfig['preview.urlprefix'] = "{proto}://{host}/"; // domain name
	$mcFileManagerConfig['preview.urlsuffix'] = "";
	$mcFileManagerConfig['preview.include_file_pattern'] = '';
	$mcFileManagerConfig['preview.exclude_file_pattern'] = '';
	$mcFileManagerConfig['preview.extensions'] = "*";
	$mcFileManagerConfig['preview.allow_export'] = "urlprefix,urlsuffix";
	$mcFileManagerConfig['preview.allow_override'] = "*";

	// General file system options
	$mcFileManagerConfig['filesystem'] = "Moxiecode_LocalFileImpl";
	$mcFileManagerConfig['filesystem.path'] = ''; // absolute or relative from this script path.
	$mcFileManagerConfig['filesystem.rootpath'] = LOCALFILES.'tinymce/'; // absolute or relative from this script path.
	$mcFileManagerConfig['filesystem.datefmt'] = "Y-m-d H:i"; // "Y-m-d H:i";
	$mcFileManagerConfig['filesystem.include_directory_pattern'] = '';
	$mcFileManagerConfig['filesystem.exclude_directory_pattern'] = '/^thumbs$/i';
	$mcFileManagerConfig['filesystem.invalid_directory_name_msg'] = "";
	$mcFileManagerConfig['filesystem.include_file_pattern'] = '';
	$mcFileManagerConfig['filesystem.exclude_file_pattern'] = '/^\.|mcic_/i';
	$mcFileManagerConfig['filesystem.invalid_file_name_msg'] = "";
	//$mcFileManagerConfig['filesystem.extensions'] = "gif,jpg,htm,html,pdf,zip,txt,php,png,swf,dcr,mov,qt,ram,rm,avi,mpg,mpeg,asf,flv,doc";
	$mcFileManagerConfig['filesystem.extensions'] = "7z,ai,avi,bak,bmp,cdr,csv,dat,db,dbf,dmg,doc,docx,dot,dwg,dxf,eps,flv,gif,gz,htm,html,iso,jpg,mdb,mdf,mid,mov,mp3,mp4,mpg,odp,ods,odt,ogg,pdf,png,pps,ppt,pptx,ps,psd,rar,rar,raw,rss,rtf,swf,tar,tex,tga,tif,tiff,txt,wav,wma,wmv,xls,xlsx,xlt,zip";//"gif,jpg,png,psd,cdr,txt,rtf,pdf,doc,xls,ppt,docx,pptx,xlsx,odp,ods,odt,rar,zip,mp3,ogg";
	$mcFileManagerConfig['filesystem.file_templates'] = '${rootpath}/templates/document.htm'; //,${rootpath}/templates/another_document.htm
	$mcFileManagerConfig['filesystem.directory_templates'] = '${rootpath}/templates/directory'; //,${rootpath}/templates/another_directory
	$mcFileManagerConfig['filesystem.readable'] = true;
	$mcFileManagerConfig['filesystem.writable'] = true;
	$mcFileManagerConfig['filesystem.delete_recursive'] = true;
	$mcFileManagerConfig['filesystem.force_directory_template'] = false;
	$mcFileManagerConfig['filesystem.clean_names'] = true;
	$mcFileManagerConfig['filesystem.allow_export'] = "extensions,readable,writable,file_templates,directory_templates,force_directory_template,clean_names";
	$mcFileManagerConfig['filesystem.allow_override'] = "*";

	// Upload options
	$mcFileManagerConfig['upload.maxsize'] = "100MB";
	$mcFileManagerConfig['upload.overwrite'] = false;
	$mcFileManagerConfig['upload.include_file_pattern'] = '';
	$mcFileManagerConfig['upload.exclude_file_pattern'] = '';
	$mcFileManagerConfig['upload.invalid_file_name_msg'] = "";
	//$mcFileManagerConfig['upload.extensions'] = "gif,jpg,png,pdf,zip,doc";
	$mcFileManagerConfig['upload.extensions'] = "7z,ai,avi,bak,bmp,cdr,csv,dat,db,dbf,dmg,doc,docx,dot,dwg,dxf,eps,flv,gif,gz,htm,html,iso,jpg,mdb,mdf,mid,mov,mp3,mp4,mpg,odp,ods,odt,ogg,pdf,png,pps,ppt,pptx,ps,psd,rar,rar,raw,rss,rtf,swf,tar,tex,tga,tif,tiff,txt,wav,wma,wmv,xls,xlsx,xlt,zip";//"gif,jpg,png,psd,cdr,txt,rtf,pdf,doc,xls,ppt,docx,pptx,xlsx,odp,ods,odt,rar,zip,mp3,ogg";
	$mcFileManagerConfig['upload.multiple_upload'] = true;
	$mcFileManagerConfig['upload.chunk_size'] = '1mb';
	$mcFileManagerConfig['upload.allow_export'] = "maxsize,multiple_upload,chunk_size,overwrite,extensions";
	$mcFileManagerConfig['upload.allow_override'] = "*";

	// Download options
	$mcFileManagerConfig['download.include_file_pattern'] = "";
	$mcFileManagerConfig['download.exclude_file_pattern'] = "";
	//$mcFileManagerConfig['download.extensions'] = "gif,jpg,htm,html,pdf,txt,zip,doc";
	$mcFileManagerConfig['download.extensions'] = "7z,ai,avi,bak,bmp,cdr,csv,dat,db,dbf,dmg,doc,docx,dot,dwg,dxf,eps,flv,gif,gz,htm,html,iso,jpg,mdb,mdf,mid,mov,mp3,mp4,mpg,odp,ods,odt,ogg,pdf,png,pps,ppt,pptx,ps,psd,rar,rar,raw,rss,rtf,swf,tar,tex,tga,tif,tiff,txt,wav,wma,wmv,xls,xlsx,xlt,zip";//gif,jpg,png,psd,cdr,txt,rtf,pdf,doc,xls,ppt,docx,pptx,xlsx,odp,ods,odt,rar,zip,mp3,ogg";
	$mcFileManagerConfig['download.allow_override'] = "*";

	// Create document options
	$mcFileManagerConfig['createdoc.fields'] = "Document title=title";
	$mcFileManagerConfig['createdoc.include_file_pattern'] = '';
	$mcFileManagerConfig['createdoc.exclude_file_pattern'] = '';
	$mcFileManagerConfig['createdoc.invalid_file_name_msg'] = "";
	$mcFileManagerConfig['createdoc.allow_export'] = "fields";
	$mcFileManagerConfig['createdoc.allow_override'] = "*";

	// Create directory options
	$mcFileManagerConfig['createdir.include_directory_pattern'] = '';
	$mcFileManagerConfig['createdir.exclude_directory_pattern'] = '/[^a-z0-9_\.]/i';
	$mcFileManagerConfig['createdir.invalid_directory_name_msg'] = "";
	$mcFileManagerConfig['createdir.allow_override'] = "*";

	// Rename options
	$mcFileManagerConfig['rename.include_file_pattern'] = '';
	$mcFileManagerConfig['rename.exclude_file_pattern'] = '';
	$mcFileManagerConfig['rename.invalid_file_name_msg'] = "";
	$mcFileManagerConfig['rename.include_directory_pattern'] = '';
	$mcFileManagerConfig['rename.exclude_directory_pattern'] = '';
	$mcFileManagerConfig['rename.invalid_directory_name_msg'] = "";
	$mcFileManagerConfig['rename.allow_override'] = "*";

	// Edit file options
	$mcFileManagerConfig['edit.include_file_pattern'] = '';
	$mcFileManagerConfig['edit.exclude_file_pattern'] = '';
	$mcFileManagerConfig['edit.extensions'] = "html,htm,txt";
	$mcFileManagerConfig['edit.allow_override'] = "*";

	// Zip file(s) options
	$mcFileManagerConfig['zip.include_file_pattern'] = '';
	$mcFileManagerConfig['zip.exclude_file_pattern'] = '';
	$mcFileManagerConfig['zip.extensions'] = "*";
	$mcFileManagerConfig['zip.allow_override'] = "*";

	// Unzip file(s) file options
	$mcFileManagerConfig['unzip.include_file_pattern'] = '';
	$mcFileManagerConfig['unzip.exclude_file_pattern'] = '';
	$mcFileManagerConfig['unzip.extensions'] = "*";
	$mcFileManagerConfig['unzip.allow_override'] = "*";

	// Authenication
	$mcFileManagerConfig['authenticator'] = "";
	$mcFileManagerConfig['authenticator.login_page'] = "login_session_auth.php";
	$mcFileManagerConfig['authenticator.allow_override'] = "*";

	// SessionAuthenticator
	$mcFileManagerConfig['SessionAuthenticator.logged_in_key'] = "isLoggedIn";
	$mcFileManagerConfig['SessionAuthenticator.groups_key'] = "groups";
	$mcFileManagerConfig['SessionAuthenticator.user_key'] = "user";
	$mcFileManagerConfig['SessionAuthenticator.path_key'] = "mc_path";
	$mcFileManagerConfig['SessionAuthenticator.rootpath_key'] = "mc_rootpath";
	$mcFileManagerConfig['SessionAuthenticator.config_prefix'] = "filemanager";

	// ExternalAuthenticator config
	$mcFileManagerConfig['ExternalAuthenticator.external_auth_url'] = "auth_example.jsp";
	$mcFileManagerConfig['ExternalAuthenticator.secret_key'] = "someSecretKey";

	// Local filesystem options
	$mcFileManagerConfig['filesystem.local.access_file_name'] = "mc_access";
	$mcFileManagerConfig['filesystem.local.allow_override'] = "*";
	$mcFileManagerConfig['filesystem.local.file_mask'] = "0777";
	$mcFileManagerConfig['filesystem.local.directory_mask'] = "0777";
	$mcFileManagerConfig['filesystem.allow_override'] = "*";

	// Stream options
	$mcFileManagerConfig['stream.mimefile'] = "mime.types";
	$mcFileManagerConfig['stream.include_file_pattern'] = '';
	$mcFileManagerConfig['stream.exclude_file_pattern'] = '/\.php$|\.shtm$/i';
	$mcFileManagerConfig['stream.extensions'] = "*";
	$mcFileManagerConfig['stream.allow_override'] = "*";

	// Logging options
	$mcFileManagerConfig['log.enabled'] = true;
	$mcFileManagerConfig['log.level'] = "debug"; // debug, warn, error
	$mcFileManagerConfig['log.path'] = "logs"; //"logs";
	$mcFileManagerConfig['log.filename'] = "filemanager_{level}.log";
	$mcFileManagerConfig['log.format'] = "[{time}] [{level}] {message}";
	$mcFileManagerConfig['log.max_size'] = "100k";
	$mcFileManagerConfig['log.max_files'] = "10";

	// Image manager options
	$mcFileManagerConfig['imagemanager.urlprefix'] = "../../../imagemanager/?type=im";  // need to add "imagemanager" button to tools as well.
	$mcFileManagerConfig['imagemanager.allow_override'] = "*";
	$mcFileManagerConfig['imagemanager.allow_export'] = "urlprefix";
?>