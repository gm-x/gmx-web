<?php
namespace GameX\Core\Auth;

use Cartalyst\Sentinel\Users\UserInterface;
use \Psr\Container\ContainerInterface;
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

    public function __construct(ContainerInterface $container) {
        /** @var \Cartalyst\Sentinel\Sentinel $auth */
        $auth = $container->get('auth');
        $this->userRepository = $auth->getUserRepository();
        $this->roleRepository = $auth->getRoleRepository();
    }

    /**
     * @param string $name
     * @param string $slug
     */
    public function createRole($name, $slug) {
        $this->roleRepository->createModel()->create([
            'name' => $name,
            'slug' => $slug,
            'permissions' => []
        ]);
    }

    /**
     * @param string $role
     * @param UserInterface|string|int $user
     */
    public function assignUser($role, $user) {
        $this
            ->getRole($role)
            ->users()
            ->attach($this->getUser($user));
    }

    /**
     * @param string $role
     * @param UserInterface|string|int $user
     */
    public function removeUser($role, $user) {
        $this
            ->getRole($role)
            ->users()
            ->detach($this->getUser($user));
    }

    /**
     * @param string $role
     * @param string $permission
     * @param bool $allow
     */
    public function addPermission($role, $permission, $allow = true) {
        $this
            ->getRole($role)
            ->updatePermission((string) $permission, (bool) $allow, true)
            ->save();
    }

    /**
     * @param string $role
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     * @throws ValidationException
     */
    protected function getRole($role) {
        $role = $this->roleRepository->findBySlug($role);
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
