<?php


namespace DevCoding\Command\Base;

/**
 * Collection of convenience utilities for Symfony commands on a Mac.
 *
 * Class AbstractMacConsole
 *
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @package DevCoding\Command\Base
 */
class AbstractMacConsole extends AbstractConsole
{
  /** @var string */
  protected $_user;

  /**
   * Determines if running on a Mac
   *
   * @return bool
   */
  public function isMac()
  {
    return ( PHP_OS === 'Darwin' );
  }

  /**
   * Returns the user name for this command.  If no user name was previously set, retrieves the current user's username
   * from the command line.
   *
   * @return string|null
   */
  public function getUser()
  {
    if( empty( $this->_user ) )
    {
      try
      {
        if( $bin = $this->getBinaryPath( 'id' ) )
        {
          $this->setUser( $this->getShellExec( $bin . ' -in' ) );
        }
      }
      catch( \Exception $e )
      {
        return null;
      }
    }

    return $this->_user;
  }

  /**
   * Returns the user directory of the currently handled user.
   *
   * @return string|null
   */
  public function getUserDir()
  {
    return ( $user = $this->getUser() ) ? '/Users/' . $user : null;
  }

  /**
   * Returns the full path to the currently handled user's Library directory.
   * @return string|null
   */
  public function getUserLibraryDir()
  {
    return ( $folder = $this->getUserDir() ) ? $folder . '/Library' : null;
  }

  /**
   * Creates a directory as a subdirectory of the currently handled user's directory.
   *
   * @param string $dir       The path of the directory to create.
   * @param int    $mode      0777 by default for the widest possible access.
   * @param bool   $recursive Whether to create the directory recursively.  Defaults to false.
   * @param bool   $throw     Whether to throw an exception if the directory cannot be created.
   *
   * @return bool             TRUE if the directory is created.  FALSE if not.
   * @throws \Exception       If the directory cannot be created, and $throw is TRUE.
   */
  public function mkdirUser( $dir, $mode = 0777, $recursive = false, $throw = false )
  {
    if( $userDir = $this->getUserDir() )
    {
      $userDir = $this->getUserDir() . DIRECTORY_SEPARATOR . $dir;

      return $this->mkdir( $userDir, $mode, $recursive, $throw );
    }

    throw new \Exception( 'Could not determine user directory.' );
  }

  /**
   * @param string $user  An alphanumeric string for this command's username.
   *
   * @return $this
   * @throws \Exception   If a non-alphanumeric string is used.
   */
  public function setUser( $user )
  {
    if( !$this->isAlphaNumeric( $user ) )
    {
      throw new \Exception( 'Cannot specify non-alphanumeric characters for this command\'s username.' );
    }

    $this->_user = $user;

    return $this;
  }
}