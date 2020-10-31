<?php

namespace App\Model;

use App\Constant;
/**
 * Model base class.
 */
class OtherModel extends BaseModel
{

    public function insertPoster($values, $lang) {
		$this->db->begin();
		$this->db->query('UPDATE FINGA_POSTERS SET ACTIVE=0 WHERE LANG=%s', $lang);
		$this->db->query('INSERT INTO FINGA_POSTERS', array(
            'NAME' => $values['name'],
            'ID_IMAGE' => $values['image'],
            'ID_IMAGE_PREVIEW' => $values['imagePreview'],
            'DATE' => date("Y-m-d H:i:s"),
            'ACTIVE' => 1,
            'ID_USER' => 1,
            'LANG' => $lang));
		$this->db->commit();
    }
    
    public function switchPosterActivity($idPoster, $lang) {
		$this->db->begin();
        $state = dibi::query('SELECT ACTIVE FROM FINGA_POSTERS WHERE ID_POSTER=%i AND LANG=%s', $idPoster, $lang)->fetchSingle();
        //dibi::query('UPDATE FINGA_POSTERS SET ACTIVE=0');   
        if ($state == 0) {
			$this->db->query('UPDATE FINGA_POSTERS SET ACTIVE=1 WHERE ID_POSTER=%i AND LANG=%s', $idPoster, $lang);
        } else {
			$this->db->query('UPDATE FINGA_POSTERS SET ACTIVE=0 WHERE ID_POSTER=%i AND LANG=%s', $idPoster, $lang);
        }
		$this->db->commit();
    }
    
    public function selectPosters($lang) {
        return $this->db->query('SELECT * FROM FINGA_POSTERS JOIN FINGA_IMAGES USING(ID_IMAGE) WHERE LANG=%s', $lang)->fetchAll();
    }
    
    public function deletePoster($id) {
        //TODO obrázek
       // return dibi::query('DELETE FROM FINGA_POSTERS WHERE ID_POSTER=%i', $id)->fetchAll();
    }

    public function getActivePoster($lang) {
        return $this->db->query('SELECT T2.FILENAME AS IMAGE, T3.FILENAME AS IMAGE_PREVIEW FROM FINGA_POSTERS T1 JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE
                            JOIN FINGA_IMAGES T3 ON T1.ID_IMAGE_PREVIEW=T3.ID_IMAGE WHERE T1.ACTIVE=1 AND LANG=%s', $lang)->fetch();
    }
    
    public function getMpImage() {
        $images = array();
        $images['preview'] = $this->db->query('SELECT FILENAME FROM FINGA_IMAGES WHERE TYPE=%i ORDER BY ID_IMAGE DESC LIMIT 1', Constant::IMAGE_RIGHT_IMAGE_PREVIEW)->fetchSingle();
        $images['image'] = $this->db->query('SELECT FILENAME FROM FINGA_IMAGES WHERE TYPE=%i ORDER BY ID_IMAGE DESC LIMIT 1', Constant::IMAGE_RIGHT_IMAGE)->fetchSingle();
        return $images;
    }
    
    public function getFrontPageImage() {
        if($this->settings->getLang() == 'cs') {
            $idImage = Constant::IMAGE_FRONT_CZ_IMAGE;
        } else {
            $idImage = Constant::IMAGE_FRONT_EN_IMAGE;
        }
        return $this->db->query('SELECT FILENAME FROM FINGA_IMAGES WHERE TYPE=%i ORDER BY ID_IMAGE DESC LIMIT 1', $idImage)->fetchSingle();
   }
    
    public function getActiveVideo() {
        return $this->db->query('SELECT * FROM FINGA_VIDEOS WHERE STATUS=%i', Constant::VIDEO_MAINPAGE)->fetch();
    }
        
    public function getAbout() {
        return $this->db->query('SELECT * FROM FINGA_TEXTS')->fetchPairs('ID', 'TEXT');
    }
    
    //stejné jako editText, někde se to asi ještě používá
    public function editAbout($value, $id) {
		$this->db->query('UPDATE FINGA_TEXTS SET TEXT=%s WHERE ID=%i', $value, $id);
    }
    public function editText($value, $id) {
		$this->db->query('UPDATE FINGA_TEXTS SET TEXT=%s WHERE ID=%i', $value, $id);
    }
    
    public function selectAllMembers() {
        return $this->db->query('SELECT * FROM FINGA_MEMBERS')->fetchAll();
    }
    
    public function addMember($values) {
		$this->db->query('INSERT INTO FINGA_MEMBERS', array('NAME' => $values['member']));
    }
    
    public function deleteMember($id) {
		$this->db->query('DELETE FROM FINGA_MEMBERS WHERE ID=%i', $id);
    }
    
    public function getText($id) {
        return $this->db->query('SELECT TEXT FROM FINGA_TEXTS WHERE ID=%i', $id)->fetchSingle();
    }
    
    public function getContact() {
        if($this->settings->getLang() == 'cs') {
            return $this->db->query('SELECT CONTACT_CITY, CONTACT_NAME, CONTACT_MAIL, CONTACT_STREET, CONTACT_ZIP, CONTACT_ICO, CONTACT_ICQ, CONTACT_FIRM, CONTACT_STATE_CZ AS CONTACT_STATE FROM FINGA_OPTIONS LIMIT 1')->fetch();
        } else {
            return $this->db->query('SELECT CONTACT_CITY, CONTACT_NAME, CONTACT_MAIL, CONTACT_STREET, CONTACT_ZIP, CONTACT_ICO, CONTACT_ICQ, CONTACT_FIRM, CONTACT_STATE_EN AS CONTACT_STATE FROM FINGA_OPTIONS LIMIT 1')->fetch();
        }
        
    }
    public function getContactAdmin() {
        return $this->db->query('SELECT * FROM FINGA_OPTIONS LIMIT 1')->fetch();
    }
    
    public function updateContact($values) {
		$this->db->query('UPDATE FINGA_OPTIONS SET', array(
            'CONTACT_NAME' => $values['name'],
            'CONTACT_FIRM' => $values['firm'],
            'CONTACT_STREET' => $values['street'],
            'CONTACT_CITY' => $values['city'],
            'CONTACT_ZIP' => $values['zip'],
            'CONTACT_MAIL' => $values['email'],
            'CONTACT_ICO' => $values['ico'],
            'CONTACT_ICQ' => $values['icq'],
            'CONTACT_STATE_EN' => $values['state_en'],
            'CONTACT_STATE_CZ' => $values['state_cz']
        ));
    }
    
    public function selectShopCategories() {
        return $this->db->query('SELECT T1.*, T2.NAME_CZ AS PARENT_NAME FROM SHOP_CATEGORIES T1 LEFT JOIN SHOP_CATEGORIES T2 ON T1.PARENT=T2.ID')->fetchAll();
    }
    
    public function insertDistribution($values) {
		$this->db->query('INSERT INTO FINGA_DISTRIBUTION', array(
            'ID_IMAGE' => $values['image'],
            'TEXT' => $values['text'],
            'NAME' => $values['name'],
            'LINK' => $values['link'],
            'STATUS' => Constant::DISTRIBUTION_ACTIVE,
            'LANG' => $values['lang']));
    }
    
    public function editDistribution($values) {
		$this->db->query('UPDATE FINGA_DISTRIBUTION SET', array(
            'TEXT' => $values['text'],
            'LINK' => $values['link'],
            'NAME' => $values['name'],
            'LANG' => $values['lang']
        ), 'WHERE ID_DISTRIBUTION=%i', $values['id_distribution']);
        
        if(!empty($values['image'])){
			$this->db->query('UPDATE FINGA_DISTRIBUTION SET', array(
                'ID_IMAGE' => $values['image']
            ), 'WHERE ID_DISTRIBUTION=%i', $values['id_distribution']);
        }
    }
    
    public function selectAllDistributions($filter) {
        $status = '';
        if ($filter['active']==1) {
            $status = Constant::DISTRIBUTION_ACTIVE;
        }
        if ($filter['deleted']==1) {
            $status === '' ? $status = $status . Constant::DiSTRIBUTION_DELETED : $status = $status . ',' . Constant::DiSTRIBUTION_DELETED;
        }
        return $this->db->query('SELECT * FROM FINGA_DISTRIBUTION T1 LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE WHERE STATUS IN (' . $status . ')')->fetchAll();
    }
    
    public function selectDistribution($id) {
        return $this->db->query('SELECT * FROM FINGA_DISTRIBUTION T1 LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE WHERE ID_DISTRIBUTION=%i', $id)->fetch();
    }
    
    public function getDistributions($lang) {
        if($lang == 'cs') {
            return $this->db->query('SELECT * FROM FINGA_DISTRIBUTION T1 LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE WHERE STATUS=%i AND LANG=%i', Constant::DISTRIBUTION_ACTIVE, 0)->fetchAll();
        } else {
            return $this->db->query('SELECT * FROM FINGA_DISTRIBUTION T1 LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE WHERE STATUS=%i AND LANG=%i', Constant::DISTRIBUTION_ACTIVE, 1)->fetchAll();
        }
    }
    
    public function setDistributionStatus($id, $status ) {
		$this->db->query('UPDATE FINGA_DISTRIBUTION SET STATUS=%i WHERE ID_DISTRIBUTION=%i', $status, $id);
    }
    
    public function selectLinks() {
        if($this->settings->getLang()  == 'cs') {
            return $this->db->query('SELECT LINK, NAME_CZ AS NAME FROM FINGA_LINKS WHERE STATUS=%i', Constant::LINK_ACTIVE)->fetchAll();
        } else {
            return $this->db->query('SELECT LINK, NAME_EN AS NAME FROM FINGA_LINKS WHERE STATUS=%i', Constant::LINK_ACTIVE)->fetchAll();
        }
    }
    
    public function selectLinksAdmin() {
        return $this->db->query('SELECT * FROM FINGA_LINKS')->fetchAll();
    }
    
    public function insertLink($values) {
		$this->db->query('INSERT INTO FINGA_LINKS', array(
            'NAME_CZ' => $values['name_cz'],
            'NAME_EN' => $values['name_en'],
            'LINK' => $values['link'],
            'STATUS' => Constant::LINK_ACTIVE,
         ));
    }
    
    public function setLinkStatus($id, $status) {
		$this->db->query('UPDATE FINGA_LINKS SET STATUS=%i WHERE ID_LINK=%i', $status, $id);
    }
}
