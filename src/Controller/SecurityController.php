<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserPhotoUploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/users")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="app_users_register", methods={"POST"})
     */
    public function register(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserPhotoUploadHelper $userPhotoUploadHelper)
    {
        try {
            /* @var User $user */
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        } catch (\Throwable $throwable) {
            // Exception will be thrown when json invalid or empty content
            return $this->json(['errors' => [$throwable->getMessage()]], 400);
        }

        // Set User default avatar
        $user->setAvatar(User::AVATARS[random_int(0, 3)]);

        // Validate the User object
        $errors = $validator->validate($user);

        // Check if has errors, return a validation error
        if (count($errors) > 0) {
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[] = $error->getPropertyPath() . ' : ' . $error->getMessage();
            }

            return $this->json(['errors' => $formattedErrors], 422);
        }

        // User plain text password should be hashed before persist
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        // Upload user photos to AWS S3 Bucket
        // For a better user experience, this should be done async with the Symfony Messenger component
        $userPhotoUploadHelper->uploadUserPhotosToS3($user);

        return $this->json(['message' => 'Created successfully'], 201);
    }

    /**
     * @Route("/me", name="app_users_me", methods={"GET"})
     */
    public function me()
    {
        return $this->json([
            'user' => $this->getUser(),
        ], 200, [], [
            'groups' => ['user:read', 'photo:read'],
        ]);
    }
}
