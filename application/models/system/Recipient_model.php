<?php
/**
 * Copyright (C) 2023 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class Recipient_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_msg_recipient';
		$this->pk = array('person_id', 'message_id');
		$this->hasSequence = false;
	}

	/**
	 * Get data for a received message
	 */
	public function getMessage($message_id, $person_id)
	{
		$query = 'SELECT mr.message_id,
						 mr.person_id,
						 mm.subject,
						 mm.body,
						 ks.kontakt,
						 p.nachname,
						 p.vorname,
						 b.uid,
						 mr.sent
					FROM public.tbl_msg_recipient mr INNER JOIN public.tbl_msg_message mm USING (message_id)
						INNER JOIN public.tbl_person p ON (mm.person_id = p.person_id)
						LEFT JOIN public.tbl_benutzer b ON (mr.person_id = b.person_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = \'email\'
						) ks ON (ks.person_id = mr.person_id)
				   WHERE mr.message_id = ? AND mr.person_id = ?';

		$parametersArray = array($message_id, $person_id);

		// Get data of the messages to sent
		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * Get a received message identified by token
	 */
	public function getMessageByToken($token)
	{
		$sql = 'SELECT r.message_id,
						m.person_id as sender_id,
						r.person_id as receiver_id,
						r.sent,
						m.subject,
						m.body,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						s.status,
						s.statusinfo,
						s.insertamum as statusamum,
						b.uid
				  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_message m USING (message_id)
						JOIN (
							SELECT * FROM public.tbl_msg_status WHERE status < ? ORDER BY insertamum DESC, status DESC
						) s ON (r.message_id = s.message_id AND r.person_id = s.person_id),
						LEFT JOIN public.tbl_benutzer b USING(person_id)
				 WHERE r.token = ?
				 LIMIT 1';

		return $this->execQuery($sql, array(MSG_STATUS_DELETED, $token));
	}

	/**
	 * Get all received messages for a person identified by person_id
	 */
	public function getMessagesByPerson($person_id, $oe_kurzbz, $all)
	{
		$sql = 'SELECT DISTINCT ON (r.message_id) r.message_id,
						m.person_id,
						m.subject,
						m.body,
						m.priority,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						p.anrede,
						p.titelpost,
						p.titelpre,
						p.nachname,
						p.vorname,
						p.vornamen,
						s.status,
						s.statusinfo,
						s.insertamum AS statusamum
				  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_message m USING (message_id)
						JOIN public.tbl_person p ON (p.person_id = m.person_id)
						JOIN (
							SELECT message_id, person_id, status, statusinfo, insertamum
							  FROM public.tbl_msg_status
							 %s
						  ORDER BY insertamum DESC
						) s ON (m.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE r.person_id = ?';

		$parametersArray = array($person_id);

		if ($all == 'true')
		{
			$sql = sprintf($sql, '');
		}
		else
		{
			array_push($parametersArray, $person_id, $person_id);
			$sql = sprintf($sql, 'WHERE person_id = ? AND message_id NOT IN (SELECT message_id FROM public.tbl_msg_status WHERE status >= 3 AND person_id = ?)');
		}

		if ($oe_kurzbz != null)
		{
			array_push($parametersArray, $oe_kurzbz);
			$sql .= ' AND m.oe_kurzbz IN (
						WITH RECURSIVE organizations(_pk, _ppk) AS
							(
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o
								 WHERE o.oe_kurzbz = ?
							 UNION ALL
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
							)
							SELECT orgs._pk
							FROM organizations orgs
						)';
		}

		$sql .= ' ORDER BY r.message_id DESC, s.status DESC';

		return $this->execQuery($sql, $parametersArray);
	}

	/**
	 * Get all received messages for a person identified by uid
	 */
	public function getMessagesByUID($uid, $oe_kurzbz, $all)
	{
		// get Data
		$sql = 'SELECT DISTINCT ON (r.message_id) r.message_id,
						m.person_id,
						m.subject,
						m.body,
						m.priority,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						p.anrede,
						p.titelpost,
						p.titelpre,
						p.nachname,
						p.vorname,
						p.vornamen,
						s.status,
						s.statusinfo,
						s.insertamum AS statusamum
				  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_message m USING (message_id)
						JOIN public.tbl_person p ON (r.person_id = p.person_id)
						JOIN public.tbl_benutzer b ON (r.person_id = b.person_id)
						JOIN (
							SELECT message_id, person_id, status, statusinfo, insertamum
							  FROM public.tbl_msg_status
						  ORDER BY insertamum DESC
						) s ON (m.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE b.uid = ?';

		$parametersArray = array($uid);

		if ($all == 'true')
		{
			$sql .= ' AND (status < 3 OR status IS NULL)';
		}

		if ($oe_kurzbz != null)
		{
			array_push($parametersArray, $oe_kurzbz);
			$sql .= ' AND m.oe_kurzbz IN (
						WITH RECURSIVE organizations(_pk, _ppk) AS
							(
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o
								 WHERE o.oe_kurzbz = ?
							 UNION ALL
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
							)
							SELECT orgs._pk
							FROM organizations orgs
						)';
		}

		return $this->execQuery($sql, $parametersArray);
	}

	/**
	 * Gets all messages for which notice emails are still not sent
	 *
	 * @param kontaktType specifies the type of the kontakt to get (email,...)
	 * @param limit specifies the max number of messages to get
	 * @param since specifies from which date messages have to be retrieved
	 */
	public function getNotSentMessages($limit, $since)
	{
		$query = 'SELECT mm.message_id
					FROM public.tbl_msg_recipient mr
					JOIN public.tbl_msg_message mm USING (message_id)
				   WHERE mr.sent IS NULL
				   	 AND mr.sentinfo IS NULL
				     AND mm.insertamum > ?
				ORDER BY mr.insertamum ASC
				   LIMIT ?';

		return $this->execQuery($query, array($since, $limit));
	}

	/**
	 * Get all unread messages for a person identified by person_id
	 */
	public function getCountUnreadMessages($person_id, $oe_kurzbz)
	{
		$sql = 'SELECT COUNT(r.message_id) AS unreadMessages
				  FROM public.tbl_msg_recipient r
				  JOIN public.tbl_msg_status s ON (r.message_id = s.message_id AND r.person_id = s.person_id)
				  JOIN public.tbl_msg_message m ON (r.message_id = m.message_id)
				 WHERE r.person_id = ?
				   AND s.status = ?
				   AND r.message_id NOT IN (
							SELECT r.message_id
							  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_status s
								ON (r.message_id = s.message_id AND r.person_id = s.person_id)
							 WHERE r.person_id = ?
							   AND s.status > ?
						)';

		$parametersArray = array($person_id, MSG_STATUS_UNREAD, $person_id, MSG_STATUS_UNREAD);

		if ($oe_kurzbz != null)
		{
			array_push($parametersArray, $oe_kurzbz);
			$sql .= ' AND m.oe_kurzbz IN (
						WITH RECURSIVE organizations(_pk, _ppk) AS
							(
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o
								 WHERE o.oe_kurzbz = ?
							 UNION ALL
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
							)
							SELECT orgs._pk
							FROM organizations orgs
						)';
		}

		return $this->execQuery($sql, $parametersArray);
	}

	/**
	 * - Gets the directly recieved messages using the given person id
	 * - Gets the recieved messages from an organisation unit where this person plays a role given by the parameter functions
	 */
	public function getReceivedMessages($person_id, $functions)
	{
		return $this->execQuery(
			$this->_getReceivedMessagesQuery().' ORDER BY sent DESC',
			array(
				$person_id,
				$functions,
				$person_id
			)
		);
	}

	/**
	 * Gets all the sent message by the given person
	 */
	public function getSentMessages($person_id)
	{
		return $this->execQuery($this->_getSentMessagesQuery().' ORDER BY sent DESC', array($person_id));
	}

	/**
	 *
	 */
	public function getMessagesById($messageIds)
	{
		$sql = 'SELECT mm.message_id,
						mm.person_id AS sender_id,
						mm.subject,
						mm.body,
						mm.relationmessage_id,
						mm.oe_kurzbz AS sender_ou,
						mr.person_id AS receiver_id,
						mr.token,
						mr.sent,
						mr.sentinfo,
						mr.oe_kurzbz AS receiver_ou
				  FROM public.tbl_msg_message mm
				  JOIN public.tbl_msg_recipient mr ON (mr.message_id = mm.message_id)
				 WHERE mm.message_id IN ?';

		return $this->execQuery($sql, array($messageIds));
	}

	/**
	 *
	 */
	public function getReceivedAndSentMessages($person_id, $functions)
	{
		return $this->execQuery(
			$this->_getReceivedMessagesQuery().
			' UNION '.
			$this->_getSentMessagesQuery().
			' ORDER BY sent DESC',
			array(
				$person_id,
				$functions,
				$person_id,
				$person_id
			)
		);
	}

	// -------------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Returns the query used to get the sent messages for a given person
	 */
	private function _getSentMessagesQuery()
	{
		return '-- Messages sent from the given person
			SELECT mm.message_id,
				mm.relationmessage_id,
				mm.subject,
				mm.body,
				mm.insertamum AS sent,
				pr.person_id AS "recipientPersonId",
				pr.vorname AS "recipientName",
				pr.nachname AS "recipientSurname",
				ps.person_id AS "senderPersonId",
				ps.vorname AS "senderName",
				ps.nachname AS "senderSurname",
				(SELECT MAX(status) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatus",
				(SELECT MAX(insertamum) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatusDate",
				oe.oe_kurzbz AS "oeId",
				COALESCE(sg.bezeichnung, oe.bezeichnung) AS oe,
				mr.token
			  FROM public.tbl_msg_message mm
			  JOIN public.tbl_msg_recipient mr ON (mr.message_id = mm.message_id)
			  JOIN public.tbl_person pr ON (pr.person_id = mr.person_id)
			  JOIN public.tbl_person ps ON (ps.person_id = mm.person_id)
		     LEFT JOIN public.tbl_organisationseinheit oe ON (oe.oe_kurzbz = mr.oe_kurzbz)
		     LEFT JOIN public.tbl_studiengang sg ON (sg.oe_kurzbz = mr.oe_kurzbz)
			 WHERE mm.person_id = ?
		      GROUP BY mm.message_id,
		  		mm.relationmessage_id,
				mm.subject,
				mm.body,
				mm.insertamum,
				pr.person_id,
				pr.vorname,
				pr.nachname,
				ps.person_id,
				ps.vorname,
				ps.nachname,
				"lastStatus",
				"lastStatusDate",
				oe.oe_kurzbz,
				oe,
				mr.token';
	}

	/**
	 * Returns the query used to get the received messages for a given person
	 */
	private function _getReceivedMessagesQuery()
	{
		return '-- Messages sent directly to the person
			SELECT mr.message_id,
				mm.relationmessage_id,
				mm.subject,
				mm.body,
				mm.insertamum AS sent,
				pr.person_id AS "recipientPersonId",
				pr.vorname AS "recipientName",
				pr.nachname AS "recipientSurname",
				ps.person_id AS "senderPersonId",
				ps.vorname AS "senderName",
				ps.nachname AS "senderSurname",
				(SELECT MAX(status) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatus",
				(SELECT MAX(insertamum) FROM public.tbl_msg_status WHERE message_id = mm.message_id AND person_id = mr.person_id) AS "lastStatusDate",
				oe.oe_kurzbz AS "oeId",
				COALESCE(sg.bezeichnung, oe.bezeichnung) AS oe,
				mr.token
			  FROM public.tbl_msg_recipient mr
			  JOIN public.tbl_msg_message mm ON (mm.message_id = mr.message_id)
			  JOIN public.tbl_person ps ON (ps.person_id = mm.person_id)
			  JOIN public.tbl_person pr ON (pr.person_id = mr.person_id)
		     LEFT JOIN public.tbl_organisationseinheit oe ON (oe.oe_kurzbz = mm.oe_kurzbz)
		     LEFT JOIN public.tbl_studiengang sg ON (sg.oe_kurzbz = mm.oe_kurzbz)
			 WHERE mr.person_id = ?
		      GROUP BY mr.message_id,
		  		mm.relationmessage_id,
				mm.subject,
				mm.body,
				mm.insertamum,
				pr.person_id,
				pr.vorname,
				pr.nachname,
				ps.person_id,
				ps.vorname,
				ps.nachname,
				"lastStatus",
				"lastStatusDate",
				oe.oe_kurzbz,
				oe,
				mr.token
			 UNION
			-- Messages sent to a person that belongs to the recipient organisation unit
			SELECT mrou.message_id,
				mm.relationmessage_id,
				mm.subject,
				mm.body,
				mm.insertamum AS sent,
				pr.person_id AS "recipientPersonId",
				pr.vorname AS "recipientName",
				pr.nachname AS "recipientSurname",
				ps.person_id AS "senderPersonId",
				ps.vorname AS "senderName",
				ps.nachname AS "senderSurname",
				(SELECT MAX(status) FROM public.tbl_msg_status WHERE message_id = mrou.message_id AND person_id = mrou.person_id) AS "lastStatus",
				(SELECT MAX(insertamum) FROM public.tbl_msg_status WHERE message_id = mrou.message_id AND person_id = mrou.person_id) AS "lastStatusDate",
				oe.oe_kurzbz AS "oeId",
				COALESCE(sg.bezeichnung, oe.bezeichnung) AS oe,
				mrou.token
			  FROM public.tbl_person p
			  JOIN public.tbl_benutzer b ON (b.person_id = p.person_id)
			  JOIN (
			  	SELECT uid, oe_kurzbz
				  FROM public.tbl_benutzerfunktion
				 WHERE (datum_von IS NULL OR datum_von <= NOW())
			  	   AND (datum_bis IS NULL OR datum_bis >= NOW())
				   AND funktion_kurzbz IN ?
			  ) bf ON (bf.uid = b.uid)
			  JOIN public.tbl_msg_recipient mrou ON (mrou.oe_kurzbz = bf.oe_kurzbz)
			  JOIN public.tbl_msg_message mm ON (mm.message_id = mrou.message_id)
			  JOIN public.tbl_person ps ON (ps.person_id = mm.person_id)
			  JOIN public.tbl_person pr ON (pr.person_id = mrou.person_id)
		     LEFT JOIN public.tbl_organisationseinheit oe ON (oe.oe_kurzbz = mrou.oe_kurzbz)
		     LEFT JOIN public.tbl_studiengang sg ON (sg.oe_kurzbz = mrou.oe_kurzbz)
			 WHERE p.person_id = ?
		      GROUP BY mrou.message_id,
		  		mm.relationmessage_id,
				mm.subject,
				mm.body,
				mm.insertamum,
				pr.person_id,
				pr.vorname,
				pr.nachname,
				ps.person_id,
				ps.vorname,
				ps.nachname,
				"lastStatus",
				"lastStatusDate",
				oe.oe_kurzbz,
				oe,
				mrou.token';
	}
}

