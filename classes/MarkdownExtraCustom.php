<?php
/**
 * MarkdownExtraCustom  -  A text-to-HTML conversion tool for web writers
 * customized on top of MarkdownExtra
 *
 * @package   php-markdown
 * @author    Michel Fortin <michel.fortin@michelf.com> - Aurélien Pierre <contact@aurelienpierre.com>
 * @copyright 2021 Aurélien PIERRE <https://eng.aurelienpierre.com>
 * @copyright 2004-2019 Michel Fortin <https://michelf.com/projects/php-markdown/>
 * @copyright (Original Markdown) 2004-2006 John Gruber <https://daringfireball.net/projects/markdown/>
 *
 */

use \Michelf\MarkdownExtra;
require_once 'MarkdownExtra.inc.php';

class MarkdownExtraCustom extends \Michelf\MarkdownExtra {

  public $ignore_code_class_prefix = "mermaid|flowchart|sequence";

  protected $ignored_code_class_re = '';

	public function __construct() {
    $this->escape_chars .= '$~^';

    $this->span_gamut += array(
      "doThirdPartyMath"    => 1,
      "doMathDisplay"       => 2,
      "doMathInline"        => 3,
			"doStriked"           => 71,
      "doSuperscript"       => 72,
      "doSubscript"         => 73,
		);

    $this->block_gamut += array(
      "doMathBlock"         => 6,
		);

    $this->document_gamut += array(
      //"importOldLaTeX"   => 20,
    );

    $this->ignored_code_class_re = '('. $this->ignore_code_class_prefix .')';
		parent::__construct();
	}

	protected function _appendFootnotes_callback($matches) {
		$node_id = $this->fn_id_prefix . $matches[1];

		// Create footnote marker only if it has a corresponding footnote *and*
		// the footnote hasn't been used by another marker.
		if (isset($this->footnotes[$node_id])) {
			$num =& $this->footnotes_numbers[$node_id];
			if (!isset($num)) {
				// Transfer footnote content to the ordered list and give it its
				// number
				$this->footnotes_ordered[$node_id] = $this->footnotes[$node_id];
				$this->footnotes_ref_count[$node_id] = 1;
				$num = $this->footnote_counter++;
				$ref_count_mark = '';
			} else {
				$ref_count_mark = $this->footnotes_ref_count[$node_id] += 1;
			}

			$attr = "";
			if ($this->fn_link_class !== "") {
				$class = $this->fn_link_class;
				$class = $this->encodeAttribute($class);
				$attr .= " class=\"$class\"";
			}
			if ($this->fn_link_title !== "") {
				$title = $this->fn_link_title;
				$title = $this->encodeAttribute($title);
				$attr .= " title=\"$title\"";
			}
			$attr .= " role=\"doc-noteref\"";

			$attr = str_replace("%%", $num, $attr);
			$node_id = $this->encodeAttribute($node_id);

			return
				"<sup id=\"fnref$ref_count_mark:$node_id\">".
				"<a href=\"#fn:$node_id\"$attr>[$num]</a>".
				"</sup>";
		}

		return "[^" . $matches[1] . "]";
	}

	protected function _doFencedCodeBlocks_callback($matches) {
		$classname =& $matches[2];
		$attrs     =& $matches[3];
		$codeblock = $matches[4];

		if ($this->code_block_content_func) {
			$codeblock = call_user_func($this->code_block_content_func, $codeblock, $classname);
		} else {
			$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		}

		$codeblock = preg_replace_callback('/^\n+/',
			array($this, '_doFencedCodeBlocks_newlines'), $codeblock);
    $codeblock = preg_replace('/[\n ]$/','', $codeblock);

		$classes = array();
    $has_graph = false;
		if ( $classname !== "" )
    {
			if ( $classname[0] === '.' )
      {
				$classname = substr($classname, 1);
			}
      // exclude the ignored code classes from prefixing
      if( preg_match( $this->ignored_code_class_re, $classname) )
      {
        $classes[] = $classname;
        $has_graph = true;
      }
      else
      {
			  $classes[] = $this->code_class_prefix . $classname;
      }
    }
		$code_attr_str = $this->doExtraAttributes("code", $attrs, null, $classes);
    if($has_graph) $classes = array('graph');
    $pre_attr_str = $this->doExtraAttributes("code", $attrs, null, $classes);
		$codeblock  = "<pre $pre_attr_str><code$code_attr_str>$codeblock</code></pre>";

		return "\n".$this->hashBlock($codeblock)."\n";
	}

  protected function doMathBlock($text) {
		$text = preg_replace_callback('{
      # 1: Empty lines
      (?:\n|\A)
      # 2: Opening marker
      (
        (?:\${2}) # 2 dollars
      )
      [ ]* \n # Whitespace and newline following marker.

      # 3: Content
      (
        (?>
          (?!\1 [ ]* \n)	# Not a closing marker.
          .*\n+
        )+
      )

      # Closing marker.
      \1 [ ]* (?= \n )
    }xm',
    array($this, '_doMathBlock_callback'), $text);

  return $text;
  }

  protected function _doMathBlock_callback( $matches )
  {
    $codeblock = $matches[2];
    $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

    // Deal with newlines and trailing spaces
    $codeblock = preg_replace_callback('/^\n+/',
			array($this, '_doFencedCodeBlocks_newlines'), $codeblock);

    $codeblock = "\n".$codeblock."\n";
    $codeblock = '<pre class="math"><code class="math">$$' . $codeblock . '$$</code></pre>';
    return "\n" . $this->hashBlock($codeblock) . "\n";
  }

  protected function doStriked($text) {
		$text = preg_replace_callback('/(~{2})(.+?)\1/',
    array($this, '_doStriked_callback'), $text);

  return $text;
  }

  protected function _doStriked_callback( $matches )
  {
    return $this->hashPart('<del>' . $matches[2]  . '</del>');
  }

  protected function doSuperscript($text) {
		$text = preg_replace_callback('/(\^{1})(.+?)\1/',
    array($this, '_doSuperscript_callback'), $text);
    return $text;
  }

  protected function _doSuperscript_callback( $matches )
  {
    return $this->hashPart('<sup>' . $matches[2] . '</sup>');
  }

  protected function doSubscript($text) {
		$text = preg_replace_callback('/(~{1})(.+?)\1/',
    array($this, '_doSubscript_callback'), $text);
    return $text;
  }

  protected function _doSubscript_callback( $matches )
  {
    return $this->hashPart('<sub>' . $matches[2] . '</sub>');
  }

  protected function doMathInline($text) {
		$text = preg_replace_callback('/(\${1})(.+?)\1/',
    array($this, '_doMathInline_callback'), $text);
    return $text;
  }

  protected function _doMathInline_callback( $matches )
  {
    $code = htmlspecialchars(trim($matches[2]), ENT_NOQUOTES);
    return $this->hashPart('<code class="math">'.$matches[1].$code.$matches[1].'</code>');
  }

  protected function doMathDisplay($text) {
		$text = preg_replace_callback('/(\${2})(.+?)\1/s',
    array($this, '_doMathDisplay_callback'), $text);
    return $text;
  }

  protected function _doMathDisplay_callback( $matches )
  {
    $codeblock = $matches[2];
    $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

    // Deal with newlines and trailing spaces
    $codeblock = preg_replace_callback('/^\n+/',
			array($this, '_doFencedCodeBlocks_newlines'), $codeblock);

    $codeblock = "\n".$codeblock."\n";
    $codeblock = '<pre class="math"><code class="math">$$' . $codeblock . '$$</code></pre>';
    return $this->hashBlock($codeblock);
  }

  // Convert latex WordPress shortcodes used by some plugins
  // to vanilly $...$ inline math
  protected function doThirdPartyMath($text) {
    $text = preg_replace('/\$latex (([\s\n]*.[\s\n]*)+?)\$/s', '$\1$', $text);
		$text = preg_replace('/\[latex\](([\s\n]*.[\s\n]*)+?)\[\/latex\]/s', '$\1$', $text);
    return $text;
  }

  protected function _doImages_inline_callback($matches) {
		$alt_text		= $matches[2];
		$url			= $matches[3] === '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];
		$attr  = $this->doExtraAttributes("img", $dummy =& $matches[8]);

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeURLAttribute($url);

    // make a link to the picture and add lightboxing options
		if(isset($title)) {
			$title = $this->encodeAttribute($title);
		}
    else
    {
			$title = "";
    }

    $result = "<a href=\"$url\" rel=\"lightbox\" class=\"lightbox\" data-rel=\"iLightbox\" data-title=\"$title\" data-caption=\"$alt_text\">" .
              "<img src=\"$url\" alt=\"$alt_text\" title=\"$title\" $attr$this->empty_element_suffix</a>";

		return $this->hashPart($result);
	}
}
