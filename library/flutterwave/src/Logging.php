<?php
namespace Flutterwave;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
* A singleton class for logging.
*/
class Logging
{
  private $log;
  private function __construct()
  {
    $this->log = new Logger('General');
    $this->log->pushHandler(new StreamHandler(__DIR__.'/flutterwave.log', Logger::INFO));
  }
  public static function getLoggerInstance()
  {
    return new Logging();
  }
  public function warning($message, array $context = [])
  {
    $this->log->warning($message, $context);
  }
  public function notice($message, $context = [])
  {
    $this->log->notice($message, $context);
  }
  public function error($message, $context = [])
  {
    $this->log->error($message, $context);
  }
  public function debug($message, $context = [])
  {
    $this->log->debug($message, $context);
  }
  public function info($message, $context = [])
  {
    $this->log->info($message, $context);
  }
}
