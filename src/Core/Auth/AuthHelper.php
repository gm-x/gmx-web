<?php
namespace GameX\Core\Auth;

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

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->auth = $container->get('auth');
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
		if ($password !== $password_repeat) {
			throw new FormException('password_repeat', 'Password didn\'t match');
		}

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

		$activation = $this->auth->getActivationRepository()->create($user);

		return $this->sendEmail($email, 'Activation', 'activation', [
			'link' => $this->getLink('activation', ['code' => $activation->getCode()])
		]);
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

//		/** @var MailHelper $mail */
//		$mail = $this->container->get('mail');
//
//		$mailBody = $mail->render('activation', [
//			'link' => $this->getActivationLink('activation', ['code' => $activation->getCode()])
//		]);
//		return $mail->send([
//			'name' => $email,
//			'email' => $email
//		], 'Activation', $mailBody);
	}

	/**
	 * @param $email
	 * @param $password
	 * @return bool|\Cartalyst\Sentinel\Users\UserInterface
	 * @throws ValidationException
	 */
	public function loginUser($email, $password) {
		$user =  $this->auth->authenticate([
			'email' => $email,
			'password' => $password
		]);

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

		return $this->sendEmail($email, 'Reset Password', 'reset_password', [
			'link' => $this->getLink('complete_reset', ['code' => $reminder->getCode()])
		]);
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

	/**
	 * @param string $email
	 * @param string $title
	 * @param string $template
	 * @param array $data
	 * @return bool
	 */
	protected function sendEmail($email, $title, $template, array $data = []) {
		/** @var MailHelper $mail */
		$mail = $this->container->get('mail');

		$mailBody = $mail->render($template, $data);
		return $mail->send([
			'name' => $email,
			'email' => $email
		], $title, $mailBody);
	}
}
