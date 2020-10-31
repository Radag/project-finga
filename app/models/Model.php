<?php


/**
 * Model base class.
 */
class Model extends NObject
{

	public function createAuthenticatorService()
	{
		return new Authenticator();
	}

}
