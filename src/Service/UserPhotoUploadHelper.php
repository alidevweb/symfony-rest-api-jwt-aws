<?php

namespace App\Service;

use App\Entity\User;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class UserPhotoUploadHelper
{
    private $s3Client;
    private $awsS3BucketName;
    private $entityManager;
    private $logger;

    public function __construct(string $awsS3Region, string $awsS3AccessKeyId, string $awsS3SecretAccessKey, string $awsS3BucketName, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $awsS3Region,
            'credentials' => [
                'key' => $awsS3AccessKeyId,
                'secret' => $awsS3SecretAccessKey
            ]
        ]);
        $this->awsS3BucketName = $awsS3BucketName;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function uploadUserPhotosToS3(User $user) : int
    {
        $uploadCount = 0;
        $httpClient = HttpClient::create();

        foreach ($user->getPhotos() as $photo) {
            try {
                $response = $httpClient->request('GET', $photo->getUrl());
                if (200 === $response->getStatusCode()) {
                    $result = $this->s3Client->putObject([
                        'Bucket' => $this->awsS3BucketName,
                        'Key' => $photo->getName(),
                        'Body' => $response->getContent(),
                        'ACL' => 'public-read', // make file 'public'
                    ]);

                    if ($result) {
                        $photo->setUrl($result->get('ObjectURL'));
                        $this->entityManager->persist($photo);
                        $uploadCount++;
                    } else {
                        // Log error message
                        $this->logger->error('Photo upload to S3 failed', [
                            'source' => 'UserPhotoUploadHelper -> uploadUserPhotosToS3',
                            'photo_id' => $photo->getId(),
                        ]);
                    }
                } else {
                    // Log error message
                    $this->logger->error('Invalid photo from link', [
                        'source' => 'UserPhotoUploadHelper -> uploadUserPhotosToS3',
                        'photo_id' => $photo->getId(),
                    ]);
                }
            } catch (\Throwable $error) {
                // Log error message
                $this->logger->error($error->getMessage());
            }
        }

        if ($uploadCount) {
            $this->entityManager->flush();
        }

        return $uploadCount;
    }
}
