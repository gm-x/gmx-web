<?php

namespace GameX\Core\Auth\Repository;

use \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use \Cartalyst\Sentinel\Users\UserInterface;
use \Cartalyst\Sentinel\Sessions\SessionInterface;
use \Cartalyst\Sentinel\Activations\EloquentActivation;
use \Carbon\Carbon;

class ActivationRepository implements ActivationRepositoryInterface
{
    /**
     * The activation expiration time, in seconds.
     *
     * @var int
     */
    protected $expires = 259200;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * Create a new Illuminate activation repository.
     * @param SessionInterface $session
     * @param int $expires
     * @return void
     */
    public function __construct(SessionInterface $session, $expires = null)
    {
        $this->session = $session;
        if ($expires !== null) {
            $this->expires = $expires;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function create(UserInterface $user)
    {
        $activation = new EloquentActivation();

        $code = $this->generateActivationCode();

        $activation->fill(compact('code'));

        $activation->user_id = $user->getUserId();

        $activation->save();

        return $activation;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(UserInterface $user, $code = null)
    {
        $expires = $this->expires();

        $activation = EloquentActivation::where('user_id', $user->getUserId())
            ->where('completed', false)
            ->where('created_at', '>', $expires);

        if ($code) {
            $activation->where('code', $code);
        }

        return $activation->first() ?: false;
    }

    /**
     * {@inheritDoc}
     */
    public function complete(UserInterface $user, $code)
    {
        $expires = $this->expires();

        $activation = EloquentActivation::where('user_id', $user->getUserId())
            ->where('code', $code)
            ->where('completed', false)
            ->where('created_at', '>', $expires)
            ->first();

        if ($activation === null) {
            return false;
        }

        $activation->fill([
            'completed'    => true,
            'completed_at' => Carbon::now(),
        ]);

        $activation->save();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function completed(UserInterface $user)
    {
        $completed = $this->session->get();
        if ($completed === null) {
            $activation = EloquentActivation::where('user_id', $user->getUserId())
                ->where('completed', true)
                ->first();
            $completed = $activation ?: false;
            $this->session->put($completed);
        }

        return $completed;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(UserInterface $user)
    {
        $activation = $this->completed($user);

        if ($activation === false) {
            return false;
        }

        return $activation->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function removeExpired()
    {
        $expires = $this->expires();

        return EloquentActivation::where('completed', false)
            ->where('created_at', '<', $expires)
            ->delete();
    }

    /**
     * Returns the expiration date.
     *
     * @return \Carbon\Carbon
     */
    protected function expires()
    {
        return Carbon::now()->subSeconds($this->expires);
    }

    /**
     * Return a random string for an activation code.
     *
     * @return string
     */
    protected function generateActivationCode()
    {
        return str_random(32);
    }
}