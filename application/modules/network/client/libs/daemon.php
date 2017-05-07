<?php

/**
 * Php daemon.
 *
 * @author abatukhtin
 */
class Daemon
{
    const DEV_NULL = '/dev/null';
    private $logs_dir =  './logs/';
    private $omit_output = true;
    public $pid_file;
    public $err_log_file;
    public $stdout_file;
    public $stderr_file;
    public $pid;

    /**
     * Constructor
     *
     * @param string $pid_file
     * @param string $err_log_file
     * @param string $stdout_file
     * @param string $stderr_file
     */
    public function __construct($dir, $omit_output = true)
    {
        // Without that directive php won't catch signals
        $this->omit_output($omit_output);
        $this->setOutputDir($dir);
    }

    public function omit_output($omit_output)
    {
        $this->omit_output = (bool) $omit_output;

        return $this;
    }

    private function setOutputDir($dir)
    {
        $this->logs_dir = $dir;
        $this->pid_file = $this->logs_dir . 'daemon.pid';
        if ($this->omit_output) {
            $this->err_log_file = self::DEV_NULL;
            $this->stdout_file = self::DEV_NULL;
            $this->stderr_file = self::DEV_NULL;
        } else {
            $this->err_log_file = $this->logs_dir . 'error.log';
            $this->stdout_file = $this->logs_dir . 'stdout';
            $this->stderr_file = $this->logs_dir . 'stderr';
        }

        return $dir;
    }

    /**
     * Check file existence and try to create id if necessary
     *
     * @param string $path
     *
     * @throws Exception
     *
     * @return boolean
     */
    private function checkFile($path)
    {
        if (file_exists($path)) {
            if (!is_file($path)) {
                throw new Exception('err_create_file ' . $path);
            }
        } else {
            if (!file_exists(dirname($path)) && !mkdir(dirname($path), 0777, true)) {
                throw new Exception('err_create_file ' . $path);
            }
            if (!touch($path)) {
                throw new Exception('err_create_file ' . $path);
            }
            chmod($path, 0777);
        }

        return true;
    }

    /**
     * Create child process and unbind it from console.
     * Process will be stored in $this->pid_file
     */
    private function forkProcess()
    {
        $this->checkFile($this->pid_file);
        // Create child process. Code after pcntl_fork() will be executed
        // by two processes: parent and child.
        $child_pid = pcntl_fork();
        if ($child_pid) {
            // Parent goes here
            exit();
        } else {
            // Child goes here
        }
        // Make the child process the main
        posix_setsid();
        $this->pid = getmypid();
        file_put_contents($this->pid_file, $this->pid);

        return $this->pid;
    }

    /**
     * Close STDIN, STDOUT, STDERR and redirect system output to a file.
     * (IMPORTANT!) Using the constants after that most likely will fail.
     *
     * @global resource $STDIN
     * @global resource $STDOUT
     * @global resource $STDERR
     */
    private function overrideStdOutput()
    {
        global $STDIN, $STDOUT, $STDERR;
        if (!$this->omit_output) {
            $this->checkFile($this->err_log_file);
            $this->checkFile($this->stdout_file);
            $this->checkFile($this->err_log_file);
        }
        ini_set('error_log', $this->err_log_file);

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        $STDIN = fopen(self::DEV_NULL, 'r');
        $STDOUT = fopen($this->stdout_file, 'a+');
        $STDERR = fopen($this->stderr_file, 'a+');
    }

    public function bind($signal, $cb)
    {
        pcntl_signal($signal, $cb);
    }

    /**
     * Run the daemon
     *
     * @throws Exception
     */
    public function run()
    {
        if ($this->is_active()) {
            throw new Exception('err_already_started');
        } else {
            $pid = $this->forkProcess();
            $this->overrideStdOutput();

            return $pid;
        }
    }

    /**
     * Is daemon running
     *
     * @return boolean
     */
    public function is_active()
    {
        $pid_exists = (int)shell_exec('test -f ' . $this->pid_file . ' && echo 1 || echo 0');
        if ($pid_exists) {
            $pid = file_get_contents($this->pid_file);
            if ($pid /*&& posix_kill($pid, 0)*/) { // TODO: проверка пида часто не работает, смотрим по наличию файла
                // Pid file is not empty and the process exists -> daemon is running
                return true;
            } elseif (is_writable($this->pid_file)) {
                unlink($this->pid_file);
            } else {
                throw new Exception('Cannot delete pid file. Check access rights (' . $this->pid_file . ')');
            }
        }

        return false;
    }
}
