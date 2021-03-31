<?php
/**
 * Plugin Name:       WP Scholar
 * Plugin URI:        https://eng.aurelienpierre.com/wp-scholar
 * Description:       Efficient Markdown typing with maths, footnotes and charts support for technical writers.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Aurélien PIERRE
 * Author URI:        https://aurelienpierre.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-scholar
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}


/**
* Utils
**/
function sanitize_attributes( $content )
{
  $content = html_entity_decode($content);
  return preg_replace('/[^a-z0-9]/', '-', strtolower($content));
}


/**
 * Allow HTML, SVG and JSON in media library. This is under user's responsibility.
**/
define('ALLOW_UNFILTERED_UPLOADS', true);
add_filter( 'upload_mimes', 'allow_html_uploads', 20, 1 );
function allow_html_uploads( $mime_types ) {
  // SVG images
  $mime_types['svg'] = 'image/svg+xml';
  $mimes['svgz'] = 'application/x-gzip';

  //(x)HTML and brothers
  $mime_types['html'] = 'text/html';
  $mime_types['htm'] = 'text/html';
  $mime_types['xhtm'] = 'application/xhtml+xml';
  $mime_types['md'] = 'text/markdown';

  // IPython notebooks
  // see https://jupyter.readthedocs.io/en/latest/reference/mimetype.html
  $mime_types['ipynb'] = 'application/x-ipynb+json';

  // Plotly JSON
  // see https://plotly.com/chart-studio-help/json-chart-schema/
  //$mime_types['json'] = 'application/vnd.plotly.v1+json';

  // Vanilla JSON
  $mime_types['json'] = 'application/json';
  return $mime_types;
}


/**
 * Declare external scripts for later enqueuing
 **/
add_action("wp_enqueue_scripts", "register_scripts");
function register_scripts()
{
  // main style and script
  wp_register_style( 'wp-scholar', plugin_dir_url( __FILE__ ).'css/wp-scholar.min.css', array(), '0.1');
  wp_register_script( 'wp-scholar', plugin_dir_url( __FILE__ ).'js/wp-scholar.min.js', array(), '0.1', true);
  wp_register_style( 'prism-style', plugin_dir_url( __FILE__ ).'css/code.min.css', array(), '1.23.0');

  // Localize the script with new data
  $translation_array = array(
      'success_message' => __( 'The section URL has been copied to your clipboard !', 'wp-scholar' ),
      'failure_message' => __( 'The section URL could not be copied to your clipboard.', 'wp-scholar' ),
  );
  wp_localize_script( 'wp-scholar', 'wp_scholar_translation', $translation_array );

  // Jupyter Notebook styling
  wp_register_style( 'jupyter', plugin_dir_url( __FILE__ ).'css/jupyter.min.css', array(), '1.2');

  // Plotly graphs
  wp_register_script('plotly', "//cdnjs.cloudflare.com/ajax/libs/plotly.js/1.58.4/plotly-basic.min.js", array(), '1.58.4', false);

  // Bokeh graphs
  wp_register_script('bokeh', "//cdnjs.cloudflare.com/ajax/libs/bokeh/2.3.0/bokeh.min.js", array(), '2.3.0', false);

  // Mathjax - loading Matjax this way fails 50 % of times, and 80 % with a caching plugin
  //wp_register_script('mathjax', "//cdnjs.cloudflare.com/ajax/libs/mathjax/3.1.2/es5/tex-svg-full.js", array(), '3.1.2', false);
  //wp_register_script('mathjax', "//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js", array(), '2.7.7', false);

  // Prism syntax highlighting
  wp_register_script('prism-core', "//cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/components/prism-core.min.js", array(), "1.23.0", true);
  wp_register_script('prism-autoloader', "//cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/autoloader/prism-autoloader.min.js", array('prism-core'), "1.23.0", true);
  wp_register_script('prism-line-numbers', "//cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/plugins/line-numbers/prism-line-numbers.min.js", array('prism-autoloader'), "1.23.0", true);

  // Marmaid charts
  wp_register_script('mermaid', "//cdnjs.cloudflare.com/ajax/libs/mermaid/8.9.1/mermaid.min.js", array(), '8.9.1', true);
}

function resize_ui()
{
  // Nasty trick to force responsive elements to recompute their size once
  // the whole page is done loading, so elements get reset with their final size.
  // Mostly useful for Plotly graphs that may overflow floating objects otherwise
  echo <<<'DATA'
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      window.dispatchEvent(new Event('resize'));
    }, false);
  </script>
  DATA;
}

function configure_mathjax()
{
/*
  // Code for Mathjax 2.7
  echo <<<'DATA'
  <script type="text/x-mathjax-config">
    document.addEventListener('DOMContentLoaded', function() {
      MathJax.Hub.Config({
        tex2jax: {
            tags: 'all',
            inlineMath: [ ['$','$'] ],
            displayMath: [ ['$$','$$'] ],
            processEscapes: true,
            processEnvironments: true,
            processRefs: true,
        },
        'HTML-CSS': {
          styles: {'.MathJax_Display': {'margin': 0}},
          linebreaks: { automatic: true },
          extensions: ['handle-floats.js'],
        },
        TeX: {
          extensions: ["autoload-all.js", "AMSmath.js", "AMSsymbols.js"],
          equationNumbers: { autoNumber: "all" },
        }
      })
    });
  </script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js"></script>
  DATA;
*/
  // Code for Mathjax 3.1 and later
  echo <<<'DATA'
  <script>
    MathJax = {
        packages: {'[+]': ['autoload', 'require']},
        tex: {
          tags: 'all',
          inlineMath: [ ['$','$'] ],
          displayMath: [ ['$$','$$'] ],
          processEscapes: true,
          processEnvironments: true,
          processRefs: true,
        },
        svg: {
          scale: 1,
          minScale: .5,
          mtextInheritFont: true,
          merrorInheritFont: true,
          mathmlSpacing: false,
          skipAttributes: {},
          exFactor: .5,
          displayAlign: 'center',
          displayIndent: '0',
          fontCache: 'global',
          localID: null,
          internalSpeechTitles: true,
          titleID: 0
        },
        options: {
          ignoreHtmlClass: 'no_math',    //  class that marks tags not to search
          processHtmlClass: 'math',  //  class that marks tags that should be searched
        }
    };
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/3.1.2/es5/tex-svg.js" async></script>
  DATA;
}

function configure_prism()
{
  echo <<<'DATA'
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Prism.plugins.autoloader.languages_path = '//cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/components/';
    }, false);
  </script>
  DATA;
}

function configure_mermaid()
{
  echo <<<'DATA'
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var config = {
      startOnLoad: true,
      theme: 'neutral',
      htmlLabels: true,
      themeCSS: ':root { --mermaid-font-family: sans-serif; }',
      };
      mermaid.initialize(config);
    }, false);
  </script>
  DATA;
}

function toc_transient_id()
{
  // build the transient name for the TOC transient
  return '_transient_wp_scholar_toc_'.get_the_ID();
}

/**
 * Parse Markdown
 **/
require_once 'classes/MarkdownExtraCustom.php';
function parse_markdown( $content )
{
  // Clean-up editors mess first because
  // PHP Markdown expects pure ASCII with no HTML markup

  // Convert HTML elements to ASCII
  $content = str_replace(array("&amp;", "&quot", "&apos;", "&dollar;", "&nbsp;"),
                         array("&", "\"", "\'", "$", " "),
                         $content);
  $content = preg_replace('/\&lt\;([a-z\=\'\"\-\_ ]+?)\&gt\;/', "<\1>", $content);
  $content = preg_replace(array('/\&gt\;/', '/\&lt\;/') ,
                          array('>', '<'),
                          $content);

  // Clean weak HTML markup with no class or id
  $content = preg_replace('/<span>(.*?)\<\/span>/s', '\1', $content);
  $content = preg_replace('/<p>(.*?)<\/p>/s', "\n" . '\1' . "\n", $content);
  $content = preg_replace('/<div>(.*?)<\/div>/s', "\n" . '\1' . "\n", $content);
  $content = preg_replace('/<br\s?\/?>/', "\n", $content);

  // Ensure LaTeX newline is escaped, so \\ is turned into \\\ preemptively
  $content= preg_replace('/\\\\{2}/', '\\\\\\\\\\\\', $content);

  // parse markdown
  $parser = new MarkdownExtraCustom;
  $parser->code_class_prefix = 'language-';

  // remove possibly harmfull URLs and protocols
  $parser->url_filter_func = function ($url) {
    return wp_kses( $url, wp_kses_allowed_html( $context = 'post' ), wp_allowed_protocols() );
  };
  $parser->hashtag_protection = true;
  $parser->fn_backlink_html = "&#8629;";
  return $parser->transform($content);
}

function parse_comments( $content )
{
  // Filter XSS and dangerous markup and protocols
  $content = wp_kses( $content, wp_kses_allowed_html( $context = 'data' ), wp_allowed_protocols() );
  return parse_markdown( $content );
}

function parse_content( $content )
{
  return parse_markdown($content);
}


/**
 * Enhance typography
**/
require_once 'classes/SmartyPantsTypographerCustom.php';
function typographer($content)
{
  // replace more than one space
  // don't do that here, messes up with code blocks indentation
  //$content = preg_replace('/[ ]{2,}/', ' ', $content);
  //$content = preg_replace('/[\n\r]{2,}/', "\n", $content);

  // replace empty paragraphs
  $content = preg_replace('/\n?<p>\s*?<\/p>\n?/s', "\n", $content);

  $typographer = new SmartyPantsTypographerCustom(1);
  $typographer->tags_to_skip = 'pre|code|kbd|script|style|math|a';
  $typographer->do_quotes = 1;
  $typographer->do_backticks = 2;
  $typographer->do_dashes = 3;
  $typographer->do_ellipses = 1;
  $typographer->convert_quot = 1;
  $typographer->do_comma_quotes = 1;
  $typographer->do_space_colon = 2;
  $typographer->do_space_semicolon = 2;
  $typographer->do_space_marks = 2;
  $typographer->do_space_frenchquote = 2;
  $typographer->do_space_emdash = 1;
  $typographer->do_space_endash = 1;
  $typographer->do_guillemets = 2;
  $typographer->do_space_unit = 2;

  // replace unbreakable spaces by unbreakable thin spaces which is correct typography
  $typographer->space_colon = "&#8239;";
  $typographer->space_semicolon = "&#8239;";
  $typographer->space_marks = "&#8239;";
  $typographer->space_frenchquote = "&#8239;";
  $typographer->space_thousand = "&#8239;";
  $typographer->space_unit = "&#8239;";
  $typographer->space = '(?: | | |&#8200;|&#8194;|&#8201;|&thinsp;|&#8239;|&nbsp;|&#160;|&#0*160;|&#x0*[aA]0;)';

  $locale = get_locale();

  if(preg_match('/fr/', $locale))
  {
    $typographer->smart_doublequote_open = "&laquo;&#8239;";
    $typographer->smart_doublequote_close = "&#8239;&raquo;";
    $typographer->do_space_thousand = 2;
  }
  else
  {
    $typographer->smart_doublequote_open = "&#8220;";
    $typographer->smart_doublequote_close = "&#8221;";
    $typographer->do_space_thousand = 1;
  }

  return $typographer->transform($content);
}


/**
 * Parse post content to extract settings
 * If Markdown enabled, dequeue WP default typo filter and enqueue ours for later
**/
add_filter('the_content', 'enqueue_wp_scholar', 1);
function enqueue_wp_scholar( $content )
{
  wp_enqueue_style('wp-scholar');
  add_action('wp_head', 'resize_ui');

  $typo_priority = 90;

  $has_typography_off = preg_match('/\<\!\-\- no-typography \-\-\>/', $content);
  $has_markdown_off = preg_match('/\<\!\-\- no-markdown \-\-\>/', $content);
  $bypass_instructions = preg_match('/\<\!\-\- skip-instructions \-\-\>/', $content);
  $force_markdown = preg_match('/\<\!\-\- force-markdown \-\-\>/', $content);

  if(!$has_typography_off || $bypass_instructions)
  {
    remove_filter('the_content', 'wptexturize');
    remove_filter('comment_text', 'wptexturize');
    remove_filter('single_post_title', 'wptexturize');
    remove_filter('the_title', 'wptexturize');
    remove_filter('the_excerpt', 'wptexturize');
    remove_filter('the_excerpt', 'wpautop');
    remove_filter('the_widget', 'wptexturize');
    remove_filter('the_widget', 'wpautop');

    add_filter('the_content', 'typographer', $typo_priority);
    add_filter('comment_text', 'typographer', $typo_priority);
    add_filter('the_title', 'typographer', $typo_priority);
    add_filter('single_post_title', 'typographer', $typo_priority);
    add_filter('the_excerpt', 'typographer', $typo_priority);
    add_filter('widget_title', 'typographer', $typo_priority);
    add_filter('the_widget', 'typographer', $typo_priority);
  }

  if(is_single() || is_page() || is_archive() || is_home() || $force_markdown)
  {
    // apply that only on blog-ish content, that is no custom post type
    delete_transient(toc_transient_id());
    add_filter('the_content', 'toc_and_headings', 90);
    add_filter('the_content', 'autoenqueue_external_ressource', 100);

    if(!$has_markdown_off || $bypass_instructions)
    {
      // Remove stupid WP filters that mess-up with markup
      remove_filter('the_content', 'wpautop');
      remove_filter('the_content', 'prepend_attachment');
      remove_filter('the_content', 'convert_chars');

      remove_filter('the_excerpt', 'wpautop');
      remove_filter('the_excerpt', 'prepend_attachment');
      remove_filter('the_excerpt', 'convert_chars');

      remove_filter('comment_text', 'wpautop');

      // Shortcodes are 8th in the order of filters, so we go straight before
      add_filter('the_content', 'parse_content', 7);
      add_filter('the_excerpt', 'parse_content', 7);
      add_filter('comment_text', 'parse_comments', 7);
    }
  }

  return $content;
}


/**
 * Detect which external scripts needs to be loaded
**/
function autoenqueue_external_ressource( $content )
{
  // Look for markup hints to guess what libs should be loaded
  if(preg_match('/((\$|&#36;|&dollar;|&#x24;){1,2})(.+?)\1/s', $content) ||
     preg_match('/<[a-z]+.*class=([\'\"]).*math.*\1.*>/', $content) ||
     strpos($content, '<!-- mathjax -->') !== false)
  {
    // enqueuing the clean WP way breaks Mathjax, too bad.
    //wp_enqueue_script( 'mathjax' );
    add_action('wp_head', 'configure_mathjax');
    echo "<!-- maths detected -->";
  }
  if(preg_match('/<[a-z]+.*class=([\"\']).*(plotly).*\1.*>/', $content) ||
     strpos($content, '<!-- plotly -->') !== false)
  {
    wp_enqueue_script( 'plotly' );
    echo "<!-- plotly detected -->";
  }
  if(strpos($content, '<!-- bokeh -->') !== false)
  {
    wp_enqueue_script( 'bokeh' );
    echo "<!-- bokeh detected -->";
  }
  if(preg_match('/<(pre|code).*>/s', $content))
  {
    wp_enqueue_style( 'prism-style' );
  }
  if(preg_match('/<(pre|code).*class=([\"\']).*(language-).*\2.*>/s', $content))
  {
    wp_enqueue_script( 'prism-core' );
    wp_enqueue_script( 'prism-autoloader' );

    if(preg_match('/<[a-z]+.*class=([\"\']).*(line-numbers).*\1.*>/s', $content))
    {
      wp_enqueue_script( 'prism-line-numbers' );
    }
    add_action('wp_head', 'configure_prism');
    echo "<!-- code highlighting detected -->";
  }
  if(preg_match('/<code.*class=([\"\']).*(mermaid).*\1.*>/', $content))
  {
    wp_enqueue_script( 'mermaid' );
    add_action('wp_head', 'configure_mermaid');
    echo "<!-- mermaid detected -->";
  }
  return $content;
}

/**
* TOC and headings links
**/
require_once 'classes/TableOfContents.php';
function toc_and_headings($content)
{
  wp_enqueue_script('wp-scholar');
  $toc = new TableOfContents($content);
  $toc->transient = toc_transient_id();
  return $toc->process($content);
}

// add the TOC widget
require_once 'classes/widget.php';
function wp_scholar_load_widget() {
  register_widget( 'wp_scholar_widget' );
}
add_action( 'widgets_init', 'wp_scholar_load_widget' );

/**
 * Declare shortcodes
 **/

// Shortcode to include any arbitrary HTML into content
// We don't have many options to embed Jupyter notebooks or plots…
function include_shortcode( $atts )
{
  // Security-check, in case someone enabled shortcodes in comments
  // exit and do nothing
  if( !(is_single() || is_page()) )
  {
    return _("Including arbitrary HTML here is disabled for security reasons");
  }

  // Read shortcode
  $atts = shortcode_atts(
    array('path' => '', 'load' => ''),
    $atts,
    'include'
  );

  // Unpack args
  $class_names = "include";


  if(strpos($atts['load'], 'plotly') !== false)
  {
    wp_enqueue_script( 'plotly' );
    $class_names = $class_names . " plotly";
  }

  if(strpos($atts['load'], 'jupyter') !== false)
  {
    wp_enqueue_style( 'jupyter' );
    $class_names = $class_names . " jupyter";
  }

  if(strpos($atts['load'], 'bokeh') !== false)
  {
    wp_enqueue_script( 'bokeh' );
    $class_names = $class_names . " bokeh";
  }

  if(strpos($atts['load'], 'mathjax') !== false)
  {
    add_action('wp_head', 'configure_mathjax');
    $class_names = $class_names . " mathjax";
  }

  // Include the HTML
  $uploads = wp_upload_dir();
  $file = $uploads["basedir"].'/'.(trim($atts['path']));
  ob_start();
  include $file;
  $buffer = ob_get_clean();

  if(preg_match('/(.*)\.md/', $file))
    $buffer = do_shortcode(parse_markdown($buffer));

  return "<div class='". $class_names . "'>".$buffer."</div>";
}

add_shortcode( 'include', 'include_shortcode' );


// Image formatting shortcodes
add_shortcode( 'figcaption', 'figcaption_shortcode' );
function figcaption_shortcode( $atts, $content = null )
{
	return "<figcaption>$content</figcaption>";
}

add_shortcode( 'figure', 'figure_shortcode' );
function figure_shortcode( $atts, $content = null )
{
  $atts = shortcode_atts(
    array('align' => '',
           'id'   => '',
           'w'    => '',
           'no_p' => '0'),
    $atts,
    'figure'
  );
  $class_name = sanitize_attributes($atts["align"]);
  $id_name = sanitize_attributes($atts["id"]);
  $width = $atts["w"];
  $style = '';
  if($id_name !== '') $id_name = 'id="' . $id_name . '" ';
  if($class_name !== '') $class_name = 'class="align' . $class_name . '" ';
  if($width !== '') $style = 'style="max-width: '. $width .';"';

  $no_p = $atts["no_p"];
  if($no_p === 'false') $no_p = false;
  if($no_p) $content = preg_replace('/\<p\>(.*?)\<\/p\>/', '\1', $content);

	return "<figure $id_name $class_name $style>".do_shortcode($content)."</figure>";
}


// Stupid toc shortcode to replace it with an html comment
add_shortcode( 'toc', 'toc_shortcode' );
function toc_shortcode( $atts, $content = null )
{
  $atts = shortcode_atts(
    array('title' => '',
          'depth'   => ''),
    $atts,
    'toc'
  );

  $title = html_entity_decode($atts["title"]);
  $depth = html_entity_decode($atts["depth"]);

	return "<!-- toc:$depth:\"$title\" -->";
}

/* Translation */

add_action('plugins_loaded', 'wp-scholar-load-textdomain');
function wan_load_textdomain() {
	load_plugin_textdomain( 'wp-scholar', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

/*
* DEBUG
*/
function print_filters_for( $hook = '' ) {
  global $wp_filter;
  if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
      return;

  print '<pre>';
  print_r( $wp_filter[$hook] );
  print '</pre>';
}

//print_filters_for( 'the_content' );
//print_filters_for('upload_mimes');
