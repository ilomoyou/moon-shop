<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class DbSqlListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        if (!app()->environment(['testing', 'local'])) {
            return;
        }

        if (getenv('LISTENING_SQL_LOG') != "true") {
            return;
        }

        $sql = $event->sql;
        $bindings = $event->bindings;
        $time = $event->time;

        $bindings = array_map(function ($binding) {
            if (is_string($binding)) {
                return "'$binding'";
            }
            if ($binding instanceof \DateTime) {
                return $binding->format("'Y-m-d H:i:s'");
            }
            return $binding;
        }, $bindings);

        $sql = str_replace("?", "%s", $sql);
        $sql = sprintf($sql, ...$bindings);
        Log::info('SQL Log', ['sql' => $sql, 'time' => $time]);
    }
}
