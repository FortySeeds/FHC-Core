-- INSERTS fuer St.Poelten DB

-- Erhalter

INSERT INTO public.tbl_erhalter(erhalter_kz, kurzbz, bezeichnung, dvr, logo, zvr) VALUES(13,'FHSTP', 'Fachhochschule St. P�lten', '','','');

-- OrgForm

INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('VZ', 1, 'Vollzeit');
INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('BB', 2, 'Berufsbegleitend');
INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('VBB', 3, 'Vollzeit und Berufsbeleitend');
INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('ZGS', 4, 'Zielgruppenspezifisch');

-- Studiengang

INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('0','fh','d','Fachhochschule','1','A','1','13','VZ',null);
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('2','sb','d','xxxSozialarbeit- berufsbegleitend','8','C','2','13','VZ','14');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('1','vo','d','xxxVerkehrsinformatik und Verkehrs�kologie','8','C','2','13','VZ','15');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('261','mt','b','Bakkalaureatsstudiengang Medientechnik','6','C','2','13','VZ','16');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('323','sa','m','Magisterstudiengang Sozialarbeit','3','C','2','13','VZ','17');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('410','is','b','Bakkalaureatsstudiengang IT Security','6','C','2','13','VZ','18');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('405','bc','b','Bakkalaureatsstudiengang Computersimulation','6','C','2','13','VZ','19');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('403','bm','b','Bachelorstudiengang Medienmanagement','6','C','2','13','VZ','20');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('409','mk','b','Bachelorstudiengang Media- und Kommunikationsberatung','6','C','2','13','VZ','21');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('407','di','b','Di�tologie','6','C','2','13','VZ','23');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('408','pt','b','Physiotherapie','6','C','2','13','VZ','24');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('406','bs','b','Bachelorstudiengang Soziale Arbeit','6','C','2','13','VZ','22');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('38','tm','d','Telekommunikation und Medien','8','C','2','13','VZ','10');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('88','mm','d','Medienmanagement','8','C','2','13','VZ','11');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('95','cs','d','Computersimulation','8','C','2','13','VZ','12');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('96','so','d','Sozialarbeit','8','C','2','13','VZ','13');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('262','ma','m','Masterstudiengang Telekommunikation und Medien','3','C','2','13','VZ','25');

-- Sprache

INSERT INTO public.tbl_sprache(sprache) VALUES('German');
INSERT INTO public.tbl_sprache(sprache) VALUES('English');
INSERT INTO public.tbl_sprache(sprache) VALUES('Espanol');

-- Fachbereich

INSERT INTO public.tbl_fachbereich(fachbereich_kurzbz, bezeichnung, farbe, studiengang_kz, ext_id, aktiv) VALUES('Dummy','','','0',null,true);

-- Ausbildung

INSERT INTO bis.tbl_ausbildung VALUES (1, 'PhD', 'Universit�tsabschluss mit Doktorat als Zweit- oder Drittabschluss oder PhD-Abschluss');
INSERT INTO bis.tbl_ausbildung VALUES (3, 'FH-Master', 'Fachhochschulabschluss auf Diplom- oder Masterebene');
INSERT INTO bis.tbl_ausbildung VALUES (4, 'Univ.-Bachelor', 'Universit�ts- oder Hochschulabschluss auf Bachelorebene (einschlie�lich Kurzstudien)');
INSERT INTO bis.tbl_ausbildung VALUES (5, 'FH-Bachelor', 'Fachhochschulabschluss auf Bachelorebene');
INSERT INTO bis.tbl_ausbildung VALUES (6, 'Akad-Diplom', 'Diplom einer Akademie f�r Lehrerbildung, Akademie f�r Sozialarbeit, Medizinisch-technische Akademie, Hebammenakademie, Milit�rakademie oder einer anderen anerkannten postsekund�ren Bildungseinrichtung');
INSERT INTO bis.tbl_ausbildung VALUES (8, 'AHS', 'Reifepr�fung an einer allgemeinbildenden h�heren Schule');
INSERT INTO bis.tbl_ausbildung VALUES (9, 'BHS', 'Reife- und Diplompr�fung einer berufsbildenden oder lehrer- und erzieherbildenden h�heren Schule');
INSERT INTO bis.tbl_ausbildung VALUES (10, 'Lehrabschluss', 'Lehrabschlusspr�fung, berufsbildende mittlere Schule oder vergleichbare Berufsausbildung');
INSERT INTO bis.tbl_ausbildung VALUES (11, 'Pflichtschule', 'Pflichtschule');
INSERT INTO bis.tbl_ausbildung VALUES (7, 'terti�r', 'Anderer terti�rer Bildungsabschluss (Kolleg; Meisterpr�fung; Universit�tslehrgang oder Lehrgang gem�� �14a Abs.3 FHStG, mit dem kein akademischer Grad verbunden war)');
INSERT INTO bis.tbl_ausbildung VALUES (2, 'Univ.-Master', 'Universit�ts- oder Hochschulabschluss auf Diplom- oder Masterebene, Doktorat der Medizin bzw. der Human- oder Zahnmedizin oder Doktorat auf Grund von Studienvorschriften aus der Zeit vor dem Inkrafttretendes AHStG BGBl. Nr. 177/1966 oder Abschluss eines Universit�tslehrganges oder Lehrganges universit�ren Charakters (�51 Abs. 2 Z 23 UG 2002 oder ��26 Abs.1 und 28 Abs.1 UniStG) oder eines Lehrganges zur Weiterbildung (�14a Abs.2 FHStG) mit Mastergrad');

-- Studiensemester

INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2001', '2001-09-03', '2002-01-31', NULL, 'Wintersemester 2001/2002');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2006', '2006-02-13', '2006-07-01', 9, 'Sommersemester 2006');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2005', '2005-02-14', '2005-07-02', 7, 'Sommersemester 2005');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2004', '2004-02-03', '2004-07-03', 5, 'Sommersemester 2004');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2003', '2003-02-02', '2003-07-02', 3, 'Sommersemester 2003');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2002', '2002-02-01', '2002-07-01', 1, 'Sommersemester 2002');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2009', '2009-02-05', '2009-07-05', 15, 'Sommersemester 2009');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2007', '2007-02-12', '2007-07-01', 11, 'Sommersemester 2007');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2008', '2008-02-04', '2008-07-04', 13, 'Sommersemester 2008');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2009', '2009-09-04', '2010-02-04', 16, 'Wintersemester 2009/2010');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2008', '2008-09-03', '2009-02-03', 14, 'Wintersemester 2008/2009');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2006', '2006-09-04', '2007-02-03', 10, 'Wintersemester 2006/2007');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2005', '2005-09-05', '2006-02-04', 8, 'Wintersemester 2005/2006');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2004', '2004-09-06', '2005-02-05', 6, 'Wintersemester 2004/2005');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2003', '2003-09-02', '2004-02-02', 4, 'Wintersemester 2003/2004');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2002', '2002-09-01', '2003-02-01', 2, 'Wintersemester 2002/2003');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2007', '2007-09-01', '2008-02-01', 12, 'Wintersemester 2007/2008');


-- tbl_kontakttyp; Type: TABLE DATA; Schema: public; 

SET search_path = public, pg_catalog;
INSERT INTO tbl_kontakttyp VALUES ('email', 'E-Mail');
INSERT INTO tbl_kontakttyp VALUES ('telefon', 'Telefonnummer');
INSERT INTO tbl_kontakttyp VALUES ('mobil', 'Mobiltelefonnummer');
INSERT INTO tbl_kontakttyp VALUES ('fax', 'Faxnummer');
INSERT INTO tbl_kontakttyp VALUES ('so.tel', 'sonstige Telefonnummer');


-- tbl_ausbildung; Type: TABLE DATA; Schema: bis; 

SET search_path = bis, pg_catalog;
INSERT INTO tbl_ausbildung VALUES (1, 'PhD', 'Universit�tsabschluss mit Doktorat als Zweit- oder Drittabschluss oder PhD-Abschluss');
INSERT INTO tbl_ausbildung VALUES (3, 'FH-Master', 'Fachhochschulabschluss auf Diplom- oder Masterebene');
INSERT INTO tbl_ausbildung VALUES (4, 'Univ.-Bachelor', 'Universit�ts- oder Hochschulabschluss auf Bachelorebene (einschlie�lich Kurzstudien)');
INSERT INTO tbl_ausbildung VALUES (5, 'FH-Bachelor', 'Fachhochschulabschluss auf Bachelorebene');
INSERT INTO tbl_ausbildung VALUES (6, 'Akad-Diplom', 'Diplom einer Akademie f�r Lehrerbildung, Akademie f�r Sozialarbeit, Medizinisch-technische Akademie, Hebammenakademie, Milit�rakademie oder einer anderen anerkannten postsekund�ren Bildungseinrichtung');
INSERT INTO tbl_ausbildung VALUES (8, 'AHS', 'Reifepr�fung an einer allgemeinbildenden h�heren Schule');
INSERT INTO tbl_ausbildung VALUES (9, 'BHS', 'Reife- und Diplompr�fung einer berufsbildenden oder lehrer- und erzieherbildenden h�heren Schule');
INSERT INTO tbl_ausbildung VALUES (10, 'Lehrabschluss', 'Lehrabschlusspr�fung, berufsbildende mittlere Schule oder vergleichbare Berufsausbildung');
INSERT INTO tbl_ausbildung VALUES (11, 'Pflichtschule', 'Pflichtschule');
INSERT INTO tbl_ausbildung VALUES (7, 'terti�r', 'Anderer terti�rer Bildungsabschluss (Kolleg; Meisterpr�fung; Universit�tslehrgang oder Lehrgang gem�� �14a Abs.3 FHStG, mit dem kein akademischer Grad verbunden war)');
INSERT INTO tbl_ausbildung VALUES (2, 'Univ.-Master', 'Universit�ts- oder Hochschulabschluss auf Diplom- oder Masterebene, Doktorat der Medizin bzw. der Human- oder Zahnmedizin oder Doktorat auf Grund von Studienvorschriften aus der Zeit vor dem Inkrafttretendes AHStG BGBl. Nr. 177/1966 oder Abschluss eines Universit�tslehrganges oder Lehrganges universit�ren Charakters (�51 Abs. 2 Z 23 UG 2002 oder ��26 Abs.1 und 28 Abs.1 UniStG) oder eines Lehrganges zur Weiterbildung (�14a Abs.2 FHStG) mit Mastergrad');


-- tbl_zeitsperretyp; Type: TABLE DATA; Schema: campus; 

SET search_path = campus, pg_catalog;
INSERT INTO tbl_zeitsperretyp VALUES ('ReiseAL', 'Dienstreise Ausland', '00BFFF');
INSERT INTO tbl_zeitsperretyp VALUES ('Amt', 'Beh�rdenweg', 'B3B3B3');
INSERT INTO tbl_zeitsperretyp VALUES ('Schulung', 'Weiterbildung', '99FF99');
INSERT INTO tbl_zeitsperretyp VALUES ('Sonstige', 'Sonstiges', '9966CC');
INSERT INTO tbl_zeitsperretyp VALUES ('Telework', 'Heimarbeit', 'FFCCFF');
INSERT INTO tbl_zeitsperretyp VALUES ('ReiseIL', 'Diensreise Inland', '00D926');
INSERT INTO tbl_zeitsperretyp VALUES ('DienstV', 'Dienstverhinderung', 'B3B364');
INSERT INTO tbl_zeitsperretyp VALUES ('DienstF', 'Dienstfreistellung', '39DFA4');
INSERT INTO tbl_zeitsperretyp VALUES ('Krank', 'Krankheit/Spitalsaufenthalt', 'B3B300');
INSERT INTO tbl_zeitsperretyp VALUES ('ZA', 'Zeitausgleich', 'FFA605');
INSERT INTO tbl_zeitsperretyp VALUES ('Arzt', 'Arztbesuch', '0066FF');
INSERT INTO tbl_zeitsperretyp VALUES ('Konfernz', 'Konferenz/Tagung/Seminar', 'CC6633');
INSERT INTO tbl_zeitsperretyp VALUES ('Urlaub', 'Urlaub', 'FF0000');


-- tbl_lehrfunktion; Type: TABLE DATA; Schema: lehre; 

SET search_path = lehre, pg_catalog;
INSERT INTO tbl_lehrfunktion VALUES ('LV-Leitung', 'Lehrveranstaltungsleiter', 1.10);
INSERT INTO tbl_lehrfunktion VALUES ('Betreuung', 'Betreuer', 0.90);
INSERT INTO tbl_lehrfunktion VALUES ('Lektor', 'Lektor', 1.00);
INSERT INTO tbl_lehrfunktion VALUES ('Zweitbetreuung', 'Zweitbetreuung', 0.90);