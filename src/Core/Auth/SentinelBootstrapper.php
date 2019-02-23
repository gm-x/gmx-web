<?php
namespace GameX\Core\Auth;

use \Slim\Http\Request;
use \Illuminate\Events\Dispatcher;
use \Cartalyst\Sentinel\Activations\EloquentActivation;
use \Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use \Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use \Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use \Cartalyst\Sentinel\Hashing\NativeHasher;
use \Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;
use \Cartalyst\Sentinel\Reminders\EloquentReminder;
use \Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use \Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use \Cartalyst\Sentinel\Sentinel;
use \Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use \Cartalyst\Sentinel\Users\IlluminateUserRepository;
use \Cartalyst\Sentinel\Cookies\CookieInterface;
use \Cartalyst\Sentinel\Sessions\SessionInterface;
use \GameX\Core\Session\Session;
use \GameX\Core\Auth\Models\PersistenceModel;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Auth\Repository\UsersRepository;
use \GameX\Core\Auth\Http\Cookie as SentinelCookie;
use \GameX\Core\Auth\Http\FakeCookie as SentinelFakeCookie;
use \GameX\Core\Auth\Http\Session as SentinelSession;
use \GameX\Core\Auth\Http\FakeSession as SentinelFakeSession;
use \GameX\Core\Helpers\IpHelper;

class SentinelBootstrapper {
    
    /**
     * @var Request|null
     */
    protected $request;

    /**
     * @var Session|null
     */
    protected $session;

    /**
     * The event dispatcher.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Constructor.
     *
     * @param Request $request
     * @param Session $session
     */
    public function __construct(Request $request = null, Session $session = null) {
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * Creates a sentinel instance.
     *
     * @return \Cartalyst\Sentinel\Sentinel
     */
    public function createSentinel() {
        $persistence = $this->createPersistence();
        $users       = $this->createUsers();
        $roles       = $this->createRoles();
        $activations = $this->createActivations();
        $dispatcher  = $this->getEventDispatcher();

        $sentinel = new Sentinel(
            $persistence,
            $users,
            $roles,
            $activations,
            $dispatcher
        );

        $throttle = $this->createThrottling();

        $sentinel->addCheckpoint('activation', new ActivationCheckpoint($activations));

        if ($this->request !== null) {
            $sentinel->addCheckpoint('throttle',
                new ThrottleCheckpoint($throttle, IpHelper::getIPAddress($this->request))
            );
        }

        $reminders = $this->createReminders($users);

        $sentinel->setActivationRepository($activations);

        $sentinel->setReminderRepository($reminders);

        $sentinel->setThrottleRepository($throttle);

        return $sentinel;
    }

    /**
     * Creates a persistences repository.
     *
     * @return \Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository
     */
    protected function createPersistence() {
        return new IlluminatePersistenceRepository($this->createSession(), $this->createCookie(), PersistenceModel::class, false);
    }

    /**
     * Creates a session.
     *
     * @return SessionInterface
     */
    protected function createSession() {
        if ($this->session) {
            return new SentinelSession($this->session, 'auth_data');
        } else {
            return new SentinelFakeSession();
        }
    }

    /**
     * Creates a cookie.
     *
     * @return CookieInterface
     */
    protected function createCookie() {
        if ($this->request) {
            return new SentinelCookie($this->request, [
                'name' => 'persistence_key',
                'secure' => $this->request->getUri()->getScheme() == 'https',
                'http_only' => true
            ]);
        } else {
            return new SentinelFakeCookie();
        }
    }

    /**
     * Creates a user repository.
     *
     * @return UsersRepository
     */
    protected function createUsers() {
        return new UsersRepository($this->createHasher(), $this->getEventDispatcher(), UserModel::class);
    }

    /**
     * Creates a hasher.
     *
     * @return \Cartalyst\Sentinel\Hashing\NativeHasher
     */
    protected function createHasher() {
        return new NativeHasher;
    }

    /**
     * Creates a role repository.
     *
     * @return \Cartalyst\Sentinel\Roles\IlluminateRoleRepository
     */
    protected function createRoles() {
        return new IlluminateRoleRepository(RoleModel::class);
    }

    /**
     * Creates an activation repository.
     *
     * @return \Cartalyst\Sentinel\Activations\IlluminateActivationRepository
     */
    protected function createActivations() {
        return new IlluminateActivationRepository(EloquentActivation::class, 259200);
    }


    /**
     * Create activation and throttling checkpoints.
     *
     * @param  \Cartalyst\Sentinel\Activations\IlluminateActivationRepository  $activations
     * @param  \Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository  $throttle
     * @param  string  $ipAddress
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function createCheckpoints(IlluminateActivationRepository $activations, IlluminateThrottleRepository $throttle, $ipAddress) {
        $result = [
            'activation' => new ActivationCheckpoint($activations)
        ];

        if ($ipAddress !== null) {
            $result['throttle'] = new ThrottleCheckpoint($throttle, $ipAddress);
        }

        return $result;
    }

    /**
     * Create a throttling repository.
     *
     * @return \Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository
     */
    protected function createThrottling() {
//        $model = $this->config['throttling']['model'];
//
//        foreach (['global', 'ip', 'user'] as $type) {
//            ${"{$type}Interval"} = $this->config['throttling'][$type]['interval'];
//
//            ${"{$type}Thresholds"} = $this->config['throttling'][$type]['thresholds'];
//        }
//
//        return new IlluminateThrottleRepository(
//            $model,
//            $globalInterval,
//            $globalThresholds,
//            $ipInterval,
//            $ipThresholds,
//            $userInterval,
//            $userThresholds
//        );
    
        return new IlluminateThrottleRepository(
            'Cartalyst\Sentinel\Throttling\EloquentThrottle',
            900, [
                10 => 1,
                20 => 2,
                30 => 4,
                40 => 8,
                50 => 16,
                60 => 12
            ],
            900, 5,
            900, 5
        );
    }

    /**
     * Returns the event dispatcher.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    protected function getEventDispatcher() {
        if (! $this->dispatcher) {
            $this->dispatcher = new Dispatcher;
        }

        return $this->dispatcher;
    }

    /**
     * Create a reminder repository.
     *
     * @param  \Cartalyst\Sentinel\Users\IlluminateUserRepository  $users
     * @return \Cartalyst\Sentinel\Reminders\IlluminateReminderRepository
     */
    protected function createReminders(IlluminateUserRepository $users) {
        return new IlluminateReminderRepository($users, EloquentReminder::class, 14400);
    }
}
