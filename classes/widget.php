<?php

// Creating the widget
class wp_scholar_widget extends WP_Widget {

  function __construct()
  {
    parent::__construct('wp_scholar_widget',
                        __('WP Scholar Table of Contents', 'wp-scholar'),
                        array( 'description' => __( 'Display the post, page and custom post, TOC in sidebar.', 'wp-scholar' ), ) );
  }

  // Creating widget front-end
  public function widget( $args, $instance ) {
    // Get the TOC content
    $toc = new TableOfContents();
    $toc->transient = toc_transient_id();

    if(empty(get_transient($toc->transient)))
      return;

    $title = $instance['title'];
    if(!empty($title)) $toc->toc_title = $title;
    $depth = $instance['depth'];
    if(!empty($depth)) $toc->toc_depth = $depth;

    $content = $toc->insert_toc_widget();

    if(!empty($content))
    {
      echo $args['before_widget'];
      echo $content;
      echo $args['after_widget'];
    }
  }

  // Widget Backend
  public function form( $instance )
  {
    if ( isset( $instance[ 'title' ] ) ) {
      $title = $instance[ 'title' ];
    }
    else {
      $title = __( 'New title', 'wp-scholar' );
    }
    if ( isset( $instance[ 'depth' ] ) ) {
      $depth = $instance[ 'depth' ];
    }
    else {
      $depth = 6;
    }
    // Widget admin form
    $field_id_title = $this->get_field_id( 'title' );
    $field_id_depth = $this->get_field_id( 'depth' );
    $field_name_title = $this->get_field_name( 'title' );
    $field_name_depth = $this->get_field_name( 'depth' );
    $title_value = esc_attr( $title );
    $depth_value = esc_attr( $depth);

    $form = '<p>';
    $form .= "<label for='$field_id_title'>".__( 'Title:' )."</label>";
    $form .= "<input class='widefat' id='$field_id_title' name='$field_name_title' type='text' value='$title_value' />";
    $form .= "<label for='$field_id_depth'>".__( 'Depth of titles list:' )."</label>";
    $form .= "<input class='tiny-text' id='$field_id_depth' name='$field_name_depth' type='number' step='1' min='1' value='$depth_value' size='3'>";
    $form .= '</p>';

    echo $form;
  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance )
  {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    $instance['depth'] = ( ! empty( $new_instance['depth'] ) ) ? strip_tags( $new_instance['depth'] ) : '';
    return $instance;
  }

// Class wpb_widget ends here
}
