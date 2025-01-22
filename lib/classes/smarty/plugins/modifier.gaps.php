<?php

function smarty_modifier_gaps($string)
{
    $return = "";
    $words = explode(' ', $string);
    foreach($words as $word) {
        if(mb_strlen($word) < 3 && strpos($word, '<') === false && strpos($word, '>') === false) {
            $return.= $word.'&nbsp;';
        } else {
            $return.= $word.' ';
        }
    }
    $return = trim($return);
    return $return;
}
