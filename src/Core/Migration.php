<?php

namespace GameX\Core;

use Phpmig\Migration\Migration as BaseMigration;
use \Illuminate\Database\Schema\Builder;

abstract class Migration extends BaseMigration
{
    /**
     * @return Builder
     */
    protected function getSchema()
    {
        return $this->container['db']->schema();
    }
}
