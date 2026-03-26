<?php
/*
Plugin Name: Game Leaderboard
Description: Stores and displays user quiz scores
Version: 1.0
Author: Your Name
*/

// Create table on plugin activation
function gl_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leaderboard';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name text NOT NULL,
        score int NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'gl_create_table');


// Handle form submission
function gl_handle_submission() {
    if (isset($_POST['gl_name']) && isset($_POST['gl_score'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'leaderboard';

        $name = sanitize_text_field($_POST['gl_name']);
        $score = intval($_POST['gl_score']);

        $wpdb->insert($table_name, [
            'name' => $name,
            'score' => $score
        ]);
    }
}
add_action('init', 'gl_handle_submission');


// Display form + leaderboard
function gl_display_leaderboard() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leaderboard';

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY score DESC LIMIT 10");

    ob_start();
    ?>

    <h2>Submit Your Score</h2>
    <form method="post">
        <input type="text" name="gl_name" placeholder="Enter Name" required><br><br>
        <input type="number" name="gl_score" placeholder="Enter Score" required><br><br>
        <button type="submit">Submit Score</button>
    </form>

    <h2>Leaderboard</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>Name</th>
            <th>Score</th>
        </tr>

        <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo esc_html($row->name); ?></td>
                <td><?php echo esc_html($row->score); ?></td>
            </tr>
        <?php endforeach; ?>

    </table>

    <?php
    return ob_get_clean();
}

add_shortcode('game_leaderboard', 'gl_display_leaderboard');