<?php

namespace App\Adapter\Primary\Http\Security;

use App\Adapter\Secondary\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @codeCoverageIgnore I will also not test basic auth'a due to this just giving me a way to do any manual tests.
 *  Wouldn't make it to production.
 */
final class BasicAuthAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordHasher,
        protected UserRepository $userRepository,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('Authorization');
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No Basic Auth token provided');
        }

        $apiToken = substr($apiToken, 6);

        $decoded = base64_decode($apiToken) ?: '';
        $credentials = explode(':', $decoded);
        if (2 !== count($credentials)) {
            throw new CustomUserMessageAuthenticationException('Invalid Basic Auth token provided');
        }
        [$mail, $password] = $credentials;
        $user = $this->userRepository->findOneBy(['email' => $mail]);
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('User not found');
        }
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AccessDeniedHttpException();
        }

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            'success' => false,
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
