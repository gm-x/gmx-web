<?php
namespace GameX\Core\Auth\Helpers;

use Cartalyst\Sentinel\Users\UserInterface;
use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Cartalyst\Sentinel\Reminders\EloquentReminder;
use \GameX\Core\Mail\MailHelper;
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

    /**
     * @var MailHelper
     */
	protected $mail;

	public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->auth = $container->get('auth');
        $this->mail = $container->get('mail');
	}

	/**
	 * @param $email
	 * @param $password
	 * @param $password_repeat
	 * @return bool
	 * @throws FormException
	 * @throws ValidationException
	 */
	public function registerUser($email, $password, $password_repeat) {
        $user = $this->auth->getUserRepository()->findByCredentials([
			'email' => $email
		]);

        if ($user) {
			throw new FormException('email', 'User already exists');
		}

		$user = $this->auth->register([
			'email'  => $email,
			'password' => $password,
		]);

		if (!$user) {
			throw new ValidationException('Something wrong. Please Try again later.');
		}

		return $this->auth->getActivationRepository()->create($user)->getCode();
	}

	/**
	 * @param $email
	 * @param $code
	 * @return \Cartalyst\Sentinel\Users\UserInterface
	 * @throws FormException
	 * @throws ValidationException
	 */
	public function activateUser($email, $code) {
		$user = $this->auth->getUserRepository()->findByCredentials([
			'email' => $email
		]);

		if (!$user) {
			throw new FormException('email', 'User not found');
		}

		$activation = $this->auth->getActivationRepository()->complete($user, $code);
		if (!$activation) {
			throw new ValidationException('Something wrong. Please Try again later.');
		}

		return $user;
	}

	/**
	 * @param $email
	 * @param $password
	 * @return bool|\Cartalyst\Sentinel\Users\UserInterface
	 * @throws ValidationException
	 */
	public function loginUser($email, $password, $remember) {
		$user =  $this->auth->authenticate([
			'email' => $email,
			'password' => $password
		], (bool)$remember);

		if (!$user) {
			throw new ValidationException('Bad email or password');
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
     * @param $email
     * @return bool
     * @throws FormException
     * @throws ValidationException
     */
	public function resetPassword($email) {
		$user = $this->auth->getUserRepository()->findByCredentials([
			'email' => $email
		]);

		if (!$user) {
			throw new FormException('email', 'User not found');
		}

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

    public function resetPasswordComplete($email, $password, $password_repeat, $code) {
        $user = $this->auth->getUserRepository()->findByCredentials([
            'email' => $email
        ]);

        if (!$user) {
            throw new FormException('email', 'User not found');
        }

        $reminderRepository = $this->auth->getReminderRepository();
        if (!$reminderRepository->exists($user)) {
            throw new ValidationException('Bad code');
        }

        /** @var EloquentReminder $reminder */
        return $reminderRepository->complete($user, $code, $password);
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
