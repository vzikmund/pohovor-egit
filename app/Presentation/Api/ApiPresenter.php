<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use App\Model\Clock;
use App\Model\Partner\Partner;
use App\Model\Partner\PartnerRepository;
use App\Model\Voucher\VoucherFacade;
use App\Model\Voucher\VoucherValidationException;
use Nette;
use Nette\Http\IResponse;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;


/**
 * JSON API pro partnery (pokladní systémy).
 */
class ApiPresenter extends Nette\Application\UI\Presenter
{
	protected ?Partner $partner = null;


	public function __construct(
		private readonly PartnerRepository $partnerRepository,
		private readonly VoucherFacade $voucherFacade,
		private readonly Clock $clock,
	) {
	}


	public function actionPing(): void
	{
		$this->sendJson(['status' => 'ok']);
	}


	/**
	 * GET /api/v1/vouchers/<code> — info o poukazu partner
	 */
	public function actionGetVoucher(string $code): void
	{
		if (!$this->getHttpRequest()->isMethod('GET')) {
			$this->sendError(IResponse::S405_MethodNotAllowed, 'method_not_allowed', 'Povolená je jen metoda GET.');
		}

		$this->requirePartner();

		try {
			$voucher = $this->voucherFacade->getForPartner($this->partner->id, $code);
		} catch (VoucherValidationException $e) {
			$this->sendError($this->httpCodeForValidation($e->error), $e->error, $e->getMessage());
		}

		$this->sendJson([
			'code' => $voucher->code,
			'balance' => $voucher->balance,
			'expiresAt' => $voucher->expiresAt->format('Y-m-d'),
			'redeemable' => $voucher->isRedeemable($this->clock->now()),
		]);
	}


	/**
	 * POST /api/v1/vouchers — vydání poukazu partnerem.
	 */
	public function actionCreateVoucher(): void
	{
		if (!$this->getHttpRequest()->isMethod('POST')) {
			$this->sendError(IResponse::S405_MethodNotAllowed, 'method_not_allowed', 'Povolená je jen metoda POST.');
		}

		$this->requirePartner();
		$body = $this->getJsonBody();

		$amount = $body['amount'] ?? null;
		if (!is_int($amount) && !(is_string($amount) && ctype_digit($amount))) {
			$this->sendError(IResponse::S400_BadRequest, 'invalid_amount', 'Pole amount musí být celé číslo (haléře).');
		}
		$amount = (int) $amount;

		$transactionId = $body['transactionId'] ?? null;
		if ($transactionId !== null && !is_string($transactionId)) {
			$this->sendError(IResponse::S400_BadRequest, 'invalid_transaction_id', 'Pole transactionId musí být řetězec.');
		}
		$transactionId = is_string($transactionId) ? $transactionId : '';

		$customerEmail = $body['customerEmail'] ?? null;
		if ($customerEmail !== null && !is_string($customerEmail)) {
			$this->sendError(IResponse::S400_BadRequest, 'invalid_email', 'Pole customerEmail musí být řetězec.');
		}
		$customerEmail = is_string($customerEmail) && $customerEmail !== '' ? $customerEmail : null;

		try {
			$voucher = $this->voucherFacade->issueForPartner(
				partnerId: $this->partner->id,
				transactionId: $transactionId,
				amount: $amount,
				customerEmail: $customerEmail,
			);
		} catch (VoucherValidationException $e) {
			$this->sendError(IResponse::S400_BadRequest, $e->error, $e->getMessage());
		}

		$this->getHttpResponse()->setCode(IResponse::S201_Created);
		$this->sendJson([
			'id' => $voucher->id,
			'code' => $voucher->code,
			'amount' => $voucher->initialAmount,
			'expiresAt' => $voucher->expiresAt->format('Y-m-d'),
		]);
	}


	/**
	 * Ověří Authorization: Bearer <API klíč> a nastaví $this->partner.
	 */
	protected function requirePartner(): void
	{
		$header = $this->getHttpRequest()->getHeader('Authorization');
		if ($header === null || $header === '') {
			$this->sendError(IResponse::S401_Unauthorized, 'missing_token', 'Chybí Authorization Bearer token.');
		}

		if (!Strings::startsWith($header, 'Bearer ')) {
			$this->sendError(IResponse::S401_Unauthorized, 'invalid_token', 'Authorization musí být ve tvaru Bearer <klíč>.');
		}

		$apiKey = trim(substr($header, strlen('Bearer ')));
		if ($apiKey === '') {
			$this->sendError(IResponse::S401_Unauthorized, 'invalid_token', 'Bearer token je prázdný.');
		}

		$partner = $this->partnerRepository->findByApiKey($apiKey);
		if (!$partner) {
			$this->sendError(IResponse::S401_Unauthorized, 'invalid_token', 'Neplatný API klíč.');
		}

		$this->partner = $partner;
	}


	/**
	 * Načte tělo požadavku jako JSON objekt. Při chybě rovnou odpoví 400.
	 * @return array<string, mixed>
	 */
	protected function getJsonBody(): array
	{
		try {
			$data = Json::decode($this->getHttpRequest()->getRawBody() ?? '', forceArrays: true);
		} catch (JsonException) {
			$this->sendError(IResponse::S400_BadRequest, 'invalid_json', 'Tělo požadavku není platný JSON.');
		}

		if (!is_array($data)) {
			$this->sendError(IResponse::S400_BadRequest, 'invalid_json', 'Tělo požadavku musí být JSON objekt.');
		}

		return $data;
	}


	/**
	 * Odešle chybovou odpověď ve tvaru {"error": "...", "message": "..."}.
	 */
	protected function sendError(int $httpCode, string $error, string $message): never
	{
		$this->getHttpResponse()->setCode($httpCode);
		$this->sendJson(['error' => $error, 'message' => $message]);
	}


	private function httpCodeForValidation(string $error): int
	{
		return match ($error) {
			'voucher_not_found' => IResponse::S404_NotFound,
			'voucher_forbidden' => IResponse::S403_Forbidden,
			default => IResponse::S400_BadRequest,
		};
	}
}
