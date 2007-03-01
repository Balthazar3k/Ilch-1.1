<?php 
#   Copyright by Manuel Staechele
#   Support www.ilch.de

defined ('main') or die ( 'no direct access' );

##
###
####
#####  W E I T E R L E I T U N G S   F U N K T I O N
function wd ($wdLINK,$wdTEXT,$wdZEIT=3) {  
	global $lang;

  if (!is_array($wdLINK)) {
	  $urls  = '<a href="'.$wdLINK.'">'.$lang['forward2'].'</a>';
	  $wdURL = $wdLINK; 
	} else {
	  $urls  = '';
    $i = 0;
		foreach($wdLINK as $k => $v) {
		  if ( $i == 0 ) {
			  $wdURL = $v;
			}
			$urls .= '<a href="'.$v.'">'.$k.'</a><br />';
		  $i++;
		}
	}
	$tpl = new tpl ( 'weiterleitung.htm' );
	$ar = array
	(
    'LINK' => $urls,
	  'URL'  => $wdURL,
		'ZEIT' => $wdZEIT,
		'TEXT' => $wdTEXT 
	);
	$tpl->set_ar_out ( $ar, 0 );
	unset($tpl); 
}

##
###
####
##### g e t   R e c h t
function getrecht ($RECHT, $USERRECHT) {
	if ( empty ( $USERRECHT ) ) {
	  return (false);
	} else {
	  if($USERRECHT <= $RECHT) {
	    return (true);
	  } else {
	    return (false);
	  }
  }
}

##
###
####
##### g e t   U s e r   N a m e
function get_n($uid) {
	$row = db_fetch_object(db_query("SELECT name FROM prefix_user WHERE id = '".$uid."'"));
	return $row->name;
}

##
###
####
##### wochentage sonntag 0 samstag 6
function wtage ($tag) {
  $wtage = array('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag');
  return ($wtage[$tag]);
}

##
###
####
##### monate in deutsch
function getDmon ($mon) {
  $monate = array('Januar','Februar','M&auml;rz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember');
  return($monate[$mon-1]);
}


##
###
####
##### a l l g e m e i n e s   A r r a y
function getAllgAr () {
  
	# v1 = schluessel
	# v2 = wert
	# v3 = feldtyp
  # v4 = kurze beschreibung wenn n�tig
	
	$ar = array();
	$abf = "SELECT schl, wert FROM `prefix_config`";
	$erg = db_query($abf);
	while($row = db_fetch_assoc($erg) ) {
	  $ar[$row['schl']] = $row['wert'];
	}
	return $ar;
}

##
###
####
##### UserRang ermitteln
function userrang ($post,$uid) {
  global $global_user_rang_array;
  
  if (!isset($global_user_rang_array[$uid])) {
    if (!isset($global_user_rang_array)) {
      $global_user_rang_array = array();
    }
    if ( empty($uid) ) {
      $rRang = 'Gast';
    } else {
      $rRang = @db_result(db_query("SELECT bez FROM prefix_user LEFT JOIN prefix_ranks ON prefix_ranks.id = prefix_user.spezrank WHERE prefix_user.id = ".$uid),0);
    }
    if ( empty($rRang) ) {
      $post = ( $post == 0 ? 1 : $post );
      $rRang = @db_result(db_query("SELECT bez FROM `prefix_ranks` WHERE spez = 0 AND min <= ".$post." ORDER BY min DESC LIMIT 1"),0);
    } elseif ( $rRang != 'Gast' ) {
      $rRang = '<i><b>'.$rRang.'</b></i>';
    }
    $global_user_rang_array[$uid] = $rRang;
  }
  
  return ($global_user_rang_array[$uid]); 
}


##
###
####
##### makiert suchwoerter
function  markword($text,$such) { 
  $erg  = '<span style="background-color: #EBF09B;">';
  $erg .= $such."</span>";
	$text = str_replace($such,$erg,$text);
  return $text; 
} 


##
###
####
##### gibt die smiley lilste zurueck
function getsmilies () {
  global $lang;
  $zeilen = 3; $i = 0;
	$b = '<script language="JavaScript" type="text/javascript">function moreSmilies () { var x = window.open("about:blank", "moreSmilies", "width=250,height=200,status=no,scrollbars=yes,resizable=yes"); ';
  $a = '';
  $erg = db_query('SELECT emo, ent, url FROM `prefix_smilies`');
	while ($row = db_fetch_object($erg) ) {

    $b .= 'x.document.write ("<a href=\"javascript:opener.put(\''.addslashes(addslashes($row->ent)).'\')\">");';
    $b .= 'x.document.write ("<img style=\"border: 0px; padding: 5px;\" src=\"include/images/smiles/'.$row->url.'\" title=\"'.$row->emo.'\"></a>");';
        
    if ($i<12) {
      # float einbauen
      if($i%$zeilen == 0 AND $i <> 0) { $a .= '<br /><br />'; }
      $a .= '<a href="javascript:put(\''.addslashes($row->ent).'\')">';
      $a .= '<img style="margin: 2px;" src="include/images/smiles/'.$row->url.'" border="0" title="'.$row->emo.'"></a>';
    }
    $i++;
	}
  $b .= ' x.document.write("<br /><br /><center><a href=\"javascript:window.close();\">'.$lang['close'].'</a></center>"); x.document.close(); }</script>';
  if ($i>12) { $a .= '<br /><br /><center><a href="javascript:moreSmilies();">'.$lang['more'].'</a></center>'; }
  $a = $b.$a;
  return ($a);
}



##
###
####
##### generey key with x length
function genkey ( $anz ) {
	$letterArray = array ('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0');
  $key = '';
	for ($i=0;$i < $anz ; $i ++) 
	{   
	    mt_srand((double)microtime()*1000000);
	    $zufallZahl = mt_rand(0,62);
      $key .= $letterArray[$zufallZahl];		
  } 
	return ( $key );
}

function icmail ($mail,$bet,$txt, $from = '') {
  global $allgAr;
  if ( $from == '' ) {
    $from = $allgAr['allg_default_subject'].' <'.$allgAr['adminMail'].'>';
  }
  $from    = preg_replace ("/\015\012|\015|\012/", "", $from);
  $header  = "From: ".$from."\n";
  $header .= "MIME-Version: 1.0\n";
  $header .= "Content-Type: text/plain; charset=\"iso-8859-1\";\n";
  $header .= "Content-Transfer-Encoding: 8bit";
  
  $mail = escape_for_email($mail);
  $bet  = escape_for_email($bet,true);
  
	if ( mail ( $mail, $bet, $txt,$header) ) {
	  return ( true );
	} else {
	  return ( false );
	}
}


function html_enc_substr($text, $start, $length) {
   $trans_tbl = get_html_translation_table(HTML_ENTITIES);
   $trans_tbl = array_flip($trans_tbl);
   return(htmlentities(substr(strtr($text, $trans_tbl), $start, $length)));
}

function get_datum ($d) {
  if (strpos($d,'.') !== FALSE) { $d = str_replace('.','-',$d); }
  if (strpos($d,'/') !== FALSE) { $d = str_replace('/','-',$d); }
  if (is_numeric(substr($d,-4))) {
    list($t,$m,$j) = explode('-', $d);
  } elseif (is_numeric(substr($d,0,4))) {
    list($j,$m,$t) = explode('-', $d);
  }
  $d = $j.'-'.$m.'-'.$t;
  return ($d);
}

function get_homepage ($h) {
  $h = trim($h);
  if (!empty($h) AND substr($h, 0, 7) != 'http://') {
    $h = 'http://'.$h;
  }
  return ($h);
}

function get_wargameimg ($img) {
  if (file_exists('include/images/wargames/'.$img.'.gif')) {
    return ('<img src="include/images/wargames/'.$img.'.gif" alt="'.$img.'" border="0">');
  } elseif (file_exists('include/images/wargames/'.$img.'.jpg')) {
    return ('<img src="include/images/wargames/'.$img.'.jpg" alt="'.$img.'" border="0">');
  } elseif (file_exists('include/images/wargames/'.$img.'.jpeg')) {
    return ('<img src="include/images/wargames/'.$img.'.jpeg" alt="'.$img.'" border="0">');
  } elseif (file_exists('include/images/wargames/'.$img.'.png')) {
    return ('<img src="include/images/wargames/'.$img.'.png" alt="'.$img.'" border="0">');
  }
  return ('');
}

function iurlencode_help ($a) {
  if (preg_match("/(http:|https:|ftp:)/", $a[0])) {
    return ($a[0]);
  }

  return (rawurlencode($a[1]).substr($a[0], -1));
}

function iurlencode ($s) {
  return (preg_replace_callback("/([^\/]+|\/[^\.])[\.\/]/", 'iurlencode_help', $s));
  /*
  $x = 'false';
  if (preg_match ('/(http:|https:|ftp:)[^:]+:[^@]+@./', $s)) {
    $x = preg_replace('/([^:]+:[^@]+@)./',"\\1",$s);
  	$s = str_replace($x,'',$s);
	} elseif (substr($s, 0, 7) == 'http://') {
	  $s = substr ($s, 7);
		$x = 'http://';
	} elseif (substr($s, 0, 8) == 'https://') {
	  $s = substr ($s, 8);
	  $x = 'https://';
	} elseif (substr($s, 0, 6) == 'ftp://') {
	  $s = substr ($s, 6);
	  $x = 'ftp://';
	}
	
	
	$a = explode('/', $s);
  $r = '';
  for ($i=0;$i<count($a);$i++) {
    $r .= rawurlencode($a[$i]).'/';
  }
	
	if ($x !== 'false') {
	  $r = $x.$r;
	}
	
  $r = substr($r, 0, -1);
  return ($r);
  */
}

# antispam
function chk_antispam ($m) {
  global $allgAr;
  
  if (is_numeric($allgAr['antispam_'.$m]) AND has_right($allgAr['antispam_'.$m])) { return (true); }
  
  if (isset($_POST['antispam']) AND md5($_POST['antispam']) == $_POST['antispam_e'.$m]) {
	  unset ($_SESSION['antispam'][$m]);
	  return (true);
	}
	
	return (false);
}

function get_antispam ($m, $t) {
  global $allgAr;
  
  if (is_numeric($allgAr['antispam_'.$m]) AND has_right($allgAr['antispam_'.$m])) { return (''); }

	if (!is_array($_SESSION['antispam'])) {
	  $_SESSION['antispam'] = array();
	}
	
	$_SESSION['antispam'][$m] = array();
	
	$zeichen = array ('+', '-');
	
	mt_srand((double)microtime()*1000000);
	$z = $zeichen[mt_rand(0,1)];
	
	mt_srand((double)microtime()*1040404);
	$i1 = mt_rand (2,8);
	
	switch ($z) {
	  case '+' : $i2_2 = 9 - $i1; break;
		case '-' : $i2_2 = $i1 - 1; break;
	}
	
	mt_srand((double)microtime()*1059595);
	$i2 = mt_rand (1,$i2_2);
	
	if ($z == '+') {
	  $e = $i1 + $i2;
	} else {
	  $e = $i1 - $i2;
	}
  
	$za = array ('+' => 'plus', '-' => 'minus');
	$_SESSION['antispam'][$m] = array($i1, $za[$z], $i2);
	
	$rs = '<span style="display: inline; width: 100px; vertical-align: middle; text-align: center; background-color: #000000; border: 0px; padding: 2px; margin: 0px;"><img src="include/images/spam/z.php?m='.$m.'&amp;w=0&amp;'.session_name().'='.session_id().'" alt=""><img src="include/images/spam/z.php?m='.$m.'&amp;w=1&amp;'.session_name().'='.session_id().'" alt=""><img src="include/images/spam/z.php?m='.$m.'&amp;w=2&amp;'.session_name().'='.session_id().'" alt=""><img src="include/images/spam/ist.jpg" alt=""><input name="antispam_e'.$m.'" value="'.md5($e).'" type="hidden" /><input name="antispam" size="1" maxlength="1" style="background-color: #FFFFFF; border: 0px; margin: 0px; padding: 0px;" /></span>';
  if ($t == 0) {
	  return ($rs);
	} elseif ($t == 1) {
	  return ('<tr><td class="Cmite">Antispam</td><td class="Cnorm">'.$rs.'</td></tr>');
  } elseif ($t > 10) {
	  return ('<label style="float:left; width: '.$t.'px; ">Antispam</label>'.$rs.'<br />');
	} else {
	  return ('');
	}
}
# antispam

?>
