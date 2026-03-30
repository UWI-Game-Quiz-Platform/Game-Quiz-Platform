<?php
/*
Plugin Name: Profile Dashboard & Quiz Ideas
Description: Adds a My Profile dashboard with quiz stats, recent activity, and a front-end quiz idea submission form.
Version: 1.0.0
Author: OpenAI
*/

if (!defined('ABSPATH')) {
    exit;
}

class PDQI_Profile_Dashboard_Quiz_Ideas {
    const NONCE_ACTION = 'pdqi_submit_quiz_idea';
    const NONCE_NAME   = 'pdqi_nonce';

    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_existing_taxonomies_for_quiz_ideas'), 20);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('init', array($this, 'handle_form_submission'));
        add_shortcode('my_profile_dashboard', array($this, 'render_dashboard_shortcode'));
        add_filter('manage_quiz_idea_posts_columns', array($this, 'admin_columns'));
        add_action('manage_quiz_idea_posts_custom_column', array($this, 'admin_column_content'), 10, 2);
    }

    public function register_post_type() {
        register_post_type('quiz_idea', array(
            'labels' => array(
                'name'               => 'Quiz Ideas',
                'singular_name'      => 'Quiz Idea',
                'add_new'            => 'Add New Idea',
                'add_new_item'       => 'Add New Quiz Idea',
                'edit_item'          => 'Edit Quiz Idea',
                'new_item'           => 'New Quiz Idea',
                'view_item'          => 'View Quiz Idea',
                'search_items'       => 'Search Quiz Ideas',
                'not_found'          => 'No quiz ideas found',
                'not_found_in_trash' => 'No quiz ideas found in trash',
                'menu_name'          => 'Quiz Ideas',
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'supports'            => array('title', 'editor', 'author'),
            'menu_icon'           => 'dashicons-lightbulb',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
        ));
    }

    public function register_existing_taxonomies_for_quiz_ideas() {
        if (taxonomy_exists('quiz_genre')) {
            register_taxonomy_for_object_type('quiz_genre', 'quiz_idea');
        }
        if (taxonomy_exists('quiz_difficulty')) {
            register_taxonomy_for_object_type('quiz_difficulty', 'quiz_idea');
        }
    }

    public function enqueue_assets() {
        wp_register_style(
            'pdqi-profile-dashboard',
            plugin_dir_url(__FILE__) . 'assets/profile-dashboard-quiz-ideas.css',
            array(),
            '1.0.0'
        );
    }

    public function handle_form_submission() {
        if (!isset($_POST['pdqi_action']) || $_POST['pdqi_action'] !== 'submit_quiz_idea') {
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        $user_id      = get_current_user_id();
        $title        = isset($_POST['pdqi_title']) ? sanitize_text_field(wp_unslash($_POST['pdqi_title'])) : '';
        $description  = isset($_POST['pdqi_description']) ? wp_kses_post(wp_unslash($_POST['pdqi_description'])) : '';
        $genre        = isset($_POST['pdqi_genre']) ? sanitize_text_field(wp_unslash($_POST['pdqi_genre'])) : '';
        $difficulty   = isset($_POST['pdqi_difficulty']) ? sanitize_text_field(wp_unslash($_POST['pdqi_difficulty'])) : '';

        if ($title === '' || $description === '') {
            $redirect = add_query_arg('pdqi_status', 'missing', wp_get_referer() ? wp_get_referer() : home_url('/'));
            wp_safe_redirect($redirect);
            exit;
        }

        $post_id = wp_insert_post(array(
            'post_type'    => 'quiz_idea',
            'post_status'  => 'pending',
            'post_title'   => $title,
            'post_content' => $description,
            'post_author'  => $user_id,
        ));

        if (is_wp_error($post_id) || !$post_id) {
            $redirect = add_query_arg('pdqi_status', 'error', wp_get_referer() ? wp_get_referer() : home_url('/'));
            wp_safe_redirect($redirect);
            exit;
        }

        update_post_meta($post_id, '_pdqi_submitted_by', $user_id);

        if ($genre !== '') {
            if (taxonomy_exists('quiz_genre') && term_exists((int)$genre, 'quiz_genre')) {
                wp_set_object_terms($post_id, array((int)$genre), 'quiz_genre', false);
            } else {
                update_post_meta($post_id, '_pdqi_quiz_genre_text', $genre);
            }
        }

        if ($difficulty !== '') {
            if (taxonomy_exists('quiz_difficulty') && term_exists((int)$difficulty, 'quiz_difficulty')) {
                wp_set_object_terms($post_id, array((int)$difficulty), 'quiz_difficulty', false);
            } else {
                update_post_meta($post_id, '_pdqi_quiz_difficulty_text', $difficulty);
            }
        }

        $redirect = add_query_arg('pdqi_status', 'success', wp_get_referer() ? wp_get_referer() : home_url('/'));
        wp_safe_redirect($redirect);
        exit;
    }

    public function render_dashboard_shortcode() {
        wp_enqueue_style('pdqi-profile-dashboard');

        if (!is_user_logged_in()) {
            return '<div class="pdqi-wrap"><div class="pdqi-panel"><h3>Login Required</h3><p>Please log in to view your profile dashboard and submit quiz ideas.</p></div></div>';
        }

        $user            = wp_get_current_user();
        $user_id         = $user->ID;
        $stats           = $this->get_user_stats($user_id);
        $recent_attempts = $this->get_recent_attempts($user_id, 5);
        $quiz_ideas      = $this->get_user_quiz_ideas($user_id, 5);
        $status_message  = $this->get_status_message();

        ob_start();
        ?>
        <div class="pdqi-wrap">
            <?php if ($status_message) : ?>
                <div class="pdqi-alert <?php echo esc_attr($status_message['class']); ?>">
                    <?php echo esc_html($status_message['message']); ?>
                </div>
            <?php endif; ?>

            <div class="pdqi-panel pdqi-hero">
                <div>
                    <div class="pdqi-eyebrow">My Profile Dashboard</div>
                    <h2>Welcome, <?php echo esc_html($user->display_name); ?></h2>
                    <p>Track your quiz performance, monitor standout results, and submit new quiz ideas for review.</p>
                </div>
                <div class="pdqi-user-meta">
                    <div><strong>Username:</strong> <?php echo esc_html($user->user_login); ?></div>
                    <div><strong>Email:</strong> <?php echo esc_html($user->user_email); ?></div>
                    <div><strong>Role:</strong> <?php echo esc_html(implode(', ', array_map('ucfirst', $user->roles))); ?></div>
                </div>
            </div>

            <div class="pdqi-stats-grid">
                <div class="pdqi-stat-card">
                    <span class="pdqi-stat-label">Quizzes Attempted</span>
                    <span class="pdqi-stat-value"><?php echo esc_html($stats['attempts']); ?></span>
                </div>
                <div class="pdqi-stat-card">
                    <span class="pdqi-stat-label">Best Score</span>
                    <span class="pdqi-stat-value"><?php echo esc_html($stats['best_score']); ?>%</span>
                </div>
                <div class="pdqi-stat-card">
                    <span class="pdqi-stat-label">Average Score</span>
                    <span class="pdqi-stat-value"><?php echo esc_html($stats['average_score']); ?>%</span>
                </div>
                <div class="pdqi-stat-card">
                    <span class="pdqi-stat-label">Perfect Scores</span>
                    <span class="pdqi-stat-value"><?php echo esc_html($stats['perfect_scores']); ?></span>
                </div>
                <div class="pdqi-stat-card">
                    <span class="pdqi-stat-label">Top 3 Finishes</span>
                    <span class="pdqi-stat-value"><?php echo esc_html($stats['top_three_finishes']); ?></span>
                </div>
                <div class="pdqi-stat-card">
                    <span class="pdqi-stat-label">Quiz Ideas Submitted</span>
                    <span class="pdqi-stat-value"><?php echo esc_html($stats['quiz_ideas_count']); ?></span>
                </div>
            </div>

            <?php if ((int) $stats['attempts'] === 0) : ?>
                <div class="pdqi-panel">
                    <p>No quiz results yet. Take a quiz to start building your profile stats.</p>
                </div>
            <?php endif; ?>

            <div class="pdqi-grid-two">
                <div class="pdqi-panel">
                    <h3>Recent Quiz Activity</h3>
                    <?php if (!empty($recent_attempts)) : ?>
                        <div class="pdqi-list">
                            <?php foreach ($recent_attempts as $attempt) : ?>
                                <div class="pdqi-list-item">
                                    <div class="pdqi-list-main">
                                        <div class="pdqi-list-title"><?php echo esc_html($attempt['quiz_title']); ?></div>
                                        <div class="pdqi-list-meta">
                                            Score: <?php echo esc_html($attempt['score']); ?>/<?php echo esc_html($attempt['total']); ?>
                                            • <?php echo esc_html($attempt['percentage']); ?>%
                                            <?php if ($attempt['time_taken'] !== '') : ?>
                                                • <?php echo esc_html($attempt['time_taken']); ?> sec
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="pdqi-list-side"><?php echo esc_html($attempt['date']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p>No quiz activity yet. Try a quiz to see your stats and recent attempts here.</p>
                    <?php endif; ?>
                </div>

                <div class="pdqi-panel">
                    <h3>My Submitted Quiz Ideas</h3>
                    <?php if (!empty($quiz_ideas)) : ?>
                        <div class="pdqi-list">
                            <?php foreach ($quiz_ideas as $idea) : ?>
                                <div class="pdqi-list-item">
                                    <div class="pdqi-list-main">
                                        <div class="pdqi-list-title"><?php echo esc_html($idea['title']); ?></div>
                                        <div class="pdqi-list-meta">
                                            <?php
                                                $genre_name = '';
                                                $difficulty_name = '';

                                                // Genre
                                                if (!empty($idea['genre'])) {
                                                    if (is_numeric($idea['genre'])) {
                                                        $term = get_term((int)$idea['genre']);
                                                        if ($term && !is_wp_error($term)) {
                                                            $genre_name = $term->name;
                                                        }
                                                    } else {
                                                        $genre_name = $idea['genre'];
                                                    }
                                                }

                                                // Difficulty
                                                if (!empty($idea['difficulty'])) {
                                                    if (is_numeric($idea['difficulty'])) {
                                                        $term = get_term((int)$idea['difficulty']);
                                                        if ($term && !is_wp_error($term)) {
                                                            $difficulty_name = $term->name;
                                                        }
                                                    } else {
                                                        $difficulty_name = $idea['difficulty'];
                                                    }
                                                }
                                                ?>

                                                <?php if ($genre_name || $difficulty_name) : ?>
                                                    <div class="pdqi-list-meta">
                                                        <?php echo esc_html(trim($genre_name . ' • ' . $difficulty_name, ' •')); ?>
                                                    </div>
                                                <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="pdqi-badge pdqi-status-<?php echo esc_attr($idea['status']); ?>">
                                        <?php echo esc_html(ucfirst($idea['status'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p>You have not submitted any quiz ideas yet. Use the form below to send one for review.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pdqi-panel">
                <h3>Submit a Quiz Idea</h3>
                <p>Share a new quiz concept. It will be saved as <strong>Pending Review</strong> until approved by the team. Only logged-in users can submit ideas through this form.</p>
                <form class="pdqi-form" method="post">
                    <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>
                    <input type="hidden" name="pdqi_action" value="submit_quiz_idea" />

                    <div class="pdqi-form-grid">
                        <div class="pdqi-field pdqi-field-full">
                            <label for="pdqi_title">Quiz Idea Title</label>
                            <input type="text" id="pdqi_title" name="pdqi_title" required />
                        </div>

                        <div class="pdqi-field">
                            <label for="pdqi_genre">Genre</label>
                            <?php echo $this->render_genre_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>

                        <div class="pdqi-field">
                            <label for="pdqi_difficulty">Difficulty</label>
                            <?php echo $this->render_difficulty_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>

                        <div class="pdqi-field pdqi-field-full">
                            <label for="pdqi_description">Short Description</label>
                            <textarea id="pdqi_description" name="pdqi_description" rows="5" required placeholder="Describe the idea, possible questions, theme, or why players would enjoy it."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="pdqi-button">Submit Quiz Idea</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_status_message() {
        if (!isset($_GET['pdqi_status'])) {
            return null;
        }

        $status = sanitize_text_field(wp_unslash($_GET['pdqi_status']));
        switch ($status) {
            case 'success':
                return array('class' => 'pdqi-alert-success', 'message' => 'Your quiz idea was submitted successfully and is now pending review.');
            case 'missing':
                return array('class' => 'pdqi-alert-error', 'message' => 'Please fill in the title and description before submitting.');
            case 'error':
                return array('class' => 'pdqi-alert-error', 'message' => 'Something went wrong while submitting your quiz idea. Please try again.');
            default:
                return null;
        }
    }

    private function get_user_stats($user_id) {
        $entries = get_posts(array(
            'post_type'      => 'leaderboard_entry',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_leaderboard_user_id',
                    'value'   => (string) $user_id,
                    'compare' => '='
                )
            )
        ));

        $attempts = count($entries);
        $best     = 0;
        $sum      = 0;
        $perfect  = 0;

        foreach ($entries as $entry) {
            $pct = (float) get_post_meta($entry->ID, '_leaderboard_percentage', true);
            $best = max($best, $pct);
            $sum += $pct;
            if ($pct >= 100) {
                $perfect++;
            }
        }

        $average = $attempts > 0 ? round($sum / $attempts, 1) : 0;

        return array(
            'attempts'            => $attempts,
            'best_score'          => rtrim(rtrim(number_format($best, 1, '.', ''), '0'), '.'),
            'average_score'       => rtrim(rtrim(number_format($average, 1, '.', ''), '0'), '.'),
            'perfect_scores'      => $perfect,
            'top_three_finishes'  => $this->count_top_three_finishes($user_id),
            'quiz_ideas_count'    => (int) count_user_posts($user_id, 'quiz_idea', true),
        );
    }

    private function count_top_three_finishes($user_id) {
        $all_entries = get_posts(array(
            'post_type'      => 'leaderboard_entry',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ));

        if (empty($all_entries)) {
            return 0;
        }

        $best_by_user_quiz = array();

        foreach ($all_entries as $entry) {
            $entry_user = (int) get_post_meta($entry->ID, '_leaderboard_user_id', true);
            $quiz_id    = (int) get_post_meta($entry->ID, '_leaderboard_quiz_id', true);
            $pct        = (float) get_post_meta($entry->ID, '_leaderboard_percentage', true);
            $time       = (float) get_post_meta($entry->ID, '_leaderboard_time_taken', true);
            $time       = $time > 0 ? $time : PHP_INT_MAX;

            if (!$entry_user || !$quiz_id) {
                continue;
            }

            if (!isset($best_by_user_quiz[$quiz_id])) {
                $best_by_user_quiz[$quiz_id] = array();
            }

            $should_replace = false;
            if (!isset($best_by_user_quiz[$quiz_id][$entry_user])) {
                $should_replace = true;
            } else {
                $current = $best_by_user_quiz[$quiz_id][$entry_user];
                if ($pct > $current['pct']) {
                    $should_replace = true;
                } elseif ($pct === $current['pct'] && $time < $current['time']) {
                    $should_replace = true;
                }
            }

            if ($should_replace) {
                $best_by_user_quiz[$quiz_id][$entry_user] = array(
                    'pct'  => $pct,
                    'time' => $time,
                );
            }
        }

        $top_three_count = 0;

        foreach ($best_by_user_quiz as $quiz_id => $user_scores) {
            uasort($user_scores, function ($a, $b) {
                if ($a['pct'] === $b['pct']) {
                    return $a['time'] <=> $b['time'];
                }
                return ($a['pct'] > $b['pct']) ? -1 : 1;
            });

            $rank = 0;
            foreach ($user_scores as $entry_user => $score_data) {
                $rank++;
                if ((int) $entry_user === (int) $user_id && $rank <= 3) {
                    $top_three_count++;
                    break;
                }
                if ($rank >= 3 && (int) $entry_user !== (int) $user_id) {
                    continue;
                }
            }
        }

        return $top_three_count;
    }

    private function get_recent_attempts($user_id, $limit = 5) {
        $entries = get_posts(array(
            'post_type'      => 'leaderboard_entry',
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => array(
                array(
                    'key'     => '_leaderboard_user_id',
                    'value'   => (string) $user_id,
                    'compare' => '='
                )
            )
        ));

        $results = array();
        foreach ($entries as $entry) {
            $quiz_id = (int) get_post_meta($entry->ID, '_leaderboard_quiz_id', true);
            $results[] = array(
                'quiz_title'  => $quiz_id ? get_the_title($quiz_id) : 'Unknown Quiz',
                'score'       => (string) get_post_meta($entry->ID, '_leaderboard_score', true),
                'total'       => (string) get_post_meta($entry->ID, '_leaderboard_total', true),
                'percentage'  => (string) get_post_meta($entry->ID, '_leaderboard_percentage', true),
                'time_taken'  => (string) get_post_meta($entry->ID, '_leaderboard_time_taken', true),
                'date'        => get_the_date('M j, Y', $entry),
            );
        }

        return $results;
    }

    private function get_user_quiz_ideas($user_id, $limit = 5) {
        $posts = get_posts(array(
            'post_type'      => 'quiz_idea',
            'posts_per_page' => $limit,
            'post_status'    => array('pending', 'publish', 'draft', 'future', 'private'),
            'author'         => $user_id,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        $results = array();
        foreach ($posts as $post) {
            $genre_terms = taxonomy_exists('quiz_genre') ? get_the_terms($post->ID, 'quiz_genre') : false;
            $difficulty_terms = taxonomy_exists('quiz_difficulty') ? get_the_terms($post->ID, 'quiz_difficulty') : false;
            $genre = (!is_wp_error($genre_terms) && !empty($genre_terms)) ? $genre_terms[0]->name : get_post_meta($post->ID, '_pdqi_quiz_genre_text', true);
            $difficulty = (!is_wp_error($difficulty_terms) && !empty($difficulty_terms)) ? $difficulty_terms[0]->name : get_post_meta($post->ID, '_pdqi_quiz_difficulty_text', true);
            $results[] = array(
                'title'      => $post->post_title,
                'status'     => $post->post_status,
                'genre'      => $genre ? $genre : 'No genre selected',
                'difficulty' => $difficulty,
            );
        }
        return $results;
    }

    private function render_genre_field() {
        if (taxonomy_exists('quiz_genre')) {
            $terms = get_terms(array(
                'taxonomy'   => 'quiz_genre',
                'hide_empty' => false,
            ));

            if (!is_wp_error($terms) && !empty($terms)) {
                ob_start();
                ?>
                <select id="pdqi_genre" name="pdqi_genre">
                    <option value="">Select Genre</option>
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
                return ob_get_clean();
            }
        }

        return '<input type="text" id="pdqi_genre" name="pdqi_genre" placeholder="e.g. FPS, Sports, RPG" />';
    }

    private function render_difficulty_field() {
        if (taxonomy_exists('quiz_difficulty')) {
            $terms = get_terms(array(
                'taxonomy'   => 'quiz_difficulty',
                'hide_empty' => false,
            ));

            if (!is_wp_error($terms) && !empty($terms)) {
                ob_start();
                ?>
                <select id="pdqi_difficulty" name="pdqi_difficulty">
                    <option value="">Select Difficulty</option>
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
                return ob_get_clean();
            }
        }

        return '<input type="text" id="pdqi_difficulty" name="pdqi_difficulty" placeholder="e.g. Easy, Medium, Hard" />';
    }

    public function admin_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;
            if ($key === 'title') {
                $new_columns['pdqi_genre'] = 'Genre';
                $new_columns['pdqi_difficulty'] = 'Difficulty';
            }
        }
        return $new_columns;
    }

    public function admin_column_content($column, $post_id) {
        if ($column === 'pdqi_genre') {
            $terms = taxonomy_exists('quiz_genre') ? get_the_terms($post_id, 'quiz_genre') : false;
            if (!is_wp_error($terms) && !empty($terms)) {
                echo esc_html($terms[0]->name);
            } else {
                echo esc_html(get_post_meta($post_id, '_pdqi_quiz_genre_text', true));
            }
        }

        if ($column === 'pdqi_difficulty') {
            $terms = taxonomy_exists('quiz_difficulty') ? get_the_terms($post_id, 'quiz_difficulty') : false;
            if (!is_wp_error($terms) && !empty($terms)) {
                echo esc_html($terms[0]->name);
            } else {
                echo esc_html(get_post_meta($post_id, '_pdqi_quiz_difficulty_text', true));
            }
        }
    }
}

new PDQI_Profile_Dashboard_Quiz_Ideas();
