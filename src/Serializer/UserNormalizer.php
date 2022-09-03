<?php

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    private $normalizer;
    private $requestStack;

    public function __construct(ObjectNormalizer $normalizer, RequestStack $requestStack)
    {
        $this->normalizer = $normalizer;
        $this->requestStack = $requestStack;
    }

    public function normalize($topic, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($topic, $format, $context);
        if ($data['avatar']) {
            $data['avatar'] = $this->requestStack->getMainRequest()->getSchemeAndHttpHost() . '/avatars/' . $data['avatar'];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof User;
    }
}
