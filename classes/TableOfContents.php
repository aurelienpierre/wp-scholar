<?php

class TableOfContents
{
  private $toc;
  private $toc_string;
  private $display_toc;
  private $display_toc_in_sidebar = true;
  public $toc_title = '';
  public $toc_depth = 6;
  public $transient;

  // We expect either a <!-- toc --> or
  // <!-- toc:X:"Table of contents title" where X is the depth
  private $toc_re = '\<\!\-\- toc:?([1-6]?):?\"(.*?)\" \-\-\>';

  public function __construct($content=null)
  {
    $this->toc = array();
    $this->toc_string = "";

    /*
    * extract TOC settings from HTML comment
    */
    if($content)
    {
      // is TOC enabled ?
      $this->display_toc = preg_match('/'.$this->toc_re.'/', $content, $matches);

      // is TOC disabled in sidebar ?
      $this->display_toc_in_sidebar = !preg_match('/\<\!--\s?no-sidebar-toc\s?--\>/', $content);

      // what is TOC depth ?
      if($matches[1]) $this->toc_depth = intval($matches[1]);

      // what is TOC title ?
      if($matches[2]) $this->toc_title = $matches[2];
    }
  }

  public function process( $content )
  {
    // process headings
    $content = preg_replace_callback('/<h([0-6]).*?>(.*)?<\/h\1>/', array($this, 'process_heading_callback'), $content);

    // cache the TOC for later use in sidebar
    if(false === get_transient($this->transient))
    {
      if($this->display_toc_in_sidebar)
        set_transient($this->transient, $this->toc, DAY_IN_SECONDS);
      else
        set_transient($this->transient, '', DAY_IN_SECONDS);
    }

    // insert the TOC in post if shortcode is there
    if($this->display_toc) $content = $this->insert_toc_post($content);

    return $content;
  }

  public function process_heading_callback($value)
  {
    $header = $value[0]; // the whole markup, like <h2 class="" id="">Text</h2>
    $level = $value[1];  // the heading level, like 2
    $text = $value[2];   // only the heading content, like Text

    // clean-up markup from header text
    $text = trim(preg_replace('/\<([a-z]+?).*?\>.*?\<\/\1\>/', '', $text));

    // find out if we have an ID
    $m = array();
    preg_match('/id=([\'\"])(.*?)\1/', $header, $m);
    $id = (count($m) >= 2) ? $m[2] : '';

    // add an id only if we don't have one
    if($id == '')
    {
      $id = preg_replace('/(\W+)/', '-', html_entity_decode($text));
      $id_attr = " id=\"$id\"";
      $header = preg_replace('/(\<h[1-6])(.*?)\>/', '\1\2'.$id_attr.'>', $header);
    }

    // if no link
    if(!preg_match('/\<a.*?\>.*?\<\/a\>/', $header))
    {
      // add anchor at the beginning of the heading
      $title1 = __("Click to copy this section URL", "wp-scholar");            // translate me
      $header_anchor = "<a class=\"anchor-link\" href=\"#$id\" title=\"$title1\">#</a>";
      $header = preg_replace('/(\<h[1-6].*?\>)/', '\1'.$header_anchor, $header);

      // add TOC link at the end of the heading
      $title2 = __("Click to go back to the table of contents", "wp-scholar"); // translate me
      $toc_anchor = ($this->display_toc) ? "<a class=\"toc-link\" href=\"#toc_container\" title=\"$title2\">↑</a>" : '';
      $header = preg_replace('/(\<\/h[1-6]\>)/', $toc_anchor.'\1', $header);
    }

    // save this heading for the TOC list later
    $this->toc[] = array($level, $text, $id);

    return $header;
  }

  public function build_toc()
  {
    // create the listing markup of the TOC content
    // $this->toc needs to have been filled already

    $previous_level = $this->toc[0][0][0];
    $toc_level = 1;
    $this->toc_string = "<ol class=\"toc-level-$toc_level\">\n<li>";

    foreach($this->toc as $entry => $value)
    {
      $current_level = $value[0];
      $id = $value[2];
      $label = $value[1];
      $title = __("Jump to this section"); // translate me

      if($current_level <= $this->toc_depth + 1)
      {
        if($current_level == $previous_level)
          $this->toc_string .= "</li>\n<li><a href=\"#$id\" title=\"$title\">$label</a>";

        if($current_level > $previous_level)
        {
          $toc_level++;
          $this->toc_string .= "\n<ol class=\"toc-level-$toc_level\">\n<li><a href=\"#$id\" title=\"$title\">$label</a>";
        }

        if($current_level < $previous_level)
        {
          $toc_level--;
          $this->toc_string .= "</li>\n</ol>\n</li>\n<li><a href=\"#$id\" title=\"$title\">$label</a>";
        }

        $previous_level = $current_level;
      }
    }

    // close the list markup
    for($i = 1; $i < $previous_level; $i++)
      $this->toc_string .= "</li>\n</ol>";

    // this logic starts the list with an empty element. Remove it
    $this->toc_string = str_replace("<li></li>\n", "", $this->toc_string);
  }

  public function insert_toc_post( $content )
  {
    $this->build_toc();

    // if we have a title
    if(empty($this->toc_title))
      $this->toc_title = __('Table of Contents', 'wp-scholar');

    $this->toc_string = "\n<summary class=\"toc-title\">".$this->toc_title."</summary>\n".$this->toc_string;

    // add container
    $div = "<details id=\"toc_container\" class=\"toc-post\" open>".$this->toc_string."</details>";

    // replace the comment tag by the container, with its <p> container if any, to avoid empty lines
    $content = preg_replace('/(<p>)?\s*?'.$this->toc_re.'(<\/p>)?/', $div, $content);
    return $content;
  }

  public function insert_toc_widget()
  {
    // this method can run on its own, witout calling process() and without content
    // but if not cached, then we have a solid problem somewhere earlier in content filtering
    if(false === ($this->toc = get_transient($this->transient)))
      return __("There is no table of contents cached in database for this post. This is a bug.");
    elseif(empty($this->toc))
      return '';
    else
      $this->build_toc();

    if(empty($this->toc_title))
      $this->toc_title = __('Table of Contents', 'wp-scholar');

    $this->toc_string = "\n<summary class=\"toc-title\">".$this->toc_title."</summary>\n".$this->toc_string;

    // add container
    $div = "<details class=\"toc-widget\" open>".$this->toc_string."</details>";

    return $div;
  }
}
