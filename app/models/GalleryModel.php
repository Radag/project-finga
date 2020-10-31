<?php

namespace App\Model;

use App\Constant;
/**
 * Model base class.
 */
class GalleryModel extends BaseModel
{
    public function getVideos($year) {
        return $this->db->query("SELECT * FROM FINGA_VIDEOS WHERE DATE_FORMAT( DATE,'%Y')=%i AND STATUS!=%i ORDER BY DATE DESC", $year, Constant::VIDEO_DELETED)->fetchAll();
    }
    
    public function getAllVideos($filter) {
        $status = '';
        if ($filter['active']==1) {
            $status = Constant::VIDEO_ACTIVE . ', ' . Constant::VIDEO_MAINPAGE;
        }
        if ($filter['deleted']==1) {
            $status === '' ? $status = $status . Constant::VIDEO_DELETED : $status = $status . ',' . Constant::VIDEO_DELETED;
        }
        
        return $this->db->query('SELECT * FROM FINGA_VIDEOS WHERE STATUS IN (' . $status . ')')->fetchAll();
    }
    
    public function insertVideo($values) {
        if(!empty($values['year'])) {
            $date = date($values['year'] . "-m-d H:i:s");
        } else {
            $date = date("Y-m-d H:i:s");
        }
		$this->db->query('INSERT INTO FINGA_VIDEOS', array(
            'NAME' => $values['name'],
            'KEY' => $values['videoId'],
            'DATE' => $date,
            'STATUS' => Constant::VIDEO_ACTIVE,
            'ID_USER' => 1));
    }
    
    public function setVideoStatus($id, $status) {
		$this->db->begin();
        if ($status == Constant::VIDEO_MAINPAGE) {
			$this->db->query('UPDATE FINGA_VIDEOS SET STATUS=%i WHERE ID_VIDEO=%i', Constant::VIDEO_ACTIVE, Constant::VIDEO_MAINPAGE);
        }
		$this->db->query('UPDATE FINGA_VIDEOS SET STATUS=%i WHERE ID_VIDEO=%i', $status ,$id);

		$this->db->commit();
    }

}
