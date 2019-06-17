<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ReihungstestJob extends CLI_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load models
        $this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
        $this->load->model('crm/RtStudienplan_model', 'RtStudienplanModel');
        $this->load->model('organisation/Studiengang_model', 'StudiengangModel');
        $this->load->model('organisation/Studienplan_model', 'StudienplanModel');

        // Load helpers
        $this->load->helper('hlp_sancho_helper');
    }

    /**
     * runReihungstestJob
     */
    public function runReihungstestJob()
    {
        // Get study plans that have no assigned placement tests yet
        $result = $this->ReihungstestModel->checkMissingReihungstest();

		$missing_rt_arr = array();
		if (hasData($result))
		{
			$missing_rt_arr = $result->retval;
		}
		elseif (isError($result))
		{
			show_error($result->error);
		}

        // Get free places
        $result = $this->ReihungstestModel->getFreePlaces();

		$free_places_arr = array();
		if (hasData($result))
		{
			$free_places_arr = $result->retval;
		}
		elseif (isError($result))
		{
			show_error($result->error);
		}

        // Prepare data for mail template 'ReihungstestJob'
        $content_data_arr = $this->_getContentData($missing_rt_arr, $free_places_arr);

		// Send email in Sancho design
		if (!empty($missing_rt_arr) || !empty($free_places_arr))
		{
			sendSanchoMail(
				'ReihungstestJob',
				$content_data_arr,
				MAIL_INFOCENTER,
				'Support für die Reihungstest-Verwaltung');
		}
	}

	/*
	 * Sends an email to all assistants of a placement test when an anmeldeschluss has been reached
	 *
	 * @param integer $degreeProgram. Kennzahl of Degree Program to check
	 * @param string $bcc. Optional. BCC-Mailadress to send the Mails to
	 * @param string $from. Optional. Sender-Mailadress shown to recipient
	 */
	public function runZentraleReihungstestAnmeldefristAssistenzJob($degreeProgram, $bcc = null, $from = null)
	{
		// Encode Params
		if ($bcc != '')
		{
			// $bcc can be given as null-string, so check that too
			if ($bcc == 'null')
			{
				$bcc = '';
			}
			else
			{
				$bcc = urldecode($bcc);
			}
		}
		if ($from != '')
		{
			$from = urldecode($from);
		}

		// Get placement tests where registration date was yesterday
		$result = $this->ReihungstestModel->checkReachedRegistrationDate($degreeProgram);

        $reachedRegistration_rt_arr = array();

		if (hasData($result))
		{
			$reachedRegistration_rt_arr = $result->retval;
		}
		elseif (isError($result))
		{
			show_error($result->error);
		}

        $applicants_arr = array();

        foreach ($reachedRegistration_rt_arr as $reihungstest)
        {
            $applicants = $this->ReihungstestModel->getApplicantsOfPlacementTestForCronjob($reihungstest->reihungstest_id);

			if (hasData($applicants))
			{
				$applicants_arr = $applicants->retval;
			}
			elseif (isError($applicants))
			{
				show_error($applicants->error);
			}

			// Get all Bachelor-Degree-Programs with Mailadress
			$bachelorStudiengeange = $this->StudiengangModel->loadStudiengaengeFromTyp('b');
			$bachelorStudiengeange_arr = array();

			if (hasData($bachelorStudiengeange))
			{
				$bachelorStudiengeange_arr = $bachelorStudiengeange->retval;
			}
			elseif (isError($bachelorStudiengeange))
			{
				show_error($bachelorStudiengeange->error);
			}

			// If a person ist an applicant of this degree-program send mail with application data
			// Otherwise inform assistant, that no applicant is registered in this test
			foreach ($bachelorStudiengeange_arr as $bachelorStudiengang)
			{
				$studiengang_kuerzel = strtoupper($bachelorStudiengang->typ.$bachelorStudiengang->kurzbz);
				$applicantCounter = 0;
				$mailcontent_data_arr = array();
				foreach ($applicants_arr as $applicant)
				{
					if ($bachelorStudiengang->studiengang_kz == $applicant->studiengang_kz)
					{
						$applicantCounter ++;
					}
				}
				if ($applicantCounter == 0)
				{
					$mailcontent = '<p style="font-family: verdana, sans-serif;">Der Anmeldeschluss für den zentralen Reihungstest am ' . date_format(date_create($reihungstest->datum), 'd.m.Y') . ' um ' . $reihungstest->uhrzeit . ' Uhr wurde gestern erreicht.</p>';
					$mailcontent .= '<p style="font-family: verdana, sans-serif;"><b>Für den Studiengang '.$studiengang_kuerzel.' nehmen keine InteressentInnen an diesem Reihungstest teil</b></p>';
				}
				else
				{
					$mailcontent = '<p style="font-family: verdana, sans-serif;">Der Anmeldeschluss für den zentralen Reihungstest am ' . date_format(date_create($reihungstest->datum), 'd.m.Y') . ' um ' . $reihungstest->uhrzeit . ' Uhr wurde gestern erreicht.</p>';
					$mailcontent .= '
					<p style="font-family: verdana, sans-serif;"><b>' . $applicantCounter . '</b> InteressentIn(nen) des Studiengangs ' . $studiengang_kuerzel . ' nehmen daran teil:</p>
					<p style="font-family: verdana, sans-serif;">
						<a href="'.APP_ROOT.'vilesci/stammdaten/auswertung_fhtw.php?reihungstest='.$reihungstest->reihungstest_id.'&studiengang='.$bachelorStudiengang->studiengang_kz.'" target="_blank">
							Liste der Anmeldungen
						</a>
					</p>';
				}
				$mailcontent_data_arr['table'] = $mailcontent;

				// Send email in Sancho design
				if (!isEmptyString($mailcontent))
				{
					sendSanchoMail(
						'Sancho_ReihungstestteilnehmerJob',
						$mailcontent_data_arr,
						$bachelorStudiengang->email,
						'Anmeldeschluss Reihungstest ' . date_format(date_create($reihungstest->datum), 'd.m.Y') . ' ' . $reihungstest->uhrzeit . ' Uhr',
						'sancho_header_min_bw.jpg',
						'sancho_footer_min_bw.jpg',
						$from,
						'',
						$bcc);
				}
			}
		}
	}

	/*
	 * Checks, if an applicant was assigned to a test after Anmeldefrist and sends an email to all responsible assistants
	 *
	 * @param integer $degreeProgram. Kennzahl of Degree Program to check
	 * @param string $bcc. Optional. BCC-Mailadress to send the Mails to
	 * @param string $from. Optional. Sender-Mailadress shown to recipient
	 */
	public function runZentraleReihungstestNachtraeglichHinzugefuegtJob($degreeProgram, $bcc = null, $from = null)
	{
		// Encode Params
		if ($bcc != '')
		{
			// $bcc can be given as null-string, so check that too
			if ($bcc == 'null')
			{
				$bcc = '';
			}
			else
			{
				$bcc = urldecode($bcc);
			}
		}
		if ($from != '')
		{
			$from = urldecode($from);
		}

		// Get applicants that have been added to a test after Anmeldefrist
		$result = $this->ReihungstestModel->getApplicantAssignedAfterDate($degreeProgram);

        $applicants_after_anmeldefrist_arr = array();

        if (hasData($result))
        {
            $applicants_after_anmeldefrist_arr = $result->retval;
        }
        elseif (isError($result))
        {
            show_error($result->error);
        }

        $studiengang = '';
        $mailReceipients = ''; // String with all mailadresses
        $mailcontent_data_arr = array();
        $headerstyle = 'style="background: #DCE4EF; border: 1px solid #FFF; padding: 4px; text-align: left;"';
        $rowstyle = 'style="background-color: #EEEEEE; padding: 4px;"';
        $mailcontent = '';
        $applicants_list = '';

		if (count($applicants_after_anmeldefrist_arr) > 0)
		{
			foreach ($applicants_after_anmeldefrist_arr as $applicant)
			{
				if ($studiengang != $applicant->studiengang_kz)
				{
					if ($studiengang != '' && $studiengang != $applicant->studiengang_kz)
					{
						$bachelorStudiengang = $this->StudiengangModel->load($studiengang);
						$mailcontent .= $applicants_list;
						$mailcontent .= '</tbody></table>';
						$mailcontent .= '<p style="font-family: verdana, sans-serif;">
											<a href="'.APP_ROOT.'vilesci/stammdaten/auswertung_fhtw.php?reihungstest='.$applicant->reihungstest_id.'&studiengang='.$studiengang.'" target="_blank">
												Liste der Anmeldungen
											</a>
										</p>';
						$mailcontent_data_arr['table'] = $mailcontent;
						sendSanchoMail(
							'Sancho_ReihungstestteilnehmerJob',
							$mailcontent_data_arr,
							$bachelorStudiengang->retval[0]->email,
							'InteressentIn nach Reihungstest-Anmeldeschluss hinzugefügt',
							'sancho_header_min_bw.jpg',
							'sancho_footer_min_bw.jpg',
							$from,
							'',
							$bcc);
						$applicants_list = '';
						$mailcontent_data_arr = array();
					}

					$mailcontent = '<p style="font-family: verdana, sans-serif;">Folgende InteressentInnen wurden <b>nach</b> der Anmeldefrist zu einem Reihungstest hinzugefügt.<br>Details siehe Link</p>';
					$mailcontent .= '
					<table width="100%" style="cellpadding: 3px; font-family: verdana, sans-serif; border: 1px solid #000000;">
						<thead>
						<th ' . $headerstyle . '>Datum des Tests</th>
						<th ' . $headerstyle . '>Uhrzeit des Tests</th>
						<th ' . $headerstyle . '>OrgForm</th>
						<th ' . $headerstyle . '>Semester</th>
						<th ' . $headerstyle . '>Nachname</th>
						<th ' . $headerstyle . '>Vorname</th>
						</thead>
						<tbody>
						';
                }

                $studiengang = $applicant->studiengang_kz;
                $mailReceipients .= $applicant->email . ';';
                $applicants_list .= '
						<tr ' . $rowstyle . '>
						<td>' . date_format(date_create($applicant->datum), 'd.m.Y') . '</td>
						<td>' . $applicant->uhrzeit . '</td>
						<td>' . $applicant->orgform_kurzbz . '</td>
						<td>' . $applicant->ausbildungssemester . '</td>
						<td>' . $applicant->nachname . '</td>
						<td>' . $applicant->vorname . '</td>
						</tr>
					';
			};
			$bachelorStudiengang = $this->StudiengangModel->load($studiengang);
			$mailcontent .= $applicants_list;
			$mailcontent .= '</tbody></table>';
			$mailcontent .= '<p style="font-family: verdana, sans-serif;">
								<a href="'.APP_ROOT.'vilesci/stammdaten/auswertung_fhtw.php?reihungstest='.$applicant->reihungstest_id.'&studiengang='.$studiengang.'" target="_blank">
									Liste der Anmeldungen
								</a>
							</p>';
			$mailcontent_data_arr['table'] = $mailcontent;
			sendSanchoMail(
				'Sancho_ReihungstestteilnehmerJob',
				$mailcontent_data_arr,
				$bachelorStudiengang->retval[0]->email,
				'InteressentIn nach Reihungstest-Anmeldeschluss hinzugefügt',
				'sancho_header_min_bw.jpg',
				'sancho_footer_min_bw.jpg',
				$from,
				'',
				$bcc);
		}
	}

	/*
	 * Sends an email to all applicants of a placement test to remind them 3 working days before
	 *
	 * @param integer $degreeProgram. Kennzahl of Degree Program to check
	 * @param string $bcc. Optional. BCC-Mailadress to send the Mails to
	 * @param string $from. Optional. Sender-Mailadress shown to recipient
	 */
	public function remindApplicantsOfPlacementTest($degreeProgram, $bcc = null, $from = null)
	{
		// Encode Params
		if ($bcc != '')
		{
			// $bcc can be given as null-string, so check that too
			if ($bcc == 'null')
			{
				$bcc = '';
			}
			else
			{
				$bcc = urldecode($bcc);
			}
		}
		if ($from != '')
		{
			$from = urldecode($from);
		}

        // Get placement tests with testdate within the next 2 weeks
        $resultNextTestDates = $this->ReihungstestModel->getNextPlacementtests($degreeProgram, 14);
        if (hasData($resultNextTestDates))
        {
            $nextTestDates = $resultNextTestDates->retval;
            $enddate = '';
            // Loop through the dates
            foreach ($nextTestDates as $testDates)
            {
                $workingdays = 0;
                $testsOndate = array();

                // Deduct days till 3 working days are reached
                for ($i = 1; ; $i++)
                {
                    if (isDateWorkingDay($testDates->datum, $i) === true)
                    {
                        $workingdays++;
                    }
                    if ($workingdays == 3)
                    {
                        $enddate = date("Y-m-d", strtotime("$testDates->datum -" . $i . " days"));
                        break;
                    }
                    else
                    {
                        continue;
                    }
                }

                // If $enddate is today -> load all tests of $testDates->datum
                if (date("Y-m-d", strtotime($enddate)) == date('Y-m-d'))
                {
                    $resultTestsOnDate = $this->ReihungstestModel->getTestsOnDate($testDates->datum, $degreeProgram);

                    if (hasData($resultTestsOnDate))
                    {
                        $testsOndate = $resultTestsOnDate->retval;
                    }
                    elseif (isError($resultTestsOnDate))
                    {
                        show_error($resultTestsOnDate->error);
                    }
                }

                if (!isEmptyArray($testsOndate))
                {
                    foreach ($testsOndate as $reihungstest)
                    {
                        // Loads applicants of a test
                        $applicants = $this->ReihungstestModel->getApplicantsOfPlacementTest($reihungstest->reihungstest_id);

                        if (hasData($applicants))
                        {
                            $applicants_arr = $applicants->retval;
                        }
                        elseif (isError($applicants))
                        {
                            show_error($applicants->error);
                        }

						foreach ($applicants_arr as $applicant)
						{
							$mailcontent_data_arr = array();
							$mailcontent_data_arr['anrede'] = $applicant->anrede;
							$mailcontent_data_arr['nachname'] = $applicant->nachname;
							$mailcontent_data_arr['vorname'] = $applicant->vorname;
							$mailcontent_data_arr['rt_datum'] = date_format(date_create($reihungstest->datum), 'd.m.Y');
							$mailcontent_data_arr['rt_uhrzeit'] = date_format(date_create($reihungstest->uhrzeit), 'H:i');
							$mailcontent_data_arr['rt_raum'] = $applicant->planbezeichnung;
							if ($applicant->lageplan == '')
							{
								$mailcontent_data_arr['wegbeschreibung'] = 'Für diesen Raum liegt noch keine Wegbeschreibung vor.<br><br>No directions were found for this room';
							}
							else
							{
								$mailcontent_data_arr['wegbeschreibung'] = $applicant->lageplan;
							}

                            sendSanchoMail(
                                'Sancho_RemindApplicantsOfTest',
                                $mailcontent_data_arr,
                                $applicant->email,
                                'Ihre Anmeldung zum Reihungstest - Reminder / Your registration for the placement test - Reminder',
                                DEFAULT_SANCHO_HEADER_IMG,
                                DEFAULT_SANCHO_FOOTER_IMG,
                                $from,
                                '',
                                $bcc);
                        }
                    }
                }
            }
        }
    }

    /**
     * This job sends eMail(s) to the relevant stg assistance(s) informing about:
     * All applicants, who have sent new applications AFTER they had absolved a
     * placement test in the actual studiensemester
     * AND who have been confirmed yesterday.
     */
    public function mailNewApplicants()
    {
        // Get yesterdays confirmed applicants for Bachelor-studies
        $this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
        $this->PrestudentstatusModel->addSelect('
            tbl_person.person_id,
            tbl_prestudentstatus.prestudent_id,
            tbl_prestudent.studiengang_kz,
            tbl_prestudentstatus.studiensemester_kurzbz,
            tbl_prestudentstatus.bestaetigtam,
            tbl_prestudentstatus.bewerbung_abgeschicktamum
        ');
        $this->PrestudentstatusModel->addJoin('public.tbl_prestudent', 'prestudent_id');
        $this->PrestudentstatusModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
        $this->PrestudentstatusModel->addJoin('public.tbl_studiengangstyp', 'typ');
        $this->PrestudentstatusModel->addJoin('public.tbl_person', 'person_id');

        $yesterdays_applicants_arr = $this->PrestudentstatusModel->loadWhere('
            status_kurzbz = \'Interessent\' AND
            typ = \'b\' AND
            bestaetigtam = current_date - 1
        ');

        // Retrieve the person_ids of yesterdays confirmed applicants
        $person_id_arr = array();
        if (hasData($yesterdays_applicants_arr))
        {
            foreach ($yesterdays_applicants_arr->retval as $yesterdays_applicant)
            {
                if (isset($yesterdays_applicant->person_id)) {
                    $person_id_arr[] = $yesterdays_applicant->person_id;
                }
            }
        }
        elseif (isError($yesterdays_applicants_arr))
        {
            show_error($yesterdays_applicants_arr->error);
        }

        // Get all other prestudenten of the given persons.
        if (!isEmptyArray($person_id_arr))
        {
            $this->load->model('crm/Prestudent_model', 'PrestudentModel');
            $this->PrestudentModel->addDistinct();
            $this->PrestudentModel->addSelect('
                person_id,
                tbl_reihungstest.studiensemester_kurzbz,
                tbl_reihungstest.reihungstest_id,
                (SELECT(tbl_reihungstest.datum::text || \' \' || tbl_reihungstest.uhrzeit::text)::timestamp) AS reihungstest_timestamp
            ');
            $this->PrestudentModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
            $this->PrestudentModel->addJoin('public.tbl_studiengangstyp', 'typ');
            $this->PrestudentModel->addJoin('public.tbl_prestudentstatus', 'prestudent_id');
            $this->PrestudentModel->addJoin('public.tbl_person', 'person_id');
            $this->PrestudentModel->addJoin('public.tbl_rt_person', 'person_id');
            $this->PrestudentModel->addJoin('public.tbl_reihungstest', 'tbl_reihungstest.reihungstest_id = tbl_rt_person.rt_id');

            // Store them, if they have already absolved a placement test in the same study term they have applied for.
            $placement_absolvents_arr = $this->PrestudentModel->loadWhere('
                person_id IN (' . implode(', ', $person_id_arr) . ') AND
                typ = \'b\' AND 
                teilgenommen = \'t\' AND
                tbl_reihungstest.studiensemester_kurzbz IN (
                    SELECT
                        studiensemester_kurzbz
                    FROM
                        public.tbl_studiensemester
                    WHERE
                        ende >= now()
                )
            ');
        }

        // Store data to be send in the email-link
        $result_arr = array();
        foreach($yesterdays_applicants_arr->retval as $yesterdays_applicant)
        {
            foreach ($placement_absolvents_arr->retval as $placement_absolvent)
            {
                if ($yesterdays_applicant->person_id == $placement_absolvent->person_id &&
                    $yesterdays_applicant->studiensemester_kurzbz == $placement_absolvent->studiensemester_kurzbz &&
                    $yesterdays_applicant->bewerbung_abgeschicktamum >= $placement_absolvent->reihungstest_timestamp)
                {
                    $obj = new stdClass();
                    $obj->prestudent_id = $yesterdays_applicant->prestudent_id;     // prestudent_id of the yesterdays applicant
                    $obj->studiengang_kz = $yesterdays_applicant->studiengang_kz;   // study program of interest of the yesterdays applicant
                    $obj->reihungstest_id = $placement_absolvent->reihungstest_id;  // reihungstest_id of absolved reihungstest of that person

                    $result_arr[]= $obj;
                }
            }
        }

        // Sort by STG. This is important to send the mails clustered by STG to the different STG assistances.
        usort($result_arr, function ($a, $b)
        {
            if ($a->studiengang_kz == $b->studiengang_kz) {
                return 0;
            }
            return ($a->studiengang_kz < $b->studiengang_kz) ? -1 : 1;
        });

        $to = ''; // mail recipient (stg assistance)
        $content_arr = array(); // url paths to the new applicants
        $base_link = base_url('vilesci/stammdaten/auswertung_fhtw.php');
       
        $i = 0; // loop counter
        $len = count($result_arr);

        // Loop trough list of new applicants
        foreach($result_arr as $result)
        {
            $studiengang = $this->StudiengangModel->load($result->studiengang_kz);
            $mail_stg_assistance = $studiengang->retval[0]->email;

            // If first loop
            if ($i == 0)
            {
                $to = $mail_stg_assistance; // set recipient initially
            }

            // If new study is encountered but is not the first loop
            if ($to != $mail_stg_assistance && $i != 0)
            {
                // Prepare content for mail template
                $content_data_arr = $this->_getContentDataNewApplicant($content_arr);
                
                // Send mail
                 sendSanchoMail(
                     'BewerberNachReihungstest',
                     $content_data_arr,
                     $to,
                     'Neue Bewerbungen nach absolviertem Reihungstest',
                     'sancho_header_min_bw.jpg',
                     'sancho_footer_min_bw.jpg'
                 );

                 // Reset content for new study applicants & reset recipient (new stg assistance)
                $content_arr = array($base_link. '?reihungstest='. $result->reihungstest_id. '&prestudent_id='. $result->prestudent_id);
                $to = $mail_stg_assistance;
            }
            // If same study
            else
            {
                // just add content
                $content_arr[]= $base_link. '?reihungstest='. $result->reihungstest_id. '&prestudent_id='. $result->prestudent_id; // add to content
            }

            // If last loop
            if (($i == $len - 1))
            {
                // Prepare content for mail template
                $content_data_arr = $this->_getContentDataNewApplicant($content_arr);

                // Send mail
                  sendSanchoMail(
                      'BewerberNachReihungstest',
                      $content_data_arr,
                      $to,
                      'Neue Bewerbungen nach absolviertem Reihungstest',
                      'sancho_header_min_bw.jpg',
                      'sancho_footer_min_bw.jpg'
                  );
            }
            $i++; // iterate counter
        }
    }

    // ------------------------------------------------------------------------
    // Private methods
    /**
     * Returns associative array with data as needed in the reihungstest job template.
     * @param array $missing_rt_arr Array with studienpläne, which have no assigned placement tests.
     * @param array $free_places_arr Array with info and amount of free placement test places.
     * @return array
     */
    private function _getContentData($missing_rt_arr, $free_places_arr)
    {
        $style_tbl1 = ' cellpadding="0" cellspacing="10" width="100%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border: 1px solid #000000;" ';
        $style_tbl2 = ' cellpadding="0" cellspacing="20" width="100%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border: 1px solid #000000;" ';

        // Prepare HTML table with study plans that have no placement tests yet
        if (!empty($missing_rt_arr)) {
            $studienplan_list
                = '
				<table' . $style_tbl2 . '>
			';

            foreach ($missing_rt_arr as $rt) {
                $studienplan_list .= '
					<tr><td>' . $rt->bezeichnung . '</td></tr>
				';
            }

            $studienplan_list .= '
				</table>
			';
        } else {
            $studienplan_list = '
				<table' . $style_tbl1 . '>
					<tr><td>Alles okay! Alle Studienpläne haben zumindest einen Reihungstest.</td></tr>
				</table>
			';
        }

        // Prepare HTML table with information and amount of free places
        if (!empty($free_places_arr)) {
            $freie_plaetze_list = '
				<table' . $style_tbl2 . '>
					<tr>
						<th>Fakultät</th>
						<th>Reihungstesttermine</th>
						<th>Freie Plätze</th>
					</tr>
			';

            foreach ($free_places_arr as $free_place) {
                $datum = new DateTime($free_place->datum);
                $style_alarm = ($free_place->freie_plaetze <= 5) ? ' style=" color: red; font-weight: bold" ' : '';    // mark if <=5 free places

                $freie_plaetze_list .= '
					<tr>
						<td width="350">' . $free_place->fakultaet . '</td>
						<td align="center">' . $datum->format('d.m.Y') . '</td>
						<td align="center"' . $style_alarm . '>' . $free_place->freie_plaetze . '</td>
					</tr>
				';
            }

            $freie_plaetze_list .= '
				</table>
			';
        } else {
            $freie_plaetze_list = '
				<table' . $style_tbl1 . '>
					<tr><td>Es gibt heute keine Ergebnisse zu freien Reihungstestplätze.</td></tr>
				</table>
			';
        }

        // Set associative array with the prepared HTML tables and URL be used by the template's variables
        $content_data_arr['studienplan_list'] = $studienplan_list;
        $content_data_arr['freie_plaetze_list'] = $freie_plaetze_list;
        $content_data_arr['link'] = site_url('/organisation/Reihungstest');

        return $content_data_arr;
    }

    /**
     * Returns associative array with data as needed in the BewerberNachReihungstest-template.
     * @param array $content_arr Array with links to the testtool evaluation page of the new applicants.
     * @return array
     */
    private function _getContentDataNewApplicant($content_arr)
    {
        $content = '';
        $counter = 1;
        foreach ($content_arr as $row)
        {
            $content .= '<br>Link zu: <a href="'. $row. '">Bewerber '. $counter. '</a>';
            $counter++;
        }

        $content_data_arr['link'] = $content;
        return $content_data_arr;
    }


    /**
     * Checks the upcoming placement tests if there are correct studyplans assigned
     * If there are invalid studyplans assigned (outdated because there exists a new version),
     * it tries to find a better one and assigns it additionaly
     */
    public function correctStudienplan()
    {
        // get all placement tests with incorrect studyplan
        $qry = "
		SELECT
			tbl_reihungstest.reihungstest_id,
			tbl_studienplan.studienplan_id,
			tbl_reihungstest.studiensemester_kurzbz,
			tbl_studienordnung.studiengang_kz
		FROM
			public.tbl_reihungstest
			JOIN public.tbl_rt_studienplan ON(tbl_rt_studienplan.reihungstest_id=tbl_reihungstest.reihungstest_id)
			JOIN lehre.tbl_studienplan USING(studienplan_id)
			JOIN lehre.tbl_studienordnung USING(studienordnung_id)
		WHERE
			NOT EXISTS(
				SELECT 1 FROM lehre.tbl_studienplan_semester
				WHERE studienplan_id=tbl_rt_studienplan.studienplan_id
					AND tbl_studienplan_semester.studiensemester_kurzbz=tbl_reihungstest.studiensemester_kurzbz
			)
			AND tbl_reihungstest.datum >= now()
			AND NOT EXISTS(
				SELECT
					1
				FROM
					public.tbl_rt_studienplan rtstp
					JOIN lehre.tbl_studienplan stp USING(studienplan_id)
					JOIN lehre.tbl_studienordnung sto USING(studienordnung_id)
					JOIN lehre.tbl_studienplan_semester stpsem USING(studienplan_id)
				WHERE
					sto.studiengang_kz=tbl_studienordnung.studiengang_kz
					AND rtstp.reihungstest_id=tbl_reihungstest.reihungstest_id
					AND stpsem.studiensemester_kurzbz=tbl_reihungstest.studiensemester_kurzbz
			)
		";

        $db = new DB_Model();
        $result_rt = $db->execReadOnlyQuery($qry);

        if (hasdata($result_rt)) {
            foreach ($result_rt->retval as $row_rt) {
                // find an active studyplan for the same degree program with is valid in this semester
                $result_stpl = $this->StudienplanModel->getStudienplaeneBySemester(
                    $row_rt->studiengang_kz,
                    $row_rt->studiensemester_kurzbz
                );

                if (hasData($result_stpl)) {
                    foreach ($result_stpl->retval as $row_stpl) {
                        // Add new Studyplan to RtStudienplan if missing
                        $rt_studienplan = $this->RtStudienplanModel->loadWhere(array(
                            "reihungstest_id" => $row_rt->reihungstest_id,
                            "studienplan_id" => $row_stpl->studienplan_id
                        ));

                        if (!hasData($rt_studienplan)) {
                            echo "\nAdding StudienplanId: $row_stpl->studienplan_id";
                            echo " to ReihungstestId: $row_rt->reihungstest_id";

                            $this->RtStudienplanModel->insert(array(
                                "reihungstest_id" => $row_rt->reihungstest_id,
                                "studienplan_id" => $row_stpl->studienplan_id
                            ));
                        }
                    }
                }
            }
        }
    }
}

