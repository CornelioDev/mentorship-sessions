<?php
/*
 * Template Name: Mentorship Form
 */

get_header();

// form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_mentorship'])) {

    $mentee_name = sanitize_text_field($_POST['mentee_name']);
    $session_date = sanitize_text_field($_POST['session_date']);
    $session_time = sanitize_text_field($_POST['session_time']);
    $session_notes = sanitize_textarea_field($_POST['session_notes']);

    $errors = array();

    // Check if the session date is in the future
    $current_time = current_time('timestamp');
    $selected_datetime = strtotime($_POST['session_date'] . ' ' . $_POST['session_time']);
    
    if ($selected_datetime <= $current_time) {
        $errors['session_date'] = 'Please choose a future date and time.';
    }

    // Save to the database if no validation errors
    if (empty($errors)) {
        $post_id = wp_insert_post(array(
            'post_title' => $mentee_name,
            'post_type' => 'mentorship_session',
            'post_status' => 'publish',
        ));

        // Add custom fields for session details
        update_post_meta($post_id, 'mentee_name', $mentee_name);
        update_post_meta($post_id, 'session_date', $session_date);
        update_post_meta($post_id, 'session_time', $session_time);
        update_post_meta($post_id, 'session_notes', $session_notes);

        // success message
        echo '<h3 class="container success-message">Mentorship session scheduled successfully!</h3>';
    } else {
        // validation errors
        foreach ($errors as $error) {
            echo '<p class="error-message">' . esc_html($error) . '</p>';
        }
    }
}

?>

<div class="container">
    <form id="mentorship-form" method="post" action="">
        <label for="mentee-name">Mentee's Name:</label>
        <input type="text" id="mentee-name" name="mentee_name" required>

        <label for="session-date">Date:</label>
        <input type="date" id="session-date" name="session_date" required>
        
        <label for="session-time">Time:</label>
        <input type="time" id="session-time" name="session_time" required>

        <label for="session-notes">Session Notes:</label>
        <textarea id="session-notes" name="session_notes" rows="4" required></textarea>

        <button type="submit" name="submit_mentorship">Schedule Session</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const currentDate = new Date();
            const formattedDate = currentDate.toISOString().split('T')[0];

            const sessionDateInput = document.getElementById('session-date');
            sessionDateInput.min = formattedDate;
        });
    </script>
</div>

<?php get_footer(); ?>
