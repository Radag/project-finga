<?php

namespace App\Model;

use App\Constant;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Model base class.
 */
class ArticleModel extends BaseModel {

    public function insertArticle($values) {
		$this->db->query('INSERT INTO FINGA_ARTICLES', array(
            'TITLE_CZ' => $values['title_cz'],
            'TITLE_EN' => $values['title_en'],
            'SHORT_TEXT_CZ' => $values['short_text_cz'],
            'SHORT_TEXT_EN' => $values['short_text_en'],
            'TEXT_CZ' => $values['text_cz'],
            'TEXT_EN' => $values['text_en'],
            'TITLE_URL_CZ' => Strings::webalize($values['title_cz']),
            'TITLE_URL_EN' => Strings::webalize($values['title_en']),
            'DATE' => date("Y-m-d H:i:s"),
            'ID_IMAGE' => $values['image'],
            'STATUS' => Constant::ARTICLE_ACTIVE,
            'ID_USER' => 1));
    }
    
    public function updateArticle($values) {
		$this->db->query('UPDATE FINGA_ARTICLES SET', array(
            'TITLE_CZ' => $values['title_cz'],
            'TITLE_EN' => $values['title_en'],
            'SHORT_TEXT_CZ' => $values['short_text_cz'],
            'SHORT_TEXT_EN' => $values['short_text_en'],
            'TEXT_CZ' => $values['text_cz'],
            'TEXT_EN' => $values['text_en'],
            'TITLE_URL_CZ' => Strings::webalize($values['title_cz']),
            'TITLE_URL_EN' => Strings::webalize($values['title_en'])
        ), 'WHERE [ID_ARTICLE]=%i', $values['id_article']);
        
        if(!empty($values['image'])) {
			$this->db->query('UPDATE FINGA_ARTICLES SET', array(
                'ID_IMAGE' => $values['image']
            ), 'WHERE [ID_ARTICLE]=%i', $values['id_article']);
        }
    }

	private function getArticleFilter($filter) {
		$status = '';
		if ($filter['active']==1) {
			$status = Constant::ARTICLE_ACTIVE;
		}
		if ($filter['deleted']==1) {
			$status === '' ? $status = $status . Constant::ARTICLE_DELETE : $status = $status . ',' . Constant::ARTICLE_DELETE;
		}
		return $status;
	}

	public function selectArticlesCount($filter)
	{

		return $this->db->query('SELECT COUNT(*) FROM FINGA_ARTICLES WHERE STATUS IN (' . $this->getArticleFilter($filter) . ')')->fetchSingle();
	}

    public function selectArticles($filter, $items, $offset)
	{
        return $this->db->query('SELECT * FROM FINGA_ARTICLES WHERE STATUS IN (' . $this->getArticleFilter($filter) . ') ORDER BY DATE DESC LIMIT ? OFFSET ?', $items, $offset)->fetchAll();
    }
    
    public function selectArticle($id) {
        return $this->db->query('SELECT * FROM FINGA_ARTICLES LEFT JOIN FINGA_IMAGES USING (ID_IMAGE) WHERE ID_ARTICLE=%i', $id)->fetch();
    }
        
    public function getArticlePreview($page = 0) {
        $offset  = 0;
        if($page > 0) {
            $offset  = $page * 3;
        }
		if ($this->settings->getLang()  == 'cs') {
            $sql = 'SELECT SHORT_TEXT_CZ AS SHORT_TEXT, TITLE_CZ AS TITLE, TITLE_URL_CZ AS TITLE_URL, FILENAME FROM FINGA_ARTICLES LEFT JOIN FINGA_IMAGES USING (ID_IMAGE) WHERE STATUS=%i AND TITLE_URL_CZ!="" ORDER BY DATE DESC LIMIT %i, 3';
        } else {
            $sql = 'SELECT SHORT_TEXT_EN AS SHORT_TEXT, TITLE_EN AS TITLE, TITLE_URL_EN AS TITLE_URL, FILENAME FROM FINGA_ARTICLES LEFT JOIN FINGA_IMAGES USING (ID_IMAGE) WHERE STATUS=%i AND TITLE_URL_EN!="" ORDER BY DATE DESC LIMIT %i, 3';       
        }
        return $this->db->query($sql, Constant::ARTICLE_ACTIVE, $offset)->fetchAll();
    }
    
    public function setArticleStatus($id, $status ) {
		$this->db->query('UPDATE FINGA_ARTICLES SET STATUS=%i WHERE ID_ARTICLE=%i', $status, $id);
    }
    
    public function getArticleByUrlId($id) {
        if ($this->settings->getLang()  == 'cs') {
            return $this->db->query('SELECT TITLE_CZ AS TITLE, TEXT_CZ AS TEXT, SHORT_TEXT_CZ AS SHORT_TEXT, FILENAME FROM FINGA_ARTICLES LEFT JOIN FINGA_IMAGES USING (ID_IMAGE) WHERE TITLE_URL_CZ=%s', $id)->fetch();
        } else {
            return $this->db->query('SELECT TITLE_EN AS TITLE, TEXT_EN AS TEXT, SHORT_TEXT_EN AS SHORT_TEXT, FILENAME FROM FINGA_ARTICLES LEFT JOIN FINGA_IMAGES USING (ID_IMAGE) WHERE TITLE_URL_EN=%s', $id)->fetch();
        }
            
    }
    
    public function getArticleIdCz($titleUrl) {
        return $this->db->query('SELECT ID_ARTICLE FROM FINGA_ARTICLES WHERE TITLE_URL_CZ=%s', $titleUrl)->fetchSingle();
    }
    
    public function getArticleIdEn($titleUrl) {
        return $this->db->query('SELECT ID_ARTICLE FROM FINGA_ARTICLES WHERE TITLE_URL_EN=%s', $titleUrl)->fetchSingle();
    }
}
