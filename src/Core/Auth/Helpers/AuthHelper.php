<?php
namespace GameX\Core\Auth\Helpers;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Users\UserInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Cartalyst\Sentinel\Reminders\EloquentReminder;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\ValidationException;

class AuthHelper {

    /**
     * @var ContainerInterface
     */
    protected $container;
    
	/**
	 * @var Sentinel
	 */
	protected $auth;

	public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->auth = $container->get('auth');
	}

	/**
	 * @param string $login
	 * @return UserInterface
	 */
	public function findUser($login) {
		return $this->auth->getUserRepository()->findByCredentials([
			'login' => $login
		]);
	}

	/**
	 * @param string $login
	 * @param string $email
	 * @return bool
	 */
	public function exists($login, $email) {
		/** @var \Illuminate\Database\Eloquent\Builder $query */
		$query = $this->auth->getUserRepository()->createModel()->newQuery();
		$query
			->where('login', $login)
			->orWhere('email', $email);

		return $query->exists();
	}

	/**
	 * @param string $login
	 * @param string $email
	 * @param string $password
	 * @return bool
	 * @throws FormException
	 * @throws ValidationException
	 */
	public function registerUser($login, $email, $password) {
		$user = $this->auth->register([
			'login'  => $login,
			'email'  => $email,
			'password' => $password,
		]);

		if (!$user) {
			throw new ValidationException('Something wrong. Please Try again later.');
		}

		return $this->auth->getActivationRepository()->create($user)->getCode();
	}

	/**
	 * @param UserInterface $user
	 * @param string $code
	 * @return bool
	 */
	public function activateUser(UserInterface $user, $code) {
		return $this->auth->getActivationRepository()->complete($user, $code);
	}

	/**
	 * @param string $login
	 * @param string $password
	 * @return bool|\Cartalyst\Sentinel\Users\UserInterface
	 * @throws ValidationException
	 */
	public function loginUser($login, $password, $remember) {
		$user =  $this->auth->authenticate([
			'login' => $login,
			'password' => $password
		], (bool)$remember);

		if (!$user) {
			throw new ValidationException('Bad login or password');
		}

		return $user;
	}

	/**
	 * @return bool
	 */
	public function logoutUser() {
		return $this->auth->logout();
	}

    /**
     * @param UserInterface $user
     * @return string
     * @throws FormException
     * @throws ValidationException
     */
	public function resetPassword(UserInterface $user) {
		$reminderRepository = $this->auth->getReminderRepository();
		if ($reminderRepository->exists($user)) {
			throw new ValidationException('You already reset password');
		}

		/** @var EloquentReminder $reminder */
		$reminder = $reminderRepository->create($user);
		if (!$reminder) {
			throw new ValidationException('Something wrong. Please Try again later.');
		}

		return $reminder->code;
	}

	/**
	 * @param UserInterface $user
	 * @param $password
	 * @param $code
	 * @throws ValidationException
	 */
    public function resetPasswordComplete(UserInterface $user, $password, $code) {
        $reminderRepository = $this->auth->getReminderRepository();
        if (!$reminderRepository->exists($user)) {
            throw new ValidationException('Bad code');
        }

        /** @var EloquentReminder $reminder */
        if (!$reminderRepository->complete($user, $code, $password)) {
			throw new ValidationException('Something wrong. Please Try again later.');
		}
    }

	/**
	 * @param $name
	 * @param array $data
	 * @return string
	 */
	protected function getLink($name, array $data = []) {
		/** @var \Slim\Router $router */
		$router = $this->container->get('router');

		/** @var \Slim\Http\Request $router */
		$request = $this->container->get('request');

		return (string)$request
			->getUri()
			->withPath($router->pathFor($name, $data));
	}
}
