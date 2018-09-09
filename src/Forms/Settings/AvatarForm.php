<?php
namespace GameX\Forms\Settings;

use \GameX\Core\BaseForm;
use \GameX\Core\Upload\Upload;
use \GameX\Core\Auth\Models\UserModel;
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
	 * @var Upload
	 */
	protected $upload;

	/**
	 * @param UserModel $user
	 * @param Upload $upload
	 */
	public function __construct(UserModel $user, Upload $upload) {
		$this->user = $user;
		$this->upload = $upload;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new FileElement('avatar', '', [
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
		$model = $this->upload->upload($this->user, $element->getValue());
	    $this->user->avatar = $model->id;
	    $this->user->save();
		return true;
	}
}
