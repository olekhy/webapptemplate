<?php

declare(strict_types=1);

namespace App;

use Aura\Sql\ExtendedPdo;

trait DbConnection
{
    public function getConnection() : ExtendedPdo
    {
        [
            'dsn'      => $dsn,
            'username' => $username,
            'password' => $password,
            'options'  => $options,
            'queries'  => $queries,
            'profiler' => $profiler,

        ] = App::getConfig()['db'];

        return new ExtendedPdo(
            $dsn,
            $username,
            $password,
            $options,
            $queries,
            $profiler
        );
    }
}
