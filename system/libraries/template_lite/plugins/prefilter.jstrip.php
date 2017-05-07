<?php

function template_prefilter_jstrip($tpl_source, &$template_object)
{
    return preg_replace_callback("/\{jstrip\}(.*?)\{\/jstrip\}/s", "template_prefilter_jstrip_cb", $tpl_source);
}

function template_prefilter_jstrip_one($code)
{
    return template_prefilter_jstrip_cb(array("", $code), false);
}

function template_prefilter_jstrip_cb($m, $literal = true)
{
    $c = $m[1];
    $o = ""; //stripped output
    $comment = 0; //comments
    $string = ""; //current string delimiter
    $last = ""; //last char in the output
    for ($i = 0;$i < strlen($c);++$i) {
        //if ($i%100==0) {
        //print_v(array($i,$string,$comment));
        //}
        $s = true; //save the character ?
        //if we're in a string or phpcode
        if (!empty($string)) {
            //end of the string
            if ($c[$i] == $string or substr($c, $i, 2) == $string) {
                $string = "";
            }
            //not in a string
        } else {
            //strip comments
            if (substr($c, $i, 2) == "//") {
                $comment = 1;
            }

            if (substr($c, $i, 2) == "/*") {
                $comment = 2;
            }

            if ($comment == 1 and $c[$i] == "\n") {
                $comment = 0;
            }

            if ($comment == 2 and substr($c, $i - 1, 2) == "*/") {
                $comment = 0;
                $s = false;
            }

            if ($comment == 0) {
                //start a string
                if ($c[$i] == "'" or $c[$i] == '"') {
                    $string = $c[$i];
                }

                //start phpcode
                if (substr($c, $i, 2) == "<" . "?") {
                    $string = "?" . ">";
                }

                //line break
                if ($c[$i] == "\n" or $c[$i] == "\r") {
                    //is the current line finished ?
                    // ")" and "}" is not OK ! (var x=function a() {}.......var )
                    $finishers = array(";","{","(",",","\n",":");
                    if (in_array($last, $finishers)) {
                        $s = false;
                    }
                }

                //a space ! can we cut it ?
                if ($c[$i] == " " or $c[$i] == "\t") {
                    $cutme = array(" ","\t","}","{",")","(","[","]","<",">","=",";","+","-","/","*","\n",":","&");
                    if (in_array($c[$i - 1], $cutme) or in_array($c[$i + 1], $cutme)) {
                        $s = false;
                    }
                }
                //todo : rename vars/functions !!
            }
        }
        //save the character
        if ($s and $comment == 0) {
            $o .= $c[$i];
            $last = $c[$i];
        }
    }

    if ($literal) {
        return "{literal}" . $o . "{/literal}";
    } else {
        return $o;
    }
}
