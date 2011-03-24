<?php
/* Copyright (C) 2011 FH Technikum Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../config/cis.config.inc.php');
require_once('../include/content.class.php');
require_once('../include/template.class.php');
require_once('../include/functions.inc.php');
require_once('../include/sprache.class.php');
require_once('../include/gruppe.class.php');
require_once('../include/xsdformprinter/xsdformprinter.php');

$user = get_uid();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>FH Complete CMS ContentEditor</title>
	<link href="../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<link href="../skin/jquery.css" rel="stylesheet" type="text/css"/>
	<link href="../skin/fhcomplete.css" rel="stylesheet" type="text/css">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="../include/js/jquery.js"></script>
		
	<script type="text/javascript">

	tinyMCE.init
	(
		{
		mode : "textareas",
		theme : "advanced",
		file_browser_callback: "FHCFileBrowser"
		}
	);
	function FHCFileBrowser(field_name, url, type, win) 
	{
		cmsURL = "<?php echo APP_ROOT;?>cms/tinymce_dms.php?type"+type;
		tinyMCE.activeEditor.windowManager.open({
			file: cmsURL,
			title : "FHComplete File Browser",
			width: 420,
			heigth: 400,
			resizable: "yes",
			close_previous: "no"
		},{
			window: win,
			input: field_name
		});
		return false;
	}
	</script>
	<style>
	ul
	{
		padding-left: 20px;
	}
	li
	{
		padding-left: 0px;
	}
	.marked
	{
		text-decoration: underline;
	}
	</style>
</head>

<body>
<?php

$sprache = isset($_GET['sprache'])?$_GET['sprache']:'German';
$version = isset($_GET['version'])?$_GET['version']:null;
$content_id = isset($_GET['content_id'])?$_GET['content_id']:null;
$action = isset($_GET['action'])?$_GET['action']:'';
$message = '';

//Inhalt Speichern
if(isset($_POST['XSDFormPrinter_XML']))
{
	$content = new content();
	$content->getContent($content_id, $sprache, $version);

	
	if($content->saveContent($content->contentsprache_id, $_POST['XSDFormPrinter_XML']))
		$message.= '<span class="ok">Inhalt wurde erfolgreich gespeichert</span>';
	else
		$message.= '<span class="error">'.$content->errormsg.'</span>';
}

if(isset($_GET['method']))
{
	switch($_GET['method'])
	{
		case 'rights_add_group':
			if(!isset($_POST['gruppe_kurzbz']))
				die('Fehlender Parameter');
			
			$content = new content();
			$content->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
			$content->insertamum = date('Y-m-d H:i:s');
			$content->insertvon = $user;
			$content->content_id=$content_id;
			
			if(!$content->addGruppe())
				$message .= '<span class="error">'.$content->errormsg.'</span>';
			else
				$message .= '<span class="ok">Gruppe wurde erfolgreich hinzugefügt</span>';
			
			break;
		case 'rights_delete_group':
			if(!isset($_GET['gruppe_kurzbz']))
				die('Fehlender Parameter');
			
			$content = new content();
			if(!$content->deleteGruppe($content_id, $_GET['gruppe_kurzbz']))
				$message .= '<span class="error">'.$content->errormsg.'</span>';
			else
				$message .= '<span class="ok">Gruppe wurde erfolgreich entfernt</span>';
			
			break;
		default: break;
	}
}
//Menue Baum
echo '<table width="100%">
	<tr>
		<td colspan="2">
		<h1>FH Complete CMS</h1>
		</td>
	</tr>
	<tr>
		<td valign="top" width="200px">';


$db = new basis_db();

echo '
<a href="#Neu">Neuen Eintrag hinzufügen</a>
<br><br>
<table class="treetable">';
$qry = "SELECT * FROM (
				SELECT 
					distinct on(content_id) *					 
				FROM 
					campus.tbl_content
					LEFT JOIN campus.tbl_contentchild USING(content_id)
				WHERE content_id NOT IN (SELECT child_content_id FROM campus.tbl_contentchild WHERE child_content_id=tbl_content.content_id)
				) as a
				ORDER BY contentchild_id, titel";
if($result = $db->db_query($qry))
{
	echo '<tr>';
	while($row = $db->db_fetch_object($result))
	{
	
		$content = new content();
	
		echo '<td>';
		drawmenulink($row->content_id, $row->titel);
		echo '</td>';
		drawsubmenu($row->content_id);
	}
	echo '</td>';
}

echo '</table>';

echo '</td><td valign="top">';

//Editieren
if(!is_null($content_id))
{
	echo '<a href="'.$_SERVER['PHP_SELF'].'?action=prefs&content_id='.$content_id.'" '.($action=='prefs'?'class="marked"':'').'>Eigenschaften</a>';
	echo ' | <a href="'.$_SERVER['PHP_SELF'].'?action=content&content_id='.$content_id.'" '.($action=='content'?'class="marked"':'').'>Inhalt</a>';
	echo ' | <a href="'.$_SERVER['PHP_SELF'].'?action=preview&content_id='.$content_id.'" '.($action=='preview'?'class="marked"':'').'>Vorschau</a>';
	echo ' | <a href="'.$_SERVER['PHP_SELF'].'?action=rights&content_id='.$content_id.'" '.($action=='rights'?'class="marked"':'').'>Rechte</a>';
	echo '<div style="float: right;">'.$message.'</div>';
	echo '<br><br>';

	
	switch($action)
	{
		case 'prefs': break;
		case 'content': 
					print_content();
					break;
		case 'preview': 
					echo '<iframe src="content.php?content_id='.$content_id.'&version='.$version.'" style="width: 600px; height: 500px; border: 1px solid black;">';
					break;
		case 'rights': 
					print_rights();
					break;
		default: break;
	}
	
}
echo '</td></tr></table>';
echo '</body>
</html>';

/******* FUNCTIONS **********/
function drawmenulink($id, $titel)
{
	global $content_id, $action, $sprache, $version;
	echo '<a href="admin.php?content_id='.$id.'&action='.$action.'&sprache='.$sprache.'&version='.$version.'" '.($content_id==$id?'class="marked"':'').'>'.$titel.'</a>';
}

function drawsubmenu($content_id, $einrueckung="&nbsp;&nbsp;")
{
	global $db, $action;
	
	$qry = "SELECT 
				tbl_contentchild.content_id,
				tbl_contentchild.child_content_id,
				tbl_content.titel
			FROM
				campus.tbl_contentchild
				JOIN campus.tbl_content ON(tbl_contentchild.child_content_id=tbl_content.content_id)
			WHERE
				tbl_contentchild.content_id='".addslashes($content_id)."'";
	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			
			while($row = $db->db_fetch_object($result))
			{
				$vorhanden[]=$row->child_content_id;
				echo "<tr>\n";
				echo '<td>';
				echo $einrueckung;
				drawmenulink($row->child_content_id, $row->titel);
				drawsubmenu($row->child_content_id, $einrueckung."&nbsp;&nbsp;");
				echo "</td>\n";
				echo "</tr>\n";
			}
			
			//echo '<br>';
		}
	}
}

function print_rights()
{
	global $content_id, $sprache, $version;
	$content = new content();
	$content->loadGruppen($content_id);
	
	if(count($content->result)>0)
	{
		echo 'Die Mitglieder der folgenden Gruppen dürfen die Seite ansehen:<br><br>';
		echo '
		<script type="text/javascript">
			$(document).ready(function() 
			{ 
				$("#rights_table").tablesorter(
				{
					sortList: [[1,1]],
					widgets: ["zebra"]
				});
			});
		</script>';
		echo '<table id="rights_table" class="tablesorter" style="width: auto;">
			<thead>
			<tr>
				<th>Gruppe Kurzbz</th>
				<th>Bezeichnung</th>
				<th></th>
			</tr>
			</thead>
			<tbody>';
		foreach($content->result as $row)
		{
			echo '<tr>';
			echo '<td>',$row->gruppe_kurzbz,'</td>';
			echo '<td>',$row->bezeichnung,'</td>';
			echo '<td>
					<a href="'.$_SERVER['PHP_SELF'].'?action=rights&content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&gruppe_kurzbz='.$row->gruppe_kurzbz.'&method=rights_delete_group" title="entfernen">
						<img src="../skin/images/delete_x.png">
					</a>
				</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		
		$gruppe = new gruppe();
		$gruppe->getgruppe(null, null, null, null, true);
		
		echo '<form action="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=rights&method=rights_add_group" method="POST">';
		echo 'Gruppe <select name="gruppe_kurzbz">';
		foreach($gruppe->result as $row)
		{
			echo '<option value="'.$row->gruppe_kurzbz.'">'.$row->gruppe_kurzbz.'</option>';
		}
		echo '</select>';
		echo '<input type="submit" value="Hinzufügen" name="addgroup">';
		echo '</form>';
	}
	else
		echo 'Diese Seite darf von allen angezeigt werden!';
}

function print_content()
{
	global $content_id, $sprache, $version;
	
	$content = new content();

	if(!$content->getContent($content_id, $sprache, $version))
		die($content->errormsg);
		
	echo '<div>';
	$template = new template();
	$template->load($content->template_kurzbz);

	$xfp = new XSDFormPrinter\XSDFormPrinter();
	$xfp->getparams='?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=content';
	$xfp->output($template->xsd,$content->content);
	echo '</div>';
}
?>
