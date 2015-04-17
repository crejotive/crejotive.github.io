<?php
session_start();

$logged = (isset($_SESSION['logged']) && $_SESSION['logged']=='in');

function rrmdir($dir) {
     foreach(glob($dir . '/*') as $file) {
         if(is_dir($file))
             rrmdir($file);
         else
             unlink($file);
     }
     rmdir($dir);
 }

if ($logged) {
  if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_POST['keyword'])) {
      mkdir($_POST['keyword']);
      mkdir($_POST['keyword'].'/ori');
      mkdir($_POST['keyword'].'/big');
      mkdir($_POST['keyword'].'/thumbnails');
      copy('templatecreaindex.php', $_POST['keyword'].'/index.php');
      copy('templatecreadesc.html', $_POST['keyword'].'/desc.html');
      header("Location: ".$_POST['keyword'].'/');
      exit();
    } else if(isset($_FILES['thumbs'])) {
      
      $count=0;
      foreach ($_FILES['thumbs']['name'] as $filename) {
        $oriDir='ori/';
        $bigDir='big/';
        $smallDir='thumbnails/';
        $bigWidth=750;
        $smallWidth=100;
        $smallHeight=100;
        $tmp=$_FILES['thumbs']['tmp_name'][$count];
        $count++;
        
        $mime = exif_imagetype($tmp);
        if ($mime==IMAGETYPE_GIF)
          $ext = '.gif';
        else if ($mime==IMAGETYPE_JPEG)
          $ext = '.jpg';
        else if ($mime==IMAGETYPE_PNG)
          $ext = '.png';
        else if ($mime==IMAGETYPE_BMP)
          $ext = '.bmp';

        $i=0;
        while(file_exists('big/img'.$i.'.jpg') || file_exists('big/img'.$i.'.png') || file_exists('big/img'.$i.'.gif') || file_exists('big/img'.$i.'.bmp'))
          $i++;
        $filename = 'img'.$i.$ext;

        $oriDest=$oriDir.$filename;
        move_uploaded_file($tmp,$oriDest);

        if ($mime==IMAGETYPE_GIF)
          $image = imagecreatefromgif($oriDest);
        else if ($mime==IMAGETYPE_JPEG)
          $image = imagecreatefromjpeg($oriDest);
        else if ($mime==IMAGETYPE_PNG)
          $image = imagecreatefrompng($oriDest);
        else if ($mime==IMAGETYPE_BMP)
          $image = imagecreatefromwbmp($oriDest);
        
        $bigDest=$bigDir.$filename;
        list($width_orig, $height_orig) = getimagesize($oriDest);
        if ($width_orig>$bigWidth) {
          $height = $bigWidth/($width_orig/$height_orig);
          $image_p = imagecreatetruecolor($bigWidth, $height);
          imagecopyresampled($image_p, $image, 0, 0, 0, 0, $bigWidth, $height, $width_orig, $height_orig);
          if ($mime==IMAGETYPE_GIF)
            imagegif($image_p, $bigDest);
          else if ($mime==IMAGETYPE_JPEG)
            imagejpeg($image_p, $bigDest);
          else if ($mime==IMAGETYPE_PNG)
            imagepng($image_p, $bigDest);
          else if ($mime==IMAGETYPE_BMP)
            imagewbmp($image_p, $bigDest);
        } else
          copy($oriDest, $bigDest);

        
        $smallDest=$smallDir.$filename;
        if ($width_orig>$smallWidth || $heigh_orig>$smallHeight) {
          $ratio = $width_orig/$height_orig;
          if ($smallWidth/$smallHeight > $ratio) {
            $x = 0;
            $y = ($height_orig-$width_orig)/2;
            $width = $width_orig;
            $height = $width_orig;
          } else {
            $x = ($width_orig-$height_orig)/2;
            $y = 0;
            $width = $height_orig;
            $height = $height_orig;
          }
          $image_p = imagecreatetruecolor($smallWidth, $smallHeight);
          imagecopyresampled($image_p, $image, 0, 0, $x, $y, $smallWidth, $smallHeight, $width, $height);
          if ($mime==IMAGETYPE_GIF)
            imagegif($image_p, $smallDest);
          else if ($mime==IMAGETYPE_JPEG)
            imagejpeg($image_p, $smallDest);
          else if ($mime==IMAGETYPE_PNG)
            imagepng($image_p, $smallDest);
          else if ($mime==IMAGETYPE_BMP)
            imagewbmp($image_p, $smallDest);
        } else
          copy($oriDest, $smallDest);
      }
    } else if(isset($_POST['delimgname'])) {
      unlink('big/'.$_POST['delimgname']);
      unlink('thumbnails/'.$_POST['delimgname']);
      if (substr($_POST['delimgname'], 0, 4)=='img0') {
        $i=0;
        while($i<100 && !file_exists('big/img'.$i.'.jpg') && !file_exists('big/img'.$i.'.png') && !file_exists('big/img'.$i.'.gif') && !file_exists('big/img'.$i.'.bmp'))
          $i++;
        if(file_exists('big/img'.$i.'.jpg')) {
          rename('big/img'.$i.'.jpg', 'big/img0.jpg');
          rename('thumbnails/img'.$i.'.jpg', 'thumbnails/img0.jpg');
        } else if(file_exists('big/img'.$i.'.gif')) {
          rename('big/img'.$i.'.gif', 'big/img0.gif');
          rename('thumbnails/img'.$i.'.gif', 'thumbnails/img0.gif');
        } else if(file_exists('big/img'.$i.'.png')) {
          rename('big/img'.$i.'.png', 'big/img0.png');
          rename('thumbnails/img'.$i.'.png', 'thumbnails/img0.png');
        } else if(file_exists('big/img'.$i.'.bmp')) {
          rename('big/img'.$i.'.bmp', 'big/img0.bmp');
          rename('thumbnails/img'.$i.'.bmp', 'thumbnails/img0.bmp');
        }
        
        // si on a supprimé l'image 0, reprendre la première en tant que 0
      }
    } else if (isset($_POST['delcreaname'])) {
      rrmdir($_POST['delcreaname']);
      //echo $_POST['delcreaname'];
      header("Location: /");
      exit();
    } else if(isset($_POST['logout'])) {
      unset($_SESSION['mode']);
      unset($_SESSION['logged']);
      $logged = FALSE;
    } else if(isset($_POST['preview'])) {
      unset($_SESSION['mode']);
    } else if(isset($_POST['admin'])) {
      $_SESSION['mode'] = 'admin';
    }
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
  }
}
$admin = (isset($_SESSION['mode']) && $_SESSION['mode']=='admin');

if(file_exists('big/img0.jpg'))
  $mainImg = 'img0.jpg';
else if(file_exists('big/img0.gif'))
  $mainImg = 'img0.gif';
else if(file_exists('big/img0.png'))
  $mainImg = 'img0.png';
else if(file_exists('big/img0.bmp'))
  $mainImg = 'img0.bmp';


?>
<!DOCTYPE HTML>
<!--[if lt IE 7 ]> <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7 ]>    <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8 ]>    <html class="lt-ie9"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html> <!--<![endif]-->
<head>
<meta charset="Windows-1252">
<link rel="stylesheet" href="/styles/style.css">
<?php
if (isset($_GET['style'])) {
  $style = $_GET['style'];
  if ($style=='b')
    echo '<link rel="stylesheet" href="/styles/gridshift.css">';
  else if ($style=='c')
    echo '<link rel="stylesheet" href="/styles/brokensquares.css">';
  else 
    echo '<link rel="stylesheet" href="/styles/ceramic.css">';
} else {
?>
<link rel="stylesheet" href="/styles/ceramic.css" title="Ceramic">
<link rel="alternate stylesheet" href="/styles/gridshift.css" title="Gridshift">
<link rel="alternate stylesheet" href="/styles/brokensquares.css" title="Broken Squares">
<?php } ?>
<!--meta name="viewport" content="width=device-width"-->
<title>cre-Jo-tive</title>
<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
</head>
<body>
<?php
if ($logged) {
  echo '<div><form action="" method="POST"><input type="hidden" name="logout" value="logout"/><input type="submit" value="logout"/></form></div>';
  if ($admin)
    echo '<div><form action="" method="POST"><input type="hidden" name="preview" value="preview"/><input type="submit" value="Preview"/></form></div>';
  else
    echo '<div><form action="" method="POST"><input type="hidden" name="admin" value="admin"/><input type="submit" value="Admin"/></form></div>';
}
?>
<div class="title">cre<span class="t">-</span>Jo<span class="t">-</span>tive</div>

<div class="creations">
<?php
if ($handle = opendir('..')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_dir('../'.$entry)) {
            if(file_exists('../'.$entry.'/big/img0.jpg'))
              $mainImg2 = 'img0.jpg';
            else if(file_exists('../'.$entry.'/big/img0.gif'))
              $mainImg2 = 'img0.gif';
            else if(file_exists('../'.$entry.'/big/img0.png'))
              $mainImg2 = 'img0.png';
            else if(file_exists('../'.$entry.'/big/img0.bmp'))
              $mainImg2 = 'img0.bmp';
            else
              unset($mainImg2);
            echo '<div class="thumbsdiv'.(file_exists('../'.$entry.'/forsale')?' forsale':'').(file_exists('../'.$entry.'/sold')?' sold':'').'">';
            echo '<a href="../'.$entry.'">';
            $isCurrent = (realpath('.')==realpath('../'.$entry));
            if ($isCurrent)
              $currentEntry = $entry;
            echo '<img '.($isCurrent?'class="selected" ':'').(isset($mainImg2)?'src="../'.$entry.'/thumbnails/'.$mainImg2.'" ':'').'width="100" height="100"></a></div>'."\n";
        }
    }
    closedir($handle);
}
if ($logged && $admin) {
  echo '<form style="display:inline" action=".." method="POST"><input type="hidden" name="keyword"/><div id="creadd" onclick="creadd();">+</div></form>'."\n";
}
?>
</div>

<table><tbody><tr><td style="padding:10px;padding-right:5px;vertical-align:top;">
<div class="thumbs" style="float:left">
<?php
if ($handle = opendir('thumbnails')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && $entry != "Thumbs.db") {
            echo '<div class="imgdiv">';
            if ($logged && $admin)
              echo '<div class="del"><form id="delthumbform" style="display:inline" action="" method="POST"><input type="hidden" name="delimgname" value="'.$entry.'"/><a href="#" onclick="delthumb(event);">X</a></form></div>';
            echo '<a href="#"><img src="thumbnails/'.$entry.'" data-name="'.$entry.'" width="100" height="100" onclick="javascript:event.preventDefault ? event.preventDefault() : event.returnValue = false;document.getElementById(\'pic\').src=\'big/\'+this.getAttribute(\'data-name\');"></a></div>'."\n";
        }
    }
    closedir($handle);
}
if ($logged && $admin) {
  echo '<form class="imgdiv" action="" method="POST" enctype="multipart/form-data"><div id="thumbadd" onclick="thumbadd();">+<input name="thumbs[]" type="file" multiple accept="image/*" style="opacity:0;width:1px;height:1px" onchange="handleFiles(this.files)"></div></form>'."\n";
}
?>
</div></td>
<td style="padding:10px;padding-left:5px;vertical-align:top;">
<img id="pic" <?php if (isset($mainImg)) echo 'src="big/'.$mainImg.'"'; ?> style="width:100%;max-width:750px"/>

<div class="descr">
<?php include 'desc.html';?>
</div>
</td></tbody></table>

<?php
  if ($logged && $admin) {
     echo '<div><form style="display:inline" action=".." method="POST"><input type="hidden" name="delcreaname" value="'.$currentEntry.'"/><a href="#" onclick="delcrea(event);">Supprimer cette création</a></form></div>';
?>

<script>
  function creadd() {
    var keyword = prompt("Choisis un mot-clé (c'est le mot qui va se retrouver dans l'adresse web de la création)");
    if (keyword!=null && keyword!="") {
      // vérifier que ça n'existe déjà
      var http = new XMLHttpRequest();
      http.open('HEAD', '../'+keyword+'/', false);
      http.send();
      if (http.status!=404) {
        alert("Ce mot-clé existe déjà");
      } else {
        var form = $("#creadd").parent();
        form.children("input").val(keyword);
        form.submit();
      }
    }
  }

  function thumbadd() {
    $("#thumbadd").parent().find("input")[0].click();
  }
  
  function handleFiles() {
    $("#thumbadd").parent().submit();
  }

  function delthumb(event) {
    event.preventDefault ? event.preventDefault() : event.returnValue = false;
    if (confirm("Êtes-vous sûr de vouloir supprimer définitivement cette image ?"))
      $(event.target).parent().submit();
  }
  
  function delcrea(event) {
    event.preventDefault ? event.preventDefault() : event.returnValue = false;
    if (confirm("Êtes-vous sûr de vouloir supprimer définitivement cette création ?"))
      $(event.target).parent().submit();
  }
</script>
<?php } ?>
<script>
  var creations = $(".creations")[0];
  var selected = $(".creations .selected")[0];
  var center = selected.offsetLeft+selected.clientWidth/2;
  var half = creations.clientWidth/2;
  var newOffset = center-half-creations.offsetLeft;
  creations.scrollLeft = newOffset;
</script>

<div style="clear:both"></div>
</body>
</html>
