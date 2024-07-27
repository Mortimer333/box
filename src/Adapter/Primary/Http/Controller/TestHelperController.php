<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Http\Controller;

use App\Adapter\Primary\Http\DTO\TestHelper\CreateTestUserDTO;
use App\Adapter\Secondary\Entity\User;
use App\Adapter\Secondary\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[SWG\Tag('Test Helper')]
#[Route('/_/api/tester/', name: 'api_tester_')]
class TestHelperController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected UserPasswordHasherInterface $passwordHasher,
        protected UserRepository $userRepository,
    ) {
    }

    #[Route('user/create', name: 'create_user', methods: 'POST')]
    public function createTestUser(#[MapRequestPayload] CreateTestUserDTO $model): JsonResponse
    {
        $user = $this->userRepository->findOneBy([
            'email' => $model->email,
        ]);
        if ($user) {
            throw new \Exception('User already exists');
        }

        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            (string) $model->password
        );

        $user->setEmail((string) $model->email)
            ->setPassword($hashedPassword)
            ->setFirstname((string) $model->name)
            ->setSurname((string) $model->surname)
        ;
        $this->em->persist($user);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
