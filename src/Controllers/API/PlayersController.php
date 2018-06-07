<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
use \Carbon\Carbon;
use \Form\Validator;
use \Slim\Exception\NotFoundException;

class PlayersController extends BaseApiController {

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return static
     * @throws NotFoundException
     */
    public function indexAction(Request $request, Response $response, array $args) {
        $steamid = $request->getQueryParam('steamid');
        if (!$steamid || !preg_match('/^(?:STEAM|VALVE)_\d:\d:\d+$/', $steamid)) {
            throw new NotFoundException($request, $response);
        }
        $player = Player::where('steamid', '=', $request->getQueryParam('steamid'))->first();
        if (!$player) {
            $player = new Player();
            $player->steamid = $request->getQueryParam('steamid');
            $player->nick = $request->getQueryParam('nick', '');
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
            'player' => [
                'id' => $player->id,
            ],
            'punishments' => $punishments
        ]);
    }

    public function punishAction(Request $request, Response $response, array $args) {
    	try {
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

			if (!$validator->validate($request->getQueryParams())) {
				return $response->withJson([
					'query' => $request->getQueryParams(),
					'values' => $validator->getValues(),
					'errors' => $validator->getErrors(),
				]);
				throw new \Exception('Error validation');
			}

			$punishment = new Punishment($validator->getValues());
			$punishment->server_id = $request->getAttribute('server_id');
			$punishment->status = Punishment::STATUS_PUNISHED;
			$punishment->save();
			return $response->withJson([
				'success' => true,
				'punishments' => $punishment->toArray()
			]);
		} catch (\Exception $e) {
			return $response->withJson([
				'success' => false,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
    }
}
