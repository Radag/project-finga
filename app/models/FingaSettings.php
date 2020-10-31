<?php

namespace App\Model;

class FingaSettings
{
	/** @var User */
	private $lang = 'cs';

	public function setLang($lang)
	{
		$this->lang = $lang;
	}

	public function getLang()
	{
		return $this->lang;
	}
}
