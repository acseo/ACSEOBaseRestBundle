<?php

namespace ACSEO\Bundle\BaseRestBundle\Processor;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bridge\Monolog\Processor\WebProcessor;

class RequestProcessor extends WebProcessor implements \Monolog\Formatter\FormatterInterface
{
    private $_session;
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
        $this->_session = $container->get('session');
    }

    public function processRecord(array $record)
    {
        $record['extra']['serverData'] = '';

        if (is_array($this->serverData)) {
            foreach ($this->serverData as $key => $value) {
                if (is_array($value)) {
                    $value = print_r($value, true);
                }

                $record['extra']['serverData'] .= $key.': '.$value."\n";
            }
        }

        foreach ($_SERVER as $key => $value) {
            if (is_array($value)) {
                $value = print_r($value, true);
            }

            $record['extra']['serverData'] .= $key.': '.$value."\n";
        }

        return $record;
    }

    /**
     * Formats a log record.
     *
     * @param array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        return serialize($record);
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     *
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        echo 'formatBatch';
    }
}
