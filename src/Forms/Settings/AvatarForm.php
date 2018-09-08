<?php
namespace GameX\Forms\Settings;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\UserModel;
use \Slim\Http\UploadedFile;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Forms\Elements\File as FileElement;
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
			->add(new FileElement('avatar', $this->getPath(), [
				'title' => 'Avatar',
				'required' => true
			]));
		
		$this->form->getValidator()
			->set('avatar', true, [
                new FileRule(),
                new FileExtension(['jpg', 'png', 'gif']),
                new Image([IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF], [100, 1000], [100, 1000])
            ], [
                'check' => Validator::CHECK_EMPTY,
                'trim' => false,
            ]);
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
	
	protected function getPath() {
	    // TODO: need to make refactoring
        return '/upload/avatar_' . $this->user->id . '.jpg';
    }
}
