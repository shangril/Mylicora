<?php
require_once('./config.php');
error_reporting(E_WARNING|E_NOTICE);

require_once('./crero-lib.php');
$olddir="/";
?><!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="<?php echo $favicon;?>" />
<link rel="stylesheet" href="http://<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title><?php echo strip_tags($title);?></title>
<meta name="description" content="<?php echo htmlspecialchars($description); ?>" />

</head>
<body>
	<h1>
		<?php echo $title;?>
	</h1>
	<h2>
		<?php echo $description;?>
	</h2>
<?php
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
	
}

echo '<br/>';
echo $homepage_content;
echo '<hr/>';
echo $footerhtmlcode;
?>

</body>
</html>
