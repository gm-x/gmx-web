<?php
namespace GameX\Core\Rememberable;

trait Rememberable {

    /**
     * @var string|null
     */
    protected $rememberCacheKey = null;

    /**
     * @var bool
     */
    protected $rememberCache = true;

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder() {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        $builder = new Builder($conn, $grammar, $conn->getPostProcessor());
        $builder->remember($this->rememberCache, $this->rememberCacheKey);

        return $builder;
    }
}