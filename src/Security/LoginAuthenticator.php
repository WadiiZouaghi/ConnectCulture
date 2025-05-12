<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->get('email');
        $password = $request->get('password');
        
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function($userIdentifier) {
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);
                
                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Email could not be found.');
                }
                
                return $user;
            }),
            new CustomCredentials(
                function($credentials, User $user) {
                    $storedPassword = $user->getPassword();
                    
                    // Check if this is a plain text password (marked with PLAIN_ prefix)
                    if (str_starts_with($storedPassword, 'PLAIN_')) {
                        $plainPassword = substr($storedPassword, 6); // Remove the PLAIN_ prefix
                        return $credentials === $plainPassword;
                    }
                    
                    // Check if this is a SHA256 hash (64 characters long)
                    if (strlen($storedPassword) === 64 && !str_starts_with($storedPassword, '$2y$')) {
                        // Calculate SHA256 hash of the provided password
                        $sha256Hash = hash('sha256', $credentials);
                        return hash_equals($storedPassword, $sha256Hash);
                    }
                    
                    // Otherwise use the password hasher
                    return $this->passwordHasher->isPasswordValid($user, $credentials);
                },
                $password
            ),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
{
    $user = $token->getUser();

    if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
        return new RedirectResponse($this->urlGenerator->generate('app_admin')); // Redirect to admin
    }

    return new RedirectResponse($this->urlGenerator->generate('app_home')); // Default redirect
}

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
