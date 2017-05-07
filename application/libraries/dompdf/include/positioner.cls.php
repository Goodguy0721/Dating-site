<?php

/**
 * @package dompdf
 *
 * @link    http://www.dompdf.com/
 *
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @version $Id: positioner.cls.php 448 2011-11-13 13:00:03Z fabien.menager $
 */

/**
 * Base Positioner class
 *
 * Defines postioner interface
 *
 * @package dompdf
 */
abstract class Positioner
{
  /**
   * @var Frame_Decorator
   */
  protected $_frame;

  //........................................................................

  public function __construct(Frame_Decorator $frame)
  {
      $this->_frame = $frame;
  }

  /**
   * Class destructor
   */
  public function __destruct()
  {
      clear_object($this);
  }
  //........................................................................

  abstract public function position();

    public function move($offset_x, $offset_y, $ignore_self = false)
    {
        list($x, $y) = $this->_frame->get_position();

        if (!$ignore_self) {
            $this->_frame->set_position($x + $offset_x, $y + $offset_y);
        }

        foreach ($this->_frame->get_children() as $child) {
            $child->move($offset_x, $offset_y);
        }
    }
}
