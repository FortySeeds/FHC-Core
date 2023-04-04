<?php
/* Copyright (C) 2023 FH Technikum-Wien
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
 * Authors: Cristina Hainberger	<hainberg@technikum-wien.at>
 *
 * Beschreibung:
 * The script checks phrases and phrase-texts for actuality in the database.
 * Missing attributes are inserted.
 */

$phrases = array(
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'studiensemesterGeplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiensemester geplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Semester planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungHochladen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung hochladen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme löschen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalskills',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International skills',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'International skills',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Internationalisierungsmaßnahmen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Internationalization measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalbeschreibung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ab dem Studienjahr 2022/23 ist der Erwerb von internationalen und interkulturellen Kompetenzen Teil des Curriculums. <br />
							Auf der Grundlage der vorliegenden Maßnahmen absolvieren Sie im Laufe ihres Studiums Internationalisierungsaktivitäten, die mit unterschiedlichen ECTS-Punkten hinterlegt sind. <br />
							In Summe müssen 5 ECTS erworben werden, die im 6. Semester wirksam werden. <br/>
							Das Modul „International skills“ wird mit der Beurteilung „Mit Erfolg teilgenommen“ abgeschlossen. <br />
							Bitte wählen Sie die für Sie in Frage kommenden Maßnahmen aus und planen Sie das entsprechende Semester. <br />
							Sobald die 5 ECTS erreicht wurden, überprüft der Studiengang die von Ihnen hochgeladenen Dokumente. <br /><br />
							Fragen zum Status Ihrer Maßnahme u.ä. richten Sie bitte an den Studiengang. <br />
							Bei allen weiteren Fragen zum Thema Organisation und Finanzierung des Auslandsaufenthalts und/oder Sprachkurs gibt Ihnen das International Office der FH Technikum Wien unter <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a> gerne Auskunft.',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Starting with the study year 2022/23, the acquisition of international and intercultural competencies is part of the curriculum.<br />
							On the basis of the measures at-hand, you will complete internationalization activities during the course of your studies, which are assigned different ECTS credits.<br />
							In total, 5 ECTS must be acquired, which become effective in the 6th semester.<br />
							The module “International skills” is completed with the assessment "Successfully participated". <br />
							Please select the measures that apply to you and schedule the appropriate semester. <br />
							Once the 5 ECTS have been achieved, the degree program will review the documents you have uploaded. <br /><br />
							Please direct questions regarding the status of your measure and the like should be directed to the study program. <br />
							For all further questions regarding the organization and financing of your stay abroad and/or language course, please contact the International Office of the UAS Technikum Wien at <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a>.
							',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'nurBachelor',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur für Bachelorstudiengänge.',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Only for bachelor programmes.',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Deutsch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'title german',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnungeng',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Englisch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'title english',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Deutsch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'description german',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibungeng',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Englisch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'description english',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeBearbeiten',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Massnahme bearbeiten',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Maßnahme wirklich löschen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the measure?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'fileLoeschenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Bestätigung wirklich löschen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the confirmation?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAblehnen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan ablehnen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entbestaetigenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung widerrufen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Revoke confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entakzeptierenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die Planbestätigung wirklich widerrufen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to cancel the plan confirmation?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'allegeplanten',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle geplanten',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'All planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleMassnahmenJetzt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Maßnahmen anzeigen die im jetzigen Studiensemester geplant sind',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all measures that are planned for the current study semester',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleStudierendeJetzt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem jetzigen Studiensemester',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the current study semester',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'lastSemester',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem letzten Semester',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the last semester"',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'meinMassnahmeplan',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mein Maßnahmenplan',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'My action plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsBestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsMassnahme',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS - Maßnahme',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - Measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'geplanteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - geplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'akzpetierteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan - akzeptiert',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Plan - accepted',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'durchgefuehrteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - durchgeführt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - performed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS - bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'abgelehnteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - abgelehnt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - declined',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAkzeptieren',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAkzeptieren',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAblehnen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung ablehnen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'grund',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Grund',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'anmerkungstgl',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anmerkung - Studiengangsleitung',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Note - Study course Director',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrverplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 ECTS verplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerverplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 ECTS verplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrbestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerbestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleAkzeptierenPlan',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle markierten Pläne akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept all marked plans',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'downloadBestaetigung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung herunterladen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Download confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'addMassnahme',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme hinzufügen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Add measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'studiensemesterGeplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiensemester geplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Semester planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungHochladen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung hochladen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme löschen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalskills',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International skills',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'International skills',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Internationalisierungsmaßnahmen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Internationalization measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalbeschreibung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ab dem Studienjahr 2022/23 ist der Erwerb von internationalen und interkulturellen Kompetenzen Teil des Curriculums. <br />
							Auf der Grundlage der vorliegenden Maßnahmen absolvieren Sie im Laufe ihres Studiums Internationalisierungsaktivitäten, die mit unterschiedlichen ECTS-Punkten hinterlegt sind. <br />
							In Summe müssen 5 ECTS erworben werden, die im 6. Semester wirksam werden. <br/>
							Das Modul „International skills“ wird mit der Beurteilung „Mit Erfolg teilgenommen“ abgeschlossen. <br />
							Bitte wählen Sie die für Sie in Frage kommenden Maßnahmen aus und planen Sie das entsprechende Semester. <br />
							Sobald die 5 ECTS erreicht wurden, überprüft der Studiengang die von Ihnen hochgeladenen Dokumente. <br /><br />
							Fragen zum Status Ihrer Maßnahme u.ä. richten Sie bitte an den Studiengang. <br />
							Bei allen weiteren Fragen zum Thema Organisation und Finanzierung des Auslandsaufenthalts und/oder Sprachkurs gibt Ihnen das International Office der FH Technikum Wien unter <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a> gerne Auskunft.',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Starting with the study year 2022/23, the acquisition of international and intercultural competencies is part of the curriculum.<br />
							On the basis of the measures at-hand, you will complete internationalization activities during the course of your studies, which are assigned different ECTS credits.<br />
							In total, 5 ECTS must be acquired, which become effective in the 6th semester.<br />
							The module “International skills” is completed with the assessment "Successfully participated". <br />
							Please select the measures that apply to you and schedule the appropriate semester. <br />
							Once the 5 ECTS have been achieved, the degree program will review the documents you have uploaded. <br /><br />
							Please direct questions regarding the status of your measure and the like should be directed to the study program. <br />
							For all further questions regarding the organization and financing of your stay abroad and/or language course, please contact the International Office of the UAS Technikum Wien at <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a>.
							',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'nurBachelor',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur für Bachelorstudiengänge.',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Only for bachelor programmes.',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Deutsch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'title german',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnungeng',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Englisch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'title english',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Deutsch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'description german',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibungeng',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Englisch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'description english',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeBearbeiten',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Massnahme bearbeiten',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Maßnahme wirklich löschen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the measure?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'fileLoeschenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Bestätigung wirklich löschen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the confirmation?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAblehnen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan ablehnen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entbestaetigenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung widerrufen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Revoke confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entakzeptierenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die Planbestätigung wirklich widerrufen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to cancel the plan confirmation?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'allegeplanten',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle geplanten',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'All planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleMassnahmenJetzt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Maßnahmen anzeigen die im jetzigen Studiensemester geplant sind',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all measures that are planned for the current study semester',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleStudierendeJetzt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem jetzigen Studiensemester',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the current study semester',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'lastSemester',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem letzten Semester',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the last semester"',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'meinMassnahmeplan',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mein Maßnahmenplan',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'My action plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsBestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsMassnahme',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS - Maßnahme',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - Measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'geplanteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - geplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'akzpetierteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan - akzeptiert',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Plan - accepted',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'durchgefuehrteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - durchgeführt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - performed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS - bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'abgelehnteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - abgelehnt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - declined',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAkzeptieren',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAkzeptieren',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAblehnen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung ablehnen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'grund',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Grund',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'anmerkungstgl',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anmerkung - Studiengangsleitung',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Note - Study course Director',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrverplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 ECTS verplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerverplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 ECTS verplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrbestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerbestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleAkzeptierenPlan',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle markierten Pläne akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept all marked plans',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'downloadBestaetigung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung herunterladen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Download confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'addMassnahme',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme hinzufügen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Add measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'studiensemesterGeplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiensemester geplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Semester planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungHochladen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung hochladen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme löschen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalskills',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International skills',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'International skills',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Internationalisierungsmaßnahmen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Internationalization measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalbeschreibung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ab dem Studienjahr 2022/23 ist der Erwerb von internationalen und interkulturellen Kompetenzen Teil des Curriculums. <br />
							Auf der Grundlage der vorliegenden Maßnahmen absolvieren Sie im Laufe ihres Studiums Internationalisierungsaktivitäten, die mit unterschiedlichen ECTS-Punkten hinterlegt sind. <br />
							In Summe müssen 5 ECTS erworben werden, die im 6. Semester wirksam werden. <br/>
							Das Modul „International skills“ wird mit der Beurteilung „Mit Erfolg teilgenommen“ abgeschlossen. <br />
							Bitte wählen Sie die für Sie in Frage kommenden Maßnahmen aus und planen Sie das entsprechende Semester. <br />
							Sobald die 5 ECTS erreicht wurden, überprüft der Studiengang die von Ihnen hochgeladenen Dokumente. <br /><br />
							Fragen zum Status Ihrer Maßnahme u.ä. richten Sie bitte an den Studiengang. <br />
							Bei allen weiteren Fragen zum Thema Organisation und Finanzierung des Auslandsaufenthalts und/oder Sprachkurs gibt Ihnen das International Office der FH Technikum Wien unter <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a> gerne Auskunft.',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Starting with the study year 2022/23, the acquisition of international and intercultural competencies is part of the curriculum.<br />
							On the basis of the measures at-hand, you will complete internationalization activities during the course of your studies, which are assigned different ECTS credits.<br />
							In total, 5 ECTS must be acquired, which become effective in the 6th semester.<br />
							The module “International skills” is completed with the assessment "Successfully participated". <br />
							Please select the measures that apply to you and schedule the appropriate semester. <br />
							Once the 5 ECTS have been achieved, the degree program will review the documents you have uploaded. <br /><br />
							Please direct questions regarding the status of your measure and the like should be directed to the study program. <br />
							For all further questions regarding the organization and financing of your stay abroad and/or language course, please contact the International Office of the UAS Technikum Wien at <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a>.
							',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'nurBachelor',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur für Bachelorstudiengänge.',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Only for bachelor programmes.',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Deutsch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'title german',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnungeng',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Englisch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'title english',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Deutsch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'description german',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibungeng',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Englisch',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'description english',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeBearbeiten',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Massnahme bearbeiten',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit measure',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Maßnahme wirklich löschen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the measure?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'fileLoeschenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Bestätigung wirklich löschen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the confirmation?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAblehnen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan ablehnen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entbestaetigenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung widerrufen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Revoke confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entakzeptierenConfirm',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die Planbestätigung wirklich widerrufen?',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to cancel the plan confirmation?',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'allegeplanten',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle geplanten',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'All planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleMassnahmenJetzt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Maßnahmen anzeigen die im jetzigen Studiensemester geplant sind',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all measures that are planned for the current study semester',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleStudierendeJetzt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem jetzigen Studiensemester',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the current study semester',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'lastSemester',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem letzten Semester',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the last semester"',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'meinMassnahmeplan',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mein Maßnahmenplan',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'My action plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsBestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsMassnahme',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS - Maßnahme',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - Measures',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'geplanteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - geplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'akzpetierteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan - akzeptiert',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Plan - accepted',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'durchgefuehrteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - durchgeführt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - performed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS - bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'abgelehnteMassnahmen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - abgelehnt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - declined',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAkzeptieren',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept plan',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAkzeptieren',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAblehnen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung ablehnen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'grund',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Grund',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'anmerkungstgl',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anmerkung - Studiengangsleitung',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Note - Study course Director',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrverplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 ECTS verplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerverplant',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 ECTS verplant',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS planned',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrbestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerbestaetigt',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 ECTS bestätigt',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS confirmed',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleAkzeptierenPlan',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle markierten Pläne akzeptieren',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept all marked plans',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'downloadBestaetigung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung herunterladen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Download confirmation',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'addMassnahme',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme hinzufügen',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'Add measure',
				'description' => '',
			)
		)
	)
);

