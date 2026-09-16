<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;


/**
 * Předek všech presenterů, které vyžadují přihlášení.
 */
abstract class SecuredPresenter extends Nette\Application\UI\Presenter
{
	/** Role, kterou musí uživatel mít. Null znamená, že stačí být přihlášený. */
	protected ?string $requiredRole = 'admin';


	protected function startup(): void
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
		}

		if ($this->requiredRole !== null && !$this->getUser()->isInRole($this->requiredRole)) {
			$this->error('Na tuto stránku nemáte oprávnění.', Nette\Http\IResponse::S403_Forbidden);
		}
	}
}
