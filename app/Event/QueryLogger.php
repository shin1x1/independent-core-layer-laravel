<?php
declare(strict_types=1);

namespace App\Event;

use Illuminate\Database\Events\QueryExecuted;
use Psr\Log\LoggerInterface;

class QueryLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param QueryExecuted $event
     */
    public function handle(QueryExecuted $event)
    {
        $message = sprintf(
            '[%s] %s %s %dms',
            $event->connectionName,
            $event->sql,
            json_encode($event->bindings),
            $event->time
        );

        $this->logger->info($message);
    }
}
