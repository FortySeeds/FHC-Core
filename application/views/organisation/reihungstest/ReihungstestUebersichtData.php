<?php
	$schwund = 0;
	if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && !is_null(REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND))
		$schwund = REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND;
	
	$filterWidgetArray = array(
		'query' => "
		SELECT
			reihungstest_id,
			datum,
			uhrzeit,
			freigeschaltet,
			max_plaetze,
			oeffentlich,
			studiensemester_kurzbz,
			anmeldefrist,
			anzahl_angemeldet,
			studiengaenge,
			fakultaet,
			max_plaetze - anzahl_angemeldet as freie_plaetze,
			raeume
		FROM
		(
			SELECT
				reihungstest_id, datum, uhrzeit, freigeschaltet,

				/* Plaetze aus Termin oder zugeteilten Raeumen minus Schwund */
				COALESCE(
					max_teilnehmer,
					(SELECT sum(arbeitsplaetze) - ceil(sum(arbeitsplaetze)/100.0*".$schwund.")
					FROM
						public.tbl_rt_ort
						JOIN public.tbl_ort ON(tbl_rt_ort.ort_kurzbz=tbl_ort.ort_kurzbz)
					WHERE
						tbl_rt_ort.rt_id=tbl_reihungstest.reihungstest_id
					)
				) as max_plaetze,

				oeffentlich, studiensemester_kurzbz, anmeldefrist,

				(SELECT count(*)
				 FROM public.tbl_rt_person
				 WHERE rt_id=tbl_reihungstest.reihungstest_id
				) as anzahl_angemeldet,

				/* Bezeichnung der Studiengaenge der zugeordneten Personen*/
				(SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(sg.typ || sg.kurzbz) || ':' || sp.orgform_kurzbz), ', ')
				FROM
					public.tbl_rt_person
					JOIN lehre.tbl_studienplan sp USING(studienplan_id)
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					JOIN public.tbl_studiengang sg USING(studiengang_kz)
				WHERE
					tbl_rt_person.rt_id = tbl_reihungstest.reihungstest_id
				) as studiengaenge,

				/* Fakultaeten zu den zugeordneten Studienplaenen */
				(
					WITH RECURSIVE meine_oes(oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz) as
					(
						SELECT
							oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz
						FROM
							public.tbl_organisationseinheit
						WHERE
							oe_kurzbz in (
								SELECT
									oe_kurzbz
								FROM
									public.tbl_rt_studienplan
									JOIN lehre.tbl_studienplan sp USING(studienplan_id)
									JOIN lehre.tbl_studienordnung USING(studienordnung_id)
									JOIN public.tbl_studiengang sg USING(studiengang_kz)
								WHERE
									tbl_rt_studienplan.reihungstest_id = tbl_reihungstest.reihungstest_id
								)
							AND aktiv = true
						UNION ALL
						SELECT
							o.oe_kurzbz, o.oe_parent_kurzbz, o.organisationseinheittyp_kurzbz
						FROM
							public.tbl_organisationseinheit o, meine_oes
						WHERE
							o.oe_kurzbz=meine_oes.oe_parent_kurzbz
							AND aktiv = true
					)
					SELECT
						ARRAY_TO_STRING(ARRAY_AGG(DISTINCT tbl_organisationseinheit.bezeichnung),', ')
					FROM
						meine_oes
						JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
					WHERE
						meine_oes.organisationseinheittyp_kurzbz='Fakultaet'
				) as fakultaet,

				/* Zugeteilte Raeume*/
				(SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT ort_kurzbz), ', ')
				FROM
					public.tbl_rt_ort
				WHERE
					tbl_rt_ort.rt_id = tbl_reihungstest.reihungstest_id
				) as raeume
			FROM
				public.tbl_reihungstest
			WHERE
				datum>now()-'2 weeks'::interval
				AND NOT EXISTS(
					SELECT 1 FROM public.tbl_studiengang
					WHERE studiengang_kz=tbl_reihungstest.studiengang_kz AND tbl_studiengang.typ='m'
				)
			ORDER BY datum desc
		) data
		",
		'requiredPermissions' => 'admin',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'ReihungstestID',
			'Datum',
			'Uhrzeit',
			'Freigegschaltet',
			'Maximale Teilnehmer',
			'Öffentlich',
			'Studiensemester',
			'Anmeldefrist',
			'Anzahl Angemeldet',
			'Teilnehmer Stg',
			'Fakultät',
			'Freie Plätze',
			'Räume'
		),
		'formatRow' => function($datasetRaw) {
			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?reihungstest_id=%s" target="_blank">Details</a>',
				base_url('vilesci/stammdaten/reihungstestverwaltung.php'),
				$datasetRaw->{'reihungstest_id'}
			);
			if ($datasetRaw->{'anmeldefrist'} == null)
			{
				$datasetRaw->{'anmeldefrist'} = '-';
			}
			if ($datasetRaw->{'max_plaetze'} == null)
			{
				$datasetRaw->{'max_plaetze'} = '-';
			}
			if ($datasetRaw->{'studiengaenge'} == null)
			{
				$datasetRaw->{'studiengaenge'} = '-';
			}
			if ($datasetRaw->{'raeume'} == null)
			{
				$datasetRaw->{'raeume'} = '-';
			}
			if ($datasetRaw->{'freie_plaetze'} == null)
			{
				$datasetRaw->{'freie_plaetze'} = '-';
			}
			if ($datasetRaw->{'oeffentlich'} == 'true')
			{
				$datasetRaw->{'oeffentlich'} = 'Ja';
			}
			if ($datasetRaw->{'oeffentlich'} == 'false')
			{
				$datasetRaw->{'oeffentlich'} = 'Nein';
			}

			if ($datasetRaw->{'datum'} == null)
			{
				$datasetRaw->{'datum'} = 'Not sent';
			}
			else
			{
				$datasetRaw->{'datum'} = date_format(date_create($datasetRaw->{'datum'}), 'd.m.Y');
			}

			return $datasetRaw;
		}
	);

	$filterWidgetArray['app'] = 'reihungstest';
	$filterWidgetArray['datasetName'] = 'overview';
	$filterWidgetArray['filterKurzbz'] = 'Reihungstest';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
