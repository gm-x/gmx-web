<?php
namespace GameX\Core\Auth;

use \Cartalyst\Sentinel\Sentinel;
use \GameX\Core\Mail\MailHelper;
use GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use Psr\Container\ContainerInterface;

class AuthHelper {
	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
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

		/** @var Sentinel $auth */
		$auth = $this->container->get('auth');

		$user = $auth->getUserRepository()->findByCredentials([
			'email' => $email
		]);

		if ($user) {
			throw new FormException('email', 'User already exists');
		}

		$user = $auth->register([
			'email'  => $email,
			'password' => $password,
		]);

		if (!$user) {
			throw new ValidationException('Something wrong. Please Try again later.');
		}

		$activation = $auth->getActivationRepository()->create($user);

		/** @var MailHelper $mail */
		$mail = $this->container->get('mail');

		$mailBody = $mail->render('activation', [
			'link' => $this->getActivationLink('activation', ['code' => $activation->getCode()])
		]);
		return $mail->send([
			'name' => $email,
			'email' => $email
		], 'Activation', $mailBody);
	}

	public function activateUser($email, $code) {
		/** @var Sentinel $auth */
		$auth = $this->container->get('auth');

		$user = $auth->getUserRepository()->findByCredentials([
			'email' => $email
		]);

		if (!$user) {
			throw new FormException('email', 'User not found');
		}

		$activation = $auth->getActivationRepository()->complete($user, $code);
		if (!$activation) {
			throw new ValidationException('Something wrong. Please Try again later.');
		}

		return true;

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
	 * @param $name
	 * @param array $data
	 * @return string
	 */
	protected function getActivationLink($name, array $data = []) {
		/** @var \Slim\Router $router */
		$router = $this->container->get('router');

		/** @var \Slim\Http\Request $router */
		$request = $this->container->get('request');

		return (string)$request
			->getUri()
			->withPath($router->pathFor($name, $data));
	}
}
