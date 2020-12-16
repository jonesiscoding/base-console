<?php
/**
 * IOHelper.php
 */

namespace DevCoding\Command\Base;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class IOHelper
 *
 * @method  IOHelper info($message, $width = null, $level = null)
 * @method  IOHelper infoln($message, $width = null, $level = null)
 * @method  IOHelper msg($message, $width = null, $level = null)
 * @method  IOHelper msgln($message, $width = null, $level = null)
 * @method  IOHelper success($message,$width = null, $level = null)
 * @method  IOHelper successln($message,$width = null, $level = null)
 * @method  IOHelper comment($message,$width = null, $level = null)
 * @method  IOHelper commentln($message,$width = null, $level = null)
 * @method  IOHelper error($message,$width = null, $level = null)
 * @method  IOHelper errorln($message,$width = null, $level = null)
 * 
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @package DevCoding\Command\Base
 */
class IOHelper
{
  const FORMAT_ERROR = "error";
  const FORMAT_MSG = "msg";
  const FORMAT_SUCCESS = "success";
  const FORMAT_INFO = "info";
  const FORMAT_COMMENT = "comment";

  /** @var  OutputInterface */
  protected $_Output;
  /** @var  InputInterface */
  protected $_Input;
  /** @var  QuestionHelper */
  protected $QuestionHelper;
  /** @var  FormatterHelper */
  protected $FormatterHelper;
  protected $possibleLevels = array(
    OutputInterface::VERBOSITY_NORMAL,
    OutputInterface::VERBOSITY_DEBUG,
    OutputInterface::VERBOSITY_QUIET,
    OutputInterface::VERBOSITY_VERBOSE,
    OutputInterface::VERBOSITY_VERY_VERBOSE
  );
  protected $width = null;

  /**
   * IOHelper constructor.
   *
   * @param OutputInterface $Output
   * @param InputInterface  $Input
   * @param QuestionHelper  $questionHelper
   * @param FormatterHelper $formatterHelper
   */
  public function __construct( InputInterface $Input, OutputInterface $Output, QuestionHelper $questionHelper, FormatterHelper $formatterHelper )
  {
    $this->_Output = $Output;
    $this->_Input = $Input;
    $this->QuestionHelper = $questionHelper;
    $this->FormatterHelper = $formatterHelper;

    // Changes ERROR to Red text on Clear background
    $style = new OutputFormatterStyle( 'red' );
    $Output->getFormatter()->setStyle( 'error', $style );

    // Changes MSG to Purple/Magenta
    $style = new OutputFormatterStyle( 'magenta' );
    $Output->getFormatter()->setStyle( 'msg', $style );

    // Changes INFO from Green to Cyan (because we use green for success as is logical?)
    $style = new OutputFormatterStyle( 'cyan' );
    $Output->getFormatter()->setStyle( 'info', $style );

    // Creates the SUCCESS output formatter style
    $style = new OutputFormatterStyle( 'green' );
    $Output->getFormatter()->setStyle( 'success', $style );

  }


  // region //////////////////////////////////////////////// Input / Output Pass-through Methods

  /**
   * Provides the various formatted output commands (IE - info / infoln), as well as pass through to the Input and
   * Output object methods.
   *
   * @param string $method  The name of the method that was called for.
   * @param array  $args    The arguments to pass on to the called method.
   *
   * @return IOHelper
   * @throws \Exception
   */
  public function __call($method, $args)
  {
    $message = null;
    $width = null;
    $level = null;

    switch ( $method )
    {
      case "msg":
      case "msgln":
      case "info":
      case "infoln":
      case "comment":
      case "commentln":
      case "error":
      case "errorln":
      case "success":
      case "successln":
        $message = $args[ 0 ];
        $width = (isset($args[1])) ? $args[1] : null;
        $level = (isset($args[2])) ? $args[2] : OutputInterface::VERBOSITY_NORMAL;
        if ( !in_array( $level, $this->possibleLevels ) )
        {
          throw new \Exception( 'Invalid Output Verbosity' );
        }
        break;
      default:
        if ( is_callable( array( $this->_Input, $method ) ) )
        {
          return call_user_func_array( array( $this->_Input, $method ), $args );
        }
        elseif ( is_callable( array( $this->_Output, $method ) ) )
        {
          return call_user_func_array( array( $this->_Input, $method ), $args );
        }
        break;
    }

    switch ( $method )
    {
      case "msg":
        return $this->write( $message, self::FORMAT_MSG, $width, $level );
        break;
      case "msgln":
        return $this->writeln( $message, self::FORMAT_MSG, $width, $level );
        break;
      case "info":
        return $this->write( $message, self::FORMAT_INFO, $width, $level );
        break;
      case "infoln":
        return $this->writeln( $message, self::FORMAT_INFO, $width, $level );
        break;
      case "comment":
        return $this->write( $message, self::FORMAT_COMMENT, $width, $level );
        break;
      case "commentln":
        return $this->writeln( $message, self::FORMAT_COMMENT, $width, $level );
        break;
      case "error":
        return $this->write( $message, self::FORMAT_ERROR, $width, $level );
        break;
      case "errorln":
        return $this->writeln( $message, self::FORMAT_ERROR, $width, $level );
        break;
      case "success":
        return $this->write( $message, self::FORMAT_SUCCESS, $width, $level );
        break;
      case "successln":
        return $this->writeln( $message, self::FORMAT_SUCCESS, $width, $level );
        break;
      default;
        throw new \Exception( 'Unknown method "' . $method . '" called in ' . __CLASS__ );
    }
  }

  /**
   * @return OutputInterface
   */
  public function getOutput()
  {
    return $this->_Output;
  }

  /**
   * Returns the current verbosity, which will match one of the OutputInterface verbosity constants.
   *
   * @return int
   */
  public function getVerbosity()
  {
    return $this->_Output->getVerbosity();
  }

  /**
   * @return InputInterface
   */
  public function getInput()
  {
    return $this->_Input;
  }

  /**
   * @param string $option The name of the option to retrieve.
   *
   * @return mixed         The value of the option.
   */
  public function getOption( $option )
  {
    return $this->_Input->getOption( $option );
  }

  /**
   * @param string $arg The name of the argument to retrieve.
   *
   * @return mixed      The value of the argument.
   */
  public function getArgument( $arg )
  {
    return $this->_Input->getArgument( $arg );
  }

  // endregion ///////////////////////////////////////////// End Input/Output Pass Through Methods

  // region //////////////////////////////////////////////// UI Output Methods

  public function write($message, $format = null, $width = null, $level = OutputInterface::VERBOSITY_NORMAL)
  {
    $message = $this->prepareMessage( $message, $format, $width );
    $this->_Output->write( $message, false, $level );

    return $this;
  }

  public function writeln($message, $format = null, $width = false, $level = OutputInterface::VERBOSITY_NORMAL)
  {
    $message = $this->prepareMessage( $message, $format, $width );
    $this->_Output->writeln( $message, $level );

    return $this;
  }

  public function blankln( $count = 1, $level = OutputInterface::VERBOSITY_NORMAL )
  {
    for ( $x = 1; $x <= $count; $x++ )
    {
      $this->writeln( ' ', $level );
    }

    return $this;
  }

  public function errorblk($message, $level = OutputInterface::VERBOSITY_NORMAL)
  {
    $errorMessages = (is_array($message)) ? $message : array($message);

    // Formats as Block
    $formattedBlock = $this->FormatterHelper->formatBlock($errorMessages, 'error', true);

    // Outputs
    $this->blankln();
    $this->_Output->writeln( $formattedBlock, $level );
    $this->blankln();

    return $this;
  }

  public function successblk($message, $level = OutputInterface::VERBOSITY_NORMAL)
  {
    $successMessages = (is_array($message)) ? $message : array($message);

    // Creates the SUCCESS BLOCK output formatter style
    $style = new OutputFormatterStyle( 'white', 'green' );
    $this->_Output->getFormatter()->setStyle( 'success_block', $style );

    // Formats As Block
    $formattedBlock = $this->FormatterHelper->formatBlock($successMessages, 'success_block', true);

    // Outputs
    $this->blankln();
    $this->_Output->writeln( $formattedBlock, $level );
    $this->blankln();

    return $this;
  }

  /**
   * Asks a question using Symfony2's built in input/output functions, and returns the input from the user.
   *
   * @param  string         $question  The question to ask.
   * @param  null           $default   The default answer.
   * @param  int            $indent_by The number of spaces to indent the question by.
   *
   * @return string|int|bool     The response entered by the user.  In the case of Yes/No questions the output is
   *                             converted to a bool value.
   */
  public function ask( $question, $default = null, $indent_by = 0 )
  {
    $indent = ( $indent_by ) ? str_repeat( " ", $indent_by ) : null;
    if ( $default )
    {
      $question = new Question( $indent . '<comment>' . $question . '</comment> <fg=cyan>[</>' . $default . '<fg=cyan>]</> ', $default );
    }
    else
    {
      $question = new Question( $indent . '<comment>' . $question . '</comment> ', '' );
    }

    $answer = $this->QuestionHelper->ask( $this->_Input, $this->_Output, $question );

    $boolAnswer = trim(strtolower($answer));
    if ( $boolAnswer == "yes" || $boolAnswer == "y" ) { $answer = true; }
    if ( $boolAnswer == "no" || $boolAnswer == "n" ) { $answer = false; }

    return $answer;
  }

  // endregion ///////////////////////////////////////////// End UI Output Methods

  // region //////////////////////////////////////////////// Helper Methods

  protected function prepareMessage($message, $format, $width = null)
  {
    switch ( $format )
    {
      case "success":
        $prefix = "<fg=green>";
        $suffix = "</>";
        break;
      case "info":
      case "msg":
      case "comment":
      case "error":
        $prefix = "<".$format.">";
        $suffix = "</".$format.">";
        break;
      default:
        $prefix = "";
        $suffix = "";
        break;
    }

    if ( $width )
    {
      if ( $width > strlen( $message ) )
      {
        $message = str_pad( $message, $width );
      }
      else
      {
        $message = substr( $message, 0, $width - 5 ) . '...  ';
      }
    }
    // Let's not format padding
    $trimmed = trim($message);
    // Wrap the message in the tags
    $message = str_replace( $trimmed, $prefix . $trimmed . $suffix, $message );

    return $message;

  }

  // endregion ///////////////////////////////////////////// End Helper Methods

}