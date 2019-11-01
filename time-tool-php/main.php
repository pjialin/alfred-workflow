<?php

require_once 'helpers.php';

class TimeTool
{
    /** * @var Workflow */
    protected $workflow = null;
    protected $args = [];
    protected $defaultFormat = 'Y-m-d H:i:s'; // cn
    protected $commands = [
        'show', 'run'
    ];

    protected $timestamp = 0;

    /**
     * TimeTool constructor.
     *
     * @param $argv
     */
    public function __construct($argv = [])
    {
        $this->workflow = new Workflow();
        if(count($argv) > 1)
            $this->args = explode(' ', $argv[1]);
        $this->formatArgs();
    }

    public function run()
    {
        $command = 'show';
        if($this->args && in_array($this->args[0], $this->commands))
            $command = $this->args[0];
        $method = 'command' . ucfirst($command);
        $this->$method();
    }

    public function commandShow()
    {
        $this->addDate();
        $this->addTimestamp();
        $this->show();
    }

    public function commandRun()
    {
        $icon = 'icon/run.png';
        $code = implode(' ', array_slice($this->args, 1));
        if(!$code) {
            $this->workflow->result()->icon($icon)->title('Run any php code.');

            return $this->show();
        }
        set_error_handler([$this, 'errorHandler']);
        try {
            $res = eval('return ' . $code . ';');
        } catch (\Exception $exception) {
            $res = 'Error: ' . $exception->getMessage();
        }
        $this->workflow->result()
            ->title($res)
            ->icon($icon)
            ->subtitle('Run result');

        $this->show();
    }

    protected function addDate()
    {
        $date = date($this->defaultFormat, $this->timestamp);
        $this->workflow->result()
            ->title($date)
            ->icon('icon/clock.png')
            ->subtitle('Date');
    }

    protected function addTimestamp()
    {
        $this->workflow->result()
            ->title($this->timestamp)
            ->icon('icon/chip.png')
            ->subtitle('Timestamp');
    }

    protected function formatArgs()
    {
        $timestamp = 0;
        foreach ($this->args as $arg) {
            if(!$timestamp && strtotime($arg) !== false)
                $timestamp = strtotime($arg);
            if(is_numeric($arg)) // number check
                $timestamp = $arg;
        }
        if($timestamp)
            $this->timestamp = $timestamp;
        else
            $this->timestamp = time();
    }

    protected function show()
    {
        echo $this->workflow->output();

        return true;
    }

    protected function save()
    {

    }

    protected function errorHandler($errNo, $errStr, $errFile, $errLine)
    {
        throw new Exception($errStr);
    }
}

(new TimeTool($argv))->run();

