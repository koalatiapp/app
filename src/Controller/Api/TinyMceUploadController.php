<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Storage\UserUploadStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TinyMceUploadController extends AbstractController
{
	use ApiControllerTrait;

	public const MAX_FILESIZE = 20000000; // 20 MB

	/**
	 * @Route("/internal-api/tinymce-upload/image", name="api_tinymce_upload_image", options={"expose": true})
	 */
	public function upload(Request $request, UserUploadStorage $userUploadStorage): Response
	{
		$allowedOrigins = ["https://localhost", "https://app.koalati.com"];
		$origin = $request->server->get('HTTP_ORIGIN');

		// same-origin requests won't set an origin. If the origin is set, it must be valid.
		if ($origin && !in_array($origin, $allowedOrigins)) {
			return $this->accessDenied();
		}

		// Don't attempt to process the upload on an OPTIONS request
		if ($request->isMethod("OPTIONS")) {
			return new Response("", 200, ["Access-Control-Allow-Methods" => "POST, OPTIONS"]);
		}

		/** @var UploadedFile|null */
		$file = $request->files->get("file");

		if (!$file) {
			return $this->badRequest("Missing file.");
		}

		if (!$file->isValid() || $file->getSize() > self::MAX_FILESIZE) {
			return $this->badRequest("Your file is too big. Maximum size: ".(self::MAX_FILESIZE / 1000000)."MB");
		}

		if (!str_starts_with($file->getMimeType(), "image/")) {
			return $this->badRequest("Provided file is not an image.");
		}

		$fileUrl = $userUploadStorage->upload($file->getContent());

		return new JsonResponse(
			["location" => $fileUrl],
			200,
			[
				"Access-Control-Allow-Origin" => $origin,
				"Access-Control-Allow-Credentials" => true,
				"P3P" => 'CP="There is no P3P policy."',
			],
		);
	}
}
