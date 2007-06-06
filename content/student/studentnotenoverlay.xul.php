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

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
?>

<overlay id="StudentKonto"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Zeugnis Overlay -->
<vbox id="student-noten" style="margin:0px;" flex="1">
<popupset>
	<popup id="student-noten-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentNotenDelete();" id="student-noten-tree-popup-delete" hidden="false"/>
	</popup>
</popupset>
<hbox flex="1">
	<tree id="student-noten-tree" seltype="single" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/zeugnisnote/liste"
		style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100%" enableColumnDrag="true"
		onselect="StudentNotenAuswahl()"
		context="student-noten-tree-popup"
	>
	
		<treecols>
			<treecol id="student-noten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="false" primary="true"
				class="sortDirectionIndicator"
				sortActive="true"
				sortDirection="ascending"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-uebernahmedatum" label="Uebernahmedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#uebernahmedatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiensemester_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-note" label="Note" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-student_uid" label="Uid" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_id" />
			<splitter class="tree-splitter"/>
		</treecols>
	
		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#uebernahmedatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiensemester_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_id"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	
	<vbox>
		<spacer flex="1"/>
		<button id="student-note-copy" label="&lt;=" style="font-weight: bold;" oncommand="alert(document.getElementById('student-noten-datum').value);"/>
		<spacer flex="1"/>
	</vbox>
	
	
	<tree id="student-lvgesamtnoten-tree" seltype="single" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/lvgesamtnote/liste"
		style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100%" enableColumnDrag="true"
	>
	
		<treecols>
			<treecol id="student-lvgesamtnoten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="false" primary="true"
				class="sortDirectionIndicator"
				sortActive="true"
				sortDirection="ascending"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-mitarbeiter_uid" label="MitarbeiterUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#mitarbeiter_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-freigabedatum" label="Freigabedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#freigabedatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#studiensemester_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-note" label="Note" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-student_uid" label="StudentUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_id" />
			<splitter class="tree-splitter"/>
		</treecols>
	
		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#mitarbeiter_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#freigabedatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#studiensemester_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_id"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	
</hbox>				

</vbox>
</overlay>