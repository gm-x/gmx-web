<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
use \GameX\Models\Reason;
use \Carbon\Carbon;
use \GameX\Core\Forms\Validator;
use \GameX\Core\Forms\Rules\Regexp;
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
            ->set('steamid', true, [
                new Regexp('/^(?:STEAM|VALVE)_\d:\d:\d+$/')
            ])
            ->set('emulator', true, [
                new Number()
            ])
            ->set('nick', true);
            
    
        $result = $validator->validate($this->getBody($request));
        
        if (!$result->getIsValid()) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }

        $player = Player::where([
            'steamid' => $result->getValue('steamid'),
            'emulator' => $result->getValue('emulator')
        ])->first();
        if (!$player) {
            $player = new Player();
            $player->steamid = $result->getValue('steamid');
            $player->emulator = $result->getValue('emulator');
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
            // TODO: Move it to sql
            if ($punishment->server_id != $request->getAttribute('server_id') && !$punishment->reason->overall) {
                continue;
            }
            $punishments[] = [
                'id' => $punishment->id,
                'type' => $punishment->type,
                'reason' => $punishment->reason->title,
                'reason' => $punishment->reason->title,
                'expired_at' => $punishment->expired_at,
            ];
        }

        return $response->withJson([
            'success' => true,
            'data' => [
                'player' => [
                    'id' => $player->id,
                ],
                'user' => null,
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
        $serverId = (int) $request->getAttribute('server_id');
        
        $playerExists = function ($value, array $values) {
            return Player::where('id', $value)->exists() ? $value : null;
        };

        $punisherExists = function ($value, array $values) {
            return ($value == 0 || Player::where('id', $value)->exists()) ? $value : null;
        };
    
        $validator = new Validator($this->getContainer('lang'));
        $validator
            ->set('player_id', true, [
                new Number(1),
                new Callback($playerExists)
            ])
            ->set('punisher_id', true, [
                new Number(1),
                new Callback($punisherExists)
            ])
            ->set('type', true, [
                new Number(0),
            ])
            ->set('reason', true)
            ->set('comment', false)
            ->set('time', true, [
                new Number(0)
            ]);
    
        $result = $validator->validate($this->getBody($request));
    
        if (!$result->getIsValid()) {
            throw new ApiException($result->getFirstError(), ApiException::ERROR_VALIDATION);
        }
        
        $reason = Reason::firstOrCreate([
            'server_id' =>$serverId,
            'title' => $result->getValue('reason')
        ], [
            'server_id' => $serverId,
            'title' => $result->getValue('reason'),
            'time' => null,
            'overall' => 0,
            'menu' => 0,
            'active' => 1
        ]);
    
        $time = $result->getValue('time');

        $punishment = new Punishment([
            'player_id' => $result->getValue('player_id'),
            'punisher_id' => $result->getValue('punisher_id'),
            'server_id' => $serverId,
            'type' => $result->getValue('type'),
            'reason_id' => $reason->id,
            'comment' => $result->getValue('comment'),
            'expired_at' => $time > 0 ? time() + ($time * 60) : null,
            'status' => Punishment::STATUS_PUNISHED
        ]);
        $punishment->save();
        return $response->withJson([
            'success' => true,
            'data' => $punishment->toArray()
        ]);
    }
}
