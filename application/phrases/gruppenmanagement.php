<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'benutzerSchonZugewiesen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Benutzer ist bereits der Gruppe zugewiesen",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "User is already assigned to the group",
				'description' => '',
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'gruppenmanagement',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Gruppenmanagement",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "Group management",
				'description' => '',
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'kurzbezeichnung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Kurzbezeichnung",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "Short description",
				'description' => '',
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'bezeichnung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bezeichnung",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "Name",
				'description' => '',
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'beschreibung',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Beschreibung",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "Description",
				'description' => '',
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'zuweisenloeschen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zuweisen/Entfernen",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "Assign/Remove",
				'description' => '',
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'benutzergruppe',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Benutzergruppe",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "User group",
				'description' => '',
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'benutzerHinzufuegen',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Benutzer hinzufügen",
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => "Add user",
				'description' => '',
			)
		)
	)
);

