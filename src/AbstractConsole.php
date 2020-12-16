<?php
/**
 * AbstractCommand.php
 */

namespace DevCoding\Command\Base;

use DevCoding\Command\Base\Traits\ShellTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractConsole
 *
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @package DevCoding\Command\Base
 */
abstract class AbstractConsole extends Command
{
  use ShellTrait;

  const EXIT_SUCCESS = 0;
  const EXIT_ERROR   = 1;

  /** @var  IOHelper */
  protected $_io;

  public function initialize(InputInterface $Input, OutputInterface $Output)
  {
    // Initialize IO
    $this->_io = new IOHelper( $Input, $Output, $this->getHelper('question'), $this->getHelper('formatter') );
  }

  /**
   * @return IOHelper
   */
  public function io()
  {
    return $this->_io;
  }

  /**
   * Attempts to determine the terminal width.
   *
   * @param string        $default  A default to use if the terminal width cannot be determined.
   *
   * @return string                 The terminal width returned from tput.
   * @throws \Exception
   */
  protected function getTerminalWidth($default = '74')
  {
    $tputPath = '/usr/bin/tput';
    if( !is_file( $tputPath ) )
    {
      try
      {
        $tputPath = $this->getBinaryPath( 'tput' );
      }
      catch( \Exception $e )
      {
        return $default;
      }
    }

    return $this->getShellExec( $tputPath . ' cols', $default );
  }

  /**
   * Checks if the given string is alphanumeric.
   * @param string $str
   *
   * @return bool
   */
  protected function isAlphaNumeric( $str )
  {
    return ( !preg_match( '/[^a-z_\-0-9]/i', $str ) );
  }

  /**
   * @return bool
   */
  protected function isPhar()
  {
    return strlen( \Phar::running() ) > 0 ? true : false;
  }

  /**
   * @param string $dir       The path of the directory to create.
   * @param int    $mode      0777 by default for the widest possible access.
   * @param bool   $recursive Whether to create the directory recursively.  Defaults to false.
   * @param bool   $throw     Whether to throw an exception if the directory cannot be created.
   *
   * @return bool             TRUE if the directory is created.  FALSE if not.
   * @throws \Exception       If the directory cannot be created, and $throw is TRUE.
   */
  public function mkdir( $dir, $mode = 0777, $recursive = false, $throw = false )
  {
    if( !file_exists( $dir ) )
    {
      if( @mkdir( $dir, $mode, $recursive ) )
      {
        return true;
      }
      elseif( $throw )
      {
        throw new \Exception( 'Could not create directory: ' . $dir );
      }
      else
      {
        return false;
      }
    }

    return true;
  }
}