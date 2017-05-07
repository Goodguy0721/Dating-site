<?php

/**
 * @package dompdf
 *
 * @link    http://www.dompdf.com/
 *
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @version $Id: null_positioner.cls.php 448 2011-11-13 13:00:03Z fabien.menager $
 */

/**
 * Dummy positioner
 *
 * @package dompdf
 */
class Null_Positioner extends Positioner
{
  public function __construct(Frame_Decorator $frame)
  {
      parent::__construct($frame);
  }

    public function position()
    {
        return;
    }
}
