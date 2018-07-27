<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
use \Carbon\Carbon;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Regexp;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Number;
use \GameX\Core\Forms\Rules\Callback;
use \GameX\Core\Exceptions\ApiException;

class PlayersController extends BaseApiController {

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
     */
    public function playerAction(Request $request, Response $response, array $args) {
        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->add('steamid', new Trim())
            ->add('steamid', new Required())
            ->add('steamid', new Regexp('/^(?:STEAM|VALVE)_\d:\d:\d+$/'))
            ->add('nick', new Trim())
            ->add('nick', new Required());
        
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }

        $player = Player::where('steamid', '=', $result->getValue('steamid'))->first();
        if (!$player) {
            $player = new Player();
            $player->steamid = $result->getValue('steamid');
            $player->nick = $result->getValue('nick');
            $player->auth_type = Player::AUTH_TYPE_STEAM;
            $player->save();
        }

        $punishmentsCollection = $player->punishments()
            ->where('status', '=', Punishment::STATUS_PUNISHED)
            ->where('expired_at', '>', Carbon::now()->toDateTimeString())
            ->get();

        $punishments = [];
        /** @var Punishment $punishment */
        foreach ($punishmentsCollection as $punishment) {
            $punishments[] = [
                'id' => $punishment->id,
                'type' => $punishment->type,
                'reason' => $punishment->reason,
                'expired_at' => $punishment->expired_at,
            ];
        }

        return $response->withJson([
            'success' => true,
            'data' => [
                'player' => [
                    'id' => $player->id,
                ],
                'punishments' => $punishments
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
     */
    public function punishAction(Request $request, Response $response, array $args) {
        $playerExists = function ($value, array $values) {
            return Player::where('id', $value)->exists() ? $value : null;
        };

        $punisherExists = function ($value, array $values) {
            return ($value == 0 || Player::where('id', $value)->exists()) ? $value : null;
        };
    
        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->add('player_id', new Trim())
            ->add('player_id', new Required())
            ->add('player_id', new Number(1))
            ->add('player_id', new Callback($playerExists))
            ->add('punisher_id', new Trim())
            ->add('punisher_id', new Required())
            ->add('punisher_id', new Number(1))
            ->add('punisher_id', new Callback($punisherExists))
            ->add('type', new Trim())
            ->add('type', new Required())
            ->add('type', new Number())
            ->add('reason', new Trim())
            ->add('reason', new Required())
            ->add('time', new Trim())
            ->add('time', new Required())
            ->add('time', new Number(0));
    
        $result = $validator->validate($this->getBody($request));
    
        if (!$result->getIsValid()) {
            throw new ApiException($result->getFirstError(), ApiException::ERROR_VALIDATION);
        }

        $punishment = new Punishment($result->getValues());
        $time = $result->getValue('time');
        $punishment->expired_at = $time > 0 ? time() + ($result->getValue('time') * 60) : null;
        $punishment->server_id = $request->getAttribute('server_id');
        $punishment->status = Punishment::STATUS_PUNISHED;
        $punishment->save();
        return $response->withJson([
            'success' => true,
            'data' => $punishment->toArray()
        ]);
    }
}
