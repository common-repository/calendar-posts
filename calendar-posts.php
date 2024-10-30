<?php
/*
Plugin Name: Calendar Posts
Plugin URI: http://www.swedishboy.dk/products/calendar-posts/
Description: A powerful yet simple plugin for adding calendar functionality to posts.
Version: 0.7.1
Author: Johan Str&ouml;m
Author URI: http://www.swedishboy.se/wordpress
*/

 

// set up plugin, check for conflicts.

if(!class_exists('CalendarPosts')) :

## CP vars and constants, edit if you want to customize stuff. #########################
#
#	a few constants
	define('cp_lang', 'en-GB');
	define('datepick_package', WP_PLUGIN_URL.'/calendar-posts/jquery.datepick.package');
	
#	how months are displayed for posts. we choose to not use PHP's abbrevations since they
#	are not typographicly correct.
#
	$cp_months = array(
	'en-GB' => array(0,'Jan','Feb','Mar','Apr','May','June','July','Aug','Sept','Oct','Nov','Dec'),
	'sv' => array(0,'jan','feb','mars','apr','maj','juni','juli','aug','sept','okt','nov','dec'),
	// make your custom one here and set cp_lang to it.
	'custom' => array(0,'jan','feb','mars','apr','maj','juni','juli','aug','sept','okt','nov','dec')

	);
#
#	what to separate date and post title with.
#
	$cp_separator = ': ';
#
#
########################################################################################


class CalendarPosts extends WP_Widget {
	
	// post box and save handling
	
	function dater_inner_box() {
	
	  // Use nonce for verification
	
		echo '<input type="hidden" name="swecpdater_nonce" id="swecpdater_nonce" value="' . 
		wp_create_nonce('swecp-date') . '" />';
		wp_enqueue_script("jquery"); 

	  // The actual fields for data entry
		global $post;
		$datum		= get_post_meta($post->ID,'cp_date',true);
		$datum_slut	= get_post_meta($post->ID,'cp_date_end',true);
		$tid 		= get_post_meta($post->ID,'cp_tid',true);	
	
		// output of css linking and jquery packages
	?>
	<link type="text/css" href="<?php echo datepick_package; ?>/redmond.datepick.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo datepick_package; ?>/ui-redmond.datepick.css" rel="stylesheet" />
	<style type="text/css"> @import "<?php echo datepick_package; ?>/redmond/ui.datepicker.css"; </style>
	<script type="text/javascript" src="<?php echo datepick_package; ?>/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="<?php echo datepick_package; ?>/jquery.datepick.js"></script>
	<?php // remove localization when releasing plugin ?>
	<script type="text/javascript" src="<?php echo datepick_package; ?>/jquery.datepick.lang.min.js"></script>
		
	<script type="text/javascript">

		// some necessary jquery actions for datepick package

		var $j = jQuery.noConflict();
		$j(function() {
			var saved_dates = $j('#cp_dates').val().split(',');
	
			$j.datepick.setDefaults($j.datepick.regional['<?php echo cp_lang; ?>']); // read in plugin setting later for language ...		
			$j("#calendar_pickr").datepick(
			{dateFormat: $j.datepick.ATOM, multiSelect: 10, monthsToShow: 1, 
				onSelect: function(dates) { 
					fixdates = Array();
					f = $j.datepick.ATOM;
					if(dates.length>1) {
	
						for (var i = 0; i < dates.length; i++) { 
							fixdates.push($j.datepick.formatDate(f,dates[i]));
						}
						fixdates.sort();
						
					}else fixdates = $j.datepick.formatDate(f,dates[0]);
	
					$j('#cp_dates').val(fixdates);
				} 
			});
	
			$j("#calendar_pickr").datepick('setDate', saved_dates);
		});
	
	</script>
	<br />
	<span>
<?php 

	// this hidden input field is where we store dates picked 

?>	
	<input type="hidden" id="cp_dates" name="CP_dates" value="<?php echo $datum; ?>" />
	<div id="calendar_pickr"></div>
	</span>
	<p class="howto"><?php _e('Choose up to 10 dates for the calendar'); ?></p>
	<p><label for="tid"><?php _e('Time'); ?></label> <input type="text" name="tid" value="<?php echo $tid; ?>" size="8"></p>
	<p class="howto">(<?php _e('optional'); ?>) <?php _e('Will be shown for all dates'); ?></p>
	  <?php
	 }
	
	function save_handler($post_id) {
	
	  // verify this came from the our screen and with proper authorization
	  if ( isset($_POST['swecpdater_nonce']) && !wp_verify_nonce( $_POST['swecpdater_nonce'], 'swecp-date') ) {
		return $post_id;
	  }
	
	  // verify if this is an auto save routine. 
	  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;
	
	  
	  // Check permissions
		if ( !current_user_can( 'edit_post', $post_id ) )
		  return $post_id;
	
	  // OK, we're authenticated: we need to find and save the data
	  	if(isset($_POST['CP_dates'])) {
			if($tid = $_POST['cp_tid']) 	update_post_meta($post_id,'cp_tid',$tid);
			if($datum = $_POST['CP_dates']) {
				$datum_a = split(',',$datum);
				sort($datum_a, 'SORT_NUMERIC');
				
				update_post_meta($post_id,'cp_date_start', array_shift($datum_a));
		
				$end_date = count($datum_a)>0 ? array_pop($datum_a) : $datum;
				update_post_meta($post_id,'cp_date_end', $end_date);
					
				update_post_meta($post_id,'cp_date',$datum);
			}
		}
	}
	
	function add_metaboxes() {
		add_meta_box( 'swecpdater', __( 'Calendar', 'swecp-dater' ), array('CalendarPosts','dater_inner_box'), 'post', 'side', 'core');
	}

	function CalendarPosts() {
		parent::WP_Widget(false, $name = 'Calendar Posts', array('description' => 'Widget to show a calendar linking to posts using the Calendar Posts plugin'));		
	}

	// widget output
	function widget($args, $instance) {

		// get args		
		extract($args);

		// get instances
		extract($instance);

		// strings.
        $title = apply_filters('widget_title', $title);
        
        echo '<style>
        .cp-post { display: block; }
   </style>';
		
		// vars for query
		$amount = is_int($show) ? $show : '-1';
		$fetch_from = is_int($hide_after) ? date('Y-m-d', time()-($hide_after*86400)) : date('Y-m-d');

		
		// setup arrays
		$cal = $split_dates = array();
		$mq1 = 	array('key'=> 'cp_date_end', 'value' => $fetch_from, 'compare' => '>=', 'type' => 'DATE');

		if(!is_int($show)) {
			$mq2 = array('key'	=> 'cp_date_start', 'compare' 	=> '<=','type' => 'DATE');

			switch($show) {
				case 'a' :
					$mq2 = null;
				break;

				case 'b':
					for($i=1; $i<=date('t'); $i++) {
						$cal[date('Y-m-'.$i)] = false;
					}
					$mq1['value']	= date('Y-m-01');
					$mq2['value'] 	= date('Y-m-t');

				break;
				
				case 'c':
					$mq2['value'] 	= 	$mq1['value']	= date('Y-m-d');
				 break;
			}
		}
		
		$q = array(
			'numberposts' 	=>	$amount,
			'meta_key'		=> 'cp_date_start',
			'meta_query'	=>	array($mq1, $mq2),
			'orderby'		=>	'meta_value date'
			);
		
		global $post;

		$myposts = get_posts($q);

		if(count($myposts) > 0 || !$hide_null) {
			echo $before_widget;
			if($title) echo $before_title . $title . $after_title;
		}
		
		if(count($myposts) > 0) {
		 	foreach($myposts as $post) :
				setup_postdata($post);
				$meta = get_post_custom();
				$title = the_title('','',false);
				$dates = explode(',',$meta['cp_date'][0]);
				$datetime2 = null;
				$cats = get_the_category();
				$cat = $cats[0]->cat_name;
				$author = get_the_author();
				$id = $post->ID;

//				if(count($dates)>1) {
					$end_date = end($dates);
					$recur = count($dates)>1 ? 1 : 0;
					foreach($dates as $d) {
	
						$datetime1 = strtotime($d);
						if($datetime2) {
							$diff = round(abs($datetime1-$datetime2)/60/60/24);
							if($diff>1) $split_dates[$id] = true;
						}	
						$datetime2 = strtotime($d);

						$cal[$d][] = array('id'=> $id, 'title' => $title, 'link' => get_permalink(), 'recur' => $recur, 'start' => $dates[0], 'end' => $end_date, 'author' => $author, 'categories' => $cat);
					}
			endforeach;
		} else
			if(!$hide_null) _e('No upcoming events ...');
			
			$ranges_out = array();
			global $cp_months, $cp_separator;
			$months_ab = $cp_months[cp_lang];

			$e_layouts = array(0, '<span class="cp-date">%2$s</span>%5$s %1$s', 
								'%1$s%5$s <span class="cp-date">%2$s</span> ', 
								'%1$s',
								'%1$s <span class="cp-author">(%3$s)</span>',
								'%1$s <span class="cp-cat">(%4$s)</span>',
								'<span class="cp-cat">%4$s:</span> %1$s '
							);
							
		if(count($cal) > 0) :
			foreach($cal as $datum => $ci) {
			
				if($group_monthly) {
					$month = date_i18n('F', strtotime($datum));
					if(!isset($last_month) || $last_month!=$month) echo "<div class='cp-month'>$month</div>";
					$last_month = $month;
				}
			
				if($show=='b' && substr($datum,0,7)!=date('Y-m') ) continue;
				if($show=='c' && $datum!=date('Y-m-d') ) continue;

				$events = '';

				if($ci) {

					foreach($ci as $c) {
					

					$splited = @$split_dates[$c['id']];

						if($c['recur']==0 || !$show_only_start || $splited || !in_array($c['id'], $ranges_out)) {

							if($layout<=2) {
								$start_m = $months_ab[substr($c['start'],5,7)*1];
								$end_m = $months_ab[substr($c['end'],5,7)*1];

								if($c['recur']==1 && !$splited && $show_only_start) {
									//show month only after last date if same month
									if( substr($c['start'],0,7) == substr($c['end'],0,7) ) $start_m = '';
								
									$dateout = 	date('j', strtotime($c['start'])).$start_m. '&ndash;' .
												date('j ', strtotime($c['end'])).$end_m;
									$ranges_out[] = $c['id'];
								}else
									$dateout = date('j ', strtotime($datum)).$start_m;

							}else 
								$dateout = '';
														
							$entry = sprintf($e_layouts[$layout], $c['title'], $dateout, $c['author'], $c['categories'], $cp_separator); 
							$events.= sprintf('<span class="cp-post"> <a href="%s">%s</a></span>', $c['link'], $entry);
						}
					}
				}
				
				if(!empty($events) || $show=='b') {		

//					@$count++;

?>
					<div class="cp-date-div">
					<?php if($group_dates) echo '<span class="cp-date">'.date_i18n('j', strtotime($datum)).'</span>';
							echo $events;	
					?>
					</div>
<?php
				}
			}
		endif;


		if(count($myposts) > 0 || !$hide_null)	echo $after_widget;
		
		
		wp_reset_query();

	}

	function widget_fields() {
			$fields = array('title' 	=> '',
							'show'		=> '', 
							'layout'	=> 0,
							'show_all' 	=> 0,
							'group_dates' => 0,
							'group_monthly' => 0,
							'hide_after' => '',
							'show_only_start' => 0,
							'hide_null' => 0
							);
			return $fields;	
	}


	// widget admin update settings
	function update($new_instance, $old_instance) {		
			// Remember to sanitize and format use input appropriately.

			$hide_after = $new_instance['hide_after'];
			$instance = array_merge(self::widget_fields(), $new_instance);

			$instance['hide_after'] = is_numeric($hide_after) ? $hide_after : '';
			return $instance;
	}

	// widget admin form
	function form($instance) {

		extract( array_merge(self::widget_fields(), $instance) );


?>
	<p>
	 <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label>
	  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
<?php
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		printf('<p><label for="%s">%s</label>', $this->get_field_id('show'), __('Posts to display'));
		printf('<select id="%s" name="%s" style="width: 228px;">', $this->get_field_id('show'), $this->get_field_name('show'));
		

		$options = array('a' => __('All upcoming'),'b' => __('Current month'), 'c' => __('Only current date'),
						1,2,3,4,5,6,7,8,9,10,11,12,15,20,25,30);		

		// output options in select menu
			foreach($options as $key => $val) {
				if(is_numeric($val)) $key = $val;
				echo '<option value="'.$key.'" '.( $instance['show']==$key ? ' selected' : '').'>'.$val.'</option>';
			}

		echo '</select></p>';
		
		printf('<p><label for="%s">%s</label>', $this->get_field_id('layout'), __('Output'));
		printf('<select id="%s" name="%s" style="width: 228px;">', $this->get_field_id('layout'), $this->get_field_name('layout'));
		
		$types = array(0,'datestamp + title','title + datestamp','only title', 'title + author', 'title + category', 'category + title');		

		// output options in select menu
			for($i=1; $i<count($types); $i++) {
				echo '<option value="'.$i.'" '.( $instance['layout']==$i ? ' selected' : '').'>'.$types[$i].'</option>';
			}

		echo '</select></p>';		
		
		printf('<p><input type="checkbox" value="1" id="%1$s" name="%2$s" %3$s /> <label for="%1$s">%4$s</label><small class="howto">%5$s</small></p>',
					$this->get_field_id('show_all'), 
					$this->get_field_name('show_all'), 
					( (isset($show_all) && $show_all==1) ? ' checked' : ''),
					__('Show all dates (unused too)'),
					__('used with option "Current Month"')
					);

		printf('<p><input type="checkbox" value="1" id="%1$s" name="%2$s" %3$s /> <label for="%1$s">%4$s</label></p>',
					$this->get_field_id('group_monthly'), 
					$this->get_field_name('group_monthly'), 
					( ($group_monthly==1) ? ' checked' : ''),
					__('Group by month')
					);

		printf('<p><input type="checkbox" value="1" id="%1$s" name="%2$s" %3$s /> <label for="%1$s">%4$s</label></p>',
					$this->get_field_id('group_dates'), 
					$this->get_field_name('group_dates'), 
					( ($group_dates==1) ? ' checked' : ''),
					__('Group by date')
					);

		printf('<p><input type="checkbox" value="1" id="%1$s" name="%2$s" %3$s /> <label for="%1$s">%4$s</label></p>',
					$this->get_field_id('show_only_start'), 
					$this->get_field_name('show_only_start'), 
					( $show_only_start==1 ? ' checked' : ''),
					__('Display posts for a range of dates on starting date only')
					);

		printf('<p><label for="%1$s">%4$s <input type="text" class="fat" size="4" value="%3$s" id="%1$s" name="%2$s" /> %5$s</label> </p>',
					$this->get_field_id('hide_after'), 
					$this->get_field_name('hide_after'), 
					$hide_after,
					__('Hide past posts after'),
					__('days')
					);

		printf('<p><input type="checkbox" value="1" id="%1$s" name="%2$s" %3$s /> <label for="%1$s">%4$s</label></p>',
					$this->get_field_id('hide_null'), 
					$this->get_field_name('hide_null'), 
					( $hide_null==1 ? ' checked' : ''),
					__('Hide calendar when no upcoming posts exists')
					);

	}


}

// hooks

add_action('widgets_init', create_function('','return register_widget("CalendarPosts");'));
add_action('save_post', array('CalendarPosts','save_handler'));
add_action('admin_menu', array('CalendarPosts','add_metaboxes'));

endif;

// add else: > message to warn about conflict.

// Run our code later in case this loads prior to any required plugins.



