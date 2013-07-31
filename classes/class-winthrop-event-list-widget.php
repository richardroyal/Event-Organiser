<?php
/**
 * Class used to create the event list widget
 */
class Winthrop_Event_List_Widget extends WP_Widget{

	var $w_arg = array(
		'title'=> 'Activities',
		'numberposts'=> 5,
		'event-category'=> '',
		'venue_id'=> NULL,
		'venue'=> '',
		'orderby'=> 'eventstart',
		'showpastevents'=> 0,
		'group_events_by'=>'',
		'order'=> 'ASC',
		'template'=>'',
		'no_events'=>'No Events'
		);

	function __construct() {
		$widget_ops = array('classname' => 'Winthrop_Event_List_Widget', 'description' => __('Displays a list of activities','eventorganiser') );
		parent::__construct('Winthrop_Event_List_Widget', __('Activities','eventorganiser'), $widget_ops);
	}

 
  function form($instance){	
	$instance = wp_parse_args( (array) $instance, $this->w_arg );
  ?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'eventorganiser'); ?>: </label>
	<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
</p>
  <p>
  <label for="<?php echo $this->get_field_id('numberposts'); ?>"><?php _e('Number of events','eventorganiser');?>:   </label>
	  <input id="<?php echo $this->get_field_id('numberposts'); ?>" name="<?php echo $this->get_field_name('numberposts'); ?>" type="number" size="3" value="<?php echo intval($instance['numberposts']);?>" />
</p>
  <p>
  <label for="<?php echo $this->get_field_id('event-category'); ?>"><?php _e('Event categories', 'eventorganiser'); ?>:   </label>
  <input  id="<?php echo $this->get_field_id('event-category'); ?>" class="widefat" name="<?php echo $this->get_field_name('event-category'); ?>" type="text" value="<?php echo esc_attr($instance['event-category']);?>" />
   <em><?php _e('List category slug(s), seperate by comma. Leave blank for all', 'eventorganiser'); ?> </em>
</p>
  <p>
	  <label for="<?php echo $this->get_field_id('venue'); ?>"><?php _e('Venue', 'eventorganiser'); ?>:   </label>
	<?php 	$venues = get_terms('event-venue', array('hide_empty'=>false));?>
	<select id="<?php echo $this->get_field_id('venue'); ?>" name="<?php echo $this->get_field_name('venue'); ?>" type="text">
		<option value="" <?php selected($instance['venue'], ''); ?>><?php _e('All Venues','eventorganiser'); ?> </option>
		<?php foreach ($venues as $venue):?>
			<option <?php  selected($instance['venue'],$venue->slug);?> value="<?php echo esc_attr($venue->slug);?>"><?php echo esc_html($venue->name); ?></option>
		<?php endforeach;?>
	</select>
</p>

  <p>
  <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by', 'eventorganiser'); ?></label>
	<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>" type="text">
		<option value="eventstart" <?php selected($instance['orderby'], 'eventstart'); ?>><?php _e('Start date', 'eventorganiser'); ?></option>
		<option value="title" <?php selected($instance['orderby'], 'title');?>><?php _e('Title', 'eventorganiser'); ?> </option>
	</select>
	<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>" type="text">
		<option value="asc" <?php selected($instance['order'], 'asc'); ?>><?php _e('ASC', 'eventorganiser'); ?> </option>
		<option value="desc" <?php selected($instance['order'], 'desc');?>><?php _e('DESC', 'eventorganiser'); ?> </option>
	</select>
</p>
  <p>
    <label for="<?php echo $this->get_field_id('showpastevents'); ?>"><?php _e('Include past events', 'eventorganiser'); ?>  </label>
	<input type="checkbox" id="<?php echo $this->get_field_id('showpastevents'); ?>" name="<?php echo $this->get_field_name('showpastevents'); ?>" <?php checked($instance['showpastevents'],1);?> value="1" />
</p>
  <p>
    <label for="<?php echo $this->get_field_id('group_events_by'); ?>"><?php _e('Group occurrences', 'eventorganiser'); ?>  </label>
	<input type="checkbox" id="<?php echo $this->get_field_id('group_events_by'); ?>" value="series" name="<?php echo $this->get_field_name('group_events_by'); ?>" <?php checked($instance['group_events_by'],'series');?> />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('template'); ?>">
	<?php 
		_e('Template (leave blank for default)', 'eventorganiser'); 
		echo eventorganiser_inline_help( 
			__( 'Event list widget placeholders', 'eventorganiser' ),
			sprintf(
					__( 'You can use specified tags as placeholders for event information which you want to appear in the widget. <a href="%s" target="_blank"> Find out more</a>.', 'eventorganiser' ),
					'http://wp-event-organiser.com/documentation/widgets/#whatistemplate'
				)
			);
	 ?>
	</label>
	  <input  id="<?php echo $this->get_field_id('template'); ?>" class="widefat" name="<?php echo $this->get_field_name('template'); ?>" type="text" value="<?php echo esc_attr($instance['template']);?>" />

  </p>
  <p>
    <label for="<?php echo $this->get_field_id('no_events'); ?>"><?php _e("'No events' message", 'eventorganiser'); ?>  </label>
	  <input  id="<?php echo $this->get_field_id('no_events'); ?>" class="widefat" name="<?php echo $this->get_field_name('no_events'); ?>" type="text" value="<?php echo esc_attr($instance['no_events']);?>" />
  </p>

<?php
  }
 
  function update($new_instance, $old_instance){  
	$validated=array();
	$validated['title'] = sanitize_text_field( $new_instance['title'] );
	$validated['numberposts'] = intval($new_instance['numberposts']);
	$event_cats = array_map('sanitize_text_field', explode(',',$new_instance['event-category']));
	$validated['event-category'] = implode(',',$event_cats);
	$validated['venue'] = sanitize_text_field( $new_instance['venue'] );
	$validated['order'] = ($new_instance['order'] == 'asc' ? 'asc' : 'desc');
	$validated['orderby'] = ( $new_instance['orderby'] == 'title' ? 'title' : 'eventstart' );
	$validated['showpastevents'] = ( !empty($new_instance['showpastevents']) ? 1:  0);
	$validated['group_events_by'] = ( isset($new_instance['group_events_by']) && $new_instance['group_events_by']=='series' ? 'series':  '');
	$validated['template'] = $new_instance['template'];
	$validated['no_events'] = $new_instance['no_events'];
	return $validated;
    }

 
  function widget($args, $instance){
  	extract($args, EXTR_SKIP);
  
  	$template = $instance['template'];
  	$no_events = isset($instance['no_events']) ? $instance['no_events'] :'';
  	unset($instance['template']);
  	unset($instance['no_events']);

    echo '<div class="panel brown-panel widget-event-list">';

    eventorganiser_list_events($instance, array('type'=>'widget','class'=>'eo-events eo-events-widget','template'=>$template, 'no_events'=>$no_events));

    echo '</div>';
  }
 
}

?>
