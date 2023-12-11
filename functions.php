<?php
//------------ MENTORSHIP SESSION CUSTOM CODE---------------//

// Register a custom post-type
function register_mentorship_post_type() {
    $labels = array(
        'name'               => 'Mentorship Sessions',
        'singular_name'      => 'Mentorship Session',
        'add_new'            => 'Add New',
        'menu_name'          => 'Mentorship Sessions',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_menu'       => true,
        'rewrite'            => array('slug' => 'mentorship-session'),
        'supports'           => array('title'),
    );

    register_post_type('mentorship_session', $args);
}
add_action('init', 'register_mentorship_post_type');


//-------------------CUSTOM WIDGET-------------//

class Mentorship_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'mentorship_widget',
            'Mentorship Widget',
            array('description' => 'Display upcoming mentorship sessions.')
        );
    }

    public function widget($args, $instance) {
		$title = apply_filters('widget_title', $instance['title']);
		$num_sessions = !empty($instance['num_sessions']) ? $instance['num_sessions'] : 5;
	
		echo $args['before_widget'];
		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
	
		// title and date
		$sessions = get_upcoming_mentorship_sessions($num_sessions);
		if ($sessions) {
			echo '<ul>';
			foreach ($sessions as $session) {
				$session_date = get_post_meta($session->ID, 'session_date', true);
				echo '<li><a href="' . get_permalink($session->ID) . '">' . get_the_title($session->ID) . ' (' . date('F j, Y', strtotime($session_date)) . ')</a></li>';
			}
			echo '</ul>';
		} else {
			echo '<p>No upcoming mentorship sessions found.</p>';
		}
	
		echo $args['after_widget'];
	}	

	// Widget configuration
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Upcoming Mentorship Sessions';
        $num_sessions = !empty($instance['num_sessions']) ? $instance['num_sessions'] : 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('num_sessions'); ?>">Number of Sessions to Display:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('num_sessions'); ?>" name="<?php echo $this->get_field_name('num_sessions'); ?>" type="number" value="<?php echo esc_attr($num_sessions); ?>" min="1">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['num_sessions'] = (!empty($new_instance['num_sessions'])) ? intval($new_instance['num_sessions']) : 5;
        return $instance;
    }
}

function register_mentorship_widget() {
    add_action('widgets_init', function () {
        register_widget('Mentorship_Widget');
    });
}
add_action('after_setup_theme', 'register_mentorship_widget');

function get_upcoming_mentorship_sessions($num_sessions = 5) {
    $args = array(
        'post_type' => 'mentorship_session',
        'posts_per_page' => $num_sessions,
        'meta_key' => 'session_date',
        'orderby' => 'meta_value',
        'order' => 'ASC', // closest date first
        'meta_query' => array(
            array(
                'key' => 'session_date',
                'value' => date('Y-m-d H:i:s'),
                'compare' => '>=',
                'type' => 'DATETIME',
            ),
        ),
    );

    $query = new WP_Query($args);

    return $query->posts;
}

