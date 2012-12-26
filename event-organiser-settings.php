<?php
/****** SETTINGS PAGE ******/
if(!class_exists('EventOrganiser_Admin_Page')){
    require_once(EVENT_ORGANISER_DIR.'classes/class-eventorganiser-admin-page.php' );
}
class EventOrganiser_Settings_Page extends EventOrganiser_Admin_Page{

	static $editable_roles;
	static $sup_array;
	static $eventorganiser_roles;
	static $settings;
	static $sections;

	/**
	 * Initialises the tabs.
	 */
	function setup_tabs(){
		return apply_filters('eventorganiser_settings_tabs',array(
					'general'=>__('General','eventorganiser'),
					'permissions'=>__('Permissions','eventorganiser'),
					'permalinks'=>__('Permalinks','eventorganiser'),
					'imexport'=>__('Import','eventorganiser').'/'.__('Export','eventorganiser'),
				));
	}

	function set_constants(){
		$this->hook = 'options-general.php';
		$this->title = __('Event Organiser Settings','eventorganiser');
		$this->menu = __('Event Organiser','eventorganiser');
		$this->permissions ='manage_options';
		$this->slug ='event-settings';
	}

	function  admin_init_actions(){
		//Register options
		register_setting( 'eventorganiser_options', 'eventorganiser_options', array($this,'validate'));

		//Initialise the tab array
		$this->tabs = $this->setup_tabs();
		
		foreach ($this->tabs as $tab_id => $label){
			//Add sections to each tabbed page
			switch ($tab_id){
				case 'general':
					register_setting( 'eventorganiser_'.$tab_id, 'eventorganiser_options', array($this,'validate') );
					add_settings_section($tab_id,__('General','eventorganiser'), '__return_false',  'eventorganiser_'.$tab_id);
					add_settings_section($tab_id.'_templates',__('Templates','eventorganiser'), '__return_false',  'eventorganiser_'.$tab_id);
					break;
				case 'permissions':
					register_setting( 'eventorganiser_'.$tab_id, 'eventorganiser_options', array($this,'validate') );
					add_settings_section($tab_id,'',array($this,'display_permissions'),  'eventorganiser_'.$tab_id);
					break;
				case 'permalinks':
					register_setting( 'eventorganiser_'.$tab_id, 'eventorganiser_options', array($this,'validate') );
					add_settings_section($tab_id,'',array($this,'display_permalinks'),  'eventorganiser_'.$tab_id);
					break;
				case 'imexport':
					register_setting( 'eventorganiser_'.$tab_id, 'eventorganiser_options', array($this,'validate') );
					add_settings_section($tab_id,'',array($this,'display_imexport'),  'eventorganiser_'.$tab_id);
					break;
			}
			do_action("eventorganiser_register_tab_{$tab_id}", $tab_id);
			$this->add_fields($tab_id);
		}

		global $eventorganiser_roles;
		self::$eventorganiser_roles = array(
			 'edit_events' =>__('Edit Events','eventorganiser'),
			 'publish_events' =>__('Publish Events','eventorganiser'),
			 'delete_events' => __('Delete Events','eventorganiser'),
			'edit_others_events' =>__('Edit Others\' Events','eventorganiser'),
			 'delete_others_events' => __('Delete Other\'s Events','eventorganiser'),
			'read_private_events' =>__('Read Private Events','eventorganiser'),
			 'manage_venues' => __('Manage Venues','eventorganiser'),
			 'manage_event_categories' => __('Manage Event Categories & Tags','eventorganiser'),
		);
	}


	/**
	 * This is called in the register_settings method.
	 * Once the tabs have been registered, and sections added to each tabbed page, we now add the fields for each section
	 * A section should have the form {tab_id}._{identifer} (e.g. general_main, or gateways_google). 
	 * 
	 * @param $tab_id - the string identifer for the tab (given in $this->tabs as the key).
	 * @uses add_settings_field
	 */
	function add_fields($tab_id){

		switch($tab_id){
			case 'general':
				/* General - main */
				add_settings_field('supports', __('Select which features events should support','eventorganiser'), 'eventorganiser_checkbox_field', 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'supports',
						'checked' => eventorganiser_get_option('supports'),
						'options' => array(
							'author' => __('Organiser','eventorganiser').' ('.__('Author').')',
							'thumbnail' =>__('Thumbnail'),
							'excerpt' => __('Excerpt'),
							'custom-fields' => __('Custom Fields'),
							'comments' => __('Comments'),
							'revisions' => __('Revisions')
						),
						'name'=>'eventorganiser_options[supports]'
				));

				add_settings_field('showpast',  __("Show past events:",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'showpast',
						'name'=>'eventorganiser_options[showpast]',
						'options'=> 1,
						'checked'=> eventorganiser_get_option('showpast'),
						'help'=> __("Display past events on calendars, event lists and archives (this can be over-ridden by shortcode attributes and widget options).",'eventorganiser')
				));

				add_settings_field('addtomenu',  __("Add an 'events' link to the navigation menu:",'eventorganiser'), array($this,'menu_option'), 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'addtomenu',
				));

				add_settings_field('dateformat', __('Date Format:','eventorganiser'), 'eventorganiser_select_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'dateformat',
						'selected'=> eventorganiser_get_option('dateformat'),
						'name'=>'eventorganiser_options[dateformat]',
						'options'=>array(
							'dd-mm'=> __('dd-mm-yyyy','eventorganiser'),
							'mm-dd'=> __('mm-dd-yyyy','eventorganiser'),
						),
						'help'=>__("This alters the default format for inputting dates.",'eventorganiser'),
				));

				add_settings_field('showpast',  __("Show past events:",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'showpast',
						'name'=>'eventorganiser_options[showpast]',
						'options'=> 1,
						'checked'=> eventorganiser_get_option('showpast'),
						'help'=> __("Display past events on calendars, event lists and archives (this can be over-ridden by shortcode attributes and widget options).",'eventorganiser')
				));

				add_settings_field('group_events',  __("Group occurrences",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'group_events',
						'name'=>'eventorganiser_options[group_events]',
						'options'=> 'series',
						'checked'=> eventorganiser_get_option('group_events'),
						'help'=> __("If selected only one occurrence of an event will be displayed on event lists and archives (this can be over-ridden by shortcode attributes and widget options.",'eventorganiser')
				));				

				add_settings_field('runningisnotpast',  __("Are current events past?",'eventorganiser'), 'eventorganiser_select_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'runningisnotpast',
						'name'=>'eventorganiser_options[runningisnotpast]',
						'selected'=> eventorganiser_get_option('runningisnotpast',0),
						'options'=>array(
							'0'=> __('No','eventorganiser'),
							'1'=> __('Yes','eventorganiser'),
						),
						'help'=>__("If 'no' is selected, an occurrence of an event is only past when it has finished. Otherwise, an occurrence is considered 'past' as soon as it starts.",'eventorganiser'),
				));

				add_settings_field('deleteexpired',  __("Delete expired events:",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'deleteexpired',
						'name'=>'eventorganiser_options[deleteexpired]',
						'options'=> 1,
						'checked'=> eventorganiser_get_option('deleteexpired'),
						'help'=> __("If selected the event will be automatically trashed 24 hours after the last occurrence finishes.",'eventorganiser')
				));

				add_settings_field('feed',  __("Enable events ICAL feed:",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'feed',
						'name'=>'eventorganiser_options[feed]',
						'options'=> 1,
						'checked'=>eventorganiser_get_option('feed'),
						'help'=>sprintf(__('If selected, visitors can subscribe to your events with the url: %s','eventorganiser'), '<code>'.eo_get_events_feed().'</code>')
				));

				add_settings_field('excludefromsearch',  __("Exclude events from searches:",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'excludefromsearch',
						'name'=>'eventorganiser_options[excludefromsearch]',
						'options'=> 1,
						'checked'=>eventorganiser_get_option('excludefromsearch'),
				));

				add_settings_field('templates',  __("Enable templates:",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id.'_templates',
					array(
						'label_for'=>'templates',
						'name'=>'eventorganiser_options[templates]',
						'options'=> 1,
						'checked'=>eventorganiser_get_option('templates'),
						'help'=>__("For each of the pages, the corresponding template is used. To use your own template simply give it the same name and store in your theme folder. By default, if Event Organiser cannot find a template in your theme directory, it will use its own default template. To prevent this, uncheck this option. WordPress will then decide which template from your theme's folder to use.",'eventorganiser'). sprintf("<p><strong> %s </strong><code>archive-event.php</code></p>
											<p><strong> %s </strong><code>single-event.php</code></p>
											<p><strong> %s </strong><code>venue-template.php</code></p>
											<p><strong> %s </strong><code>taxonomy-event-category.php</code></p>",
												__("Events archives:",'eventorganiser'),
												__("Event page:",'eventorganiser'),
												 __("Venue page:",'eventorganiser'),
												__("Events Category page:",'eventorganiser')
										)
					));
				break;


			case 'permissions':
				break;

			case 'permalinks':
				add_settings_field('prettyurl',  __("Enable event pretty permalinks:",'eventorganiser'), 'eventorganiser_checkbox_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'prettyurl',
						'name'=>'eventorganiser_options[prettyurl]',
						'options'=> 1,
						'checked'=>eventorganiser_get_option('prettyurl'),
						'help'=>__("If you have pretty permalinks enabled, select to have pretty premalinks for events.",'eventorganiser')
				));

				$site_url = site_url();
				add_settings_field('url_event', __("Event (single)",'eventorganiser'), 'eventorganiser_text_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'url_event',
						'name'=>'eventorganiser_options[url_event]',
						'value'=>eventorganiser_get_option('url_event'),
						'help'=>"<label><code>{$site_url}/<strong>".eventorganiser_get_option('url_event')."</strong>/[event_slug]</code></label>"
				));

				add_settings_field('url_events', __("Event (archive)",'eventorganiser'), 'eventorganiser_text_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'url_events',
						'name'=>'eventorganiser_options[url_events]',
						'value'=>eventorganiser_get_option('url_events'),
						'help'=>"<label><code>{$site_url}/<strong>".eventorganiser_get_option('url_events')."</strong></code></label>"
				));
	
				add_settings_field('url_venue', __("Venues",'eventorganiser'), 'eventorganiser_text_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'url_venue',
						'name'=>'eventorganiser_options[url_venue]',
						'value'=>eventorganiser_get_option('url_venue'),
						'help'=>"<label><code>{$site_url}/<strong>".eventorganiser_get_option('url_venue')."</strong>/[venue_slug]</code></label>"
				));

				add_settings_field('url_cat', __("Event Categories",'eventorganiser'), 'eventorganiser_text_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'url_cat',
						'name'=>'eventorganiser_options[url_cat]',
						'value'=>eventorganiser_get_option('url_cat'),
						'help'=>"<label><code>{$site_url}/<strong>".eventorganiser_get_option('url_cat')."</strong>/[event_cat_slug]</code></label>"
				));

				add_settings_field('url_cat', __("Event Tags",'eventorganiser'), 'eventorganiser_text_field' , 'eventorganiser_'.$tab_id, $tab_id,
					array(
						'label_for'=>'url_tag',
						'name'=>'eventorganiser_options[url_tag]',
						'value'=>eventorganiser_get_option('url_tag'),
						'help'=>"<label><code>{$site_url}/<strong>".eventorganiser_get_option('url_tag')."</strong>/[event_tag_slug]</code></label>"
				));

				break;
		}
	}


	function validate($option){
		/* Backwards compatibility: all EO options are in one row, but on seperate pages.
		 Just merge with existing options, and validate them all */

		if(  isset($option['tab'])  ){
			$tab = $option['tab'];
			unset($option['tab'] );
		}else{
			$tab = false;
		}
		
		$clean = array();
		
		switch( $tab ){
			case 'general':
				$checkboxes  = array('showpast','templates','excludefromsearch','deleteexpired','feed','eventtag','group_events');
				$text = array('navtitle','dateformat','runningisnotpast','addtomenu');

				foreach( $checkboxes as $cb ){

					//Empty checkboxes send no data..
					$value = isset($option[$cb]) ? $option[$cb] : 0;
				
					$clean[$cb] = $this->validate_checkbox($value);
				}

				foreach( $text as $txt ){
					if( !isset($option[$txt]) )
						continue;
				
					$clean[$txt] = $this->validate_text($option[$txt]);
				}

				//Group events is handled differently
				$clean['group_events'] = ( !empty($clean['group_events']) ? 'series' : '');

				//Post type supports
				$clean['supports'] = (isset($option['supports']) ? array_map('esc_html',$option['supports']) : array());
				$clean['supports'] = array_merge($clean['supports'],array('title','editor'));

				//Navigation menu - $addtomenu int 0 if no menu, menu databse ID otherwise
				$clean['menu_item_db_id'] = $this->update_nav_menu( $clean['addtomenu'], $clean['navtitle'] );
			break;


			case 'permalinks':
				$permalinks = array('url_event','url_events','url_venue','url_cat','url_tag');
				
				foreach( $permalinks as $permalink ){
					if( !isset($option[$permalink]) )
						continue;
			
					$value = $this->validate_permalink($option[$permalink]);

					if( !empty($value) )
						$clean[$permalink] = $value;
				}
			
				$clean['prettyurl'] = isset( $option['prettyurl']) ? $this->validate_checkbox( $option['prettyurl'] ) : 0;
			break;


			case 'permissions':
				//Permissions
				$permissions = (isset($option['permissions']) ? $option['permissions'] : array());
				$this->update_roles($permissions);
			break;
		}

		$existing_options = get_option('eventorganiser_options',array());
		$clean = array_merge( $existing_options, $clean);
		return $clean;
	}

	function validate_checkbox( $value ){
		return ( !empty($value) ? 1 : 0 );
	}

	function validate_text( $value ){
		return ( !empty( $value ) ? esc_html($value) : false );
	}

	function validate_permalink( $value ){
		$value = esc_url_raw($value);
		$value = str_replace( 'http://', '', $value );
		$value = trim($value, "/");	
		return $value;
	}

	/**
	 *
 	 *@param $menu_databse_id int 0 for no menu, 1 for 'fallback', term ID for menu otherwise
	 *
	*/
	function update_nav_menu( $menu_id, $menu_item_title ){

		//Get existing menu item
		$menu_item_db_id = (int) eventorganiser_get_option('menu_item_db_id');
	
		//Validate exiting menu item ID
		if( !is_nav_menu_item($menu_item_db_id) ){
			$menu_item_db_id=0;
		}

		//If Menu is not selected, or 'page list' fallback is, and we have an 'events' item added to some menu, remove it
		if( (empty($menu_id) || $menu_id=='1' ) && is_nav_menu_item($menu_item_db_id) ){
			//Remove menu item
			wp_delete_post( $menu_item_db_id, true );
			$menu_item_db_id=0;
		}

		//If the $menu is an int > 1, we are adding/updating an item (post type) so that it has term set to $menu_id
		if( ( !empty($menu_id) && $menu_id !='1') ){
			$menu_item_data = array();
			
			//Validate menu ID
			$menu_obj = wp_get_nav_menu_object($menu_id);
			$menu_id = ($menu_obj ? $menu_obj->term_id : 0);

			//Set status
			$status = ($menu_id==0 ? '' : 'publish');

			$menu_item_data = array(	
				'menu-item-title'=> $menu_item_title,
				'menu-item-url' =>get_post_type_archive_link('event'),
				'menu-item-object' =>'event',
				'menu-item-status' =>$status,
				'menu-item-type'=>'post_type_archive'
			);

			//Update menu item (post type) to have taxonom term $menu_id
			$menu_item_db_id = wp_update_nav_menu_item( $menu_id, $menu_item_db_id,$menu_item_data);
		}

		//Return the menu item (post type) ID. 0 For no item added, or item removed.
		return $menu_item_db_id;
	}


	function update_roles($permissions){
		global $wp_roles,$EO_Errors,$eventorganiser_roles;
		$editable_roles = get_editable_roles();
		foreach( $editable_roles as $role_name => $display_name):
			$role = $wp_roles->get_role($role_name);
			//Don't edit the administrator
			if($role_name!='administrator'):
				//Foreach custom role, add or remove option.
				foreach($eventorganiser_roles as $eo_role => $eo_role_display):
					if(isset($permissions[$role_name][$eo_role]) &&$permissions[$role_name][$eo_role]==1){
						$role->add_cap($eo_role);		
					}else{
						$role->remove_cap($eo_role);		
					}
				endforeach; //End foreach $eventRoles
			endif; // Don't change administrator
		endforeach; //End foreach $editable_roles
	}



	function display(){
		?>
    		<div class="wrap">  
      
			<?php screen_icon('options-general'); ?>
		        <?php 

				$active_tab = ( isset( $_GET[ 'tab' ] ) &&  isset( $this->tabs[$_GET[ 'tab' ]] ) ? $_GET[ 'tab' ] : 'general');
				$page = $this->slug;

				echo '<h2 class="nav-tab-wrapper">';

					foreach($this->tabs as $tab_id =>$label){
				          	printf('<a href="?page=%s&tab=%s" class="nav-tab %s">%s</a>',
							$page,
							$tab_id,
							($active_tab == $tab_id ? 'nav-tab-active' : ''),
							esc_html($label)
						);
					}
				echo '</h2>';
				?>

				<?php if( 'imexport' != $active_tab ){
					echo '<form method="post" action="options.php">';
						settings_fields( 'eventorganiser_'.$active_tab);
						do_settings_sections( 'eventorganiser_'.$active_tab ); 
						//Tab identifier - so we know which tab we are validating. See $this->validate().
						do_action('eventorganiser_event_settings_'.$active_tab); 
						printf('<input type="hidden" name="eventorganiser_options[tab]" value="%s" />',esc_attr($active_tab));
						submit_button(); 
				        echo '</form>';

				}else{
					do_action('eventorganiser_event_settings_imexport'); 
				}
				?>
          
		</div><!-- /.wrap -->  

	<?php
	}

	function display_imexport(){
		do_action('eventorganiser_event_settings_imexport'); 
	}
	
	function display_permissions(){
		global $wp_roles;
		 echo '<p>'.__('Set permissions for events and venue management','eventorganiser').'</p>';
	?>
	<table class="widefat fixed posts">
		<thead>
			<tr>
				<th><?php _e('Role'); ?></th>
				<?php foreach(self::$eventorganiser_roles as $eo_role => $eo_role_display): ?>
					<th><?php echo esc_html($eo_role_display);?></th>
				<?php endforeach; ?> 
			</tr>
		</thead>		
		<tbody id="the-list">
			<?php
			$array_index =0;
			foreach( get_editable_roles() as $role_name => $display_name):
				$role = $wp_roles->get_role($role_name); 
				$role_name = isset( $wp_roles->role_names[$role_name] ) ? translate_user_role( $wp_roles->role_names[$role_name] ) : __( 'None' );

				printf('<tr %s>', $array_index==0 ? 'class="alternate"' : '' );
					printf('<td> %s </td>',esc_html($role_name) );

					foreach(self::$eventorganiser_roles as $eo_role => $eo_role_display):
						printf('<td>
									<input type="checkbox" name="eventorganiser_options[permissions][%s][%s]" value="1" %s %s  />
								</td>',
								esc_attr($role->name),
								esc_attr($eo_role),
								checked('1', $role->has_cap($eo_role), false ),
								disabled( $role->name, 'administrator',false) 
							);
					endforeach; //End foreach $eventRoles 
				echo '</tr>';
	
				$array_index=($array_index+1)%2;

			endforeach; //End foreach $editable_role ?>
		</tbody>
		</table>
<?php
	}


	function display_permalinks(){
		$site_url = site_url();
		
		printf( '<p> %s </p>
				<p> %s %s </p>',
				esc_html__("Choose a custom permalink structure for events, venues, event categories and event tags.",'eventorganiser'),
				esc_html__("Please note to enable these structures you must first have pretty permalinks enabled on WordPress in Settings > Permalinks.",'eventorganiser'),
				esc_html__("You may also need to go to WordPress Settings > Permalinks and click 'Save Changes' before any changes will take effect.",'eventorganiser')
			);
	}
	

	function menu_option(){

		$menus = get_terms('nav_menu');?>
			<select  name="eventorganiser_options[addtomenu]">
				<option  <?php selected(0,eventorganiser_get_option('addtomenu'));?> value="0"><?php _e('Do not add to menu','eventorganiser'); ?> </option>
			<?php foreach($menus as $menu): ?>
				<option  <?php selected($menu->slug,eventorganiser_get_option('addtomenu'));?> value="<?php echo $menu->slug; ?>"><?php echo $menu->name;?> </option>
			<?php endforeach; ?>
				<option  <?php selected(1, eventorganiser_get_option('addtomenu'));?> value="1"><?php _e('Page list (fallback)','eventorganiser'); ?></option>
			</select>

			<?php printf('<input type="hidden" name ="eventorganiser_options[menu_item_db_id]" value="%d" />',eventorganiser_get_option('menu_item_db_id')); ?>
			<?php printf('<input type="text" name="eventorganiser_options[navtitle]" value="%s" />',eventorganiser_get_option('navtitle')); ?>
			
			<?php _e("(This may not work with some themes)",'eventorganiser'); 
	}




}
$settings_page = new EventOrganiser_Settings_Page();

/**
 * Utility function for printing/returning radio boxes
 *
 * The $args array - excepts the following keys
 *
 * * **id** - The id of the radiobox (alias: label_for)
 * * **name** - The name of the radiobox
 * * **checked** - The the value to have checked
 * * **options** - Array of options in 'value'=>'Label' format
 * * **label** - The label for the radiobox field set
 * * **class** - Class to be added to the radiobox field set
 * * **echo** - Whether to print the mark-up or just return it
 * * **help** - Optional, help text to accompany the field.
 *
 * @access private
 * @param $args array The array of arguments
 */
function eventorganiser_radio_field( $args ){

	$args = wp_parse_args($args,array(
			'checked'=>'', 'help' => '', 'options'=>'', 'name'=>'', 'echo'=>1,
			'class'=>'', 'label' => '','label_for'=>''
			));	

	$id = ( !empty($args['id']) ? $args['id'] : $args['label_for']);
	$name = isset($args['name']) ?  $args['name'] : '';
	$checked = $args['checked'];
	$label = !empty($args['label']) ? '<legend class="screen-reader-text"><span>'.esc_html($args['label']).'</span></legend>' : '';
	$class =  !empty($args['class']) ? 'class="'.esc_attr($args['class']).'"'  : '';

	$html = sprintf('<fieldset %s> %s', $class, $label);
	if( !empty($args['options']) ){
		foreach ($args['options'] as $value => $opt_label ){
			printf('<label for="%1$s"><input type="radio" id="%1$s" name="%3$s" value="%4$s" %2$s> <span> %5$s </span></label><br>',
				esc_attr($id.'_'.$value),
				checked($value, $checked, false),
				esc_attr($name),
				esc_attr($value),
				esc_html($opt_label));
		}
	}
	if(!empty($args['help'])){
		$html .= '<p class="description">'.esc_html($args['help']).'</p>';
	}
	$html .= '</fieldset>';
}


/**
 * Utility function for printing/returning select field
 *
 * The $args array - excepts the following keys
 *
 * * **id** - The id of the select box (alias: label_for)
 * * **name** - The name of the select box
 * * **selected** - The the value to have selected
 * * **options** - Array of options in 'value'=>'Label' format
 * * **label** - The label for the radiobox field set
 * * **class** - Class to be added to the radiobox field set
 * * **echo** - Whether to print the mark-up or just return it
 * * **help** - Optional, help text to accompany the field.
 *
 * @access private
 * @param $args array The array of arguments
 */
function eventorganiser_select_field($args){

	$args = wp_parse_args($args,array(
			'select'=>'', 'help' => '', 'options'=>'', 'name'=>'', 'echo'=>1,
			'label_for'=>''
		));	

	$id = ( !empty($args['id']) ? $args['id'] : $args['label_for']);
	$name = isset($args['name']) ?  $args['name'] : '';
	$selected = $args['selected'];
	$class = isset($args['class']) ? esc_attr($args['class'])  : '';


	$html = sprintf('<select %s name="%s" id="%s">',
		isset($args['class']) ? 'class="'.esc_attr($args['class']).'"'  : '',
			esc_attr($name),
			esc_attr($args['label_for'])
		);
		
		if( !empty($args['options']) ){
			foreach ($args['options'] as $value => $label ){
				$html .= sprintf('<option value="%s" %s> %s </option>',esc_attr($value), selected($selected, $value, false), esc_html($label));
			}
		}
	$html .= '</select>';

	if(!empty($args['help'])){
		$html .= '<p class="description">'.esc_html($args['help']).'</p>';
	}

	if( $args['echo'] )
		echo $html;

	return $html;
}


/**
 * Utility function for printing/returning text field
 *
 * The $args array - excepts the following keys
 *
 * * **id** - The id of the select box (alias: label_for)
 * * **name** - The name of the select box
 * * **value** - The value of the text field
 * * **type** - The type  of the text field (e.g. 'text','hidden','password')
 * * **options** - Array of options in 'value'=>'Label' format
 * * **label** - The label for the radiobox field set
 * * **class** - Class to be added to the radiobox field set
 * * **echo** - Whether to print the mark-up or just return it
 * * **help** - Optional, help text to accompany the field.
 *
 * @access private
 * @param $args array The array of arguments
 */
function eventorganiser_text_field($args){

	$args = wp_parse_args($args,
		array(
		 	'type' => 'text', 'value'=>'', 'placeholder' => '', 'help' => '','label_for'=>'',
			 'size'=>false, 'min' => false, 'max' => false, 'style'=>false, 'echo'=>true,
			)
		);		

	$id = ( !empty($args['id']) ? $args['id'] : $args['label_for']);
	$name = isset($args['name']) ?  $args['name'] : '';
	$value = $args['value'];
	$type = $args['type'];
	$class = isset($args['class']) ? esc_attr($args['class'])  : '';

	$min = (  !empty($args['min']) ?  sprintf('min="%d"', $args['min']) : '' );
	$max = (  !empty($args['max']) ?  sprintf('max="%d"', $args['max']) : '' );
	$size = (  !empty($args['size']) ?  sprintf('size="%d"', $args['size']) : '' );
	$style = (  !empty($args['style']) ?  sprintf('style="%s"', $args['style']) : '' );
	$placeholder = ( !empty($args['placeholder']) ? sprintf('placeholder="%s"',$args['placeholder']) : '');
	$disabled = ( !empty($args['disabled']) ? 'disabled="disabled"' : '' );
	$attributes = array_filter(array($min,$max,$size,$placeholder,$disabled, $style));

	$html = sprintf('<input type="%s" name="%s" class="%s regular-text ltr" id="%s" value="%s" autocomplete="off" %s />',
		esc_attr($type),
		esc_attr($name),
		$class,
		esc_attr($id),
		esc_attr($value),
		implode(' ', $attributes)
	);
	if( isset($args['help']) ){
		$html .= '<p class="description">'.$args['help'].'</p>';
	}

	if( $args['echo'] )
		echo $html;

	return $html;
}
	

/**
 * Utility function for printing/returning text field
 *
 * The $args array - excepts the following keys
 *
 * * **id** - The id of the checkbox (alias: label_for)
 * * **name** - The name of the select box
 * * **options** - Single or Array of options in 'value'=>'Label' format
 * * **values** - The values of the text field
 * * **type** - The type  of the text field (e.g. 'text','hidden','password')

 * * **label** - The label for the radiobox field set
 * * **class** - Class to be added to the radiobox field set
 * * **echo** - Whether to print the mark-up or just return it
 * * **help** - Optional, help text to accompany the field.
 *
 * @access private
 * @param $args array The array of arguments
 */
function eventorganiser_checkbox_field($args=array()){

	$args = wp_parse_args($args,array(
		 	'type' => 'text', 'value'=>'', 'placeholder' => '', 'help' => '','name'=>'',
			'checked'=>'', 'echo'=>true,
		));

	$id = ( !empty($args['id']) ? $args['id'] : $args['label_for']);
	$name = isset($args['name']) ?  $args['name'] : '';
	$class = isset($args['class']) ? 'class="'.esc_attr($args['class']).'"'  : '';

	/* $options and $checked are either both arrays or they are both strings. */
	$options =  isset($args['options']) ? $args['options'] : false;
	$checked =  isset($args['checked']) ? $args['checked'] : 1;

	$html ='';
	if( is_array($options) ){
		foreach( $options as $value => $opt_label ){
			$html .= sprintf('<label for="%1$s">
								<input type="checkbox" name="%2$s" id="%1$s" value="%3$s" %4$s %5$s> 
								%6$s </br>
							</label>',
							esc_attr($id.'_'.$value),
							esc_attr(trim($name).'[]'),
							esc_attr($value),
							checked( in_array($value, $checked), true, false ),
							$class,
							 esc_attr($opt_label)
							);
		}
	}else{
		$html .= sprintf('<input type="checkbox" id="%1$s" name="%2$s" value="%5$s" %3$s 4$s>',
							esc_attr($id),
							esc_attr($name),
							checked( $checked, $options, false ),
							$class,
							esc_attr($options)
							);
	}
	
	if(!empty($args['help'])){
		$html .= '<p class="description">'.$args['help'].'</p>';
	}

	if( $args['echo'] )
		echo $html;

	return $html;
}



/**
 * Utility function for printing/returning text area
 *
 * The $args array - excepts the following keys
 *
 * * **id** - The id of the checkbox (alias: label_for)
 * * **name** - The name of the select box
 * * **options** - Single or Array of options in 'value'=>'Label' format
 * * **tinymce** Whether to use the TinyMCE editor. The TinyMCE prints directly.
 * * **value** - The value of the text area
 * * **rows** - The number of rows. Default 5.
 * * **cols** - The number of columns. Default 50.
 * * **class** - Class to be added to the textarea
 * * **echo** - Whether to print the mark-up or just return it
 * * **help** - Optional, help text to accompany the field.
 *
 * @access private
 * @param $args array The array of arguments
 */
function eventorganiser_textarea_field($args){

	$args = wp_parse_args($args,array(
	 	'type' => 'text', 'value'=>'', 'tinymce' => '', 'help' => '',
		'class'=>'large-text', 'echo'=>true,'rows'=>5, 'cols'=>50
	));

	$id = ( !empty($args['id']) ? $args['id'] : $args['label_for']);
	$name = isset($args['name']) ?  $args['name'] : '';
	$value = $args['value'];
	$class = $args['class'];

	if( $args['tinymce'] ){
		wp_editor( $current, esc_attr($args['label_for']) ,array(
				'textarea_name'=>$name,
				'media_buttons'=>false,
			));
	}else{
		$html .= sprintf('<textarea cols="%s" rows="%d" name="%s" class="%s large-text" id="%s">%s</textarea>',
				intval($cols),
				intval($rows),
				esc_attr($name),
				esc_attr($class),
				esc_attr($id),
				esc_textarea($value)
		);
	}

	if( !empty($args['help']) ){
		$html .= '<p class="description"><label for="'.$args['label_for'].'">'.$args['help'].'<label></p>';
	}

	if( $args['echo'] )
		echo $html;

	return $html;
}
?>
