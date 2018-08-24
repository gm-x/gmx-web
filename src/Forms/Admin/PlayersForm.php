<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Player;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\InArray;
use \GameX\Core\Forms\Rules\Boolean;
use \GameX\Core\Forms\Rules\SteamID;
use \GameX\Core\Forms\Rules\Callback;

class PlayersForm extends BaseForm {
    
    const VALID_AUTH_TYPES = [
        Player::AUTH_TYPE_STEAM ,
        Player::AUTH_TYPE_STEAM_AND_PASS,
        Player::AUTH_TYPE_NICK_AND_PASS,
        Player::AUTH_TYPE_STEAM_AND_HASH,
        Player::AUTH_TYPE_NICK_AND_HASH,
    ];

	/**
	 * @var string
	 */
	protected $name = 'admin_players';

	/**
	 * @var Player
	 */
	protected $player;

	/**
	 * @param Player $player
	 */
	public function __construct(Player $player) {
		$this->player = $player;
	}
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
	public function checkPassword($value, array $values) {
        return $values['auth_type'] !== Player::AUTH_TYPE_STEAM  && empty($values['password']) ? null : $value;
    }
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkExists($value, array $values) {
        return !Player::where('steamid', $value)->exists() ? $value : null;
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
            ->add(new Text('steamid', $this->player->steamid, [
                'title' => 'Steam ID',
                'required' => true,
            ]))
            ->add(new Text('nick', $this->player->nick, [
                'title' => 'Nickname',
                'error' => 'Required',
                'required' => true,
            ]))
            ->add(new Select('auth_type', $this->player->auth_type, [
                Player::AUTH_TYPE_STEAM => 'Steam ID',
                Player::AUTH_TYPE_STEAM_AND_PASS => 'Steam ID + pass',
                Player::AUTH_TYPE_NICK_AND_PASS => 'Nick + pass',
                Player::AUTH_TYPE_STEAM_AND_HASH => 'Steam ID + hash',
                Player::AUTH_TYPE_NICK_AND_HASH => 'Nick + hash',
            ], [
                'title' => 'Auth Type',
                'error' => 'Required',
                'required' => true,
                'empty_option' => 'Choose auth type',
            ]))
            ->add(new Password('password', '', [
                'title' => 'Password',
                'required' => false,
            ]))
            ->add(new Checkbox('access_reserve_nick', $this->player->hasAccess(Player::ACCESS_RESERVE_NICK), [
                'title' => 'Reserve nickname',
                'required' => false,
            ]))
            ->add(new Checkbox('access_block_change_nick', $this->player->hasAccess(Player::ACCESS_BLOCK_CHANGE_NICK), [
                'title' => 'Block change nick',
                'required' => false,
            ]));
		
		$this->form->getValidator()
            ->set('steamid', true, [
                new SteamID()
            ])
            ->set('auth_type', true, [
                new InArray(self::VALID_AUTH_TYPES)
            ])
            ->set('password', false, [
                new Callback([$this, 'checkPassword'], 'Password must be provided')
            ])
            ->set('access_reserve_nick', false, [
                new Boolean()
            ])
            ->set('access_block_change_nick', false, [
                new Boolean()
            ]);
        
        if (!$this->player->exists) {
            $this->form->addRule('steamid', new Callback([$this, 'checkExists'], 'Player already exists'));
        }
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        $this->player->steamid = $this->form->getValue('steamid');
        $this->player->nick = $this->form->getValue('nick');
        $authType = $this->form->getValue('auth_type');
        $this->player->auth_type = $authType;
        if ($authType == Player::AUTH_TYPE_STEAM_AND_PASS || $authType == Player::AUTH_TYPE_NICK_AND_PASS) {
            $this->player->password = md5($this->form->getValue('password'));
        }
        $access = 0;
        if ($this->form->getValue('access_reserve_nick')) {
            $access |= Player::ACCESS_RESERVE_NICK;
        }
        if ($this->form->getValue('access_block_change_nick')) {
            $access |= Player::ACCESS_BLOCK_CHANGE_NICK;
        }
        $this->player->access = $access;
        return $this->player->save();
    }
}
