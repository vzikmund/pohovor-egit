<?php

declare(strict_types=1);

namespace App\Presentation\Voucher;

use App\Model\Clock;
use App\Model\Voucher\VoucherFacade;
use App\Model\Voucher\VoucherRepository;
use App\Presentation\SecuredPresenter;
use Nette\Application\UI\Form;


final class VoucherPresenter extends SecuredPresenter
{
	public function __construct(
		private readonly VoucherRepository $voucherRepository,
		private readonly VoucherFacade $voucherFacade,
		private readonly Clock $clock,
	) {
	}


	public function renderDefault(): void
	{
		$this->template->vouchers = $this->voucherRepository->findAll();
		$this->template->now = $this->clock->now();
	}


	public function renderDetail(int $id): void
	{
		$voucher = $this->voucherRepository->getById($id);
		if (!$voucher) {
			$this->error('Poukaz neexistuje.');
		}

		$this->template->voucher = $voucher;
		$this->template->redemptions = $this->voucherRepository->findRedemptions($id);
		$this->template->now = $this->clock->now();
	}


	public function actionEdit(int $id): void
	{
		$voucher = $this->voucherRepository->getById($id);
		if (!$voucher) {
			$this->error('Poukaz neexistuje.');
		}

		$this['editForm']->setDefaults([
			'customer_email' => $voucher->customerEmail,
			'note' => $voucher->note,
		]);
		$this->template->voucher = $voucher;
	}


	protected function createComponentEditForm(): Form
	{
		$form = new Form;
		$form->addEmail('customer_email', 'E-mail zákazníka');
		$form->addTextArea('note', 'Poznámka');
		$form->addSubmit('save', 'Uložit');
		$form->onSuccess[] = function (Form $form) {
			$data = $form->getHttpData();
			unset($data['save'], $data['_do']);

			try {
				$this->voucherRepository->update((int) $this->getParameter('id'), $data);
				$this->flashMessage('Poukaz byl uložen.');
				$this->redirect('detail', $this->getParameter('id'));
			} catch (\Exception $e) {
				$this->flashMessage('Uložení se nepovedlo.', 'error');
			}
		};
		return $form;
	}


	protected function createComponentCreateForm(): Form
	{
		$form = new Form;
		$form->addInteger('amount', 'Hodnota v Kč')
			->setRequired('Zadejte hodnotu poukazu.')
			->addRule($form::Range, 'Hodnota musí být mezi %d a %d Kč.', [100, 10_000]);
		$form->addDate('expiresAt', 'Platí do (včetně)')
			->setRequired('Zadejte datum platnosti.')
			->setDefaultValue($this->clock->now()->modify('+1 year'));
		$form->addEmail('customerEmail', 'E-mail zákazníka');
		$form->addTextArea('note', 'Poznámka')
			->setMaxLength(1000);
		$form->addProtection();
		$form->addSubmit('save', 'Vydat poukaz');
		$form->onSuccess[] = $this->createFormSucceeded(...);
		return $form;
	}


	private function createFormSucceeded(Form $form, \stdClass $data): void
	{
		$voucher = $this->voucherFacade->issue(
			amount: $data->amount * 100,
			expiresAt: $data->expiresAt,
			customerEmail: $data->customerEmail ?: null,
			note: $data->note ?: null,
		);

		$this->flashMessage("Poukaz {$voucher->code} je vydaný.");
		$this->redirect('detail', $voucher->id);
	}
}
