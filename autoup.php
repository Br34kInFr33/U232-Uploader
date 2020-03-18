<?php

define('ROOT_PATH', '/home/upload');
define('UPLOAD_PATH', ROOT_PATH.'/scan');
define('MOVE_PATH', ROOT_PATH.'/move');
define('ERROR_PATH', ROOT_PATH.'/error');
define('TORRENT_PATH', ROOT_PATH.'/torrent');
define('TEMP_TORRENT', ROOT_PATH.'/temp');

define('LOG_FILE', ROOT_PATH.'/bot.log');
define('JOB_LOG', ROOT_PATH.'/jobs');

define('SITE_ROOT', 'http://51.83.72.245');
define('ANNOUNCE_URL', 'http://51.83.72.245/announce.php');
define('QUICK_LOGIN', 'http://51.83.72.245/pagelogin.php?qlogin=90e763c53c9da922304a5aef982f7c5b533f0770efbe259161c87d0a889bcf3d320bc88839ba8a8e93a29dcb19896631');

define('TMDB_API', '2b2c0a99175ae7746878c600d8f744f7');

define('SCENE_AXX', true);

error_reporting(E_ALL);
ini_set("log_errors", true);
ini_set("error_log", LOG_FILE);

function move($source, $dest)
{
	$cmd = 'mv "'.$source.'" "'.$dest.'"'; 
	exec($cmd, $output, $return_val); 
	if ($return_val == 0) return 1;
	return 0;
}

function make_login()
{
	$login_url = QUICK_LOGIN;
	$ch = curl_init($login_url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	$rez = curl_exec($ch);
	if (!$rez) die('Cannot login!');
	echo file_put_contents(LOG_FILE, 'Cannot login! '.date('m/d/Y h:i:s')."\r\n", FILE_APPEND);
}

function make_torrent($file)
{
	$info = pathinfo($file);
	$output = TEMP_TORRENT.'/'.$info['basename'].'.torrent';
	if (file_exists($output)) unlink($output);
	$cmd = "mktorrent '$file' -o '$output' -a ".ANNOUNCE_URL;
	exec($cmd);
	if (file_exists($output)) return $output;
	else die('Cannot make torrent!');
	echo file_put_contents(LOG_FILE, "Cannot make $file torrent! ".date('m/d/Y h:i:s')."\r\n", FILE_APPEND);
}

function make_upload($file_full, $ext, $new_dir)
{
	$file = pathinfo($file_full, PATHINFO_BASENAME);
	$file_without_ext = pathinfo($file_full, PATHINFO_FILENAME);
	
	$move_file = $new_dir.'/'.$file;
	$nfo_file = $new_dir.'/'.$file;
			
	$rez = move($file_full, $move_file);
	if (!$rez) die('Cannot move file!');
	$torrent = make_torrent($move_file);
	
        $source = glob($nfo_file.'/*.nfo');
	
	$nfo = 'There was no nfo file found!';
	foreach ($source as $a) {
	if (substr(strtolower($a), -4) == '.nfo') {
	$nfo = file_get_contents($a);
	$match = array("/[^a-zA-Z0-9-._&?:'\/\s]/", "/\s{2,}/");
	$replace = array("", " ");
	$nfo = preg_replace($match, $replace, trim($nfo));
	}
	}
	
	switch(true)
	{
	case preg_match('/http:\/\/www.imdb.com\/title\/tt[\d]+\//', $nfo) : preg_match('/http:\/\/www.imdb.com\/title\/tt[\d]+\//', $nfo, $matches); break;
	default : preg_match('/http:\/\/www.imdb.com\/title\/tt[\d]+/', $nfo, $matches); break;
  	}
	$imdb = $matches[0];

	switch(true)
	{
	case preg_match('/s\d+e\d+|s\d+|hdtv|sdtv|pdtv|tvrip/i', $file) : $cat = 5; break;
	case preg_match('/xvid|brrip|bluray|dvdrip|hdrip/i', $file) : $cat = 10; break;
	case preg_match('/x86|x64|win64|lnx64|macosx/i', $file) : $cat = 1; break;
	case preg_match('/wii|wiiu|xbox|xbox360|ps3|ps4/i', $file) : $cat = 2; break;
	case preg_match('/dvdr/i', $file) : $cat = 3; break;
	case preg_match('/mp3|flac|lossless|cd|compilation|album|albums|vinyl/i', $file) : $cat = 4; break;
	case preg_match('/xxx/i', $file) : $cat = 6; break;
	case preg_match('/psp/i', $file) : $cat = 7; break;
	case preg_match('/ps2/i', $file) : $cat = 8; break;
	case preg_match('/anime/i', $file) : $cat = 9; break;
	case preg_match('/720p|1080p/i', $file) : $cat = 11; break;
	case preg_match('/pc/i', $file) : $cat = 12; break;
	default : $cat = 9;
	}

	$torrent_info = Array();
	$torrent_info['name'] = $file;
	$torrent_info['descr'] = $nfo;
	$torrent_info['url'] = $imdb;
	$torrent_info['type']= $cat;
	$torrent_info['poster'] = '';
	
	if(TMDB_API != '')
	{
	  if(($cat === 10) || ($cat === 11))
	  {
	    $file_name = $file;
	    switch(true)
	    {
	      case preg_match('/^\d+.[a-z.]+.\d+/i', $file_name) : preg_match('/\d+.[a-z.]+.\d+/i', $file_name, $matching); break;
	      case preg_match('/^\d+.\d+/', $file_name) : preg_match('/\d+.\d+/', $file_name, $matching); break;
	      default : preg_match("/[a-z.]+.\d{4}/i", $file_name, $matching);
        }
	    $year = substr($matching[0], -4);
        $title = substr_replace($matching[0],"", -5);
        $title = str_replace('.', '+', $title);
	    $obj = json_decode(file_get_contents('https://api.themoviedb.org/3/search/movie?api_key='.TMDB_API.'&language=en-US&query='.$title.'&page=1&include_adult=false&year='.$year), true);
        if($obj['total_results'] == 0)
		{
		 $torrent_info['poster'] = SITE_ROOT.'/pic/noposter.png';
		}
		else
	    {
          $copy_poster = $obj['results']['0']['poster_path'];
	      $title = str_replace('+', '.', $title);
          $poster_link = 'https://image.tmdb.org/t/p/w300_and_h450_bestv2';
          $torrent_info['poster'] = $poster_link.$copy_poster;
        }
	  }
	  if($cat === 5)
	  {
	    $file_name = $file;
	    switch(true) 
		{
	      case preg_match('/^\d+.[a-z.]+.s\d+e\d+./i', $file_name) : preg_match('/\d+.[a-z.]+.s\d+e\d+./i', $file_name, $matching); break;
	      case preg_match('/^[a-z.]+.\d+.s\d+e\d+./i', $file_name) : preg_match('/[a-z.]+.\d+.s\d+e\d+./i', $file_name, $matching); break;
	      case preg_match('/^\d+.[a-z.]+.s\d+./i', $file_name) : preg_match('/\d+.[a-z.]+.s\d+./i', $file_name, $matching); break;
	      case preg_match('/^[a-z.]+.\d+.s\d+./i', $file_name) : preg_match('/[a-z.]+.\d+.s\d+./i', $file_name, $matching); break;
	      case preg_match('/^[a-z.]+.s\d+./i', $file_name) : preg_match('/[a-z.]+.s\d+./i', $file_name, $matching); break;
	      default : preg_match("/[a-z.]+.s\d+e\d+./i", $file_name, $matching);
        }
        if(preg_match('/e\d+/i', $matching[0]))
        {
          $title = substr_replace($matching[0],"", -8);
        }
        else
        {
          $title = substr_replace($matching[0],"", -5);
        }
        $title = str_replace('.', '+', $title);
        $obj = json_decode(file_get_contents('https://api.themoviedb.org/3/search/tv?api_key='.TMDB_API.'&language=en-US&query='.$title.'&page=1'), true);
        if($obj['total_results'] == 0)
		{
		 $torrent_info['poster'] = SITE_ROOT.'/pic/noposter.png';
		}
		else
	    {
          $copy_poster = $obj['results']['0']['poster_path'];
	      $title = str_replace('+', '.', $title);
          $poster_link = 'https://image.tmdb.org/t/p/w300_and_h450_bestv2';
          $torrent_info['poster'] = $poster_link.$copy_poster;
        }
      }
	}
	upload_torrent($torrent, $torrent_info, $file);
}

function test_login()
{
	$login_url = SITE_ROOT.'/mytorrents.php';
	$ch = curl_init($login_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	$rez = curl_exec($ch);
	if (!$rez) make_login();
}

function upload_torrent($torrent, $torrent_info, $file)
{
	loged_in:
	$upload_url = SITE_ROOT.'/upload.php';
	$ch = curl_init($upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	$rez = curl_exec($ch);
	
    $torrent_info['MAX_FILE_SIZE']=3145728;
	$torrent_info['youtube']='';	
	$torrent_info['file'] = new CURLFile (TEMP_TORRENT.'/'.$file.".torrent");	
	$torrent_info['description']='Auto Upload Bot';	
	$torrent_info['fontfont']='0';
	$torrent_info['fontsize']='0';
	$torrent_info['request']='0';
	$torrent_info['release_group']='none';
	$torrent_info['strip']=	'strip';
	
    $fh = fopen(JOB_LOG.'/'.$file, 'a') or die;
	$string_data = "Name: ".$torrent_info['name'].PHP_EOL."Added: ".date("m/d/Y h:i:s").PHP_EOL."NFO: ".$torrent_info['descr']
	.PHP_EOL."Category: ".$torrent_info['type'].PHP_EOL."IMDB: ".$torrent_info['url'];
	fwrite($fh, $string_data);
	fclose($fh);
	
    $upload_url = SITE_ROOT.'/takeupload.php';
	$ch = curl_init($upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HEADER, 1); 
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $torrent_info);
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	$rez = curl_exec($ch);
	if (!$rez || strpos($rez, 'login.php')) 
	{
	  make_login();
	  goto loged_in;
	}

	unlink(TEMP_TORRENT.'/'.$file.".torrent");

	strpos($rez,'Upload failed!') ? file_put_contents(LOG_FILE, "$file failed on ".date("m/d/Y h:i:s")."\r\n", FILE_APPEND) && move(MOVE_PATH.'/'.$file, ERROR_PATH) :
	file_put_contents(LOG_FILE, "$file uploaded on ".date("m/d/Y h:i:s")."\r\n",FILE_APPEND);
	//echo $rez;
	download_torrent($file);
}

function download_torrent($file) 
{
	$search_url = SITE_ROOT.'/browse.php?search='.$file.'&searchin=title&incldead=2';
	$ch = curl_init($search_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	$rez = curl_exec($ch);

	preg_match('/download\.php\?torrent=([0-9]+)/', $rez, $sub);
	//print_r($sub);	
	$id = $sub[0];
	
	$download_url = SITE_ROOT.'/'.$id;
	$ch = curl_init($download_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	$rez = curl_exec($ch);

	file_put_contents(TORRENT_PATH.'/'.$file.".torrent", $rez);
        
    if(SCENE_AXX != true)
	{
	  exec("rm -rf ".MOVE_PATH."/".$file);
	}

}

function scan_folder()
{
	$dir = UPLOAD_PATH;
	$dir_done = MOVE_PATH;
	
	if ( !is_dir($dir_done) )
	{
	  $ok = mkdir($dir_done);
	  if (!$ok) die('Cannot create destination folder!');
	}
	
	$dh = opendir($dir);
	while ( $file = readdir($dh) )
	{
	  if ($file == '.' || $file == '..') continue;
	  $file_full = $dir.'/'.$file;
	  if ($file_full == MOVE_PATH) continue;
	  $ext = pathinfo($file_full, PATHINFO_EXTENSION);
	  make_upload($file_full, $ext, $dir_done);
	}
}

test_login();
scan_folder();
?>
