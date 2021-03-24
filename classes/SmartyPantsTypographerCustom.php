<?php
#
# SmartyPants Typographer  -  Smart typography for web sites
#
# These customizations :
# Copyright (c) 2021 - Aurélien PIERRE
# <https://eng.aurelienpierre.com >
#
# PHP SmartyPants & Typographer
# Copyright (c) 2004-2016 Michel Fortin
# <https://michelf.ca/>
#
# Original SmartyPants
# Copyright (c) 2003-2004 John Gruber
# <https://daringfireball.net/>
#

use \Michelf\SmartyPantsTypographer;
require_once 'SmartyPantsTypographer.inc.php';

class SmartyPantsTypographerCustom extends \Michelf\SmartyPantsTypographer {
  protected function educate($t, $prev_token_last_char) {
    // replace tailed arrows
    $t = preg_replace('/((&lt;|\<)(-|–|—|&ndash;|&mdash;|&#8211;|&#8212;){1}(&lt;|\<))/', '&#8610;', $t);
    $t = preg_replace('/((&gt;|\>)(-|–|—|&ndash;|&mdash;|&#8211;|&#8212;){1}(&gt;|\>))/', '&#8611;', $t);
    $t = preg_replace('/((&lt;|\<)(-|–|—|&ndash;|&mdash;|&#8211;|&#8212;){1}(\|))/', '&#8612;', $t);
    $t = preg_replace('/((\|)(-|–|—|&ndash;|&mdash;|&#8211;|&#8212;){1}(&gt;|\>))/', '&#8614;', $t);

    // replace single arrows
    $t = preg_replace('/((&lt;|\<)(-|–|—|&ndash;|&mdash;|&#8211;|&#8212;){1}(&gt;|\>))/', '&#8596;', $t);
    $t = preg_replace('/((-|–|—|&ndash;|&mdash;|&#8211;|&#8212;){1}(&gt;|\>))/', '&#8594;', $t);
    $t = preg_replace('/((&lt;|\<)(-|–|—|&ndash;|&mdash;|&#8211;|&#8212;){1})/', '&#8592;', $t);

    // replace double arrows
    $t = preg_replace('/((&lt;|\<)(=|&#x3d;|&#61;|&equals;){1}(&gt;|\>))/', '&#8660;', $t);
    $t = preg_replace('/((=|&#x3d;|&#61;|&equals;){1}(&gt;|\>))/', '&#8658;', $t);
    $t = preg_replace('/((&lt;|\<)(=|&#x3d;|&#61;|&equals;){1})/', '&#8656;', $t);

    // replace roughly equal
    $t = preg_replace('/((~|&#8764;)(=|&#x3d;|&#61;|&equals;))/', '&#8771;', $t);

    // replace not equal
    $t = preg_replace('/(\\\\=)/', '&#8800;', $t);

    // replace times
    $t = preg_replace('/([0-9])\*([0-9])/', '\1&times;\2', $t);

    // replace plus or minus
    $t = preg_replace('/(\+\/\-)/', '&plusmn;', $t);

    // replace integer fractions but avoid dates
    $t = preg_replace('/(^|\.|\?|\!|\,|\:|\;|\s)(\d{1})\/(\d{1})($|\.|\?|\!|\,|\:|\;|\s)/m', '\1&frac\2\3;\4', $t);
    $t = preg_replace('/(^|\.|\?|\!|\,|\:|\;|\s)(\d{1,})\/(\d{1,})($|\.|\?|\!|\,|\:|\;|\s)/m', '\1\2&frasl;\3\4', $t);

    $t = parent::educate($t, $prev_token_last_char);

    // insert a em-space after end-of-sentence punctuation
    $t = preg_replace('/([a-zA-Z])([.;:!?])\s+?([a-zA-Z])/', '\1\2&#8194;\3', $t);

    // insert an usual space after coma
    $t = preg_replace('/([a-zA-Z])(\,)\s*?([a-zA-Z])/', '\1\2&#8200;\3', $t);

    // Insert em space before and after inline <code>
    $t = preg_replace('/([a-zA-Z0-9])[ ]*?(\<code)/', '\1&#8194;\2', $t);
    $t = preg_replace('/(\<\/code\>)[ ]*?([a-zA-Z0-9])/', '\1&#8194;\2', $t);

    return $t;
  }
}
