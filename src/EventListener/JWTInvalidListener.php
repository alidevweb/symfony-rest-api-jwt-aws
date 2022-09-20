<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;

class JWTInvalidListener
{
    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = new JsonResponse([
            'error' => 'Your token is invalid, please login again to get a new one'
        ], JsonResponse::HTTP_FORBIDDEN);

        $event->setResponse($response);
    }
}
