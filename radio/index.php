<?php
chdir('..');
require_once('./config.php');
chdir('./radio');


if (!$hasradio){
	die();
}

if (isset($_GET['m3u'])){
	
	header('Content-type: application/x-mpegurl');
	echo 'http://'.$server.'/radio/stream.mp3';
	exit();
	
}


if (isset($_GET['ajax'])){
	if ($_GET['ajax']==='block'){
		
		$material_artists_file=htmlentities(trim(file_get_contents('../d/material_artists.txt')));
	
		$material_artists=explode("\n", $material_artists_file);
			
		$material_blacklist_file=htmlentities(trim(file_get_contents('../d/material_blacklist.txt')));
			
		$material_blacklist=explode("\n", $material_blacklist_file);

		$duration=intval(file_get_contents('./d/nowplayingduration.txt'));
		
		$nowplayingdurationminutes=floor($duration/60);
		
		$nowplayingdurationseconds=$duration-$nowplayingdurationminutes*60;
		
		$hasplayed=intval(microtime(true)-floatval(file_get_contents('./d/starttime.txt')));

		$hasplayedminutes=floor($hasplayed/60);
		
		$hasplayedseconds=$hasplayed-$hasplayedminutes*60;
		
		if ($hasplayedseconds<10){
			$hasplayedseconds='0'.$hasplayedseconds;
		}
		if ($nowplayingdurationseconds<10){
			$nowplayingdurationseconds='0'.$nowplayingdurationseconds;
		}
		
		echo '<strong style="font-size:125%;">';
		//echo '<a target="new" href="../?artist='.urlencode(file_get_contents('./d/nowplayingartist.txt')).'">';
		echo file_get_contents('./d/nowplayingartist.txt');
		//echo '</a>';
		echo '</strong>';
		echo ' - ';
		//echo '<a target="new" href="../?album='.urlencode(file_get_contents('./d/nowplayingalbum.txt')).'&track='.urlencode(file_get_contents('./d/nowplayingtitle.txt')).'">';
		echo '<em style="font-size:125%;">'.file_get_contents('./d/nowplayingtitle.txt').'</em>';
		//echo '</a>';
		echo ' - ['.htmlspecialchars($hasplayedminutes).':'.htmlspecialchars($hasplayedseconds).'/'.htmlspecialchars($nowplayingdurationminutes).':'.htmlspecialchars($nowplayingdurationseconds).']';
		if ($hasplayedminutes+0.01*$hasplayedseconds>$nowplayingdurationminutes+0.01*$nowplayingdurationseconds){
			?>
			
			<script>
			document.getElementById('player').src='./stream.mp3'+'?web=web&math='+Math.random();
			</script>
			
			<?php
		}
		echo '<br/><span  style="font-size:125%;">(';
		//echo '<a target="new" href="../?album='.urlencode(file_get_contents('./d/nowplayingalbum.txt')).'">';
		echo file_get_contents('./d/nowplayingalbum.txt');
		//echo '</a>';
		
		echo ')</span><br/>';
		if (boolval(trim(file_get_contents('./d/nowplayingisfeatured.txt')))){
			$target=file_get_contents('./d/nowplayingurl.txt');
			$targetflac=str_replace('.mp3', '.flac', $target);
			$targetogg=str_replace('.mp3', '.ogg', $target);
			
			echo '<a target="new" href="'.$target.'">mp3</a>';
			echo '<br/>'.file_get_contents('./d/license.txt');
		}
		else {
			echo ' ';
		}
		if (!in_array(trim(html_entity_decode(file_get_contents('./d/nowplayingalbum.txt'))),$material_blacklist)
			&&
			in_array(trim(html_entity_decode(file_get_contents('./d/nowplayingartist.txt'))),$material_artists)
			&& boolval(trim(file_get_contents('./d/nowplayingisfeatured.txt')))
		
		
		) {
			//echo '<br/>Available as <a target="new" href="../?listall=mixed&album='.urlencode(file_get_contents('./d/nowplayingalbum.txt')).'">material release</a> at our online shop';
			
		}
		if (!file_exists('./d/maxlisteners.txt')){
			file_put_contents('./d/maxlisteners.txt', '0');
		}
		
		$listeners=count(array_diff(scandir('./d/listeners'), Array ('.', '..')));
		
		if ($listeners>intval(file_get_contents('./d/maxlisteners.txt'))){
			file_put_contents('./d/maxlisteners.txt', $listeners);
		}
		if ((floatval(filectime('./d/maxlisteners24hours.txt'))+24*60*60)<=microtime(true)){
		
			unlink('./d/maxlisteners24hours.txt');
		
		}
		if ($listeners>intval(file_get_contents('./d/maxlisteners24hours.txt'))){
			file_put_contents('./d/maxlisteners24hours.txt', $listeners);
		}
		$peaktime=microtime(true)-filectime('./d/maxlisteners24hours.txt');
		$peakhours=ceil($peaktime/(60*60));

		echo '<br/>Auditeurs : '.$listeners.' / Max :  '.htmlspecialchars(file_get_contents('./d/maxlisteners.txt')).' (depuis toujours) '.htmlspecialchars(file_get_contents('./d/maxlisteners24hours.txt')).' (depuis '.htmlspecialchars($peakhours).' heures)';
	}
	else if ($_GET['ajax']==='cover'){
		$covers=trim(file_get_contents('../d/covers.txt'));
		$coverlines=explode("\n", $covers);
		$artworks=Array();
		for ($i=0;$i<count($coverlines);$i++){
			$artworks[$coverlines[$i]]=$coverlines[$i+1];
			$i++;
		}
		if (isset( $artworks[html_entity_decode(trim(file_get_contents('./d/nowplayingalbum.txt')))])){
			echo $artworks[html_entity_decode(trim(file_get_contents('./d/nowplayingalbum.txt')))];
		
		}
		else {
		
			echo '../favicon.png';
		}
	}
	exit();
}



$sessionstarted=session_start();

srand();




if ($activatestats&&isset($_GET['pingstat'])){
	//if audience figures are activated, let's store the hit details in the stats directory
	
	if ($sessionstarted){
		
		if (!isset($_SESSION['statid'])){
			$_SESSION['statid']=microtime(true);
			$_SESSION['css_color']='rgb('.rand(140, 255).','.rand(140, 255).','.rand(140, 255).')';
			
		}
		
		$page['data']['agent']=$_GET['reqHTTP_USER_AGENT'];
		$variable='agent';
		
		if (!(strstr($page['data'][$variable],'bot')||
		strstr($page['data'][$variable],'Yahoo! Slurp')||
		strstr($page['data'][$variable],'+http://')||
		strstr($page['data'][$variable],'+https://')||
		strstr($page['data'][$variable],'()'))) {
		//may be an human, we store it
			$figure['userid']=$_SESSION['statid'];
			$figure['css_color']=$_SESSION['css_color'];
			$figure['page']='?Radio=Radio';
			$figure['referer']=$_GET['reqHTTP_REFERER'];
			$figure['random']=$_SESSION['random'];
			file_put_contents('../admin/d/stats/'.microtime(true).'.dat', serialize($figure));
		}
		
		
	}
		
		
	exit();
}



function loginpanel($activateaccountcreation){
	if (!$activateaccountcreation) {

		return;
	}
/*	
	
	if (isset($_SESSION['logged'])&&$_SESSION['logged']) {
		
		
	}	
	
	
	else if (!isset($_GET['login'])&&!isset($_GET['createaccount'])&&!isset($_POST['validateemail'])){
//		echo '<a href="./?login=login">Login</a> or <a href="./?createaccount=createaccount">Create account</a>';
*/ else if (!isset ($_POST['validateemail'])){


		echo '<form id="orderform" style="display:inline;" method="POST" action="./"><a href="#" onclick="document.getElementById(\'friends\').style.display=\'inline\';">Let\'s make friends ! </a><span id="friends" style="display:none;"><input type="text" name="validateemail" value="your email address" onfocus="if (this.value==\'your email address\'){this.value=\'\';}"/><input type="submit"/></span></form>';
		 	
	}
	else if (isset($_GET['createaccount'])) {
		echo 'Please enter a <em>valid</em> email adress. You will receive a link to set your password and activate your account. <br/>';
		echo '<form id="orderform" style="display:inline;" method="POST" action="./">Your email address : <input type="text" name="validateemail"/><input type="submit"/></form>';
		
	}
	else if (isset ($_POST['validateemail'])&&file_exists('../d/mailing-list-owner.txt')) {
		
		$_POST['validateemail']=explode("\n",$_POST['validateemail'])[0];
		$_POST['validateemail']=trim($_POST['validateemail']);
		$message ='<html><body>Hello<br/>';
		
		$message.="\r\n".'Someone requested mailing list ';
		$message.="\r\n".'subscription using the email address <br/>'.htmlentities($_POST['validateemail']);
		$message.="\r\n".'</body></html>';
		$message=chunk_split($message);
	
		if (
	
		mail(trim(file_get_contents('../d/mailing-list-owner.txt')), 'Mailing list subscription request', $message, 'Content-Type: text/html;charset=UTF-8')
		
		){
		
			echo 'A subscription request has been sent for the address '.htmlspecialchars($_POST['validateemail']).'. We will get in touch shortly to confirm. <a href="./">Close</a>';
			
		}
		else {
			echo 'The system has not been able to subscribe '.htmlspecialchars($_POST['validateemail']).'. Please <a href="">try again</a> later';
			
			
		}
	}
	
}


?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="/favicon.png" />
<link rel="stylesheet" href="http://<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title><?php echo strip_tags($radioname); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($radiodescription); ?>" />
<style>
</style>
</head>
<body>
	<?php
	if ($acceptdonations){
		echo '<span style="float:right;text-align:right">';
		
		include ('../donate.php');
		
		echo '</span><br style="clear:both;float:none;"/>';
		
	}
	?>
	
	
	
	
	
<a name="menu"></a><div id="mainmenu" style="display:block;">	
	<span style=""><img style="float:left;width:3%;" src="/favicon.png"/></span>
		
	<h1 id="title" style="display:inline;"><?php echo $radioname; ?></h1>
	<h2 style="clear:both;"><em><?php echo htmlspecialchars($radiodescription);?></em> <br/><a href="http://<?php echo $server;?>">Home</a></h2>
</div>
<!--<div><a href="#menu" onclick="mainmenu=document.getElementById('mainmenu');if(mainmenu.style.display=='none'){mainmenu.style.display='inline';this.innerHTML='&lt;';}else{mainmenu.style.display='none';this.innerHTML='☰<?php echo str_replace("'", "\\'", htmlspecialchars($title));?>';}">☰<?php echo strip_tags($radioname);?></a></div>-->

<span id="loginpanel" style="float:right;text-align:right;margin-bottom:2%;">
	<?php
		loginpanel($activateaccountcreation);
	?>

</span><br style="clear:both;"/>
<script>
	
var cover='';

function refreshBlock() {

		  var xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function(){
			  if (xhttp.readyState==4 && xhttp.status==200) {

					document.getElementById('block').innerHTML = xhttp.responseText;
				}
			  
			  };
		  xhttp.open("GET", "./?ajax=block", true);
		  xhttp.send();
}
function refreshCover(){
			  var xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function(){
			  if (xhttp.readyState==4 && xhttp.status==200) {

					document.getElementById('cover').src = "../covers/"+xhttp.responseText;
				}
			  
			  };
		  xhttp.open("GET", "./?ajax=cover", true);
		  xhttp.send();

	
}
window.setInterval(refreshBlock, 2000);
window.setInterval(refreshCover, 30000);
setTimeout (refreshCover, 3000);
</script><?php
$menuitems = array_keys($mainmenu);

foreach ($menuitems as $menuitem)
{
	$title = $menuitem ;
	
	$target = $mainmenu[$menuitem];
	
	echo '<a href="'.$target.'" class="mainmenubutton" onclick="this.style.border='."'solid red 2px;'".'" ';
	if (basename($target)==$olddir){
		echo 'style="border:solid yellow 2px;"';
		}
	echo '>'.htmlspecialchars($title).'</a>';
	
}?>
<br style="clear:both;"/>
Stream : <a href="?m3u=m3u">m3u</a> <a href="./stream.mp3">mp3</a><br/>

<img style="float:left;width:25%;" id="cover"/>
<span style="float:left;">
<div>Now Playing</div>
<div id="block" style="padding-left:4%;"></div>
<br style="clear:both;float:none;"/>
<script>
<?php
if (!$activatechat===false&&$isforkable){
?>

function skipsong() {
	document.getElementById('skip').InnerHTML= 'Skipping song...';
				<?php
				if (!isset($_SESSION['nick'])){
					?>
				document.getElementById('social').data="../network/?login=login";

					<?php
					
				}
				?>
	
	var xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function(){
		  if (xhttp.readyState==4 && xhttp.status==200) {

				document.getElementById('skip').InnerHTML= 'Skip this song';
				location.reload();

			}

		  
		  };
	  xhttp.open("GET", "skipsong.php", true);
	  xhttp.send();

}
<?php
}
?>

</script>
<div style="text-align:left;"><audio id="player" src="" controls="controls" 
 onEnded="this.src='./stream.mp3?web=web&'+Math.random();this.load();this.play();" 
 onError="window.setTimeout(function(){document.getElementById('player').src='./stream.mp3?web=web&'+Math.random();document.getElementById('player').load();document.getElementById('player').play();}, 500);" 
 
 ></audio></div>	
<?php
if (!$activatechat===false&&$isforkable){
?>
		<a href="javascript:void(0);" id="skip" onclick="skipsong();"/>Fork the radio!</a> (other listeners may become angry)
<?php
}
?>
</span>

<hr style="float:none;clear:both;">
<script>
document.getElementById('player').src='./stream.mp3?web=web&'+Math.random();
document.getElementById('player').load();
document.getElementById('player').autoplay='autoplay';
document.getElementById('player').play();

</script>
<?php
if (!$activatechat===false){
?>
		<a name="social"/><object id="social" data="../network" style="	width:100%;height:480px;" width="100%" height="480"></object>
<?php
}


if ($activatestats){

?>
<script>

  var xhttp = new XMLHttpRequest();
  xhttp.open("GET", "./?pingstat=true&reqHTTP_REFERER=<?php echo urlencode($_SERVER['HTTP_REFERER']); 
  
  
  ?>&reqHTTP_USER_AGENT=<?php echo urlencode($_SERVER['HTTP_USER_AGENT']); 
  
  
  ?>&reqREQUEST_URI=<?php echo urlencode($_SERVER['REQUEST_URI']); 
  
  
  ?>", true);
  xhttp.send();

</script>
<hr style="clear:both;"/>
<?php
}
echo $footerhtmlcode;
?>
</body>
</html>
