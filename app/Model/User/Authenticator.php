<?php

declare(strict_types=1);

namespace App\Model\User;

use Nette\Database\Explorer;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;


final class Authenticator implements \Nette\Security\Authenticator
{
	public function __construct(
		private readonly Explorer $database,
		private readonly Passwords $passwords,
	) {
	}


	public function authenticate(string $username, string $password): IIdentity
	{
		$row = $this->database->table('user')->where('email', $username)->fetch();

		if (!$row || !$this->passwords->verify($password, $row->password_hash)) {
			throw new AuthenticationException('Nesprávný e-mail nebo heslo.');
		}

		return new SimpleIdentity($row->id, $row->role, [
			'email' => $row->email,
			'partner_id' => $row->partner_id,
		]);
	}
}
