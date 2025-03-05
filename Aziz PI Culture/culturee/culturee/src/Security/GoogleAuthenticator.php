<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'oauth_check' 
            && $request->get('service') === 'google';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                
                $email = $googleUser->getEmail();

                // Check if user exists
                $existingUser = $this->entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if (!$existingUser) {
                    // Create new user
                    $existingUser = new User();
                    $existingUser->setEmail($email);
                    $existingUser->setFullName($googleUser->getName());
                    $existingUser->setGoogleId($googleUser->getId());
                    $existingUser->setPhone(''); // Set a default value
                    $existingUser->setPassword(''); // Set a default value
                    $existingUser->setRoles(['ROLE_USER']);
                    
                    $this->entityManager->persist($existingUser);
                    $this->entityManager->flush();
                }

                return $existingUser;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('app_admin'));
        }

        if (in_array('ROLE_ORGANIZER', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('app_organizer'));
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new RedirectResponse(
            $this->router->generate('app_login', ['error' => $message])
        );
    }
}