<?php

/**
 * @param $param
 * @return false|string
 */
function urlParamDecode($param) {
  // base64 encoding can generate some HTML special characters.
  // We could urlencode, but that creates various confusion with different
  // parts of the web transaction possibly urldecoding prematurely, so
  // instead we substitute the problematic characters with others. See
  // discussion in CO-1667 and https://stackoverflow.com/questions/1374753/passing-base64-encoded-strings-in-url
  return base64_decode( str_replace(array(".", "_", "-"),
                          // This mapping is the same as the one used by the YUI library.
                          // RFC 4648 base64url is another option, but strangely doesn't
                          // map the padding character (=).
                          array("+", "/", "="),
                          $param));
}


/**
 * @param $param
 * @return array|string|string[]
 */
function urlParamEncode($param) {
  // base64 encoding can generate some HTML special characters.
  // We could urlencode, but that creates various confusion with different
  // parts of the web transaction possibly urldecoding prematurely, so
  // instead we substitute the problematic characters with others. See
  // discussion in CO-1667 and https://stackoverflow.com/questions/1374753/passing-base64-encoded-strings-in-url
  return str_replace( array("+", "/", "="),
                      array(".", "_", "-"),
                      base64_encode($param));
}