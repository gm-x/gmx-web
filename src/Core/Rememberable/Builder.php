<?php
namespace GameX\Core\Rememberable;

class Builder extends \Illuminate\Database\Query\Builder {

    /**
     * @var array
     */
    protected static $cachedData = [];

    /**
     * @var string|null
     */
    protected $cacheKey = null;

    /**
     * @var bool
     */
    protected $cache = false;

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*']) {
        if (!$this->cache) {
            return parent::get($columns);
        }

        if (is_null($this->columns)) {
            $this->columns = $columns;
        }

        // If the query is requested to be cached, we will cache it using a unique key
        // for this database connection and query statement, including the bindings
        // that are used on this query, providing great convenience when caching.
        $key = $this->getCacheKey();
        if (array_key_exists($key, self::$cachedData)) {
            return self::$cachedData[$key];
        }

        $data = parent::get($columns);
        self::$cachedData[$key] = $data;

        return $data;
    }

    /**
     * Indicate that the query results should be cached.
     *
     * @param  bool  $remember
     * @param  string|null  $key
     * @return $this
     */
    public function remember($remember, $key = null) {
        $this->cache = (bool) $remember;
        $this->cacheKey = $key;

        return $this;
    }

    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey() {
        return $this->cacheKey ?: $this->generateCacheKey();
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    public function generateCacheKey() {
        $name = $this->connection->getName();
        return hash('sha256', $name.$this->toSql().serialize($this->getBindings()));
    }
}