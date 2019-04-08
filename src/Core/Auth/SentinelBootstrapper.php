<?php
namespace GameX\Core\Auth;

use \Slim\Http\Request;
use \Illuminate\Events\Dispatcher;
use \Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use \GameX\Core\Auth\Repository\ActivationRepository;
use \Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use \Cartalyst\Sentinel\Hashing\NativeHasher;
use \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use \GameX\Core\Auth\Repository\PersistenceRepository;
use \Cartalyst\Sentinel\Reminders\EloquentReminder;
use \Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use \Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use \Cartalyst\Sentinel\Sentinel;
use \Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use \GameX\Core\Auth\Repository\ThrottleRepository;
use \Cartalyst\Sentinel\Users\IlluminateUserRepository;
use \Cartalyst\Sentinel\Cookies\CookieInterface;
use \Cartalyst\Sentinel\Sessions\SessionInterface;
use \GameX\Core\Session\Session;
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
     * @return PersistenceRepositoryInterface
     */
    protected function createPersistence() {
        return new PersistenceRepository(
            $this->createSession('auth_code'),
            $this->createSession('auth_user'),
            $this->createCookie('gmx_key'),
            false
        );
    }

    /**
     * Creates a session.
     *
     * @param string $key
     * @return SessionInterface
     */
    protected function createSession($key) {
        if ($this->session) {
            return new SentinelSession($this->session, $key);
        } else {
            return new SentinelFakeSession();
        }
    }

    /**
     * Creates a cookie.
     * @param string $key
     * @return CookieInterface
     */
    protected function createCookie($key) {
        if ($this->request) {
            return new SentinelCookie($this->request, [
                'name' => $key,
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
     * @return ActivationRepositoryInterface
     */
    protected function createActivations() {
        return new ActivationRepository($this->createSession('auth_activation'),259200);
    }

    /**
     * Create a throttling repository.
     *
     * @return ThrottleRepositoryInterface
     */
    protected function createThrottling() {
        return new ThrottleRepository(
            900, [
                10 => 1,
                20 => 2,
                30 => 4,
                40 => 8,
                50 => 16,
                60 => 12
            ],
            900, 5,
            900, 3
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
