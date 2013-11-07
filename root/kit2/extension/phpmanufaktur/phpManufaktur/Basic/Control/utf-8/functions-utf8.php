<?php

/**
 * kitFramework:Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This UTF-8 helper comes from WebsiteBaker and was heavily inspired
 * and realized by Thorn!
 */


/*
 * check for mb_string support
 */

if (!defined('UTF8_MBSTRING')) {
  if (function_exists('mb_substr') && !defined('UTF8_NOMBSTRING')) {
    define('UTF8_MBSTRING',1);
  }
  else {
    define('UTF8_MBSTRING',0);
  }
}

if (UTF8_MBSTRING) {
    mb_internal_encoding('UTF-8');
}

require_once(__DIR__.'/charsets_table.php');


/*
 * replacement for utf8_entities_to_umlauts()
 */

function utf8_fast_entities_to_umlauts($str)
{
    if (UTF8_MBSTRING) {
        // we need this for use with mb_convert_encoding
        $str = str_replace(array('&amp;','&gt;','&lt;','&quot;','&#039;','&nbsp;'), array('&amp;amp;','&amp;gt;','&amp;lt;','&amp;quot;','&amp;#39;','&amp;nbsp;'), $str);
        return (mb_convert_encoding(mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8'),'UTF-8', 'HTML-ENTITIES'));
    }
    else {
        global $named_entities;
        global $numbered_entities;
        $str = str_replace($named_entities, $numbered_entities, $str);
        $str = preg_replace("/&#([0-9]+);/e", "code_to_utf8($1)", $str);
    }
    return($str);
}

/*
 * Converts from various charsets to UTF-8
 *
 * Will convert a string from various charsets to UTF-8.
 * HTML-entities may be converted, too.
 * In case of error the returned string is unchanged, and a message is emitted.
 * Supported charsets are:
 * direct: iso_8859_1 iso_8859_2 iso_8859_3 iso_8859_4 iso_8859_5
 *         iso_8859_6 iso_8859_7 iso_8859_8 iso_8859_9 iso_8859_10 iso_8859_11
 * mb_convert_encoding: all wb charsets (except those from 'direct'); but not GB2312
 * iconv:  all wb charsets (except those from 'direct')
 *
 * @param  string  A string in supported encoding
 * @param  string  The charset to convert from, defaults to DEFAULT_CHARSET
 * @return string  A string in UTF-8-encoding, with all entities decoded, too.
 *                 String is unchanged in case of error.
 * @author thorn
 */

function charset_to_utf8($str, $charset_in='utf-8', $decode_entities=true) {
    global $iso_8859_2_to_utf8, $iso_8859_3_to_utf8, $iso_8859_4_to_utf8,
        $iso_8859_5_to_utf8, $iso_8859_6_to_utf8, $iso_8859_7_to_utf8,
        $iso_8859_8_to_utf8, $iso_8859_9_to_utf8, $iso_8859_10_to_utf8,
        $iso_8859_11_to_utf8;
    $charset_in = strtoupper($charset_in);
    if ($charset_in == "") {
        $charset_in = 'UTF-8';
    }
    $wrong_ISO8859 = false;
    $converted = false;

    if ((!function_exists('iconv') && !UTF8_MBSTRING && ($charset_in=='BIG5' ||
        $charset_in=='ISO-2022-JP' || $charset_in=='ISO-2022-KR')) ||
        (!function_exists('iconv') && $charset_in=='GB2312')) {
        throw new Exception("Can't convert from $charset_in without mb_convert_encoding() or iconv(). Use UTF-8 instead.");
    }

    // check if we have UTF-8 or a plain ASCII string
    if ($charset_in == 'UTF-8' || utf8_isASCII($str)) {
        // we have utf-8. Just replace HTML-entities and return
        if ($decode_entities && preg_match('/&[#0-9a-zA-Z]+;/',$str)) {
            return(utf8_fast_entities_to_umlauts($str));
        }
        else {
            // nothing to do
            return($str);
        }
    }

    // Convert $str to utf8
    if (substr($charset_in,0,8) == 'ISO-8859') {
        switch ($charset_in) {
            case 'ISO-8859-1': $str=utf8_encode($str); break;
            case 'ISO-8859-2': $str=strtr($str, $iso_8859_2_to_utf8); break;
            case 'ISO-8859-3': $str=strtr($str, $iso_8859_3_to_utf8); break;
            case 'ISO-8859-4': $str=strtr($str, $iso_8859_4_to_utf8); break;
            case 'ISO-8859-5': $str=strtr($str, $iso_8859_5_to_utf8); break;
            case 'ISO-8859-6': $str=strtr($str, $iso_8859_6_to_utf8); break;
            case 'ISO-8859-7': $str=strtr($str, $iso_8859_7_to_utf8); break;
            case 'ISO-8859-8': $str=strtr($str, $iso_8859_8_to_utf8); break;
            case 'ISO-8859-9': $str=strtr($str, $iso_8859_9_to_utf8); break;
            case 'ISO-8859-10': $str=strtr($str, $iso_8859_10_to_utf8); break;
            case 'ISO-8859-11': $str=strtr($str, $iso_8859_11_to_utf8); break;
            default: $wrong_ISO8859 = true;
        }
        if (!$wrong_ISO8859) {
            $converted = true;
        }
    }
    if (!$converted && UTF8_MBSTRING && $charset_in != 'GB2312') {
        // $charset is neither UTF-8 nor a known ISO-8859...
        // Try mb_convert_encoding() - but there's no GB2312 encoding in php's mb_* functions
        $str = mb_convert_encoding($str, 'UTF-8', $charset_in);
        $converted = true;
    }
    elseif (!$converted) {
        // Try iconv
        if (function_exists('iconv')) {
            $str = iconv($charset_in, 'UTF-8', $str);
            $converted = true;
        }
    }
    if ($converted) {
        // we have utf-8, now replace HTML-entities and return
        if ($decode_entities && preg_match('/&[#0-9a-zA-Z]+;/',$str)) {
            $str = utf8_fast_entities_to_umlauts($str);
        }
        return ($str);
    }

    throw new Exception("Can't convert from $charset_in without mb_convert_encoding() or iconv(). Use UTF-8 instead.");
}

/*
 * Converts from UTF-8 to various charsets
 *
 * Will convert a string from UTF-8 to various charsets.
 * HTML-entities will not! be converted.
 * In case of error the returned string is unchanged, and a message is emitted.
 * Supported charsets are:
 * direct: iso_8859_1 iso_8859_2 iso_8859_3 iso_8859_4 iso_8859_5
 *         iso_8859_6 iso_8859_7 iso_8859_8 iso_8859_9 iso_8859_10 iso_8859_11
 * mb_convert_encoding: all wb charsets (except those from 'direct'); but not GB2312
 * iconv:  all wb charsets (except those from 'direct')
 *
 * @param  string  An UTF-8 encoded string
 * @param  string  The charset to convert to, defaults to DEFAULT_CHARSET
 * @return string  A string in a supported encoding, with all entities decoded, too.
 *                 String is unchanged in case of error.
 * @author thorn
 */

function utf8_to_charset($str, $charset_out='utf-8') {
    global $utf8_to_iso_8859_2, $utf8_to_iso_8859_3, $utf8_to_iso_8859_4,
        $utf8_to_iso_8859_5, $utf8_to_iso_8859_6, $utf8_to_iso_8859_7,
        $utf8_to_iso_8859_8, $utf8_to_iso_8859_9, $utf8_to_iso_8859_10,
        $utf8_to_iso_8859_11;
    $charset_out = strtoupper($charset_out);
    $wrong_ISO8859 = false;
    $converted = false;

    if ((!function_exists('iconv') && !UTF8_MBSTRING && ($charset_out=='BIG5' ||
        $charset_out=='ISO-2022-JP' || $charset_out=='ISO-2022-KR')) ||
        (!function_exists('iconv') && $charset_out=='GB2312')) {
        // Nothing we can do here :-(
        throw new Exception("Can't convert into $charset_out without mb_convert_encoding() or iconv(). Use UTF-8 instead.");
    }

    // check if we need to convert
    if ($charset_out == 'UTF-8' || utf8_isASCII($str)) {
        // Nothing to do. Just return
        return $str;
    }

    // Convert $str to $charset_out
    if (substr($charset_out,0,8) == 'ISO-8859') {
        switch($charset_out) {
            case 'ISO-8859-1': $str=utf8_decode($str); break;
            case 'ISO-8859-2': $str=strtr($str, $utf8_to_iso_8859_2); break;
            case 'ISO-8859-3': $str=strtr($str, $utf8_to_iso_8859_3); break;
            case 'ISO-8859-4': $str=strtr($str, $utf8_to_iso_8859_4); break;
            case 'ISO-8859-5': $str=strtr($str, $utf8_to_iso_8859_5); break;
            case 'ISO-8859-6': $str=strtr($str, $utf8_to_iso_8859_6); break;
            case 'ISO-8859-7': $str=strtr($str, $utf8_to_iso_8859_7); break;
            case 'ISO-8859-8': $str=strtr($str, $utf8_to_iso_8859_8); break;
            case 'ISO-8859-9': $str=strtr($str, $utf8_to_iso_8859_9); break;
            case 'ISO-8859-10': $str=strtr($str, $utf8_to_iso_8859_10); break;
            case 'ISO-8859-11': $str=strtr($str, $utf8_to_iso_8859_11); break;
            default: $wrong_ISO8859 = true;
        }
        if (!$wrong_ISO8859) {
            $converted = true;
        }
    }
    if (!$converted && UTF8_MBSTRING && $charset_out != 'GB2312') {
        // $charset is neither UTF-8 nor a known ISO-8859...
        // Try mb_convert_encoding() - but there's no GB2312 encoding in php's mb_* functions
        $str = mb_convert_encoding($str, $charset_out, 'UTF-8');
        $converted = true;
    }
    elseif(!$converted) {
        // Try iconv
        if (function_exists('iconv')) {
            $str = iconv('UTF-8', $charset_out, $str);
            $converted = true;
        }
    }
    if ($converted) {
        return $str;
    }

    // Nothing we can do here :-(
    throw  new Exception("Can't convert into $charset_out without mb_convert_encoding() or iconv(). Use UTF-8 instead.");
}

/*
 * Convert a string from mixed html-entities/umlauts to pure $charset_out-umlauts
 *
 * Will replace all numeric and named entities except
 * &gt; &lt; &apos; &quot; &#039; &nbsp;
 * @author thorn
 */
function entities_to_umlauts2($string, $charset_out='utf-8') {
    $string = charset_to_utf8($string, 'utf-8', true);
    $string = utf8_to_charset($string, $charset_out);
    return $string;
}
