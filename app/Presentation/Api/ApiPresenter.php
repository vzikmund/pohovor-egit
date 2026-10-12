<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use App\Model\Partner\Partner;
use App\Model\Partner\PartnerRepository;
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
	) {
	}


	public function actionPing(): void
	{
		$this->sendJson(['status' => 'ok']);
	}


	/**
	 * POST /api/v1/vouchers — vydání poukazu
	 * Jen Bearer auth, ať jde ověřit requirePartner.
	 */
	public function actionCreateVoucher(): void
	{
		$this->requirePartner();
		$this->sendJson([
			'partnerId' => $this->partner->id,
			'partnerName' => $this->partner->name,
		]);
	}


	/**
	 * Ověří Authorization: Bearer token a nstaví partenera.
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
}
