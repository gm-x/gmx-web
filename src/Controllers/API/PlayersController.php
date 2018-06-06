<?php
namespace GameX\Controllers\API;

use \GameX\Core\BaseApiController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Carbon\Carbon;
use \GameX\Models\Server;
use \GameX\Models\Player;
use \GameX\Models\Punishment;
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
                'reason' => Carbon::parse($punishment->expired_at)->getTimestamp(),
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
        $player = Player::find($request->getQueryParam('player_id'));
        $adminId = $request->getQueryParam('admin_id');
        if ($adminId != 0) {
			$admin = Player::find($adminId);
		}

		$server = Server::find($request->getQueryParam('server_id'));

        $reason = $request->getQueryParam('reason');
        $expired_at = $request->getQueryParam('expired_at');
    }
}
