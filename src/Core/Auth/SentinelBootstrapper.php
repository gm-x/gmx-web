<?php
namespace GameX\Core\Auth;

use \Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use \Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use \Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use \Cartalyst\Sentinel\Hashing\NativeHasher;
use \Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;
use \Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use \Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use \Cartalyst\Sentinel\Sentinel;
use \Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use \Cartalyst\Sentinel\Users\IlluminateUserRepository;
use \GameX\Core\Auth\Repository\UsersRepository;
use \Illuminate\Events\Dispatcher;
use \InvalidArgumentException;
use \Slim\Http\Request;
use \Cartalyst\Sentinel\Native\ConfigRepository;
use \GameX\Core\Auth\Session as SentinelSession;
use \GameX\Core\Session\Session;

class SentinelBootstrapper {

	const USER_MODEL = '\GameX\Core\Auth\Models\UserModel';
	const ROLE_MODEL = '\GameX\Core\Auth\Models\RoleModel';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
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
    public function __construct(Request $request, Session $session) {
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
        $session = $this->createSession();

        $cookie = $this->createCookie();

        $model = $this->config['persistences']['model'];

        $single = $this->config['persistences']['single'];

        return new IlluminatePersistenceRepository($session, $cookie, $model, $single);
    }

    /**
     * Creates a session.
     *
     * @return SentinelSession
     */
    protected function createSession() {
        return new SentinelSession($this->session, $this->config['session']);
    }

    /**
     * Creates a cookie.
     *
     * @return Cookie
     */
    protected function createCookie() {
        return new Cookie($this->request, $this->config['cookie']);
    }

    /**
     * Creates a user repository.
     *
     * @return UsersRepository
     */
    protected function createUsers() {
        $persistences = $this->config['persistences']['model'];

        if (class_exists($persistences) && method_exists($persistences, 'setUsersModel')) {
            forward_static_call_array([$persistences, 'setUsersModel'], [self::USER_MODEL]);
        }

        return new UsersRepository($this->createHasher(), $this->getEventDispatcher(), self::USER_MODEL);
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
        return new IlluminateRoleRepository(self::ROLE_MODEL);
    }

    /**
     * Creates an activation repository.
     *
     * @return \Cartalyst\Sentinel\Activations\IlluminateActivationRepository
     */
    protected function createActivations() {
        $model = $this->config['activations']['model'];

        $expires = $this->config['activations']['expires'];

        return new IlluminateActivationRepository($model, $expires);
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
        $activeCheckpoints = $this->config['checkpoints'];

        $activation = $this->createActivationCheckpoint($activations);

        $throttle = $this->createThrottleCheckpoint($throttle, $ipAddress);

        $checkpoints = [];

        foreach ($activeCheckpoints as $checkpoint) {
            if (! isset($$checkpoint)) {
                throw new InvalidArgumentException("Invalid checkpoint [{$checkpoint}] given.");
            }

            $checkpoints[$checkpoint] = $$checkpoint;
        }

        return $checkpoints;
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
        $model = $this->config['reminders']['model'];

        $expires = $this->config['reminders']['expires'];

        return new IlluminateReminderRepository($users, $model, $expires);
    }
}
