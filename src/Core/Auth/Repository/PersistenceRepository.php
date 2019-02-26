<?php

namespace GameX\Core\Auth\Repository;

use \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use \Cartalyst\Sentinel\Persistences\PersistableInterface;
use \Cartalyst\Sentinel\Sessions\SessionInterface;
use \Cartalyst\Sentinel\Cookies\CookieInterface;
use \GameX\Core\Auth\Models\PersistenceModel;
use \GameX\Core\Auth\Models\UserModel;

class PersistenceRepository implements PersistenceRepositoryInterface
{
    /**
     * Single session.
     *
     * @var boolean
     */
    protected $single = false;

    /**
     * Session storage driver.
     *
     * @var \Cartalyst\Sentinel\Sessions\SessionInterface
     */
    protected $sessionCode;
    protected $sessionUser;

    /**
     * Cookie storage driver.
     *
     * @var \Cartalyst\Sentinel\Cookies\CookieInterface
     */
    protected $cookie;

    /**
     * Create a new Sentinel persistence repository.
     *
     * @param  \Cartalyst\Sentinel\Sessions\SessionInterface  $sessionCode
     * @param  \Cartalyst\Sentinel\Sessions\SessionInterface  $sessionUser
     * @param  \Cartalyst\Sentinel\Cookies\CookieInterface  $cookie
     * @param  bool  $single
     * @return void
     */
    public function __construct(SessionInterface $sessionCode, SessionInterface $sessionUser, CookieInterface $cookie, $single = false)
    {
        $this->sessionCode = $sessionCode;
        $this->sessionUser = $sessionUser;
        $this->cookie = $cookie;
        $this->single = $single;
    }

    /**
     * {@inheritDoc}
     */
    public function check()
    {
        if ($code = $this->sessionCode->get()) {
            return $code;
        }

        if ($code = $this->cookie->get()) {
            return $code;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findByPersistenceCode($code)
    {
        $persistence = PersistenceModel::where('code', $code)
            ->first();

        return $persistence ? $persistence : false;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByPersistenceCode($code)
    {
        $sessionUser = $this->sessionUser->get();
        if ($sessionUser !== null && $sessionUser['expired'] < time()) {
            return UserModel::find($sessionUser['user']);
        }

        $persistence = $this->findByPersistenceCode($code);
        if (!$persistence) {
            return false;
        }
        /** @var UserModel $user */
        $user = $persistence->user;
        $this->sessionUser->put([
            'user' => $user->id,
            'expired' => time() + 60
        ]);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function persist(PersistableInterface $persistable, $remember = false)
    {
        if ($this->single) {
            $this->flush($persistable);
        }

        $code = $persistable->generatePersistenceCode();

        $this->sessionCode->put($code);

        if ($remember === true) {
            $this->cookie->put($code);
        }

        $key = $persistable->getPersistableKey();
        $id = $persistable->getPersistableId();
        $persistence = new PersistenceModel([
            $key => $id,
            'code' => $code
        ]);

        return $persistence->save();
    }

    /**
     * {@inheritDoc}
     */
    public function persistAndRemember(PersistableInterface $persistable)
    {
        return $this->persist($persistable, true);
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $code = $this->check();

        if ($code === null) {
            return;
        }

        $this->sessionCode->forget();
        $this->sessionUser->forget();
        $this->cookie->forget();

        return $this->remove($code);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($code)
    {
        return PersistenceModel::where('code', $code)->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function flush(PersistableInterface $persistable, $forget = true)
    {
        if ($forget) {
            $this->forget();
        }

        foreach ($persistable->{$persistable->getPersistableRelationship()}()->get() as $persistence) {
            if ($persistence->code !== $this->check()) {
                $persistence->delete();
            }
        }
    }
}