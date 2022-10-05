<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *          Manfred Kindl	<manfred.kindl@technikum-wien.at>
 */
/**
 * Detailseite zum Zuweisen von Berechtigungen zu Benutzern
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/berechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/wawi_kostenstelle.class.php');
require_once('../../include/log.class.php');

/*
 * TODOs
 *

Checkbox-Range
Mehrfach-Löschen


Wawi und kostenstelle ausblenden
Checkbox markieren bei (doppel)klick auf Zeile

----------------------

Nach übertragen gleich zu Person springen
Bug in Kopieren - Verdoppelt alle Einträge
Sortierreihenfolge. OE wird anscheinend nicht sortiert
ART prüfen auf schreibweise
BEschreibungstexte bestehender Rechte

 */
$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));

if(!$rechte->isBerechtigt('basis/berechtigung'))
	die('Sie haben keine Berechtigung fuer diese Seite');

//$reloadstr = '';  // neuladen der liste im oberen frame
$htmlstr = '';
$errorstr = '';
$successstr = '';
$sel = '';
$chk = '';
$oe_arr = array();
$rolle_arr = array();
$berechtigung_arr = array();
$st_arr = array();
$berechtigung_user_arr = array();

$benutzerberechtigung_id = '';
$art = '';
$oe_kurzbz = '';
$studiengang_kurzbz = '';
$berechtigung_kurzbz = '';
$uid = '';
$studiensemester_kz = '';
$start = '';
$ende = '';
$neu = false;
$negativ = false;
$filter=(isset($_GET['filter'])?$_GET['filter']:'alle');

if(isset($_POST['delete']) && $_POST['delete'] != '')
{
	if(!$rechte->isBerechtigt('basis/berechtigung', null, 'suid'))
		die($rechte->errormsg);

	$benutzerberechtigung_id = $_POST['delete'];

	$ber = new benutzerberechtigung();
	if(!$ber->delete($benutzerberechtigung_id))
		$errorstr .= 'Datensatz konnte nicht gel&ouml;scht werden!';

	//$reloadstr .= "<script type='text/javascript'>";
	//$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
	//$reloadstr .= "</script>";

}

if(isset($_POST['delete_multi']) && $_POST['delete_multi'] != '')
{
	if(!$rechte->isBerechtigt('basis/berechtigung', null, 'suid'))
		die($rechte->errormsg);

	if (isset($_POST['dataset']))
	{
		$i = 0;
		foreach ($_POST['dataset'] AS $benutzerberechtigung_id => $value)
		{
			// Nur markierte Rechte kopieren
			if (!isset($value['check']))
			{
				continue;
			}

			$ber = new benutzerberechtigung();
			if(!$ber->delete($benutzerberechtigung_id))
			{
				$errorstr .= 'Datensatz konnte nicht gel&ouml;scht werden!';
			}
			else
			{
				$i ++;
				//Log schreiben
				$log = new log();

				$logdata = var_export((array) $ber, true);
				$log->new = true;
				$log->sql = $logdata;
				$log->sqlundo = 'Kein Undo vorhanden';
				$log->executetime = date('Y-m-d H:i:s');
				$log->mitarbeiter_uid = $user;
				$log->beschreibung = 'Berechtigung gelöscht';

				if(!$log->save())
				{
					$errorstr .= "<span style='color: red'><b>Fehler beim schreiben des Log-Eintrags</b></span><br>";
				}
			}
		}
		if ($errorstr == '')
		{
			$successstr .= "<span style='color: green'><b>".$i." Rechte erfolgreich gelöscht</b></span><br>";
		}
	}



	//$reloadstr .= "<script type='text/javascript'>";
	//$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
	//$reloadstr .= "</script>";

}

if(isset($_POST['uebertragen']) && $_POST['uebertragen_nach'] != '')
{
	//echo '<pre>', var_dump($_POST), '</pre>';exit();
	if($rechte->isBerechtigt('basis/berechtigung', null, 'suid'))
	{
		$uidVon = $_POST['uid'];
		$copyTo = $_POST['uebertragen_nach'];

		if (isset($_POST['dataset']))
		{
			$i = 0;
			foreach ($_POST['dataset'] AS $key => $value)
			{
				// Nur markierte Rechte kopieren
				if (!isset($value['check']))
				{
					continue;
				}

				$rolle_kurzbz = (isset($value['rolle_kurzbz']) ? $value['rolle_kurzbz'] : '');
				$berechtigung_kurzbz = (isset($value['berechtigung_kurzbz']) ? $value['berechtigung_kurzbz'] : '');
				$art = (isset($value['art']) ? $value['art'] : '');
				$oe_kurzbz = (isset($value['oe_kurzbz']) ? $value['oe_kurzbz'] : '');
				$kostenstelle_id = (isset($value['kostenstelle_id']) ? $value['kostenstelle_id'] : '');
				$start = (isset($value['start']) ? $value['start'] : '');
				$ende = (isset($value['ende']) ? $value['ende'] : '');
				$anmerkung = (isset($value['anmerkung']) ? $value['anmerkung'] : '');

				$funktion_kurzbz = (isset($value['funktion_kurzbz']) ? $value['funktion_kurzbz'] : '');
				$studiensemester_kurzbz = null;

				$ber = new benutzerberechtigung();
				$ber->insertamum = date('Y-m-d H:i:s');
				$ber->insertvon = $user;
				$ber->new = true;

				if (isset($value['negativ']))
					$ber->negativ = true;
				else
					$ber->negativ = false;

				$ber->art = $art;
				$ber->oe_kurzbz = $oe_kurzbz;
				$ber->berechtigung_kurzbz = $berechtigung_kurzbz;
				$ber->rolle_kurzbz = $rolle_kurzbz;
				$ber->uid = $copyTo;
				$ber->funktion_kurzbz = $funktion_kurzbz;
				$ber->studiensemester_kurzbz = $studiensemester_kurzbz;
				$ber->start = $start;
				$ber->ende = $ende;
				$ber->updateamum = date('Y-m-d H:i:s');
				$ber->updatevon = $user;
				$ber->kostenstelle_id = $kostenstelle_id;
				$ber->anmerkung = 'Kopiert von UID '.$uidVon.($anmerkung!=''?'. Anmerkung von UID '.$uidVon.': '.$anmerkung:'');

				if(!$ber->save())
				{
					$errorstr .= "Datensatz konnte nicht gespeichert werden!".$ber->errormsg;
				}
				else
				{
					$i ++;
					//Log schreiben
					$log = new log();

					$logdata = var_export((array) $ber, true);
					$log->new = true;
					$log->sql = $logdata;
					$log->sqlundo = 'Kein Undo vorhanden';
					$log->executetime = date('Y-m-d H:i:s');
					$log->mitarbeiter_uid = $user;
					$log->beschreibung = 'Berechtigung übertragen von '.$uidVon.' nach '.$copyTo;

					if(!$log->save())
					{
						$errorstr .= "<span style='color: red'><b>Fehler beim schreiben des Log-Eintrags</b></span><br>";
					}
				}
			}
			if ($errorstr == '')
			{
				$successstr .= "<span style='color: green'><b>".$i." Rechte erfolgreich kopiert</b></span><br>";
				echo "<script>window.location.href='".$_SERVER['PHP_SELF']."?uid=$copyTo'</script>";
			}
		}
	}
	else
	{
		$errorstr.= $rechte->errormsg;
	}
}

if(isset($_POST['schick']))
{
	if($rechte->isBerechtigt('basis/berechtigung', null, 'suid'))
	{
		if (isset($_POST['dataset']))
		{
			foreach ($_POST['dataset'] AS $benutzerberechtigung_id => $value)
			{
				$rolle_kurzbz = (isset($value['rolle_kurzbz']) ? $value['rolle_kurzbz'] : '');
				$berechtigung_kurzbz = (isset($value['berechtigung_kurzbz']) ? $value['berechtigung_kurzbz'] : '');
				$art = (isset($value['art']) ? $value['art'] : '');
				$oe_kurzbz = (isset($value['oe_kurzbz']) ? $value['oe_kurzbz'] : '');
				//echo '<pre>', var_dump($oe_kurzbz), '</pre>';
				$kostenstelle_id = (isset($value['kostenstelle_id']) ? $value['kostenstelle_id'] : '');
				$start = (isset($value['start']) ? $value['start'] : '');
				$ende = (isset($value['ende']) ? $value['ende'] : '');
				$uid = $_POST['uid'];
				$anmerkung = (isset($value['anmerkung']) ? $value['anmerkung'] : '');

				$funktion_kurzbz = (isset($_POST['funktion_kurzbz']) ? $_POST['funktion_kurzbz'] : '');
				$studiensemester_kurzbz = null;

				$ber = new benutzerberechtigung();
				if (isset($_POST['neu']))
				{
					$ber->insertamum = date('Y-m-d H:i:s');
					$ber->insertvon = $user;
					$ber->new = true;
				}
				else
				{
					if(!$ber->load($benutzerberechtigung_id))
					{
						die('Fehler beim Laden der Berechtigung');
					}
					//Nur bei geänderten Datensätzen das Updatedatum setzen
					if ($ber->berechtigung_kurzbz != $berechtigung_kurzbz
						|| $ber->art != $art
						|| $ber->oe_kurzbz != $oe_kurzbz
						|| $ber->rolle_kurzbz != $rolle_kurzbz
						|| $ber->uid != $uid
						|| $ber->funktion_kurzbz != $funktion_kurzbz
						|| $ber->studiensemester_kurzbz != $studiensemester_kurzbz
						|| $ber->start != $start
						|| $ber->ende != $ende
						|| $ber->kostenstelle_id != $kostenstelle_id
						|| $ber->anmerkung != $anmerkung
						|| $ber->negativ != isset($value['negativ'])
					)
					{
						$ber->updateamum = date('Y-m-d H:i:s');
						$ber->updatevon = $user;
					}
				}
				if (isset($value['negativ']))
					$ber->negativ = true;
				else
					$ber->negativ = false;

				$ber->benutzerberechtigung_id = $benutzerberechtigung_id;
				$ber->art = $art;
				$ber->oe_kurzbz = $oe_kurzbz;
				$ber->berechtigung_kurzbz = $berechtigung_kurzbz;
				$ber->rolle_kurzbz = $rolle_kurzbz;
				$ber->uid = $uid;
				$ber->funktion_kurzbz = $funktion_kurzbz;
				$ber->studiensemester_kurzbz = $studiensemester_kurzbz;
				$ber->start = $start;
				$ber->ende = $ende;
				$ber->kostenstelle_id = $kostenstelle_id;
				$ber->anmerkung = $anmerkung;

				if(!$ber->save())
				{
					if (!$ber->new)
						$errorstr .= "Datensatz konnte nicht upgedatet werden!".$ber->errormsg;
					else
						$errorstr .= "Datensatz konnte nicht gespeichert werden!".$ber->errormsg;
				}
				else
				{
					//Log schreiben
					$log = new log();

					$logdata = var_export((array) $ber, true);
					$log->new = true;
					$log->sql = $logdata;
					$log->sqlundo = 'Kein Undo vorhanden';
					$log->executetime = date('Y-m-d H:i:s');
					$log->mitarbeiter_uid = $user;
					if (isset($_POST['neu']))
						$log->beschreibung = 'Neue Berechtigung für '.$uid.' angelegt';
					else
						$log->beschreibung = 'Berechtigung für '.$uid.' aktualisiert';

					if(!$log->save())
					{
						$errorstr .= "<span style='color: red'><b>Fehler beim schreiben des Log-Eintrags</b></span><br>";
					}
				}
			}
		}
	}
	else
	{
		$errorstr.='Fehler beim Speichern: '.$rechte->errormsg;
	}
}

if(isset($_POST['copy']) && $_POST['copy'] != '')
{
	if($rechte->isBerechtigt('basis/berechtigung', null, 'suid'))
	{
		$ber = new benutzerberechtigung();
		if(!$ber->load($_POST['copy']))
			die('Fehler beim Laden der Berechtigung');

		$ber->new = true;
		$ber->insertamum = date('Y-m-d H:i:s');
		$ber->insertvon = $user;

		if(!$ber->save())
		{
			if (!$ber->new)
				$errorstr .= "Datensatz konnte nicht upgedatet werden!".$ber->errormsg;
			else
				$errorstr .= "Datensatz konnte nicht gespeichert werden!".$ber->errormsg;
		}
		else
		{
			//Log schreiben
			$log = new log();

			$logdata = var_export((array) $ber, true);
			$log->new = true;
			$log->sql = $logdata;
			$log->sqlundo = 'Kein Undo vorhanden';
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $user;
			$log->beschreibung = 'Berechtigung für '.$uid.' kopiert';

			if(!$log->save())
			{
				$errorstr .= "<span style='color: red'><b>Fehler beim schreiben des Log-Eintrags</b></span><br>";
			}
		}
	}
	else
	{
		$errorstr.='Fehler beim Speichern: '.$rechte->errormsg;
	}
}

if (!$b = new berechtigung())
	die($b->errormsg);

$b->getRollen();
foreach($b->result as $berechtigung)
{
	$rolle_arr[] = $berechtigung->rolle_kurzbz;
}
sort($rolle_arr);
$b->getBerechtigungen();
foreach($b->result as $berechtigung)
{
	$berechtigung_arr[$berechtigung->berechtigung_kurzbz] = $berechtigung->beschreibung;
	$berechtigung_beschreibung_arr[] = $berechtigung->beschreibung;
}

$st = new studiensemester();
$st->getAll();
foreach($st->studiensemester as $studiensemester)
{
	$st_arr[] = $studiensemester->studiensemester_kurzbz;
}

$oe = new organisationseinheit();
$oe->getAll();
foreach ($oe->result AS $row)
{
	$oe_arr[$row->oe_kurzbz] = $row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung;
}

$kostenstelle = new wawi_kostenstelle();
$kostenstelle->getAll();
foreach ($kostenstelle->result AS $row)
{
	$kst_arr[$row->kostenstelle_id] = $row->bezeichnung;
}

if (isset($_REQUEST['uid']) || isset($_REQUEST['funktion_kurzbz']))
{
	$uid='';
	$funktion_kurzbz='';
	$rights = new benutzerberechtigung();
	if(isset($_REQUEST['uid']) && $_REQUEST['uid']!='')
	{
		$uid = $_REQUEST['uid'];

		$bn = new benutzerberechtigung();
		$bn->getBerechtigungen($uid);
		foreach($bn->berechtigungen as $berechtigung)
		{
			$berechtigung_user_arr[] = $berechtigung->berechtigung_kurzbz;
		}
		$ben = new benutzer();
		if (!$ben->load($uid))
			die('Benutzer existiert nicht');

		$rights->loadBenutzerRollen($uid);
		$name = new benutzer();
		$name->load($uid);

		$htmlstr .= "Berechtigungen von <b>".$name->nachname." ".$name->vorname." (".$uid.")</b>";
		$htmlstr .= "<p class='inserterror'>".$errorstr."</p><p class='insertok'>".$successstr."</p>";

		//Formular zum Kopieren von Berechtigungen
		//$htmlstr .= "<form action='benutzerberechtigung_details.php?uid=".$uid."' method='POST' name='berechtigung_kopieren'>";
		//$htmlstr .= "Berechtigungen (aktive) kopieren von UID <input id='uid_von' name='uid_von' type='text'>";
		//$htmlstr .= "<input type='submit' name='kopieren' value='Kopieren' onclick=\"if (document.getElementById('uid_von').value == '')  {alert('UID darf nicht leer sein'); return false}\">";
		//$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>";
		//$htmlstr .= "</form>";
		$i = 0;

		// Zusätzlich jede Funktion mit einer gültigen Berechtigung anzeigen
		$benutzerfunktion = new benutzerfunktion();
		$benutzerfunktion->getBenutzerFunktionByUid($uid,null,'now()','now()');
		$bnfkt = array();
		foreach ($benutzerfunktion->result as $recht)
		{
			$bnfkt[] = $recht->funktion_kurzbz;
		}
		$bnfkt = array_unique($bnfkt); // Um doppelte Funktionen zB mehrere Assistenzfunktionen zu entfernen
		if($benutzerfunktion!='')
		{
			foreach($bnfkt as $recht)
			{
				$rechte_funktion = new benutzerberechtigung();
				$rechte_funktion->loadBenutzerRollen(null, $recht);
				$funktionsrecht = $rechte_funktion->berechtigungen; // Hat die Funktion ein Recht?
				$funktion_bezeichnung = new funktion();
				$funktion_bezeichnung->load($recht);
				if(!empty($funktionsrecht))
				{
					$i++;
					if ($i==1)
					{
						$htmlstr .= "<p>Geerbte Berechtigungen aus Funktion(en): ";
					}
					if ($i > 1)
					{
						$htmlstr .= "</a>, ";
					}

					$htmlstr .= "<a href='benutzerberechtigung_details.php?funktion_kurzbz=$funktion_bezeichnung->funktion_kurzbz' target='_blank'>";
					$htmlstr .= $funktion_bezeichnung->beschreibung;
				}
			}
			if(isset($funktionsrecht))
			{
				$htmlstr .= '</a></p>';
			}
		}
	}
	elseif(isset($_REQUEST['funktion_kurzbz']) && $_REQUEST['funktion_kurzbz']!='')
	{
		$funktion_kurzbz = $_REQUEST['funktion_kurzbz'];

		$funktion = new funktion();
		if(!$funktion->load($funktion_kurzbz))
			die('Funktion existiert nicht');

		$rights->loadBenutzerRollen(null, $funktion_kurzbz);
		$htmlstr .= "Berechtigungen der Funktion <b>".$funktion->beschreibung."</b>";
	}

	//$htmlstr .= "Berechtigungen von <b>".$name->nachname." ".$name->vorname." (".$uid.")".$funktion_kurzbz."</b>";
	/*$htmlstr .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Filter:
	  <a href="benutzerberechtigung_details.php?filter=alle&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='alle'?'style="font-weight:bold"':'').'>Alle</a>
	| <a href="benutzerberechtigung_details.php?filter=wawi&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='wawi'?'style="font-weight:bold"':'').'>nur WaWi</a>
	| <a href="benutzerberechtigung_details.php?filter=ohnewawi&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='ohnewawi'?'style="font-weight:bold"':'').'>ohne WaWi</a>

	';*/

	////////////////
	// Neue Berechtigung einfügen
	////////////////

	$htmlstr .= "<table>";
	$htmlstr .= "<thead><tr></tr>";
	$htmlstr .= "<tr>
					<th></th>
					<th>Rolle</th>
					<th>Berechtigung</th>
					<th>Art</th>
					<th>Organisationseinheit</th>
					<th>Kostenstelle</th>
					<th>Neg</th>
					<th>Gültig ab</th>
					<th>Gültig bis</th>
					<th>Anmerkung</th>
					<th>Info</th>
					<th></th>
					<th></th>
				</tr></thead><tbody>";

	$htmlstr .= "<tr id='neu'>";
	$htmlstr .= "<form action='benutzerberechtigung_details.php?uid=".$uid."&funktion_kurzbz=".$funktion_kurzbz."' method='POST' name='berechtigung_neu'>";
	$htmlstr .= "<input type='hidden' name='neu' value='1'>";
	$htmlstr .= "<input type='hidden' name='benutzerberechtigung_id' value=''>";
	$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>";
	$htmlstr .= "<input type='hidden' name='funktion_kurzbz' value='".$funktion_kurzbz."'>";

	$htmlstr .= "<td>Neu: </td>";
	//Rolle
	$htmlstr .= "		<td><select name='dataset[0][rolle_kurzbz]' id='rolle_kurzbz_neu'>";
	$htmlstr .= "			<option value=''>&nbsp;</option>";
	for ($i = 0; $i < sizeof($rolle_arr); $i++)
	{
		$sel = "";
		$htmlstr .= "				<option value='".$rolle_arr[$i]."' ".$sel.">".$rolle_arr[$i]."</option>";
	}
	$htmlstr .= "		</select></td>";

	//Berechtigung_kurzbz
	$htmlstr .= "		<td>";
	$htmlstr .= "			<input type='text' placeholder='Berechtigung' id='berechtigung_neu_autocomplete' class='berechtigung_autocomplete' name='dataset[0][berechtigung_kurzbz]'>";
	$htmlstr .= "		</td>";

	//Art
	$htmlstr .= "		<td>";
	$htmlstr .= "			<input id='art_neu' type='text' name='dataset[0][art]' value='' size='5' maxlength='5' placeholder='suid'>";
	$htmlstr .= "		</td>";

	//Organisationseinheit
	if($funktion_kurzbz != '')
	{
		$htmlstr .= "		<td class='oe_column'>OE aus MA-Funktion</td>";
	}
	else
	{
		$htmlstr .= "		<td class='oe_column'>";
		$htmlstr .= "			<input type='hidden' name='dataset[0][oe_kurzbz]' value=''>";
		$htmlstr .= "			<input type='text' placeholder='Organisationseinheit' id='oe_kurzbz_neu_autocomplete' class='oe_kurzbz_autocomplete'>";
		$htmlstr .= "		</td>";
	}

	//Kostenstelle
	$htmlstr .= "		<td class='ks_column'>";
	$htmlstr .= "			<input type='hidden' name='dataset[0][kostenstelle_id]' value=''>";
	$htmlstr .= "			<input type='text' placeholder='Kostenstelle' id='kostenstelle_autocomplete_neu' class='kostenstelle_autocomplete'>";
	$htmlstr .= "		</td>";

	/*
	$htmlstr .= "		<td class='ks_column'><select id='kostenstelle_neu' name='dataset[0][kostenstelle_id]' style='width: 200px;'>";
	$htmlstr .= "			<option value=''>&nbsp;</option>";

	foreach ($kostenstelle->result as $kst)
	{
		if(!$kst->aktiv)
			$class='class="inactive"';
		else
			$class='';

		$htmlstr .= "		<option value='".$kst->kostenstelle_id."' ".$class.">".$kst->bezeichnung.'</option>';
	}
	$htmlstr .= "		</select></td>";*/

	//Negativ
	$htmlstr .= "		<td align='center'><input type='checkbox' name='dataset[0][negativ]'></td>";

	//Start
	$htmlstr .= "		<td nowrap><input class='datepicker_datum' type='text' name='dataset[0][start]' value='' size='10' maxlength='10'></td>";

	//Ende
	$htmlstr .= "		<td nowrap><input class='datepicker_datum' type='text' name='dataset[0][ende]' value='' size='10' maxlength='10'></td>";

	//Anmerkung
	$htmlstr .= "		<td><input id='anmerkung_neu' type='text' name='dataset[0][anmerkung]' value='' size='30' maxlength='256'></td>";

	$htmlstr .= "		<td><input type='submit' name='schick' value='Speichern' onclick='return validateNewData()'></td>";
	$htmlstr .= "</form>";
	$htmlstr .= "	</tr></tbody></table>";

	$htmlstr .= "	<br/>";

	////////////////
	// Tabelle für bestehende Berechtigungen
	////////////////

	$htmlstr .= "<p style='font-size: small' id='anzahl'></p>";
	$htmlstr .= "<form action='benutzerberechtigung_details.php?uid=".$uid."&funktion_kurzbz=".$funktion_kurzbz."' method='POST'>";

	$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>";
	$htmlstr .= "<input type='hidden' name='funktion_kurzbz' value='".$funktion_kurzbz."'>";
	$htmlstr .= "	<table style='width: 100%; white-space: nowrap; margin-bottom: -8px;'>
					<tr>
						<td style='width: 33.3%; padding-left: 10px;'>
							<a href='#' data-toggle='checkboxes' data-action='toggle' id='toggle_t1'><img src='../../skin/images/checkbox_toggle.png' name='toggle'></a>
							<a href='#' data-toggle='checkboxes' data-action='uncheck' id='uncheck_t1'><img src='../../skin/images/checkbox_uncheck.png' name='toggle'></a>
						</td>
					</tr>
					</table>
					";
	$htmlstr .= "<table id='t1' class='tablesorter'>";
	$htmlstr .= "<thead><tr></tr>";
	$htmlstr .= "<tr>
					<th style='width: 30px'></th>
					<th>Rolle</th>
					<th>Berechtigung</th>
					<th style='width: 30px'>Art</th>
					<th class='oe_column'>Organisationseinheit</th>
					<th class='ks_column'>Kostenstelle</th>
					<!--<th>Semester</th>-->
					<th style='width: 30px'>Neg</th>
					<th>Gültig ab</th>
					<th>Gültig bis</th>
					<th>Anmerkung</th>
					<th style='width: 30px'>Info</th>
					<th></th>
					<!--<th></th>-->
				</tr></thead><tbody>";

	foreach($rights->berechtigungen as $b)
	{
		switch($filter)
		{
			case 'alle'; break;
			case 'wawi';
			if(!mb_strstr($b->berechtigung_kurzbz,'wawi'))
				continue 2;
			break;
			case 'ohnewawi';
			if(mb_strstr($b->berechtigung_kurzbz,'wawi'))
				continue 2;
			break;
			default: break;
		}

		$htmlstr .= "	<tr class='row_berechtigung' id='".$b->benutzerberechtigung_id."'>";
		$heute = strtotime(date('Y-m-d'));
		if ($b->ende!='' && strtotime($b->ende) < $heute)
		{
			$titel="ccc";
			$style = 'style="border-left: 10px solid tomato; border-right: 10px solid transparent; text-align: center; vertical-align: middle"';
			$data = 'rot';
		}
		elseif ($b->start!='' && strtotime($b->start) > $heute)
		{
			$titel="bbb";
			$style = 'style="border-left: 10px solid gold; border-right: 10px solid transparent; text-align: center; vertical-align: middle"';
			$data = 'gelb';
		}
		else
		{
			$titel="aaa";
			$style = 'style="border-left: 10px solid LightGreen; border-right: 10px solid transparent; text-align: center; vertical-align: middle"';
			$data = 'gruen';
		}
		// Auswahlcheckbox
		$htmlstr .= "		<td $style class='auswahlcheckboxen' name='td_$b->benutzerberechtigung_id' data-".$data."='".$data."'>";
		$htmlstr .= "			<span style='display: none'>".$titel."</span>";
		$htmlstr .= "			<input type='checkbox' class='auswahlcheckbox' name='dataset[$b->benutzerberechtigung_id][check]'>";
		$htmlstr .= "		</td>";

		//Rolle
		$htmlstr .= "		<td style='padding: 1px;'>";
		$htmlstr .= "			<select class='rolle_select' name='dataset[$b->benutzerberechtigung_id][rolle_kurzbz]'>";
		$htmlstr .= "		<option value='' name=''>&nbsp;</option>";
		for ($i = 0; $i < sizeof($rolle_arr); $i++)
		{
			if ($b->rolle_kurzbz == $rolle_arr[$i])
			{
				$sel = " selected";
			}
			else
				$sel = "";
			$htmlstr .= "<option value='".$rolle_arr[$i]."' id='".$rolle_arr[$i]."' ".$sel.">".$rolle_arr[$i]."</option>";
		}
		$htmlstr .= "		</select>";
		$htmlstr.="</td>";

		//Berechtigung
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'>";
		$htmlstr .= "			<span style='display: none'>".$b->berechtigung_kurzbz."</span>";
		$htmlstr .= "			<input type='text' 
										class='berechtigung_autocomplete' 
										name='dataset[$b->benutzerberechtigung_id][berechtigung_kurzbz]' 
										value='".$b->berechtigung_kurzbz."'
										title='".($b->berechtigung_kurzbz != '' ? $berechtigung_arr[$b->berechtigung_kurzbz] : '')."'>";
		$htmlstr .= "		</td>";

		//Art
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'>";
		$htmlstr .= "			<span style='display: none'>".$b->art."</span>";
		$htmlstr .= "			<input type='text' class='suid_input' name='dataset[$b->benutzerberechtigung_id][art]' value='".$b->art."' size='4' maxlength='4'>";
		$htmlstr .= "		</td>";

		//Organisationseinheit
		if($funktion_kurzbz != '')
		{
			$htmlstr .= "		<td class='oe_column' name='td_$b->benutzerberechtigung_id'>OE aus MA-Funktion</td>";
		}
		else
		{
			$htmlstr .= "		<td class='oe_column'>";
			$htmlstr .= "			<span style='display: none'>".($b->oe_kurzbz != '' ? $oe_arr[$b->oe_kurzbz] : '')."</span>";
			$htmlstr .= "			<input type='hidden' name='dataset[$b->benutzerberechtigung_id][oe_kurzbz]' value='$b->oe_kurzbz'>";
			$htmlstr .= "			<input type='text' class='oe_kurzbz_autocomplete' value='".($b->oe_kurzbz != '' ? $oe_arr[$b->oe_kurzbz] : '')."'>";
			$htmlstr .= "		</td>";
		}

		//Kostenstelle
		$htmlstr .= "		<td class='ks_column'>";
		$htmlstr .= "			<span style='display: none'>".$b->kostenstelle_id."</span>";
		$htmlstr .= "			<input type='hidden' name='dataset[$b->benutzerberechtigung_id][kostenstelle_id]' value='$b->kostenstelle_id'>";
		$htmlstr .= "			<input type='text' class='kostenstelle_autocomplete' value='".($b->kostenstelle_id != '' ? $kst_arr[$b->kostenstelle_id] : '')."'>";
		$htmlstr .= "		</td>";



		/*$htmlstr .= "		<select name='dataset[$b->benutzerberechtigung_id][kostenstelle_id]' style='width: 200px;'>";
		$htmlstr .= "		<option value=''>&nbsp;</option>";

		foreach ($kostenstelle->result as $kst)
		{
			if ($b->kostenstelle_id == $kst->kostenstelle_id)
				$sel = " selected";
			else
				$sel = "";
			if(!$kst->aktiv)
				$class='class="inactive"';
			else
				$class='';

			$htmlstr .= "	<option value='".$kst->kostenstelle_id."' ".$sel." ".$class.">".$kst->bezeichnung.'</option>';
		}
		$htmlstr .= "		</select></td>";*/

		//Negativ-Checkbox
		$htmlstr .= "		<td align='center'>";
		$htmlstr .= "		    <input type='checkbox' name='dataset[$b->benutzerberechtigung_id][negativ]' ".($b->negativ?'checked="checked"':'').">";
		$htmlstr .= "		</td>";

		//Gültig ab
		$htmlstr .= "		<td style='white-space: nowrap'>";
		$htmlstr .= "			<span style='display: none'>".$b->start."</span>";
		$htmlstr .= "		    <input class='datepicker_datum' type='text' name='dataset[$b->benutzerberechtigung_id][start]' value='".$b->start."' size='10' maxlength='10'>";
		$htmlstr .= "		</td>";

		// Gültig bis
		$htmlstr .= "		<td style='white-space: nowrap'>";
		$htmlstr .= "			<span style='display: none'>".$b->ende."</span>";
		$htmlstr .= "		    <input class='datepicker_datum' type='text' name='dataset[$b->benutzerberechtigung_id][ende]' value='".$b->ende."' size='10' maxlength='10'>";
		$htmlstr .= "		</td>";

		//Anmerkung
		$htmlstr .= "		<td>";
		$htmlstr .= "			<input type='text' name='dataset[$b->benutzerberechtigung_id][anmerkung]' value='".$b->anmerkung."' title='".$db->convert_html_chars(mb_eregi_replace('\r'," ",$b->anmerkung))."' size='50' maxlength='256'>";
		$htmlstr .= "		</td>";

		//Info
		$htmlstr .= "		<td align='center' name='td_$b->benutzerberechtigung_id'><img src='../../skin/images/information.png' alt='information' title='Angelegt von ".$b->insertvon." am ".$b->insertamum." Zuletzt geaendert von ".$b->updatevon." am ".$b->updateamum."'></td>";

		$htmlstr .= "		<td style='white-space: nowrap'>";
		$htmlstr .= "			<button type='submit' name='copy' value='$b->benutzerberechtigung_id' id='copy_$b->benutzerberechtigung_id' style='margin-right: 10px'>Duplizieren</button>";
		$htmlstr .= "			<button type='submit' name='delete' value='$b->benutzerberechtigung_id' id='delete_$b->benutzerberechtigung_id'>Löschen</button>";
		$htmlstr .= "		</td>";
		$htmlstr .= "	</tr>";
	}
	$htmlstr .= "</tbody></table>";
	$htmlstr .= '<div id="bottomArea">
					<input style="margin-bottom: 5px;" type="submit" name="schick" value="Speichern" onclick="return validateSpeichern()"><br>';
	$htmlstr .= '<input type="text" placeholder="Zu UID übertragen" class="benutzer_autocomplete" id="input_uebertragen_nach" name="uebertragen_nach" style="margin-left: 20px; margin-right: 0">';
	$htmlstr .= '<input type="submit" id="button_uebertragen" name="uebertragen" value="Zu UID übertragen" style="margin-left: 0" onclick="return validateUebertragen()">
					<input type="submit" id="button_mehrfachloeschen" name="delete_multi" value="Markierte löschen" style="margin-left: 20px; margin-right: 0" onclick="return validateDeleteMulti()">
				</div>';
	$htmlstr .= "</form>";

}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Berechtigung - Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script src="../../include/js/mailcheck.js"></script>
	<script src="../../include/js/datecheck.js"></script>
<!--	<script type="text/javascript" src="../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>-->
<!--	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>-->
<!--	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>-->
	<?php
	include('../../include/meta/jquery.php');
	include('../../include/meta/jquery-tablesorter.php');
	?>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js"></script>
	<style type="text/css">

	<?php if(1==2): ?>
		th.oe_column,
		td.oe_column,
		th.ks_column,
		td.ks_column
		{
			display: none;
		}
	<?php endif; ?>

		button
		{
            border: 1px solid gray;
		}
		input, select
		{
			margin: 0 4px;
			background-color: white;
		}
		input[type=submit]
		{
			padding: 1px 4px;
		}
		input[type=submit]:hover
		{
			background-color: #f3f3f3;
		}
		.berechtigung_autocomplete
		{
			width: 200px;
		}
		.oe_kurzbz_autocomplete
		{
			width: 250px;
		}
		th
		{
			text-align: left;
			padding-left: 5px;
			font-weight: normal;
		}
		#bottomArea
		{
			width: 500px;
			border-top-left-radius: 10px;
			border-top-right-radius: 10px;
			text-align: center; 
			position: sticky; 
			bottom: 0px; 
			padding: 10px;
			background-color: rgba(238,238,238,0.9);
			border-top: 1px solid #999;
            border-left: 1px solid #999;
            border-right: 1px solid #999;
			margin: auto;
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$( ".datepicker_datum" ).datepicker({
				 changeMonth: true,
				 changeYear: true,
				 dateFormat: 'yy-mm-dd',
				 showOn: "button",
			     buttonImage: "../../skin/images/date_edit.png",
			     buttonImageOnly: true,
			     buttonText: "Select date"
				 });

			$("#t1").tablesorter(
				{
					sortList: [[0,0],[1,0],[2,0],[4,0]],
					widgets: ["filter", "stickyHeaders"],
					headers: {6:{sorter:false, filter:false},10:{sorter:false, filter:false},11:{sorter:false, filter:false}},
					widgetOptions : {	filter_functions : {
							// Add select menu to this column
							0 : {
								"Grün" : function(e, n, f, i, $r, c, data) { return /a/.test(e); },
								"Gelb" : function(e, n, f, i, $r, c, data) { return /b/.test(e); },
								"Rot" : function(e, n, f, i, $r, c, data) { return /c/.test(e); }
							}
						}},
					// Um die Werte im Dropdown sortieren zu können
					textExtraction: function(node) {
						// Check if option selected is set
						if ($(node).find('option:selected').text() != "") {
							return $(node).find('option:selected').text();
						}
						// Otherwise return text
						else return $(node).text();
					}
				});
			//document.berechtigung_neu.rolle_kurzbz.focus();

			// Breite des Autocompletes korrigieren um das Springen zu verhindern
			$.extend($.ui.autocomplete.prototype.options, {
				open: function(event, ui) {
					$(this).autocomplete("widget").css({
						"width": ($(".ui-menu-item").width()+ 20 + "px"),
						"padding-left": "5px"
					});
				}
			});

			$(".berechtigung_autocomplete").autocomplete({
				source: "benutzerberechtigung_autocomplete.php?autocomplete=berechtigung",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].berechtigung_kurzbz;
						ui.content[i].label=ui.content[i].berechtigung_kurzbz+" - "+ui.content[i].beschreibung;
					}
				},
				select: function(event, ui)
				{
					//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
					$(this).val(ui.item.berechtigung_kurzbz);
				}
			});

			$(".oe_kurzbz_autocomplete").autocomplete({
				source: "benutzerberechtigung_autocomplete.php?autocomplete=oe_kurzbz",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].organisationseinheittyp_kurzbz+" "+ui.content[i].bezeichnung;
						ui.content[i].label=ui.content[i].organisationseinheittyp_kurzbz+" "+ui.content[i].bezeichnung;
					}
				},
				select: function(event, ui)
				{
					$(this).siblings('input:hidden').val(ui.item.oe_kurzbz);
				}
			});
			$(".oe_kurzbz_autocomplete").on( "input", function() {
				if ($(this).val() == '')
				{
					$(this).siblings('input:hidden').val('');
				}
			});

			$(".benutzer_autocomplete").autocomplete({
				source: "benutzerberechtigung_autocomplete.php?autocomplete=benutzer",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].uid;
						ui.content[i].label=ui.content[i].nachname+" "+ui.content[i].vorname;
					}
				},
				select: function(event, ui)
				{
					$(this).val(ui.item.uid);
				},
				position: { my : "left bottom", at: "left top" }
			});

			$(".kostenstelle_autocomplete").autocomplete({
				source: "benutzerberechtigung_autocomplete.php?autocomplete=kostenstelle",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].bezeichnung;
						ui.content[i].label=ui.content[i].bezeichnung;
					}
				},
				select: function(event, ui)
				{
					$(this).siblings('input:hidden').val(ui.item.kostenstelle_id);
				}
			});
			$(".kostenstelle_autocomplete").on( "input", function() {
				if ($(this).val() == '')
				{
					$(this).siblings('input:hidden').val('');
				}
			});

			$("#toggle_t1").on('click', function(e) {
				$(".auswahlcheckboxen").checkboxes('toggle');
				e.preventDefault();
			});

			$("#uncheck_t1").on('click', function(e) {
				$(".auswahlcheckboxen").checkboxes('uncheck');
				e.preventDefault();
			});

			var aktiv = $('td.auswahlcheckboxen[data-gruen]').length + $('td.auswahlcheckboxen[data-gelb]').length;
			var inaktiv = $('td.auswahlcheckboxen[data-rot]').length;

			$("#anzahl").html(aktiv + inaktiv + " Einträge (" + aktiv + " Aktive, " + inaktiv + " Inaktive)");

			/*$('input.auswahlcheckbox').each(function ()
			{
				$("#t1").checkboxes('range', true);
			});*/
			//if (typeof $("#filterTableDataset").checkboxes === 'function')
			//	$("#filterTableDataset").checkboxes("range", true);
			//$("input.auswahlcheckbox").checkboxes('range', true); //Wählt ALLE Checkboxen. Funktioniert nicht mit anderem selector
		});

		function validateUebertragen()
		{
			if($('input.auswahlcheckbox:checked').length == 0)
			{
				alert('Bitte mindestens eine Berechtigung auswählen');
				return false;
			}
			else if($('#input_uebertragen_nach').val() == '')
			{
				alert('Bitte eine UID angeben');
				return false;
			}
			else
				return true;
		}

		function validateDeleteMulti()
		{
			if($('input.auswahlcheckbox:checked').length == 0)
			{
				alert('Bitte mindestens einen Eintrag auswählen');
				return false;
			}
			else
			{
				if(confirm("Markierte Einträge löschen?"))
					return true;
				return false;
			}
		}

		function validateNewData()
		{
			if($('#rolle_kurzbz_neu').val() != '' && $('#berechtigung_neu_autocomplete').val() != '')
			{
				alert('Rolle und Berechtigung darf nicht gleichzeitig gesetzt sein')
				return false;
			}
			else if($('#rolle_kurzbz_neu').val() == '' && $('#berechtigung_neu_autocomplete').val() == '')
			{
				alert('Es muss entweder eine Rolle oder ein Recht ausgewählt sein')
				return false;
			}
			else if ($('#art_neu').val() == '')
			{
				alert('Art darf nicht leer sein')
				return false;
			}
			else if ($('#art_neu').val() != '')
			{
				var eingabe, c, erlaubt = 'suid', laenge;
				eingabe = $('#art_neu').val();
				eingabe = eingabe.toLowerCase();
				laenge = eingabe.length;
				for (c = 0; c < laenge; c++)
				{
					d = eingabe.charAt(c);
					if (erlaubt.indexOf(d) == -1)
					{
						alert ('Erlaubte Werte für Art sind s,u,i,d');
						return false;
					}
				}
			}
			else
				return true;
		}

		function validateSpeichern()
		{
			var ok = true;
			$('#t1 input.berechtigung_autocomplete').each(function ()
			{
				var select = $(this).parent().parent().find('.rolle_select').find(":selected").text().trim();
				var input = $(this).val().trim();

				if(input != '' && select != '')
				{
					alert('Rolle und Berechtigung dürfen nicht gleichzeitig gesetzt sein');
					ok = false;
				}
				else if(input == '' && select == '')
				{
					alert('Rolle und Berechtigung dürfen nicht beide leer sein');
					ok = false;
				}
			});

			if (ok == true)
			{
				return true;
			}
			else
				return false;
		}

		function confdel()
		{
			if(confirm("Diesen Datensatz wirklich löschen?"))
			  return true;
			return false;
		}

		function markier(id)
		{
			for (var i = 0; i < document.getElementsByName(id).length; i++)
			{
				document.getElementsByName(id)[i].style.background = "#FC988D";
			}
		}

		function unmarkier(id)
		{
			document.getElementById(id).style.background = "#eeeeee";
		}

		function checkdate(feld)
		{
			if ((feld.value != "") && (!dateCheck(feld)))
			{
				//document.studiengangform.schick.disabled = true;
				feld.className = "input_error";
				return false;
			}
			else
			{
				if(feld.value != "")
					feld.value = dateCheck(feld);

				feld.className = "input_ok";
				return true;
			}
		}

		function checkrequired(id)
		{
			if(document.getElementById(id).value == "")
			{
				document.getElementById(id).style.border = "solid red 2px";
				return false;
			}
			else
			{
				document.getElementById(id).style.border = "";
				return true;
			}
		}
		function setnull(id)
		{
			document.getElementById(id).selectedIndex=0;
		}
		function disable(id)
		{
			document.getElementById(id).disabled = true;
			//document.getElementById("art_"+id).value="";
		}
		function enable(id)
		{
			document.getElementById(id).disabled = false;
		}
		function validateArt(id)
		{
			var eingabe, c, erlaubt = 'suid', laenge;
			eingabe = document.getElementById(id).value;;
			eingabe = eingabe.toLowerCase();
			laenge = eingabe.length;
			if (eingabe == '')
			{
				alert('Geben Sie bitte einen Wert bei "Art" ein!');
				return false;
			}
			for (c = 0; c < laenge; c++)
			{
				d = eingabe.charAt(c);
				if (erlaubt.indexOf(d) == -1)
				{
					alert ('Erlaubte Werte sind s,u,i,d');
					document.getElementById(id).style.border = "solid red 2px";
					return false;
				}
				else
					document.getElementById(id).style.border = "";
			}
		}
	</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	//echo $reloadstr; Auskommentiert weils nervt
?>

</body>
</html>
