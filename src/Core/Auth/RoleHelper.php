<?php
namespace GameX\Core\Auth;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Users\UserRepositoryInterface;
use \Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use GameX\Core\Exceptions\ValidationException;

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
        ]);
    }

    /**
     * @param string $role
     * @param string $user
     */
    public function assignUser($role, $user) {
        $this
            ->getRole($role)
            ->users()
            ->attach($this->getUser($user));
    }

    /**
     * @param string $role
     * @param string $user
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
     * @param $user
     * @return \Cartalyst\Sentinel\Users\UserInterface
     * @throws ValidationException
     */
    protected function getUser($user) {
        $user = $this->userRepository->findById($user);
        if (!$user) {
            throw new ValidationException('User not found');
        }
        return $user;
    }
}
