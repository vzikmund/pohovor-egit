<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use Nette;
use Nette\Http\IResponse;
use Nette\Utils\Json;
use Nette\Utils\JsonException;


/**
 * JSON API pro partnery (pokladní systémy).
 */
class ApiPresenter extends Nette\Application\UI\Presenter
{
	public function actionPing(): void
	{
		$this->sendJson(['status' => 'ok']);
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
