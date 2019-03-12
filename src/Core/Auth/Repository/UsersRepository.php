<?php
namespace GameX\Core\Auth\Repository;

use \Cartalyst\Sentinel\Users\IlluminateUserRepository;
use \Cartalyst\Sentinel\Users\UserInterface;
use GameX\Core\Auth\Models\UserModel;
use \InvalidArgumentException;

class UsersRepository extends IlluminateUserRepository {

    /**
     * {@inheritDoc}
     */
    public function findByCredentials(array $credentials) {
        if (empty($credentials) || empty($credentials['login'])) {
            return null;
        }

        return UserModel::where('login', $credentials['login'])
			->orWhere('email', $credentials['login'])
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function validForCreation(array $credentials) {
        return $this->validateUser($credentials);
    }

    /**
     * {@inheritDoc}
     */
    public function validForUpdate($user, array $credentials) {
        return true;
    }

    /**
     * Validates the user.
     *
     * @param  array  $credentials
     * @param  int  $id
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function validateUser(array $credentials, $id = null) {
        if (empty($credentials['login'])) {
            throw new InvalidArgumentException('No login credential was passed.');
        }
        if (empty($credentials['email'])) {
            throw new InvalidArgumentException('No email credential was passed.');
        }

        if (empty($credentials['password'])) {
            throw new InvalidArgumentException('You have not passed a [password].');
        }

        return true;
    }

    /**
     * Fills a user with the given credentials, intelligently.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  array  $credentials
     * @return void
     */
    public function fill(UserInterface $user, array $credentials) {
        $this->fireEvent('sentinel.user.filling', [
            'user' => $user,
            'credentials' => $credentials
        ]);

        if (array_key_exists('password', $credentials) && $credentials['password'] !== null) {
            $credentials['password'] = $this->hasher->hash($credentials['password']);
        }

        $user->fill($credentials);


        $this->fireEvent('sentinel.user.filled', [
            'user' => $user,
            'credentials' => $credentials
        ]);
    }
}
