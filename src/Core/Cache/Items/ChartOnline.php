<?php
namespace GameX\Core\Cache\Items;

use \GameX\Core\Cache\CacheItem;
use \GameX\Models\PlayerSession;
use \Carbon\Carbon;

class ChartOnline extends CacheItem {

    /**
     * @inheritdoc
     */
    protected function getData($element) {
	    $date = Carbon::now()->subWeeks(4);

	    $list = PlayerSession::where('created_at', '>', $date)
		    ->get()
		    ->map(function (PlayerSession $session) {
			    $result = [
				    [
					    'connect' => true,
					    'date' => $session->created_at
				    ]
			    ];

			    if ($session->disconnected_at) {
				    $result[] = [
					    'connect' => false,
					    'date' => $session->disconnected_at
				    ];
			    }
			    return $result;
		    })
		    ->flatten(1)
		    ->sortBy('date');

	    $online = PlayerSession::where('created_at', '<', $date)
		    ->whereNotNull('disconnected_at')
		    ->where('disconnected_at', '>', $date)
		    ->count();

	    $data = [];
	    foreach ($list as $item) {
		    $key = $item['date']->toIso8601String();
		    if ($item['connect']) {
			    $online++;
		    } else {
			    $online--;
		    }
		    $data[$key] = $online;
	    }
        return $data;
    }

	/**
	 * @inheritdoc
	 */
	protected function getTTL()
	{
		return 86400;
	}
}