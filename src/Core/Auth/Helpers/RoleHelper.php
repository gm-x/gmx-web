<?php
namespace GameX\Core\Auth\Helpers;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Users\UserInterface;
use \Cartalyst\Sentinel\Users\UserRepositoryInterface;
use \Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use \GameX\Core\Exceptions\ValidationException;

class RoleHelper {

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var RoleRepositoryInterface
     */
    protected $roleRepository;
    
    /**
     * RoleHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        /** @var \Cartalyst\Sentinel\Sentinel $auth */
        $auth = $container->get('auth');
        $this->userRepository = $auth->getUserRepository();
        $this->roleRepository = $auth->getRoleRepository();
    }

    /**
     * @param string $role
     * @param UserInterface|string|int $user
     */
    public function assignUser($role, $user) {
        return $this->getUser($user)
            ->role()
            ->associate($this->getRole($role))
            ->save();
    }
    
    /**
     * @return array
     */
    public function getRolesAsArray() {
        $roles = [];
        foreach ($this->roleRepository->all() as $role) {
            $roles[$role->id] = $role->name;
        }
        
        return $roles;
    }

    /**
     * @param string $role
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     * @throws ValidationException
     */
    protected function getRole($role) {
        $role = $this->roleRepository->findById($role);
        if (!$role) {
            throw new ValidationException('Role not found');
        }
        return $role;
    }

    /**
     * @param UserInterface|string|int $user
     * @return UserInterface
     * @throws ValidationException
     */
    protected function getUser($user) {
    	if (!($user instanceof UserInterface)) {
			$user = $this->userRepository->findById((int)$user);
			if (!$user) {
				throw new ValidationException('User not found');
			}
		}

        return $user;
    }
}
