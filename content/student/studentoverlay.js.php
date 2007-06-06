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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
?>
// *********** Globale Variablen *****************//
var StudentSelectUid=null; //Student der nach dem Refresh markiert werden soll
var StudentKontoSelectBuchung=null; //Buchung die nach dem Refresh markiert werden soll
var StudentKontoTreeDatasource; //Datasource des KontoTrees
var StudentTreeLoadDataOnSelect=true; //Gib an ob beim Selectieren im Tree die Daten geladen werden sollen
var StudentBetriebsmittelTreeDatasource; //Datasource des BetriebsmittelTrees
var StudentBetriebsmittelSelectBetriebsmittel_id=null; //Betriebsmittelzurodnung die nach dem Refresh markiert werden soll
var StudentBetriebsmittelSelectPerson_id=null; //Betriebsmittelzurodnung die nach dem Refresh markiert werden soll
var StudentIOTreeDatasource; //Datasource des Incomming/Outgoing Trees
var StudentIOSelectID=null; //BISIO Eintrag der nach dem Refresh markiert werden soll
var StudentNotenTreeDatasource; //Datasource des Noten Trees
var StudentNotenSelectLehreinheitID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll
var StudentLvGesamtNotenTreeDatasource; //Datasource des Noten Trees
var StudentLvGesamtNotenSelectLehreinheitID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll

// ********** Observer und Listener ************* //

// ****
// * Observer fuer Studenten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Student wieder
// * markiert
// ****
var StudentTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentTreeSelectStudent,10);
  }
};

// ****
// * Observer fuer Konto Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentKontoTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-konto-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Buchung wieder
// * markiert
// ****
var StudentKontoTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentKontoTreeSelectBuchung,10);
  }
};


// ****
// * Observer fuer Betriebsmittel Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentBetriebsmittelTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-betriebsmittel-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Betriebsmittelzuordnung wieder
// * markiert
// ****
var StudentBetriebsmittelTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentBetriebsmittelTreeSelectZuordnung,10);
  }
};


// ****
// * Observer fuer BISIO Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentIOTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-io-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentIOTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentIOTreeSelectID,10);
  }
};


// ****
// * Observer fuer Noten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentNotenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-noten-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentNotenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentNotenTreeSelectID,10);
  }
};

// ****
// * Observer fuer LvGesamtNoten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentLvGesamtNotenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-lvgesamtnoten-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentLvGesamtNotenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentLvGesamtNotenTreeSelectID,10);
  }
};

// ***************** KEY Events ************************* //

// ****
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im Lehrveranstaltungs-tree ist
// * Beim Druecken von ENTF wird der markierte Student geloescht
// * Beim Druecken von F5 wird der Studententree aktualisiert
// ****
function StudentTreeKeyPress(event)
{
	if(event.keyCode==46) // Entf
		StudentDelete();
	else if(event.keyCode==116) // F5
		StudentTreeRefresh();
}

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des StudentenTrees
// ****
function StudentTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierten Studenten global speichern damit dieser Student nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	try
	{
		StudentSelectUid=tree.view.getCellText(tree.currentIndex,col);
	}
	catch(e)
	{
		StudentSelectUid=null;
	}
	StudentTreeDatasource.Refresh(false); //non blocking
}

// ****
// * Selectiert den Studenten nachdem der Tree
// * rebuildet wurde.
// ****
function StudentTreeSelectStudent()
{
	var tree=document.getElementById('student-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Student gespeichert
	if(StudentSelectUid!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			uid=tree.view.getCellText(i,col);
						
			if(uid == StudentSelectUid)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
	document.getElementById('student-toolbar-label-anzahl').value='Anzahl: '+items;
}

// ****
// * Beim Sortieren des Trees wird der markierte Eintrag gespeichert und nach dem sortieren
// * wieder markiert. 
// ****
function StudentTreeSort()
{
	var i;
	var tree=document.getElementById('student-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	StudentSelectUid = tree.view.getCellText(i,col);
	StudentTreeLoadDataOnSelect=false;
	window.setTimeout("StudentTreeSelectStudent()",10);
}

// ****
// * Selectiert die Buchung nachdem der Tree
// * rebuildet wurde.
// ****
function StudentKontoTreeSelectBuchung()
{
	var tree=document.getElementById('student-konto-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Buchung gespeichert
	if(StudentKontoSelectBuchung!=null)
	{
		//Alle subtrees oeffnen weil rowCount nur die Anzahl der sichtbaren
		//Zeilen zurueckliefert
	   	for(var i=items-1;i>=0;i--)
	   	{
	   		if(!tree.view.isContainerOpen(i))
	   			tree.view.toggleOpenState(i);
	   	}

	   	//Jetzt die wirkliche Anzahl (aller) Zeilen holen
	   	items = tree.view.rowCount;
	   	for(var i=0;i<items;i++)
	   	{
	   		//buchungsnr der row holen
			col = tree.columns ? tree.columns["student-konto-tree-buchungsnr"] : "student-konto-tree-buchungsnr";
			buchungsnr=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(buchungsnr == StudentKontoSelectBuchung)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentKontoSelectBuchung=null;
				return true;
			}
	   	}
	}
}

// ****
// * Student loeschen
// ****
function StudentDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlte UID holen
        var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
		var uid=tree.view.getCellText(tree.currentIndex,col);
		if(uid=='')
			return false
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie diesen Studenten wirklich l�schen?'))
	{
		//Script zum loeschen der Lehreinheit aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','student');
		req.add('do','delete');
		req.add('uid',uid);
		var response = req.executePOST();

		var val =  new ParseReturnValue(response)
		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		StudentTreeRefresh();
		StudentDetailReset();
	}
}

// ****
// * Loescht einen Studenten aus einer Spezialgruppe
// ****
function StudentGruppeDel()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');
	var tree_vb = document.getElementById('tree-verband');

	if (tree.currentIndex==-1)
		return;
	
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			uid = ';'+tree.view.getCellText(v,col);
			uids += uid;
			anzahl++;
		}
	}
	
	try
	{
		//Ausgewaehlte Gruppe holen
        var col = tree_vb.columns ? tree_vb.columns["gruppe"] : "gruppe";
		var gruppe_kurzbz=tree_vb.view.getCellText(tree_vb.currentIndex,col);
		if(gruppe_kurzbz=='')
		{
			alert('Studenten koennen nur aus Spezialgruppen entfernt werden');
			return false
		}
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm(anzahl+' Student(en) wirklich aus Gruppe '+gruppe_kurzbz+' entfernen?'))
	{
		//Script zum loeschen aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','deleteGruppenzuteilung');
		req.add('uid',uids);
		req.add('gruppe_kurzbz', gruppe_kurzbz);
		
		var response = req.executePOST();

		var val =  new ParseReturnValue(response)
		
		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		StudentTreeRefresh();
		StudentDetailReset();
	}
}

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function StudentDetailReset()
{	
	document.getElementById('student-detail-textbox-uid').value='';
	document.getElementById('student-detail-textbox-anrede').value='';
	document.getElementById('student-detail-textbox-titelpre').value='';
	document.getElementById('student-detail-textbox-titelpost').value='';
	document.getElementById('student-detail-textbox-vorname').value='';
	document.getElementById('student-detail-textbox-vornamen').value='';
	document.getElementById('student-detail-textbox-nachname').value='';
	document.getElementById('student-detail-textbox-geburtsdatum').value='';
	document.getElementById('student-detail-textbox-geburtsort').value='';
	document.getElementById('student-detail-textbox-geburtszeit').value='';
	document.getElementById('student-detail-textbox-anmerkung').value='';
	document.getElementById('student-detail-textbox-homepage').value='';
	document.getElementById('student-detail-textbox-svnr').value='';
	document.getElementById('student-detail-textbox-ersatzkennzeichen').value='';
	document.getElementById('student-detail-menulist-familienstand').value='l';
	document.getElementById('student-detail-menulist-geschlecht').value='m';
	document.getElementById('student-detail-checkbox-aktiv').checked=true;
	document.getElementById('student-detail-textbox-anzahlderkinder').value='';
	document.getElementById('student-detail-menulist-staatsbuergerschaft').value='A';
	document.getElementById('student-detail-menulist-geburtsnation').value='A';
	document.getElementById('student-detail-menulist-sprache').value='German';
	document.getElementById('student-detail-textbox-matrikelnummer').value='';
	document.getElementById('student-detail-image').src='';
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function StudentDetailDisableFields(val)
{
	//document.getElementById('student-detail-textbox-uid').disabled=val;
	document.getElementById('student-detail-textbox-anrede').disabled=val;
	document.getElementById('student-detail-textbox-titelpre').disabled=val;
	document.getElementById('student-detail-textbox-titelpost').disabled=val;
	document.getElementById('student-detail-textbox-vorname').disabled=val;
	document.getElementById('student-detail-textbox-vornamen').disabled=val;
	document.getElementById('student-detail-textbox-nachname').disabled=val;
	document.getElementById('student-detail-textbox-geburtsdatum').disabled=val;
	document.getElementById('student-detail-textbox-geburtsort').disabled=val;
	document.getElementById('student-detail-textbox-geburtszeit').disabled=val;
	document.getElementById('student-detail-textbox-anmerkung').disabled=val;
	document.getElementById('student-detail-textbox-homepage').disabled=val;
	document.getElementById('student-detail-textbox-svnr').disabled=val;
	document.getElementById('student-detail-textbox-ersatzkennzeichen').disabled=val;
	document.getElementById('student-detail-menulist-familienstand').disabled=val;
	document.getElementById('student-detail-menulist-geschlecht').disabled=val;
	document.getElementById('student-detail-checkbox-aktiv').disabled=val;
	document.getElementById('student-detail-textbox-anzahlderkinder').disabled=val;
	document.getElementById('student-detail-menulist-staatsbuergerschaft').disabled=val;
	document.getElementById('student-detail-menulist-geburtsnation').disabled=val;
	document.getElementById('student-detail-menulist-sprache').disabled=val;
	document.getElementById('student-detail-textbox-matrikelnummer').disabled=val;
	document.getElementById('student-detail-button-image-upload').disabled=val;
	document.getElementById('student-detail-menulist-studiengang_kz').disabled=val;
	document.getElementById('student-detail-textbox-semester').disabled=val;
	document.getElementById('student-detail-textbox-verband').disabled=val;
	document.getElementById('student-detail-textbox-gruppe').disabled=val;
}

// ****
// * Speichert die Details
// ****
function StudentDetailSave()
{
	//Werte holen
	uid = document.getElementById('student-detail-textbox-uid').value;
	anrede = document.getElementById('student-detail-textbox-anrede').value;
	titelpre = document.getElementById('student-detail-textbox-titelpre').value;
	titelpost = document.getElementById('student-detail-textbox-titelpost').value;
	vorname = document.getElementById('student-detail-textbox-vorname').value;
	vornamen = document.getElementById('student-detail-textbox-vornamen').value;
	nachname = document.getElementById('student-detail-textbox-nachname').value;
	geburtsdatum = document.getElementById('student-detail-textbox-geburtsdatum').value;
	geburtsort = document.getElementById('student-detail-textbox-geburtsort').value;
	geburtszeit = document.getElementById('student-detail-textbox-geburtszeit').value;
	anmerkung = document.getElementById('student-detail-textbox-anmerkung').value;
	homepage = document.getElementById('student-detail-textbox-homepage').value;
	svnr = document.getElementById('student-detail-textbox-svnr').value;
	ersatzkennzeichen = document.getElementById('student-detail-textbox-ersatzkennzeichen').value;
	familienstand = document.getElementById('student-detail-menulist-familienstand').value;
	geschlecht = document.getElementById('student-detail-menulist-geschlecht').value;
	aktiv = document.getElementById('student-detail-checkbox-aktiv').checked;
	anzahlderkinder = document.getElementById('student-detail-textbox-anzahlderkinder').value;
	staatsbuergerschaft = document.getElementById('student-detail-menulist-staatsbuergerschaft').value;
	geburtsnation = document.getElementById('student-detail-menulist-geburtsnation').value;
	sprache = document.getElementById('student-detail-menulist-sprache').value;
	matrikelnummer = document.getElementById('student-detail-textbox-matrikelnummer').value;
	studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
	semester = document.getElementById('student-detail-textbox-semester').value;
	verband = document.getElementById('student-detail-textbox-verband').value;
	gruppe = document.getElementById('student-detail-textbox-gruppe').value;
		
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	neu = document.getElementById('student-detail-checkbox-new').checked;

	if (neu)
	{
		req.add('do','create');
	}
	else
	{
		req.add('do','update');
	}
	
	req.add('type', 'savestudent');
	
	req.add('uid', uid);
	req.add('anrede', anrede);
	req.add('titelpre', titelpre);
	req.add('titelpost', titelpost);
	req.add('vorname', vorname);
	req.add('vornamen', vornamen);
	req.add('nachname', nachname);
	req.add('geburtsdatum', geburtsdatum);
	req.add('geburtsort', geburtsort);
	req.add('geburtszeit', geburtszeit);
	req.add('anmerkung', anmerkung);
	req.add('homepage', homepage);
	req.add('svnr', svnr);
	req.add('ersatzkennzeichen', ersatzkennzeichen);
	req.add('familienstand', familienstand);
	req.add('geschlecht', geschlecht);
	req.add('aktiv', aktiv);
	req.add('anzahlderkinder', anzahlderkinder);
	req.add('staatsbuergerschaft', staatsbuergerschaft);
	req.add('geburtsnation', geburtsnation);
	req.add('sprache', sprache);
	req.add('matrikelnummer', matrikelnummer);
	req.add('studiengang_kz', studiengang_kz);
	req.add('semester', semester);
	req.add('verband', verband);
	req.add('gruppe', gruppe);
	
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-detail-checkbox-new').checked=false;
		
		StudentSelectUid=val.dbdml_data;
		StudentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

function StudentImageUpload()
{
	person_id = document.getElementById('student-detail-textbox-person_id').value;
	if(person_id!='')
	{
		window.open('<?php echo APP_ROOT; ?>content/bildupload.php?person_id='+person_id,'Bild Upload', 'height=10,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	}
	else
		alert('Es wurde keine Person ausgewaehlt');
}

// ****
// * Auswahl eines Studenten
// * bei Auswahl eines Studenten wird dieser geladen
// * und die Daten unten angezeigt
// ****
function StudentAuswahl()
{
	if(!StudentTreeLoadDataOnSelect)
	{
		StudentTreeLoadDataOnSelect=true;
		return true;
	}
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;
	
	try
	{
		//Ausgewaehlte UID holen
        var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
		var uid=tree.view.getCellText(tree.currentIndex,col);
		if(uid!='')
		{
			//Student wurde markiert
			//loeschen button aktivieren
			StudentDetailDisableFields(false);
			StudentPrestudentDisableFields(false);
			StudentKontoDisableFields(false);
			StudentBetriebsmittelDisableFields(false);
			StudentIODisableFields(false);
			document.getElementById('student-detail-button-save').disabled=false;
		}
		else
		{
			return false;
		}	
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	var url = '<?php echo APP_ROOT ?>rdf/student.rdf.php?uid='+uid+'&stsem=true&'+gettimestamp();
	
	//hier wird GetDataSourceBlocking verwendet da sich
	//bei der Methode mit phpRequest der Mozilla aufhaengt
	//var req = new phpRequest(url,'','');
	//req.add('uid',uid);

	//var response = req.execute();
	
	// Datasource holen
	//var dsource=parseRDFString(response, 'http://www.technikum-wien.at/student/alle');

	//dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
    
    var dsource = rdfService.GetDataSourceBlocking(url);
    
	var subject = rdfService.GetResource("http://www.technikum-wien.at/student/" + uid);

	var predicateNS = "http://www.technikum-wien.at/student/rdf";

	//Daten holen

	anrede = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anrede" ));
	titelpre=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titelpre" ));
	titelpost=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titelpost" ));
	vorname=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vorname" ));
	vornamen=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vornamen" ));
	nachname=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nachname" ));
	geburtsdatum=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsdatum" ));
	geburtsort=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gebort" ));
	geburtszeit=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gebzeit" ));
	anmerkung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkungen" ));
	homepage=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#homepage" ));
	svnr=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#svnr" ));
	ersatzkennzeichen=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ersatzkennzeichen" ));
	familienstand=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#familienstand" ));
	geschlecht=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geschlecht" ));
	aktiv=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aktiv" ));	
	anzahlderkinder=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anzahlkinder" ));
	staatsbuergerschaft=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#staatsbuergerschaft" ));
	geburtsnation=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsnation" ));
	sprache=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sprache" ));
	matrikelnummer=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#matrikelnummer" ));
	person_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	studiengang_kz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	semester=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#semester" ));
	verband=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verband" ));
	gruppe=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gruppe" ));
	prestudent_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#prestudent_id" ));
	
	//Daten den Feldern zuweisen

	document.getElementById('student-detail-textbox-uid').value=uid;
	document.getElementById('student-detail-textbox-anrede').value=anrede;
	document.getElementById('student-detail-textbox-titelpre').value=titelpre;
	document.getElementById('student-detail-textbox-titelpost').value=titelpost;
	document.getElementById('student-detail-textbox-vorname').value=vorname;
	document.getElementById('student-detail-textbox-vornamen').value=vornamen;
	document.getElementById('student-detail-textbox-nachname').value=nachname;
	document.getElementById('student-detail-textbox-geburtsdatum').value=geburtsdatum;
	document.getElementById('student-detail-textbox-geburtsort').value=geburtsort;
	document.getElementById('student-detail-textbox-geburtszeit').value=geburtszeit;
	document.getElementById('student-detail-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-detail-textbox-homepage').value=homepage;
	document.getElementById('student-detail-textbox-svnr').value=svnr;
	document.getElementById('student-detail-textbox-ersatzkennzeichen').value=ersatzkennzeichen;
	document.getElementById('student-detail-menulist-familienstand').value=familienstand;
	document.getElementById('student-detail-menulist-geschlecht').value=geschlecht;
	if(aktiv=='true')
		document.getElementById('student-detail-checkbox-aktiv').checked=true;
	else
		document.getElementById('student-detail-checkbox-aktiv').checked=false;
	document.getElementById('student-detail-textbox-anzahlderkinder').value=anzahlderkinder;
	document.getElementById('student-detail-menulist-staatsbuergerschaft').value=staatsbuergerschaft;
	document.getElementById('student-detail-menulist-geburtsnation').value=geburtsnation;
	document.getElementById('student-detail-menulist-sprache').value=sprache;
	document.getElementById('student-detail-textbox-matrikelnummer').value=matrikelnummer;
	document.getElementById('student-detail-image').src='<?php echo APP_ROOT?>content/bild.php?src=person&person_id='+person_id+'&'+gettimestamp();
	document.getElementById('student-detail-textbox-person_id').value=person_id;
	document.getElementById('student-detail-menulist-studiengang_kz').value=studiengang_kz;
	document.getElementById('student-detail-textbox-semester').value=semester;
	document.getElementById('student-detail-textbox-verband').value=verband;
	document.getElementById('student-detail-textbox-gruppe').value=gruppe;
	
	//PreStudent Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/prestudent.rdf.php?prestudent_id='+prestudent_id+'&'+gettimestamp();
		
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
    
    var dsource = rdfService.GetDataSourceBlocking(url);
    
	var subject = rdfService.GetResource("http://www.technikum-wien.at/prestudent/" + prestudent_id);

	var predicateNS = "http://www.technikum-wien.at/prestudent/rdf";

	//Daten holen

	aufmerksamdurch_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufmerksamdurch_kurzbz" ));
	studiengang_kz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	berufstaetigkeit_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#berufstaetigkeit_code" ));
	ausbildungcode = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausbildungcode" ));
	zgv_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgv_code" ));
	zgvort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvort" ));
	zgvdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvdatum" ));
	zgvmaster_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmas_code" ));
	zgvmasterort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmaort" ));
	zgvmasterdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmadatum" ));
	aufnahmeschluessel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufnahmeschluessel" ));
	facheinschlberuf = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#facheinschlberuf" ));
	reihungstest_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstest_id" ));
	anmeldungreihungstest = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmeldungreihungstest" ));
	reihungstestangetreten = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstestangetreten" ));
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	bismelden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bismelden" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	
	document.getElementById('student-prestudent-menulist-aufmerksamdurch').value=aufmerksamdurch_kurzbz;
	document.getElementById('student-prestudent-menulist-berufstaetigkeit').value=berufstaetigkeit_code;
	document.getElementById('student-prestudent-menulist-ausbildung').value=ausbildungcode;
	document.getElementById('student-prestudent-menulist-zgvcode').value=zgv_code;
	document.getElementById('student-prestudent-textbox-zgvort').value=zgvort;
	document.getElementById('student-prestudent-textbox-zgvdatum').value=zgvdatum;
	document.getElementById('student-prestudent-menulist-zgvmastercode').value=zgvmaster_code;
	document.getElementById('student-prestudent-textbox-zgvmasterort').value=zgvmasterort;
	document.getElementById('student-prestudent-textbox-zgvmasterdatum').value=zgvmasterdatum;
	document.getElementById('student-prestudent-menulist-aufnahmeschluessel').value=aufnahmeschluessel;
	if(facheinschlberuf=='true')
		document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked=false;
	document.getElementById('student-prestudent-menulist-reihungstest').value=reihungstest_id;
	document.getElementById('student-prestudent-textbox-anmeldungreihungstest').value=anmeldungreihungstest;
	if(reihungstestangetreten=='true')
		document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked=false;
	document.getElementById('student-prestudent-textbox-punkte').value=punkte;
	
	if(bismelden=='true')
		document.getElementById('student-prestudent-checkbox-bismelden').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-bismelden').checked=false;
		
	document.getElementById('student-prestudent-textbox-person_id').value=person_id;
	document.getElementById('student-prestudent-textbox-prestudent_id').value=prestudent_id;
	document.getElementById('student-prestudent-checkbox-new').checked=false;
	document.getElementById('student-prestudent-menulist-studiengang_kz').value=studiengang_kz;
	document.getElementById('student-prestudent-textbox-anmerkung').value=anmerkung;
	
	
	rollentree = document.getElementById('student-prestudent-tree-rolle');
	url='<?php echo APP_ROOT;?>rdf/prestudentrolle.rdf.php?prestudent_id='+prestudent_id+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = rollentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		rollentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	rollentree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	rollentree.database.AddDataSource(datasource);

	// *** Konto ***
	kontotree = document.getElementById('student-konto-tree');
	filter = document.getElementById('student-konto-button-filter').value;
	url='<?php echo APP_ROOT;?>rdf/konto.rdf.php?person_id='+person_id+"&filter="+filter+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = kontotree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		kontotree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	kontotree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentKontoTreeDatasource = rdfService.GetDataSource(url);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	kontotree.database.AddDataSource(StudentKontoTreeDatasource);
	StudentKontoTreeDatasource.addXMLSinkObserver(StudentKontoTreeSinkObserver);
	kontotree.builder.addListener(StudentKontoTreeListener);	
	
	// *** Zeugnis ***
	zeugnistree = document.getElementById('student-zeugnis-tree');
	url='<?php echo APP_ROOT;?>rdf/akte.rdf.php?person_id='+person_id+"&dokument_kurzbz=Zeugnis&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = zeugnistree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		zeugnistree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	zeugnistree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	zeugnistree.database.AddDataSource(datasource);
	
	// *** Betriebsmittel ***
	betriebsmitteltree = document.getElementById('student-betriebsmittel-tree');
	url='<?php echo APP_ROOT;?>rdf/betriebsmittelperson.rdf.php?person_id='+person_id+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = betriebsmitteltree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		betriebsmitteltree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	betriebsmitteltree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentBetriebsmittelTreeDatasource = rdfService.GetDataSource(url);
	StudentBetriebsmittelTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentBetriebsmittelTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	betriebsmitteltree.database.AddDataSource(StudentBetriebsmittelTreeDatasource);
	StudentBetriebsmittelTreeDatasource.addXMLSinkObserver(StudentBetriebsmittelTreeSinkObserver);
	betriebsmitteltree.builder.addListener(StudentBetriebsmittelTreeListener);	
	
	// *** Incomming/Outgoing ***
	bisiotree = document.getElementById('student-io-tree');
	
	url='<?php echo APP_ROOT;?>rdf/bisio.rdf.php?uid='+uid+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = bisiotree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		bisiotree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	bisiotree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentIOTreeDatasource = rdfService.GetDataSource(url);
	StudentIOTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentIOTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	bisiotree.database.AddDataSource(StudentIOTreeDatasource);
	StudentIOTreeDatasource.addXMLSinkObserver(StudentIOTreeSinkObserver);
	bisiotree.builder.addListener(StudentIOTreeListener);	
	
	// *** Noten ***
	notentree = document.getElementById('student-noten-tree');
	
	url='<?php echo APP_ROOT;?>rdf/zeugnisnote.rdf.php?uid='+uid+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = notentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		notentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	notentree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentNotenTreeDatasource = rdfService.GetDataSource(url);
	StudentNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	notentree.database.AddDataSource(StudentNotenTreeDatasource);
	StudentNotenTreeDatasource.addXMLSinkObserver(StudentNotenTreeSinkObserver);
	notentree.builder.addListener(StudentNotenTreeListener);	
	
	// *** Noten ***
	lvgesamtnotentree = document.getElementById('student-lvgesamtnoten-tree');
	
	url='<?php echo APP_ROOT;?>rdf/lvgesamtnote.rdf.php?uid='+uid+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = lvgesamtnotentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		lvgesamtnotentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lvgesamtnotentree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentLvGesamtNotenTreeDatasource = rdfService.GetDataSource(url);
	StudentLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	lvgesamtnotentree.database.AddDataSource(StudentLvGesamtNotenTreeDatasource);
	StudentLvGesamtNotenTreeDatasource.addXMLSinkObserver(StudentLvGesamtNotenTreeSinkObserver);
	lvgesamtnotentree.builder.addListener(StudentLvGesamtNotenTreeListener);	
}

// ****
// * De-/Aktiviert die Prestudent Felder
// ****
function StudentPrestudentDisableFields(val)
{
	document.getElementById('student-prestudent-menulist-aufmerksamdurch').disabled=val;
	document.getElementById('student-prestudent-menulist-berufstaetigkeit').disabled=val;
	document.getElementById('student-prestudent-menulist-ausbildung').disabled=val;
	document.getElementById('student-prestudent-menulist-zgvcode').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvort').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvdatum').disabled=val;
	document.getElementById('student-prestudent-menulist-zgvmastercode').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvmasterort').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvmasterdatum').disabled=val;
	document.getElementById('student-prestudent-menulist-aufnahmeschluessel').disabled=val;
	document.getElementById('student-prestudent-checkbox-facheinschlberuf').disabled=val;
	document.getElementById('student-prestudent-menulist-reihungstest').disabled=val;
	document.getElementById('student-prestudent-textbox-anmeldungreihungstest').disabled=val;
	document.getElementById('student-prestudent-checkbox-reihungstestangetreten').disabled=val;
	document.getElementById('student-prestudent-textbox-punkte').disabled=val;
	document.getElementById('student-prestudent-checkbox-bismelden').disabled=val;
	document.getElementById('student-prestudent-button-anmeldungreihungstest-heute').disabled=val;
	document.getElementById('student-prestudent-button-save').disabled=val;
	document.getElementById('student-prestudent-menulist-studiengang_kz').disabled=val;
	document.getElementById('student-prestudent-textbox-anmerkung').disabled=val;
}

// ****
// * Speichert die Prestudent Daten
// ****
function StudentPrestudentSave()
{
	aufmerksamdurch_kurzbz = document.getElementById('student-prestudent-menulist-aufmerksamdurch').value;
	berufstaetigkeit_code = document.getElementById('student-prestudent-menulist-berufstaetigkeit').value;
	ausbildungcode = document.getElementById('student-prestudent-menulist-ausbildung').value;
	zgv_code = document.getElementById('student-prestudent-menulist-zgvcode').value;
	zgvort = document.getElementById('student-prestudent-textbox-zgvort').value;
	zgvdatum = document.getElementById('student-prestudent-textbox-zgvdatum').value;
	zgvmaster_code = document.getElementById('student-prestudent-menulist-zgvmastercode').value;
	zgvmasterort = document.getElementById('student-prestudent-textbox-zgvmasterort').value;
	zgvmasterdatum = document.getElementById('student-prestudent-textbox-zgvmasterdatum').value;
	aufnahmeschluessel = document.getElementById('student-prestudent-menulist-aufnahmeschluessel').value;
	facheinschlberuf = document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked;
	reihungstest_id = document.getElementById('student-prestudent-menulist-reihungstest').value;
	anmeldungreihungstest = document.getElementById('student-prestudent-textbox-anmeldungreihungstest').value;
	reihungstestangetreten = document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked;
	punkte = document.getElementById('student-prestudent-textbox-punkte').value;
	bismelden = document.getElementById('student-prestudent-checkbox-bismelden').checked;
	person_id = document.getElementById('student-prestudent-textbox-person_id').value;
	prestudent_id = document.getElementById('student-prestudent-textbox-prestudent_id').value;
	neu = document.getElementById('student-prestudent-checkbox-new').checked;
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	anmerkung = document.getElementById('student-prestudent-textbox-anmerkung').value;
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	
	if (neu)
	{
		alert('Fehler! Es wurde versucht einen neuen Prestudenten anzulegen, dies ist aber hier nicht moeglich');
		return false;
	}
	
	req.add('type', 'saveprestudent');
		
	req.add('aufmerksamdurch_kurzbz', aufmerksamdurch_kurzbz);
	req.add('berufstaetigkeit_code', berufstaetigkeit_code);
	req.add('ausbildungcode', ausbildungcode);
	req.add('zgv_code', zgv_code);
	req.add('zgvort', zgvort);
	req.add('zgvdatum', zgvdatum);
	req.add('zgvmas_code', zgvmaster_code);
	req.add('zgvmaort', zgvmasterort);
	req.add('zgvmadatum', zgvmasterdatum);
	req.add('aufnahmeschluessel', aufnahmeschluessel);
	req.add('facheinschlberuf', facheinschlberuf);
	req.add('reihungstest_id', reihungstest_id);
	req.add('anmeldungreihungstest', anmeldungreihungstest);
	req.add('reihungstestangetreten', reihungstestangetreten);
	req.add('punkte', punkte);
	req.add('bismelden', bismelden);
	req.add('person_id', person_id);
	req.add('prestudent_id', prestudent_id);
	req.add('studiengang_kz', studiengang_kz);
	req.add('anmerkung', anmerkung);
		
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				
		StudentSelectUid=document.getElementById('student-detail-textbox-uid').value;
		StudentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Anmeldungsdatum fuer den RT wird auf das Aktuelle Datum gesetzt
// ****
function StudentAnmeldungreihungstestHeute()
{
	var now = new Date();
	var jahr = now.getFullYear();
	
	monat = now.getMonth()+1;
	if(monat<10) monat='0'+monat;
	tag = now.getDate();
	if(tag<10) tag='0'+tag;
	
	document.getElementById('student-prestudent-textbox-anmeldungreihungstest').value=jahr+'-'+monat+'-'+tag;
}

// ****
// * Laedt ein Zeugnis dass in der DB gespeichert ist
// ****
function StudentZeugnisAnzeigen()
{
	var tree = document.getElementById('student-zeugnis-tree');

	if (tree.currentIndex==-1) return;
	
	try
	{
		//Ausgewaehlte ID holen
        var col = tree.columns ? tree.columns["student-zeugnis-tree-akte_id"] : "student-zeugnis-tree-akte_id";
		var akte_id=tree.view.getCellText(tree.currentIndex,col);
		if(akte_id!='')
		{
			window.open('<?php echo APP_ROOT;?>content/akte.php?id='+akte_id,'File');
			//document.location.href='<?php echo APP_ROOT;?>content/akte.php?id='+akte_id;
		}
		else
		{
			return false;
		}	
	}
	catch(e)
	{
		alert(e);
		return false;
	}
}

// ****
// * Loescht ein Zeugnis
// ****
function StudentAkteDel()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-zeugnis-tree');
	
	if (tree.currentIndex==-1)
		return;
		
	try
	{
		//Ausgewaehlte Akte holen
        var col = tree.columns ? tree.columns["student-zeugnis-tree-akte_id"] : "student-zeugnis-tree-akte_id";
		var akte_id=tree.view.getCellText(tree.currentIndex,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Zeugnis wirklich entfernen?'))
	{
		//Script zum loeschen aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','deleteAkte');
		req.add('akte_id',akte_id);
				
		var response = req.executePOST();

		var val =  new ParseReturnValue(response)
		
		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		StudentTreeRefresh();
	}
}

// **************** KONTO ******************

// ****
// * Wenn eine buchung Ausgewaehlt wird, dann werden
// * die Details geladen und angezeigt
// ****
function StudentKontoAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	if (tree.currentIndex==-1) return;

	StudentKontoDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-konto-tree-buchungsnr"] : "student-konto-tree-buchungsnr";
	var buchungsnr=tree.view.getCellText(tree.currentIndex,col);
	
	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/konto.rdf.php?buchungsnr='+buchungsnr+'&'+gettimestamp();
		
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
    
    var dsource = rdfService.GetDataSourceBlocking(url);
    
	var subject = rdfService.GetResource("http://www.technikum-wien.at/konto/" + buchungsnr);

	var predicateNS = "http://www.technikum-wien.at/konto/rdf";

	//Daten holen

	person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	studiengang_kz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	studiensemester_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiensemester_kurzbz" ));
	buchungsnr_verweis = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungsnr_verweis" ));
	betrag = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betrag" ));
	buchungsdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungsdatum" ));
	buchungstext = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungstext" ));
	mahnspanne = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mahnspanne" ));
	buchungstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungstyp_kurzbz" ));
		
	document.getElementById('student-konto-textbox-betrag').value=betrag;
	document.getElementById('student-konto-textbox-buchungsdatum').value=buchungsdatum;
	document.getElementById('student-konto-textbox-buchungstext').value=buchungstext;
	document.getElementById('student-konto-textbox-mahnspanne').value=mahnspanne;
	document.getElementById('student-konto-menulist-buchungstyp').value=buchungstyp_kurzbz;
	document.getElementById('student-konto-textbox-buchungsnr').value=buchungsnr;
}

// ****
// * Aendert den Filter fuer den Konto Tree und Refresht ihn dann
// ****
function StudentKontoFilter()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	filter = document.getElementById('student-konto-button-filter');
	
	if(filter.value=='offene')
	{
		filter.value='alle';
		filter.label='offene';
	}
	else
	{
		filter.value='offene';
		filter.label='alle';
	}
		
	//Konto Tree mit neuem Filter laden
	kontotree = document.getElementById('student-konto-tree');
	person_id = document.getElementById('student-prestudent-textbox-person_id').value
	url='<?php echo APP_ROOT;?>rdf/konto.rdf.php?person_id='+person_id+"&filter="+filter.value+"&"+gettimestamp();
	
	var buchungsnr=null;
	try
	{
		if(kontotree.currentIndex!='-1')
		{
			//Ausgewaehlte Nr holen
		    var col = kontotree.columns ? kontotree.columns["student-konto-tree-buchungsnr"] : "student-konto-tree-buchungsnr";
			buchungsnr=kontotree.view.getCellText(kontotree.currentIndex,col);
		}
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = kontotree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		kontotree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	kontotree.builder.rebuild();
	
	StudentKontoSelectBuchung = buchungsnr;
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentKontoTreeDatasource = rdfService.GetDataSource(url);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	kontotree.database.AddDataSource(StudentKontoTreeDatasource);
	StudentKontoTreeDatasource.addXMLSinkObserver(StudentKontoTreeSinkObserver);
	kontotree.builder.addListener(StudentKontoTreeListener);	
}

// ****
// * Aktiviert / Deaktiviert die Konto Felder
// ****
function StudentKontoDisableFields(val)
{
	document.getElementById('student-konto-button-filter').disabled=val;
	document.getElementById('student-konto-button-neu').disabled=val;
	document.getElementById('student-konto-button-gegenbuchung').disabled=val;
	document.getElementById('student-konto-button-loeschen').disabled=val;
	document.getElementById('student-konto-button-zahlungsbestaetigung').disabled=val;
	StudentKontoDetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die Kontodetail Felder
// ****
function StudentKontoDetailDisableFields(val)
{
	document.getElementById('student-konto-textbox-betrag').disabled=val;
	document.getElementById('student-konto-textbox-buchungsdatum').disabled=val;
	document.getElementById('student-konto-textbox-buchungstext').disabled=val;
	document.getElementById('student-konto-textbox-mahnspanne').disabled=val;
	document.getElementById('student-konto-menulist-buchungstyp').disabled=val;
	document.getElementById('student-konto-button-speichern').disabled=val;
}

// ****
// * Speichert die Buchung
// ****
function StudentKontoDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	betrag = document.getElementById('student-konto-textbox-betrag').value;
	buchungsdatum = document.getElementById('student-konto-textbox-buchungsdatum').value;
	buchungstext = document.getElementById('student-konto-textbox-buchungstext').value;
	mahnspanne = document.getElementById('student-konto-textbox-mahnspanne').value;
	buchungstyp_kurzbz = document.getElementById('student-konto-menulist-buchungstyp').value;
	buchungsnr = document.getElementById('student-konto-textbox-buchungsnr').value;
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	
	req.add('type', 'savebuchung');
	
	req.add('betrag', betrag);
	req.add('buchungsdatum', buchungsdatum);
	req.add('buchungstext', buchungstext);
	req.add('mahnspanne', mahnspanne);
	req.add('buchungstyp_kurzbz', buchungstyp_kurzbz);
	req.add('buchungsnr', buchungsnr);
		
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{			
		StudentKontoSelectBuchung=buchungsnr;
		StudentKontoTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Legt eine Gegenbuchung zu einer Buchung an
// ****
function StudentKontoGegenbuchung()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	if (tree.currentIndex==-1) return;

	StudentKontoDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-konto-tree-buchungsnr"] : "student-konto-tree-buchungsnr";
	var buchungsnr=tree.view.getCellText(tree.currentIndex,col);
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	
	req.add('type', 'savegegenbuchung');
	
	req.add('buchungsnr', buchungsnr);
		
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{			
		StudentKontoSelectBuchung=val.dbdml_data;
		StudentKontoTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Loescht eine Buchung
// ****
function StudentKontoDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	if (tree.currentIndex==-1) return;

	StudentKontoDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-konto-tree-buchungsnr"] : "student-konto-tree-buchungsnr";
	var buchungsnr=tree.view.getCellText(tree.currentIndex,col);
	
	if(confirm('Diese Buchung wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');
		
		req.add('type', 'deletebuchung');
		
		req.add('buchungsnr', buchungsnr);
			
		var response = req.executePOST();
	
		var val =  new ParseReturnValue(response)
		
		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response)
			else
				alert(val.dbdml_errormsg)
		}
		else
		{			
			StudentSelectUid=document.getElementById('student-detail-textbox-uid').value;
			StudentTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Daten wurden gespeichert');
		}
	}
}

// ****
// * Ruft einen Dialog zum Anlegen von Buchungen auf
// ****
function StudentKontoNeu()
{
	window.open("<?php echo APP_ROOT; ?>content/student/studentkontoneudialog.xul.php","","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

// ****
// * Speichert die Daten aus dem BuchungenDialog
// ****
function StudentKontoNeuSpeichern(dialog, person_ids, studiengang_kz)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	
	//Daten aus dem Dialog holen
	betrag = dialog.getElementById('student-konto-neu-textbox-betrag').value;
	buchungsdatum = dialog.getElementById('student-konto-neu-textbox-buchungsdatum').value;
	buchungstext = dialog.getElementById('student-konto-neu-textbox-buchungstext').value;
	mahnspanne = dialog.getElementById('student-konto-neu-textbox-mahnspanne').value;
	buchungstyp_kurzbz = dialog.getElementById('student-konto-neu-menulist-buchungstyp').value;
	
	req.add('type', 'neuebuchung');
	
	req.add('person_ids', person_ids);
	req.add('studiengang_kz', studiengang_kz);
	req.add('betrag', betrag);
	req.add('buchungsdatum', buchungsdatum);
	req.add('buchungstext', buchungstext);
	req.add('mahnspanne', mahnspanne);
	req.add('buchungstyp_kurzbz', buchungstyp_kurzbz);	
		
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		StudentKontoTreeDatasource.Refresh(false);
		return true;
	}
}

// *****
// * Druckt eine Zahlungsbestaetigung aus
// *****
function StudentKontoZahlungsbestaetigung()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst eine Buchung auswaehlen');
		return;
	}
	
	if(!tree.view.getParentIndex(tree.currentIndex))
	{
		alert('Zum Drucken der bestaetigung bitte die oberste Buchung waehlen');
		return false;
	}
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-konto-tree-buchungsnr"] : "student-konto-tree-buchungsnr";
	var buchungsnr=tree.view.getCellText(tree.currentIndex,col);
	var uid = document.getElementById('student-detail-textbox-uid').value;
		
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid='+uid+'&buchungsnr='+buchungsnr,'Zahlungsbestaetigung', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}


// *********** Zeugnis *****************

function StudentCreateZeugnis()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('student-tree');
	
	if (tree.currentIndex==-1) return;
	
	//Ausgewaehlte UID holen
    var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	var uid=tree.view.getCellText(tree.currentIndex,col);
	
	//Studiengang holen
	var tree_lvb = document.getElementById('tree-verband');
	
	col = tree_lvb.columns ? tree_lvb.columns["stg_kz"] : "stg_kz";
	studiengang_kz=tree_lvb.view.getCellText(tree_lvb.currentIndex,col);
	
	col = tree_lvb.columns ? tree_lvb.columns["sem"] : "sem";
	semester=tree_lvb.view.getCellText(tree_lvb.currentIndex,col);
		
	window.open('<?php echo APP_ROOT; ?>content/student/zeugnis.php?uid='+uid+'&studiengang_kz='+studiengang_kz+'&semester='+semester,'Zeugnis', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ********** Betriebsmittel ******************

// ****
// * Selectiert die Betriebsmittelzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function StudentBetriebsmittelTreeSelectZuordnung()
{
	var tree=document.getElementById('student-betriebsmittel-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Buchung gespeichert
	if(StudentBetriebsmittelSelectBetriebsmittel_id!=null && StudentBetriebsmittelSelectPerson_id!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ids der row holen
			col = tree.columns ? tree.columns["student-betriebsmittel-tree-betriebsmittel_id"] : "student-betriebsmittel-tree-betriebsmittel_id";
			betriebsmittel_id=tree.view.getCellText(i,col);
			col = tree.columns ? tree.columns["student-betriebsmittel-tree-person_id"] : "student-betriebsmittel-tree-person_id";
			person_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile ist
			if(betriebsmittel_id == StudentBetriebsmittelSelectBetriebsmittel_id && 
			   person_id == StudentBetriebsmittelSelectPerson_id)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentBetriebsmittelSelectBetriebsmittel_id=null;
				StudentBetriebsmittelSelectPerson_id=null;
				return true;
			}
	   	}
	}
}

// ****
// * Wenn ein Betriebsmittel ausgewaehlt wird, dann
// * werden die zugehoerigen Details geladen
// ****
function StudentBetriebsmittelAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-betriebsmittel-tree');

	if (tree.currentIndex==-1) return;

	StudentBetriebsmittelDetailDisableFields(false);
	
	document.getElementById('student-betriebsmittel-checkbox-neu').checked=false;
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-betriebsmittel-tree-betriebsmittel_id"] : "student-betriebsmittel-tree-betriebsmittel_id";
	var betriebsmittel_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-betriebsmittel-tree-person_id"] : "student-betriebsmittel-tree-person_id";
	var person_id=tree.view.getCellText(tree.currentIndex,col);
	
	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/betriebsmittelperson.rdf.php?betriebsmittel_id='+betriebsmittel_id+'&person_id='+person_id+'&'+gettimestamp();
		
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
    
    var dsource = rdfService.GetDataSourceBlocking(url);
    
	var subject = rdfService.GetResource("http://www.technikum-wien.at/betriebsmittel/"+person_id+'/'+betriebsmittel_id);

	var predicateNS = "http://www.technikum-wien.at/betriebsmittel/rdf";

	//Daten holen
	person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	betriebsmittel_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betriebsmittel_id" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	kaution = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kaution" ));
	ausgegebenam = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausgegebenam" ));
	retouram = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#retouram" ));
	betriebsmitteltyp = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betriebsmitteltyp" ));
	nummer = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nummer" ));
	beschreibung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschreibung" ));
		
	document.getElementById('student-betriebsmittel-textbox-person_id').value=person_id;
	document.getElementById('student-betriebsmittel-textbox-betriebsmittel_id').value=betriebsmittel_id;
	document.getElementById('student-betriebsmittel-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-betriebsmittel-textbox-kaution').value=kaution;
	document.getElementById('student-betriebsmittel-textbox-ausgegebenam').value=ausgegebenam;
	document.getElementById('student-betriebsmittel-textbox-retouram').value=retouram;
	document.getElementById('student-betriebsmittel-menulist-betriebsmitteltyp').value=betriebsmitteltyp;
	document.getElementById('student-betriebsmittel-textbox-nummer').value=nummer;
	document.getElementById('student-betriebsmittel-textbox-beschreibung').value=beschreibung;
}

// ****
// * Aktiviert / Deaktiviert die Betriebsmittel Felder
// ****
function StudentBetriebsmittelDisableFields(val)
{
	document.getElementById('student-betriebsmittel-button-neu').disabled=val;
	document.getElementById('student-betriebsmittel-button-loeschen').disabled=val;
	StudentBetriebsmittelDetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die Betriebsmitteldetail Felder
// ****
function StudentBetriebsmittelDetailDisableFields(val)
{
	document.getElementById('student-betriebsmittel-menulist-betriebsmitteltyp').disabled=val;
	document.getElementById('student-betriebsmittel-textbox-nummer').disabled=val;
	document.getElementById('student-betriebsmittel-textbox-beschreibung').disabled=val;
	document.getElementById('student-betriebsmittel-textbox-kaution').disabled=val;
	document.getElementById('student-betriebsmittel-textbox-anmerkung').disabled=val;
	document.getElementById('student-betriebsmittel-textbox-ausgegebenam').disabled=val;
	document.getElementById('student-betriebsmittel-textbox-retouram').disabled=val;	
	document.getElementById('student-betriebsmittel-button-speichern').disabled=val;
	
	if(val)
		StudentBetriebsmittelDetailResetFields();
}

// ****
// * Resetet die Betriebsmitteldetail Felder
// ****
function StudentBetriebsmittelDetailResetFields()
{
	var now = new Date();
	var jahr = now.getFullYear();
	
	var monat = now.getMonth()+1;
	
	if(monat<10) 
		monat='0'+monat;
	var tag = now.getDate();
	if(tag<10) 
		tag='0'+tag;
	
	document.getElementById('student-betriebsmittel-menulist-betriebsmitteltyp').value='Zutrittskarte';
	document.getElementById('student-betriebsmittel-textbox-nummer').value='';
	document.getElementById('student-betriebsmittel-textbox-beschreibung').value='';
	document.getElementById('student-betriebsmittel-textbox-kaution').value='';
	document.getElementById('student-betriebsmittel-textbox-anmerkung').value='';
	document.getElementById('student-betriebsmittel-textbox-ausgegebenam').value=jahr+'-'+monat+'-'+tag;
	document.getElementById('student-betriebsmittel-textbox-retouram').value='';	
}

// ****
// * Loescht eine Betriebsmittelzuordnung
// ****
function StudentBetriebsmittelDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-betriebsmittel-tree');

	if (tree.currentIndex==-1) return;

	StudentBetriebsmittelDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-betriebsmittel-tree-betriebsmittel_id"] : "student-betriebsmittel-tree-betriebsmittel_id";
	var betriebsmittel_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-betriebsmittel-tree-person_id"] : "student-betriebsmittel-tree-person_id";
	var person_id=tree.view.getCellText(tree.currentIndex,col);
	
	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');
		
		req.add('type', 'deletebetriebsmittel');
		
		req.add('betriebsmittel_id', betriebsmittel_id);
		req.add('person_id', person_id);
		
		var response = req.executePOST();
	
		var val =  new ParseReturnValue(response)
		
		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response)
			else
				alert(val.dbdml_errormsg)
		}
		else
		{			
			StudentBetriebsmittelTreeDatasource.Refresh(false);
			SetStatusBarText('Daten wurden gespeichert');
		}
	}
}

// ****
// * Speichert die Betriebsmittelzuordnung
// ****
function StudentBetriebsmittelDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	person_id = document.getElementById('student-betriebsmittel-textbox-person_id').value;
	betriebsmittel_id = document.getElementById('student-betriebsmittel-textbox-betriebsmittel_id').value;
	anmerkung = document.getElementById('student-betriebsmittel-textbox-anmerkung').value;
	kaution = document.getElementById('student-betriebsmittel-textbox-kaution').value;
	ausgegebenam = document.getElementById('student-betriebsmittel-textbox-ausgegebenam').value;
	retouram = document.getElementById('student-betriebsmittel-textbox-retouram').value;
	betriebsmitteltyp = document.getElementById('student-betriebsmittel-menulist-betriebsmitteltyp').value;
	nummer = document.getElementById('student-betriebsmittel-textbox-nummer').value;
	beschreibung = document.getElementById('student-betriebsmittel-textbox-beschreibung').value;
	neu = document.getElementById('student-betriebsmittel-checkbox-neu').checked;
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	
	req.add('type', 'savebetriebsmittel');
	
	req.add('neu', neu);
	req.add('person_id', person_id);
	req.add('betriebsmittel_id', betriebsmittel_id);
	req.add('anmerkung', anmerkung);
	req.add('kaution', kaution);
	req.add('ausgegebenam', ausgegebenam);
	req.add('retouram', retouram);
	req.add('betriebsmitteltyp', betriebsmitteltyp);
	req.add('nummer', nummer);
	req.add('beschreibung', beschreibung);
	
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{			
		StudentBetriebsmittelSelectBetriebsmittel_id=val.dbdml_data;
		StudentBetriebsmittelSelectPerson_id=person_id;
		StudentBetriebsmittelTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Neues Betriebsmittel anlegen
// ****
function StudentBetriebsmittelNeu()
{
	document.getElementById('student-betriebsmittel-checkbox-neu').checked=true;
	StudentBetriebsmittelDetailDisableFields(false);
	StudentBetriebsmittelDetailResetFields();
	document.getElementById('student-betriebsmittel-textbox-person_id').value = document.getElementById('student-prestudent-textbox-person_id').value;
}

// ****
// * Fuegt eine Rolle zu einem Studenten hinzu
// ****
function StudentAddRolle(rolle)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;
		
	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
	var prestudent_id=tree.view.getCellText(tree.currentIndex,col);
		
	if(confirm('Diesen Studenten zum '+rolle+' machen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');
		
		req.add('type', 'addrolle');
				
		req.add('prestudent_id', prestudent_id);
		req.add('rolle_kurzbz', rolle);
		
		var response = req.executePOST();
	
		var val =  new ParseReturnValue(response);
		
		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response)
			else
				alert(val.dbdml_errormsg)
		}
		else
		{			
			StudentTreeRefresh();
			SetStatusBarText('Rolle hinzugefuegt');
		}
	}
}

// ****
// * Druckt die Instkriptionsbestaetigung
// ****
function StudentPrintInskriptionsbestaetigung()
{
	tree = document.getElementById('student-tree');
	//Alle markierten Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
			col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			uid = tree.view.getCellText(v,col);
			paramList += ';'+uid;
			anzahl = anzahl+1;
			}
	}
	
	if(anzahl>0)	
		window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=student.rdf.php&xsl=Inskription&uid='+paramList,'Inskriptionsbestaetigung', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	else
		alert('Bitte einen Studenten auswaehlen');
}

// **************** Incomming/Outgoing ******************

// ****
// * Wenn ein IO Eintrag Ausgewaehlt wird, dann werden
// * die Details geladen und angezeigt
// ****
function StudentIOAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-io-tree');

	if (tree.currentIndex==-1) return;

	StudentIODetailDisableFields(false);
		
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-io-tree-bisio_id"] : "student-io-tree-bisio_id";
	var bisio_id=tree.view.getCellText(tree.currentIndex,col);
	
	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/bisio.rdf.php?bisio_id='+bisio_id+'&'+gettimestamp();
		
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
    
    var dsource = rdfService.GetDataSourceBlocking(url);
    
	var subject = rdfService.GetResource("http://www.technikum-wien.at/bisio/" + bisio_id);

	var predicateNS = "http://www.technikum-wien.at/bisio/rdf";

	//Daten holen

	mobilitaetsprogramm_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mobilitaetsprogramm_code" ));
	nation_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nation_code" ));
	von = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#von" ));
	bis = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bis" ));
	zweck_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zweck_code" ));
	student_uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#student_uid" ));
				
	document.getElementById('student-io-menulist-mobilitaetsprogramm').value=mobilitaetsprogramm_code;
	document.getElementById('student-io-menulist-nation').value=nation_code;
	document.getElementById('student-io-textbox-von').value=von;
	document.getElementById('student-io-textbox-bis').value=bis;
	document.getElementById('student-io-menulist-zweck').value=zweck_code;
	document.getElementById('student-io-detail-textbox-uid').value=student_uid;
	document.getElementById('student-io-detail-checkbox-neu').checked=false;
	document.getElementById('student-io-detail-textbox-bisio_id').value=bisio_id;
}

// ****
// * Aktiviert / Deaktiviert die IO Felder
// ****
function StudentIODisableFields(val)
{
	document.getElementById('student-io-button-neu').disabled=val;
	document.getElementById('student-io-button-loeschen').disabled=val;
	StudentIODetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die IO-Detail Felder
// ****
function StudentIODetailDisableFields(val)
{
	document.getElementById('student-io-textbox-von').disabled=val;
	document.getElementById('student-io-textbox-bis').disabled=val;
	document.getElementById('student-io-menulist-mobilitaetsprogramm').disabled=val;
	document.getElementById('student-io-menulist-nation').disabled=val;
	document.getElementById('student-io-menulist-zweck').disabled=val;
	document.getElementById('student-io-button-speichern').disabled=val;
}

// *****
// * Resettet die Werte in den Detailfeldern des Incomming/Outgoing Moduls
// *****
function StudentIOResetFileds()
{
	document.getElementById('student-io-textbox-von').value='';
	document.getElementById('student-io-textbox-bis').value='';
	document.getElementById('student-io-menulist-mobilitaetsprogramm').value='6';
	document.getElementById('student-io-menulist-zweck').value='2';
	document.getElementById('student-io-menulist-nation').value='A';
}

// ****
// * Speichert den IO Datensatz
// ****
function StudentIODetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	von = document.getElementById('student-io-textbox-von').value;
	bis = document.getElementById('student-io-textbox-bis').value;
	mobilitaetsprogramm = document.getElementById('student-io-menulist-mobilitaetsprogramm').value;
	nation_code = document.getElementById('student-io-menulist-nation').value;
	zweck_code = document.getElementById('student-io-menulist-zweck').value;
	uid = document.getElementById('student-io-detail-textbox-uid').value;
	neu = document.getElementById('student-io-detail-checkbox-neu').checked;
	bisio_id = document.getElementById('student-io-detail-textbox-bisio_id').value;
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	
	req.add('type', 'savebisio');
	
	if(neu==false)
		req.add('bisio_id', bisio_id);
	
	req.add('neu', neu);
	req.add('von', von);
	req.add('bis', bis);
	req.add('mobilitaetsprogramm_code', mobilitaetsprogramm);
	req.add('nation_code', nation_code);
	req.add('zweck_code', zweck_code);
	req.add('student_uid', uid);
		
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{			
		if(neu)
			StudentIOSelectID=val.dbdml_data;
		else
			StudentIOSelectID=bisio_id;
		StudentIOTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Loescht eines IO Eintrages
// ****
function StudentIODelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-io-tree');

	if (tree.currentIndex==-1) return;

	StudentIODetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-io-tree-bisio_id"] : "student-io-tree-bisio_id";
	var bisio_id=tree.view.getCellText(tree.currentIndex,col);
	
	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');
		
		req.add('type', 'deletebisio');
		
		req.add('bisio_id', bisio_id);
			
		var response = req.executePOST();
	
		var val =  new ParseReturnValue(response)
		
		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response)
			else
				alert(val.dbdml_errormsg)
		}
		else
		{			
			StudentIOSelectID=bisio_id;
			StudentIOTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Daten wurden geloescht');
			StudentIOResetFileds();
			StudentIODetailDisableFields(true);
		}
	}
}

// ****
// * Aktiviert die Felder zum Anlegen eines neuen Eintrages
// ****
function StudentIONeu()
{
	//Felder Resetten und Aktivieren
	StudentIOResetFileds();
	StudentIODetailDisableFields(false);
	
	var now = new Date();
	var jahr = now.getFullYear();
	
	var monat = now.getMonth()+1;
	
	if(monat<10) 
		monat='0'+monat;
	var tag = now.getDate();
	if(tag<10) 
		tag='0'+tag;
	
	//UID ins Textfeld schreiben	
	document.getElementById('student-io-detail-textbox-uid').value=document.getElementById('student-detail-textbox-uid').value;	
	document.getElementById('student-io-detail-checkbox-neu').checked=true;
	document.getElementById('student-io-textbox-von').value=jahr+'-'+monat+'-'+tag;
	document.getElementById('student-io-textbox-bis').value=jahr+'-'+monat+'-'+tag;
}

// ****
// * Selectiert den Incomming/Outgoing Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentIOTreeSelectID()
{
	var tree=document.getElementById('student-io-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentIOSelectID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-io-tree-bisio_id"] : "student-io-tree-bisio_id";
			var bisio_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(bisio_id == StudentIOSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentIOSelectID=null;
				return true;
			}
	   	}
	}
}


// **************** NOTEN ************** //

// ****
// * Selectiert den Noten Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentNotenTreeSelectID()
{
	var tree=document.getElementById('student-noten-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentNotenSelectLehreinheitID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
			var lehrveranstaltung_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(lehrveranstaltung_id == StudentNotenSelectLehrveranstaltugnID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentNotenSelectLehrveranstaltungID=null;
				StudentNotenSelectStudentUID=null;
				return true;
			}
	   	}
	}
}

// ****
// * Selectiert den Noten Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentLvGesamtNotenTreeSelectID()
{
	var tree=document.getElementById('student-lvgesamtnoten-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentLvGesamtNotenSelectLehreinheitID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-lvgesamtnoten-tree-lehrveranstaltung_id"] : "student-lvgesamtnoten-tree-lehrveranstaltung_id";
			var lehrveranstaltung_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(lehrveranstaltung_id == StudentLvGesamtNotenSelectLehrveranstaltugnID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentNotenSelectLehrveranstaltungID=null;
				StudentNotenSelectStudentUID=null;
				return true;
			}
	   	}
	}
}