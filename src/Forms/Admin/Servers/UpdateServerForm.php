<?php
namespace GameX\Forms\Admin\Servers;

use \GameX\Forms\Admin\ServerForm;

class UpdateServerForm extends ServerForm {

	/**
	 * @return boolean
	 */
	protected function processForm() {
		$this->server->fill($this->form->getValues());
		return $this->server->save();
	}
}
