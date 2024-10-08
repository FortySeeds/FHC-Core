<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> 
 *
 */
/**
 * Klasse fuer Authentifizierung fuer die DEMO Seite
 * Fuer Testzugänge ohne LDAP Server
 */

require_once(dirname(__FILE__).'/basis.class.php');

class authentication extends auth
{

	public function login($username)
	{
		// Nicht noetig da dies ueber htaccess gesteuert wird
	}

	public function getUser()
	{
		 // derzeit get_uid in functions.inc.php
		if(isset($_SERVER['REMOTE_USER']))
		{
			return mb_strtolower(trim($_SERVER['REMOTE_USER']));
		}
		else
		{
			if(isset($_SESSION['user']))
				return mb_strtolower($_SESSION['user']);
			else
				return $this->RequireLogin();
		}
	}

	// derzeit checkldapuser in functions.inc.php bzw per htaccess
	public function checkpassword($username, $passwort)
	{
		var_dump($username);
		if ($passwort=='1q2w3' 
			&& ($username=='pam'
			|| $username=='admin'
			|| $username=='assistenz1'
			|| $username=='assistenz2'
			|| $username=='assistenz2'
			|| $username=='student1'
			|| $username=='student2'
			|| $username=='student3'
			|| $username=='gl1'
			|| $username=='gl2'
			|| $username=='lektor1'
			|| $username=='lektor2'
			|| $username=='lektor3'
			|| $username=='aufnahme')
			)
			{
				$_SERVER['PHP_AUTH_USER']=$username;
				return true;
			}
		else
			return false;
	}

	/**
	 * Prueft ob der User extern (zB im LDAP) angelegt ist
	 * @param $username UID des Users
	 * @return boolean true wenn vorhanden, sonst false
	 */
	public function UserExternalExists($username)
	{
		return true;
	}

	// derzeit manual_basic_auth in functions.inc.php eventuell 
	// direkt von getUser aus aufrufen wenn nicht authentifiziert
	public function RequireLogin()
	{
		if(!(isset($_SERVER['PHP_AUTH_USER']) && $this->checkpassword($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])))
		{
			header('WWW-Authenticate: Basic realm="'.AUTH_NAME.'"');
			header('HTTP/1.0 401 Unauthorized');
			echo "Ihre Zugangsdaten sind ungueltig!";
			exit;
		}
		else
		{
			return mb_strtolower($_SERVER['PHP_AUTH_USER']);
		}
	}

	public function isUserLoggedIn()
	{
		if(isset($_SERVER['PHP_AUTH_USER']) && $this->checkpassword($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']))
			return true;
		else
			return false;
	}

	public function getOriginalUser()
	{
		if(isset($_SERVER['REMOTE_USER']))
			return mb_strtolower(trim($_SERVER['REMOTE_USER']));
		else
		{
			if(isset($_SESSION['user_original']))
				return $_SESSION['user_original'];
		}
	}

	public function loginAsUser($username)
	{
		$_SESSION['user']=$username;
		return true;
	}
	
	public function logout()
	{
		echo "LOGOUT BEI DEMO AUTH NICHT MÖGLICH";
	}
}
?>
