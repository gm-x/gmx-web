<?php
namespace GameX\Core\Auth;

use \Cartalyst\Sentinel\Sentinel;
use \GameX\Core\Mail\MailHelper;
use GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use Psr\Container\ContainerInterface;
use SlimSession\Helper;

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
