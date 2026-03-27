<?php
/*
Plugin Name: Game Leaderboard
Description: Global leaderboard with quiz filter dropdown
Version: 3.0
Author: Your Name
*/

function gl_filtered_leaderboard() {

    // Get selected quiz
    $selected_quiz = isset($_GET['quiz_filter']) ? intval($_GET['quiz_filter']) : 0;

    // Get all quizzes
    $quizzes = get_posts(array(
        'post_type' => 'quiz',
        'posts_per_page' => -1
    ));

    ob_start();

    echo '<div style="padding:40px 0;">';
    echo '<h1 style="color:#fff; margin-bottom:30px;">🏆 Leaderboard</h1>';

    // =====================
    // DROPDOWN FILTER FORM
    // =====================
    echo '<form method="GET" style="margin-bottom:30px;">';
    echo '<select name="quiz_filter" onchange="this.form.submit()" style="
        padding:10px 15px;
        border-radius:5px;
        background:#1f2326;
        color:#fff;
        border:1px solid rgba(255,255,255,0.2);
    ">';

    echo '<option value="0">All Quizzes</option>';

    foreach ($quizzes as $quiz) {
        $selected = ($selected_quiz == $quiz->ID) ? 'selected' : '';
        echo '<option value="'.$quiz->ID.'" '.$selected.'>'.$quiz->post_title.'</option>';
    }

    echo '</select>';
    echo '</form>';

    // =====================
    // BUILD QUERY
    // =====================
    $args = array(
        'post_type' => 'leaderboard_entry',
        'posts_per_page' => 20,
        'meta_key' => '_leaderboard_percentage',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    );

    // Filter by quiz if selected
    if ($selected_quiz) {
        $args['meta_query'] = array(
            array(
                'key' => '_leaderboard_quiz_id',
                'value' => $selected_quiz,
                'compare' => '='
            )
        );
    }

    $leaderboard = new WP_Query($args);

    // =====================
    // DISPLAY TABLE
    // =====================
    if ($leaderboard->have_posts()) {

        echo '<table style="width:100%; border-collapse:collapse; color:#fff;">';
        echo '<tr style="background:#212529;">
                <th style="padding:10px;">Rank</th>
                <th>Name</th>
                <th>Quiz</th>
                <th>Score</th>
                <th>%</th>
              </tr>';

        $rank = 1;

        while ($leaderboard->have_posts()) {
            $leaderboard->the_post();

            $user_id = get_post_meta(get_the_ID(), '_leaderboard_user_id', true);
            $quiz_id = get_post_meta(get_the_ID(), '_leaderboard_quiz_id', true);
            $score   = get_post_meta(get_the_ID(), '_leaderboard_score', true);
            $pct     = get_post_meta(get_the_ID(), '_leaderboard_percentage', true);

            $user = get_userdata($user_id);
            $name = $user ? $user->display_name : 'Unknown';

            $quiz_title = get_the_title($quiz_id);

            echo '<tr style="border-bottom:1px solid rgba(255,255,255,0.1);">';
            echo '<td style="padding:10px;">'.$rank.'</td>';
            echo '<td>'.esc_html($name).'</td>';
            echo '<td>'.esc_html($quiz_title).'</td>';
            echo '<td>'.$score.'</td>';
            echo '<td>'.$pct.'%</td>';
            echo '</tr>';

            $rank++;
        }

        echo '</table>';

        wp_reset_postdata();

    } else {
        echo '<p style="color:#fff;">No leaderboard data found.</p>';
    }

    echo '</div>';

    return ob_get_clean();
}

add_shortcode('global_leaderboard', 'gl_filtered_leaderboard');