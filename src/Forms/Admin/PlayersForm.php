<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use GameX\Core\Validate\Rules\IPv4;
use \GameX\Models\Player;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Validate\Rules\InArray;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Validate\Rules\SteamID;
use \GameX\Core\Validate\Rules\Callback;

class PlayersForm extends BaseForm
{
    
    const VALID_AUTH_TYPES = [
        Player::AUTH_TYPE_STEAM,
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
    public function __construct(Player $player)
    {
        $this->player = $player;
    }
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkPassword($value, array $values)
    {
        return $values['auth_type'] !== Player::AUTH_TYPE_STEAM && empty($values['password']) ? null : $value;
    }
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkExists($value, array $values)
    {
        return !Player::where('steamid', $value)->exists() ? $value : null;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->form->add(new Text('nick', $this->player->nick, [
                'title' => $this->getTranslate($this->name, 'nickname'),
                'required' => true,
            ]))->add(new Text('steamid', $this->player->steamid, [
                'title' => $this->getTranslate($this->name, 'steam_id'),
                'required' => true,
            ]))->add(new Text('ip', $this->player->ip, [
                'title' => $this->getTranslate($this->name, 'ip'),
                'required' => true,
            ]))->add(new Select('auth_type', $this->player->auth_type, [
                Player::AUTH_TYPE_STEAM => $this->getTranslate($this->name, 'steam_id'),
                Player::AUTH_TYPE_STEAM_AND_PASS => $this->getTranslate($this->name, 'steam_id_pass'),
                Player::AUTH_TYPE_NICK_AND_PASS => $this->getTranslate($this->name, 'nickname_pass'),
//                Player::AUTH_TYPE_STEAM_AND_HASH => $this->getTranslate($this->name, 'steam_id_hash'),
//                Player::AUTH_TYPE_NICK_AND_HASH => $this->getTranslate($this->name, 'nickname_hash'),
            ], [
                'title' => $this->getTranslate($this->name, 'auth_type'),
                'required' => true,
                'empty_option' => $this->getTranslate($this->name, 'choose_auth_type'),
            ]))->add(new Password('password', '', [
                'title' => $this->getTranslate($this->name, 'password'),
                'required' => false,
            ]))->add(new Checkbox('access_reserve_nick', $this->player->hasAccess(Player::ACCESS_RESERVE_NICK), [
                'title' => $this->getTranslate($this->name, 'reserve_nickname'),
                'required' => false,
            ]))->add(new Checkbox('access_block_change_nick',
                $this->player->hasAccess(Player::ACCESS_BLOCK_CHANGE_NICK), [
                    'title' => $this->getTranslate($this->name, 'block_nickname'),
                    'required' => false,
                ]));

        $validator = $this->form->getValidator();
        $validator->set('nick', true)->set('steamid', true, [
                new SteamID()
            ])->set('ip', true, [
                new IPv4()
            ])->set('auth_type', true, [
                new InArray(self::VALID_AUTH_TYPES)
            ])->set('password', false, [
                new Callback([$this, 'checkPassword'], $this->getTranslate($this->name, 'pass_error'))
            ])->set('access_reserve_nick', false, [
                new Boolean()
            ])->set('access_block_change_nick', false, [
                new Boolean()
            ]);
        
        if (!$this->player->exists) {
            $validator->add('steamid',
                new Callback([$this, 'checkExists'], $this->getTranslate($this->name, 'player_exists')));
        }
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        $this->player->steamid = $this->form->getValue('steamid');
        $this->player->nick = $this->form->getValue('nick');
        $this->player->ip = $this->form->getValue('ip');
        $authType = $this->form->getValue('auth_type');
        $this->player->auth_type = $authType;
        if ($authType == Player::AUTH_TYPE_STEAM_AND_PASS || $authType == Player::AUTH_TYPE_NICK_AND_PASS) {
            $this->player->password = $this->form->getValue('password');
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
