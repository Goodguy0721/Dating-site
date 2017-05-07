<?php

class DSCrypt
{
    /**
     * Encode string by "Double square" method
     *
     * @param string $input input string
     *
     * @return string
     */
    public function encode($input)
    {
        $o = $s1 = $s2 = array();

        $basea = array('?','(','@',';','$','#',"]","&",'*');
        $basea = array_merge($basea, range('a', 'z'), range('A', 'Z'), range(0, 9));
        $basea = array_merge($basea, array('!', ')', '_', '+', '|', '%', '/', '[', '.', ' '));
        $dimension = 9;

        for ($i = 0; $i < $dimension; ++$i) {
            for ($j = 0; $j < $dimension; ++$j) {
                $s1[$i][$j] = $basea[$i * $dimension + $j];
                $s2[$i][$j] = str_rot13($basea[($dimension * $dimension - 1) - ($i * $dimension + $j)]);
            }
        }

        unset($basea);

        $m = floor(strlen($input) / 2) * 2;
        $symbl = $m == strlen($input) ? '' : $input[strlen($input) - 1];
        $al = array();

        for ($ii = 0; $ii < $m; $ii += 2) {
            $symb1 = $symbn1 = strval($input[$ii]);
            $symb2 = $symbn2 = strval($input[$ii + 1]);
            $a1 = $a2 = array();
            for ($i = 0; $i < $dimension; ++$i) {
                for ($j = 0; $j < $dimension; ++$j) {
                    if ($symb1 === strval($s1[$i][$j])) {
                        $a1 = array($i,$j);
                    }

                    if ($symb2 === strval($s2[$i][$j])) {
                        $a2 = array($i,$j);
                    }

                    if (!empty($symbl) && $symbl === strval($s1[$i][$j])) {
                        $al = array($i,$j);
                    }
                }
            }
            if (sizeof($a1) && sizeof($a2)) {
                $symbn1 = $s2[$a1[0]][$a2[1]];
                $symbn2 = $s1[$a2[0]][$a1[1]];
            }
            $o[] = $symbn1 . $symbn2;
        }
        if (!empty($symbl) && sizeof($al)) {
            $o[] = $s2[$al[1]][$al[0]];
        }

        return implode('', $o);
    }

    /**
     * Decode string by "Double square" method
     *
     * @param string $input intut
     *
     * @return string
     */
    public function decode($input)
    {
        $o = $s1 = $s2 = array();

        $basea = array('?','(','@',';','$','#',"]","&",'*');
        $basea = array_merge($basea, range('a', 'z'), range('A', 'Z'), range(0, 9));
        $basea = array_merge($basea, array('!', ')', '_', '+', '|', '%', '/', '[', '.', ' '));
        $dimension = 9;

        for ($i = 0; $i < $dimension; ++$i) {
            for ($j = 0; $j < $dimension; ++$j) {
                $s1[$i][$j] = $basea[$i * $dimension + $j];
                $s2[$i][$j] = str_rot13($basea[($dimension * $dimension - 1) - ($i * $dimension + $j)]);
            }
        }

        unset($basea);

        $m = floor(strlen($input) / 2) * 2;
        $symbl = $m == strlen($input) ? '' : $input[strlen($input) - 1];
        $al = array();

        for ($ii = 0; $ii < $m; $ii += 2) {
            $symb1 = $symbn1 = strval($input[$ii]);
            $symb2 = $symbn2 = strval($input[$ii + 1]);
            $a1 = $a2 = array();
            for ($i = 0; $i < $dimension; ++$i) {
                for ($j = 0; $j < $dimension; ++$j) {
                    if ($symb1 === strval($s2[$i][$j])) {
                        $a1 = array($i,$j);
                    }

                    if ($symb2 === strval($s1[$i][$j])) {
                        $a2 = array($i,$j);
                    }

                    if (!empty($symbl) && $symbl === strval($s2[$i][$j])) {
                        $al = array($i,$j);
                    }
                }
            }
            if (sizeof($a1) && sizeof($a2)) {
                $symbn1 = $s1[$a1[0]][$a2[1]];
                $symbn2 = $s2[$a2[0]][$a1[1]];
            }
            $o[] = $symbn1 . $symbn2;
        }
        if (!empty($symbl) && sizeof($al)) {
            $o[] = $s1[$al[1]][$al[0]];
        }

        return implode('', $o);
    }
}
