<?php

namespace GameX\Core\Auth\Helpers;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Hybridauth\User\Profile;
use \GameX\Core\Auth\Models\UserSocialModel;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Utils;

class SocialHelper
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @param string $provider
     * @param Profile $profile
     * @return UserSocialModel|null
     */
    public function find($provider, Profile $profile)
    {
        return UserSocialModel::where([
            'provider' => $provider,
            'identifier' => $profile->identifier
        ])->first();
    }
    
    /**
     * @param string $provider
     * @param Profile $profile
     * @return UserSocialModel
     * @throws \Exception
     */
    public function register($provider, Profile $profile)
    {
        /** @var UserModel $user */
        $user = $this->getAuth()->getUserRepository()->create([
            'login' => $profile->displayName,
            'email' => $profile->email,
            'password' => null,
            'token' => Utils::generateToken(16),
            'is_social' => true
        ]);
        $this->getAuth()->activate($user);
    
        $social = new UserSocialModel();
        $social->fill([
            'user_id' => $user->id,
            'provider' => $provider,
            'identifier' => $profile->identifier,
            'photo_url' => $profile->photoURL,
        ]);
        $social->save();
        return $social;
    }
    
    /**
     * @param UserSocialModel $userSocial
     * @return UserModel|bool
     */
    public function authenticate(UserSocialModel $userSocial)
    {
        $user = $userSocial->user;
        if (!$user) {
            return false;
        }
        return $this->getAuth()->authenticate($user);
    }
    
    /**
     * @return Sentinel
     */
    protected function getAuth()
    {
        return $this->container->get('auth');
    }
}
