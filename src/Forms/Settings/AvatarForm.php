<?php
namespace GameX\Forms\Settings;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\UserModel;
use \Slim\Http\UploadedFile;
use \GameX\Core\Forms\Elements\File as FileElement;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\File as FileRule;
use \GameX\Core\Forms\Rules\FileExtension;
use \GameX\Core\Forms\Rules\Image;
use \GameX\Core\Exceptions\FormException;

class AvatarForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'user_settings_avatar';

	/**
	 * @var UserModel
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $root;

	/**
	 * @param UserModel $user
	 * @param string $root
	 */
	public function __construct(UserModel $user, $root) {
		$this->user = $user;
		$this->root = $root;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new FileElement('avatar', '', [
				'title' => 'Avatar',
				'required' => true
			]))
			->addRule('avatar', new Required())
			->addRule('avatar', new FileRule())
			->addRule('avatar', new FileExtension(['jpg', 'png', 'gif']))
			->addRule('avatar', new Image([IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF], [100, 200], [100, 200]));
	}

	/**
	 * @return boolean
	 * @throws FormException
	 */
	protected function processForm() {
		$element = $this->form->get('avatar');
		/** @var UploadedFile $file */
		$file = $element->getValue();
		$path = $this->root . 'avatar_' . $this->user->id . '.' . $element->getExtension();
	    $file->moveTo($path);
		return true;
	}
}
