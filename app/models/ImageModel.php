<?php

namespace App\Model;
/**
 * Model base class.
 */
class ImageModel extends BaseModel
{

    public function getImageMaxId() {
        $id = $this->db->query('SELECT MAX(ID_IMAGE) FROM FINGA_IMAGES')->fetchSingle();
        if (empty($id)) {
            return 0;
        } else {
            return $id;
        }
    }

    /*
     * typy: 0 - obrázky u produktů
     *       1 - obrázky na uvodní stránce
     *       2 - plakáty
     */

    public function addImage($filename, $type) {
		$this->db->query('INSERT INTO FINGA_IMAGES', array(
            'FILENAME' => $filename,
            'TYPE' => $type));
    }

    public function getImageId($filename) {
        return $this->db->query('SELECT ID_IMAGE FROM FINGA_IMAGES WHERE FILENAME=%s', $filename)->fetchSingle();
    }
}
