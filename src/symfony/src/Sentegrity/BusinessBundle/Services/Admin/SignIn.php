<?php
namespace Sentegrity\BusinessBundle\Services\Admin;

use Sentegrity\BusinessBundle\Entity\Documents\AdminSession;
use Sentegrity\BusinessBundle\Entity\Repository\AdminSessionRepository;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Service;
use Sentegrity\BusinessBundle\Services\Support\Password;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Entity\Repository\AdminUserRepository;

class SignIn extends Service
{
    /** @var AdminUserRepository $userRepository */
    private $userRepository;
    /** @var AdminSessionRepository $sessionRepository */
    private $sessionRepository;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->userRepository = $this->entityManager->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\AdminUser'
        );
        $this->sessionRepository = $this->entityManager->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\AdminSession'
        );
    }
    
    /**
     * Authenticates user and creates a session if valid user
     * 
     * @param array $userData
     * @return string $accessToken
     * @throws ValidatorException
     */
    public function signIn(array $userData)
    {
        /**
         * $organizationData template:
         * array(
         *      "username" => ...,
         *      "password" => ...
         * )
         */
        
        $seedPassword = Password::seedAndEncryptPassword($userData['password']);
        $user = $this->userRepository->getUserByUsernameAndPassword(
            $userData['username'], 
            $seedPassword
        );
        
        if (!$user) {
            throw new ValidatorException(
                null,
                "User not found",
                ErrorCodes::NOT_FOUND
            );
        }
        
        $accessToken = sha1(time() . random_int(0, 1000000) . $user->getUsername());
        $session = new AdminSession();
        $session->setUser($user)
            ->setOrganization($user->getOrganization())
            ->setPermission($user->getPermission())
            ->setAccessToken($accessToken);
        $this->entityManager->persist($session);

        return $this->flush(
            'An error occurred while signing in. Sign in failed!',
            $accessToken
        );
        
    }
    
    /**
     * Removes active session
     * 
     * @param $accessToken
     * @return true
     * @throws ValidatorException
     */
    public function signOut($accessToken)
    {
        /***/
        $session = $this->sessionRepository->getSessionByAccessToken($accessToken);
        if (!$session) {
            throw new ValidatorException(
                null,
                "Active session not found",
                ErrorCodes::NOT_FOUND
            );
        }

        $this->entityManager->remove($session);
        return $this->flush(
            'An error occurred while signing out. Sign out failed!'
        );
    }
}