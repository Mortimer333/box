<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Http\Controller;

use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[SWG\Tag('Default')]
final class DefaultController extends AbstractController
{
    #[Route('/_/health', name: 'api_health', methods: 'GET')]
    public function health(): JsonResponse
    {
        return $this->json(['status' => 'Healthy']);
    }
}
