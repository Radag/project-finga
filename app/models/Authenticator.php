<?php

namespace App\Model;


use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette;
use Tracy\Debugger;

/**
 * Users authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class Authenticator implements \Nette\Security\IAuthenticator
{
	use \Nette\SmartObject;


	/** @var \Dibi\Connection  */
	protected $db;

	public function __construct(
		\Dibi\Connection $db
	)
	{
		$this->db = $db;
	}

	public function authenticate(array $credentials): IIdentity
	{
		$passwords = new Passwords();
		list($email, $password) = $credentials;
		$row = $this->db->fetch("SELECT * FROM FINGA_USERS WHERE EMAIL=?", $email);
		if (!$row) {
			throw new Nette\Security\AuthenticationException('Špatné uživatelské jméno nebo heslo.', self::IDENTITY_NOT_FOUND);
		} else {
			if(strlen($row->PASSWORD) > 41) {
				if (!$passwords->verify($password, $row->PASSWORD)) {
					throw new Nette\Security\AuthenticationException('Špatné uživatelské jméno nebo heslo.', self::INVALID_CREDENTIAL);
				} elseif ($passwords->needsRehash($row->PASSWORD)) {
					$this->db->query("UPDATE FINGA_USERS ", ['PASSWORD' => $passwords->hash($password)], "WHERE ID_USER=?", $row->ID_USER);
				}
			} else {
				if (!md5($password . str_repeat('fingaboard', 10)) != $row->PASSWORD) {
					throw new Nette\Security\AuthenticationException('Špatné uživatelské jméno nebo heslo.', self::INVALID_CREDENTIAL);
				}
			}
		}

		$arr = $row->toArray();
		unset($arr['PASSWORD']);
		return new Nette\Security\Identity($row->ID_USER, 'user', $arr);
	}

}
