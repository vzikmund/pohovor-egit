<?php

declare(strict_types=1);

namespace App\Presentation\Sign;

use Nette;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;


final class SignPresenter extends Nette\Application\UI\Presenter
{
	#[Persistent]
	public string $backlink = '';


	public function actionOut(): void
	{
		$this->getUser()->logout(true);
		$this->flashMessage('Byli jste odhlášeni.');
		$this->redirect('in');
	}


	protected function createComponentSignInForm(): Form
	{
		$form = new Form;
		$form->addEmail('email', 'E-mail')
			->setRequired('Zadejte e-mail.');
		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte heslo.');
		$form->addSubmit('send', 'Přihlásit se');
		$form->onSuccess[] = $this->signInFormSucceeded(...);
		return $form;
	}


	private function signInFormSucceeded(Form $form, \stdClass $data): void
	{
		try {
			$this->getUser()->login($data->email, $data->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}

		$this->restoreRequest($this->backlink);
		$this->redirect('Home:default');
	}
}
