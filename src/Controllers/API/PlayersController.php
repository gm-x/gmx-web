<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
use \Carbon\Carbon;
use \Form\Validator;
use \GameX\Core\Exceptions\ApiException;
use \Exception;

class PlayersController extends BaseApiController {

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ApiException
     */
    public function playerAction(Request $request, Response $response, array $args) {
        $validator = new Validator([
            'steamid' => ['required', 'trim', 'min_length' => 1, 'regexp' => '/^(?:STEAM|VALVE)_\d:\d:\d+$/'],
            'nick' => ['required', 'trim', 'min_length' => 1],
        ]);

        if (!$validator->validate($this->getBody($request))) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }

        $player = Player::where('steamid', '=', $validator->getValue('steamid'))->first();
        if (!$player) {
            $player = new Player();
            $player->steamid = $validator->getValue('steamid');
            $player->nick = $validator->getValue('nick');
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
        $playerExists = function ($id) {
            return Player::where('id', '=', $id)->exists();
        };

        $punisherExists = function ($id) {
            return ($id == 0 || Player::where('id', '=', $id)->exists());
        };

        $validator = new Validator([
            'player_id' => ['required', 'integer', 'min' => 1, 'exists' => $playerExists],
            'punisher_id' => ['required', 'integer', 'min' => 0, 'exists' => $punisherExists],
            'type' => ['required', 'integer'],
            'reason' => ['required', 'min_length' => 1],
            'expired_at' => ['required', 'integer']
        ]);

        if (!$validator->validate($this->getBody($request))) {
            throw new ApiException('Validation', ApiException::ERROR_VALIDATION);
        }

        $punishment = new Punishment($validator->getValues());
        $punishment->server_id = $request->getAttribute('server_id');
        $punishment->status = Punishment::STATUS_PUNISHED;
        $punishment->save();
        return $response->withJson([
            'success' => true,
            'data' => $punishment->toArray()
        ]);
    }
}
