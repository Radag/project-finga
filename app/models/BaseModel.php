<?php

namespace App\Model;

use Nette;


class BaseModel
{
    use Nette\SmartObject;

	protected $user;

    /** @var \Dibi\Connection  */
    protected $db;

	protected $settings;

    public function __construct(
        Nette\Security\User $user,
        \Dibi\Connection $db,
		FingaSettings $settings
    )
    {
        $this->user = $user;
        $this->db = $db;
        $this->settings = $settings;
    }
}

