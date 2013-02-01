<?php
//namespace Verzs\Profiler;

//use Verzs\Exception;

use Zend_Log as Logger;

//use Doctrine\DBAL\Logging\SQLLogger;

/**
 * Very basic tool to measure performance
 *
 * @todo make log writer flexible (support zend log)
 * @todo make log level configurable
 *
 */
class EngineBlock_Profiler
{
    // @todo make these the same?
    const START_RECORD = 'start';
    const END_RECORD_NAME = '::END::';

    private static $bootstrapStartTime;

    /**
     * @var Zend_Log
     */
    private $logger;

    /** @var array */
    private $records;

    private $title;

    private $isStarted;

    private $totalQueryCount = 0;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        $this->records = array();
        if (self::$bootstrapStartTime) {
            $this->addRecord('Bootstrap / Routing', self::$bootstrapStartTime);
        }

        $this->isStarted = false;
    }

    public static function markBootstrapStart()
    {
        self::$bootstrapStartTime = microtime(true);
    }

    /**
     * Logs a Doctrine SQL statement.
     *
     * @param string $sql The SQL to be executed.
     * @param array $params The SQL parameters.
     * @param array $types The SQL parameter types.
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->totalQueryCount++;
    }

    /**
     * Mark the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        // do nothing
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->isStarted;
    }

    public function start($title)
    {
        $this->title = $title;

        if ($this->isStarted()) {
            throw new Exception('Profiler has already started');
        }

        $this->addRecord(self::START_RECORD);
        $this->isStarted = true;
    }

    /**
     * @param string $name
     * @throws Exception
     */
    public function startBlock($name)
    {
        if (!$this->isStarted()) {
            $this->start('Auto start');
//            throw new Exception('Profiler has not started yet');
        }

        $this->addRecord($name);
    }

    public function endBlock()
    {
        if (!$this->isStarted()) {
            throw new Exception('Profiler has not started yet');
        }

        if ($this->isPreviousEndRecord()) {
            throw new Exception('Profiler: block cannot be ended: no block was started');
        }


        $this->addRecord(self::END_RECORD_NAME);
    }


    private function isPreviousEndRecord()
    {
        if ($this->records[count($this->records) - 1]['name'] === self::END_RECORD_NAME) {
            return true;
        }
        return false;
    }


    private function addRecord($name, $time = null)
    {
        if(is_null($time)) {
            $time = microtime(true);
        }

        $recordCount = count($this->records);

        $currentMemUsage = memory_get_usage();
        if ($recordCount > 0) {
            $this->records[$recordCount - 1]['peakmem'] = memory_get_peak_usage();
            $previousMemUsage = $this->records[$recordCount - 1]['memusage'];
            $this->records[$recordCount - 1]['memusagediff'] = $currentMemUsage - $previousMemUsage;
        }


        $this->records[] = array(
            'memusagediff' => 0,
            'peakmem' => 0,
            'number' => $recordCount + 1,
            'name' => $name,
            'time' => $time,
            'memusage' => $currentMemUsage,
        );
    }

    /**
     * @return string
     */
    public function getReport()
    {
        $endTime = microtime(true);
        $topTimes = array();
        $startTime = $this->records[0]['time'];
        $totalTime = $endTime - $startTime;
        $lastEndTime = $endTime;
        foreach (array_reverse($this->records) as $record) {
            $taskTime = $lastEndTime - $record['time'];

            if ($record['name'] != self::END_RECORD_NAME) {
                $topTimes[] = array(
                    'memusage' => $record['memusage'],
                    'memusagediff' => $record['memusagediff'],
                    'peakmem' => $record['peakmem'],
                    'number' =>  $record['number'],
                    'name' => $record['name'],
                    'milliseconds' => round($taskTime * 1000),
                    'percentage' => str_pad(round(($taskTime / $totalTime) * 100), 2, ' ', STR_PAD_LEFT),
                );
            }

            $lastEndTime = $record['time'];
        }

        // Sort by time
        usort($topTimes, function($a, $b)
        {
            return $a['milliseconds'] < $b['milliseconds'] ? 1 : -1; // sort by processor time desc
//            return $a['peakmem'] < $b['peakmem'] ? 1 : -1; // sort by mem usage desc
            return $a['number'] > $b['number'] ? 1 : -1;
        });


        $totalPeakMemFormatted = str_pad(round(memory_get_peak_usage() / (1024 * 1024), 2) . 'MB', 8, ' ', STR_PAD_LEFT);
        $totalTimeFormatted = round($totalTime, 2);
        $report = '# Profiler:' . PHP_EOL
            . '# Profiler:' . PHP_EOL
            . "########### Profiler: '$this->title' {$totalTimeFormatted}s total, {$totalPeakMemFormatted} ###########" . PHP_EOL
            . 'Profiler: REQUEST: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;

        foreach ($topTimes as $record) {
            $numberFormatted = str_pad($record['number'], 5, ' ', STR_PAD_LEFT);
            $peakMemFormatted = str_pad(round($record['peakmem'] / (1024 * 1024), 2) . 'MB', 20, ' ', STR_PAD_LEFT);
            $memDiffFormatted= str_pad(round($record['memusagediff'] / (1024 * 1024), 2) . 'MB', 20, ' ', STR_PAD_LEFT);
            $timeFormatted = str_pad($record['milliseconds'] . "ms ({$record['percentage']}%)", 20, ' ', STR_PAD_LEFT);
            $report .= PHP_EOL . " - Profiler:{$numberFormatted} {$timeFormatted} {$memDiffFormatted}{$peakMemFormatted} - \"{$record['name']}\" ";
        }

        return $report;
    }

    public function logReport() {
        foreach (explode(PHP_EOL, $this->getReport()) as $line) {
            $this->logger->debug($line);
        }
    }

}