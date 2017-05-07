<?php

/**
 * @package dompdf
 *
 * @link    http://www.dompdf.com/
 *
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @version $Id: javascript_embedder.cls.php 448 2011-11-13 13:00:03Z fabien.menager $
 */

/**
 * Embeds Javascript into the PDF document
 *
 * @package dompdf
 */
class Javascript_Embedder
{
  /**
   * @var DOMPDF
   */
  protected $_dompdf;

    public function __construct(DOMPDF $dompdf)
    {
        $this->_dompdf = $dompdf;
    }

    public function insert($code)
    {
        $this->_dompdf->get_canvas()->javascript($code);
    }

    public function render($frame)
    {
        if (!DOMPDF_ENABLE_JAVASCRIPT) {
            return;
        }

        $this->insert($frame->get_node()->nodeValue);
    }
}
