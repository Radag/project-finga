<?php

namespace App\Model;

use App\Constant;
use Nette\Security\Passwords;
use Tracy\Debugger;

/**
 * Model base class.
 */
class UserModel extends BaseModel
{

    public function insertUser($values)
    {
		$passwords = new Passwords();
		$this->db->query('INSERT INTO FINGA_USERS', array(
            'EMAIL' => $values['email'],
            'PASSWORD' => $passwords->hash( $values['password1']),
            'ROLE' => 0,
            'NAME' => $values['name'],
            'SURNAME' => $values['surname'],
            'STREET' => $values['street'],
            'CITY' => $values['city'],
            'ZIP' => $values['zip'],
            'PHONE' => $values['phone'],
            'DELIVERY' => $values['delivery'],
            'D_NAME' => $values['d_name'],
            'D_SURNAME' => $values['d_surname'],
            'D_STREET' => $values['d_street'],
            'D_CITY' => $values['d_city'],
            'D_ZIP' => $values['d_zip'],
            'STATE' => $values['state'],
            'D_STATE' => $values['d_state'],
            'STATUS' => Constant::USER_ORDINARY,));
    }
    
    public function editUser($values)
    {
		$this->db->query('UPDATE FINGA_USERS SET', array(
            'EMAIL' => $values['email'],
            'NAME' => $values['name'],
            'SURNAME' => $values['surname'],
            'STREET' => $values['street'],
            'CITY' => $values['city'],
            'ZIP' => $values['zip'],
            'PHONE' => $values['phone'],
            'DELIVERY' => $values['delivery'],
            'D_NAME' => $values['d_name'],
            'D_SURNAME' => $values['d_surname'],
            'D_STREET' => $values['d_street'],
            'D_CITY' => $values['d_city'],
            'D_ZIP' => $values['d_zip'],
            'STATE' => $values['state'],
            'D_STATE' => $values['d_state']),
          'WHERE ID_USER=%i', $values['id_user']);
    }
    
    public function editUserProfile($values, $idUser)
    {
		$this->db->begin();
		$this->db->query('UPDATE FINGA_USERS SET', array(
            'EMAIL' => $values['email'],
            'NAME' => $values['name'],
            'SURNAME' => $values['surname'],
            'STREET' => $values['street'],
            'CITY' => $values['city'],
            'ZIP' => $values['zip'],
            'PHONE' => $values['phone'],
            'DELIVERY' => $values['delivery'],
            'D_NAME' => $values['d_name'],
            'D_SURNAME' => $values['d_surname'],
            'D_STREET' => $values['d_street'],
            'D_CITY' => $values['d_city'],
            'D_ZIP' => $values['d_zip'],
            'STATE' => $values['state'],
            'D_STATE' => $values['d_state']),
          'WHERE ID_USER=%i', $idUser);
        
        if (!empty($values['password1'])) {
            dibi::query('UPDATE FINGA_USERS SET', array(
                'PASSWORD' => array('SHA1(%s)', $values['password1'])),
            'WHERE ID_USER=%i', $idUser); 
        }

		$this->db->commit();
    }
    
    
    public function checkUserPassword($password) {
        $data = $this->user->getIdentity()->getData();
        $row = $this->db->query('SELECT * FROM FINGA_USERS WHERE EMAIL=%s AND PASSWORD=%s AND STATUS!=%i', $data['EMAIL'], sha1($password), Constant::USER_DELETED)->fetch();
        return (!empty($row));
        
    }
    
    public function getUserDetail($idUser) {
        return $this->db->query('SELECT * FROM FINGA_USERS WHERE ID_USER=%i', $idUser)->fetch();
    }

    private function getUserFilter($filter)
	{
		$status = '';
		if ($filter['active']==1) {
			$status = Constant::USER_ORDINARY;
		}
		if ($filter['admin']==1) {
			$status === '' ? $status = $status . Constant::USER_ADMIN : $status = $status . ',' . Constant::USER_ADMIN;
		}
		if ($filter['deleted']==1) {
			$status === '' ? $status = $status . Constant::USER_DELETED : $status = $status . ',' . Constant::USER_DELETED;
		}
		return $status;
	}

	public function getAllUsersCount($filter)
	{
		return $this->db->query('SELECT count(*) FROM FINGA_USERS WHERE STATUS IN (' . $this->getUserFilter($filter) . ')')->fetchSingle();
	}

    public function getAllUsers($filter, $items, $offset)
	{
        return $this->db->query('SELECT * FROM FINGA_USERS WHERE STATUS IN (' . $this->getUserFilter($filter) . ') LIMIT ? OFFSET ?', $items, $offset)->fetchAll();
    }
    
    public function setUserStatus($id, $status) {
		$this->db->query('UPDATE FINGA_USERS SET STATUS=%i WHERE ID_USER=%i', $status, $id);
    }
    
    public function isAdmin($id) {
        $status = $this->db->query('SELECT STATUS FROM FINGA_USERS WHERE ID_USER=%i', $id)->fetchSingle();
        return ($status == Constant::USER_ADMIN);
    }
    
    public function isValidEmail($email) {
        return $this->db->query("SELECT STATUS FROM FINGA_USERS WHERE EMAIL=%s", $email)->fetchSingle();
    }
    
    public function generateLostPassCode($email) {
        $id = TRUE;
        while ($id!=FALSE) {
            $code = base_convert(mt_rand(), 10, 20);
            $id = $this->db->query('SELECT ID_USER FROM FINGA_USERS WHERE LOST_PASS_CODE=%s',$code)->fetchSingle();
        }
		$this->db->query('UPDATE FINGA_USERS SET LOST_PASS_CODE=%s WHERE EMAIL=%s',$code, $email);
        return $code;
    }
    
    public function resetPassword($code) {
        $values['email'] = $this->db->query('SELECT EMAIL FROM FINGA_USERS WHERE LOST_PASS_CODE=%s',$code)->fetchSingle();
        if(!empty($values['email'])) {
            $values['password'] = base_convert(mt_rand(), 10, 36);
			$passwords = new Passwords();
			$this->db->query('UPDATE FINGA_USERS SET PASSWORD=%s WHERE LOST_PASS_CODE=%s', $passwords->hash( $values['password']), $code);
        }
        return $values;
    }
    
    public function existUserEmail($email) {
        if ($this->user->isLoggedIn()) {
            $data = $this->user->getIdentity()->getData();
        } else {
            $data['EMAIL'] = "";
        }
        $id = $this->db->query('SELECT ID_USER FROM FINGA_USERS WHERE UPPER(EMAIL)=%s AND EMAIL!=%s', strtoupper($email), $data['EMAIL'])->fetchSingle();
        return (!empty($id));
    }
}
