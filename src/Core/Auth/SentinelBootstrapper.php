<?php
namespace GameX\Core\Auth;

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
use \GameX\Core\Auth\Models\PersistenceModel;
use \GameX\Core\Auth\Models\RoleModel;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Auth\Repository\UsersRepository;
use \Illuminate\Events\Dispatcher;
use \InvalidArgumentException;
use \Slim\Http\Request;
use \Cartalyst\Sentinel\Native\ConfigRepository;
use \GameX\Core\Auth\Session as SentinelSession;
use \GameX\Core\Session\Session;

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
     * Configuration.
     *
     * @var array
     */
    protected $config;

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
        $this->config = new ConfigRepository(__DIR__ . '/config.php');
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

        $ipAddress = $this->getIpAddress();

        $checkpoints = $this->createCheckpoints($activations, $throttle, $ipAddress);

        foreach ($checkpoints as $key => $checkpoint) {
            $sentinel->addCheckpoint($key, $checkpoint);
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
     * @return SentinelSession
     */
    protected function createSession() {
        return new SentinelSession($this->session, 'auth_data');
    }

    /**
     * Creates a cookie.
     *
     * @return Cookie
     */
    protected function createCookie() {
        return new Cookie($this->request, 'persistence_key');
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
     * Returns the client's ip address.
     *
     * @return string
     */
    protected function getIpAddress() {
        return $this->request->getAttribute('ip_address');
    }

    /**
     * Create an activation checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Activations\IlluminateActivationRepository  $activations
     * @return \Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint
     */
    protected function createActivationCheckpoint(IlluminateActivationRepository $activations) {
        return new ActivationCheckpoint($activations);
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
    	return [
    		'activation' => $this->createActivationCheckpoint($activations),
    		'throttle' => $this->createThrottleCheckpoint($throttle, $ipAddress),
		];
    }

    /**
     * Create a throttle checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository  $throttle
     * @param  string  $ipAddress
     * @return \Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint
     */
    protected function createThrottleCheckpoint(IlluminateThrottleRepository $throtte, $ipAddress) {
        return new ThrottleCheckpoint($throtte, $ipAddress);
    }

    /**
     * Create a throttling repository.
     *
     * @return \Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository
     */
    protected function createThrottling() {
        $model = $this->config['throttling']['model'];

        foreach (['global', 'ip', 'user'] as $type) {
            ${"{$type}Interval"} = $this->config['throttling'][$type]['interval'];

            ${"{$type}Thresholds"} = $this->config['throttling'][$type]['thresholds'];
        }

        return new IlluminateThrottleRepository(
            $model,
            $globalInterval,
            $globalThresholds,
            $ipInterval,
            $ipThresholds,
            $userInterval,
            $userThresholds
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
