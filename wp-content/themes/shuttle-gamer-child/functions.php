<?php
/**
 * Shuttle Gamer Child Theme Functions
 * Games Quiz Platform - INFO3602
 * Team: Varune Rampersad, Josiah Phillip, Ijaaz Sisarran
 */


// ============================================================
// 1. ENQUEUE STYLES (Parent → Shuttle Gamer → Child)
// ============================================================
function shuttle_gamer_child_enqueue_styles() {

    // Load root parent (Shuttle) stylesheet
    wp_enqueue_style(
        'shuttle-parent-style',
        get_theme_root_uri() . '/shuttle/style.css'
    );

    // Load Shuttle Gamer stylesheet (middle layer)
    wp_enqueue_style(
        'shuttle-gamer-style',
        get_theme_root_uri() . '/shuttle-gamer/style.css',
        array( 'shuttle-parent-style' )
    );

    // Load our child theme stylesheet (our customizations)
    wp_enqueue_style(
        'shuttle-gamer-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'shuttle-gamer-style' ),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'shuttle_gamer_child_enqueue_styles' );


// ============================================================
// 2. APPLY SHUTTLE GAMER SKIN SETTINGS
// ============================================================
function shuttle_gamer_child_apply_gamer_settings() {

    $name_options_free   = 'shuttle_redux_variables';
    $name_options_child  = 'shuttle_child_settings_writer';
    $options_free        = get_option( $name_options_free );
    $options_already_set = get_option( $name_options_child );

    if ( $options_already_set != 1 ) {

        if ( ! is_array( $options_free ) ) {
            $options_free = (array) null;
        }

        $options_free['shuttle_header_styleswitch'] = 'option1';
        $options_free['shuttle_blog_style']         = 'option2';
        $options_free['shuttle_blog_style1layout']  = '';
        $options_free['shuttle_blog_style2layout']  = 'option1';
        $options_free['shuttle_styles_colorswitch'] = '';
        $options_free['shuttle_styles_colorcustom'] = '';
        $options_free['shuttle_styles_skinswitch']  = '1';
        $options_free['shuttle_styles_skin']        = 'gamer';

        update_option( $name_options_free, $options_free );
        update_option( $name_options_child, 1 );
    }
}
add_action( 'init', 'shuttle_gamer_child_apply_gamer_settings', 999 );


// ============================================================
// 3. REGISTER CUSTOM POST TYPES
// ============================================================
function gamer_register_post_types() {

    // ----------------------------------
    // CPT 1: Quiz
    // ----------------------------------
    register_post_type( 'quiz', array(
        'labels' => array(
            'name'               => 'Quizzes',
            'singular_name'      => 'Quiz',
            'add_new'            => 'Add New Quiz',
            'add_new_item'       => 'Add New Quiz',
            'edit_item'          => 'Edit Quiz',
            'new_item'           => 'New Quiz',
            'view_item'          => 'View Quiz',
            'search_items'       => 'Search Quizzes',
            'not_found'          => 'No quizzes found',
            'not_found_in_trash' => 'No quizzes found in trash',
        ),
        'public'            => true,
        'has_archive'       => true,
        'show_in_rest'      => true,
        'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
        'menu_icon'         => 'dashicons-games',
        'rewrite'           => array( 'slug' => 'quizzes' ),
        'show_ui'           => true,
        'show_in_menu'      => true,
        'capability_type'   => 'post',
    ));

    // ----------------------------------
    // CPT 2: Quiz Question
    // ----------------------------------
    register_post_type( 'quiz_question', array(
        'labels' => array(
            'name'               => 'Quiz Questions',
            'singular_name'      => 'Quiz Question',
            'add_new'            => 'Add New Question',
            'add_new_item'       => 'Add New Question',
            'edit_item'          => 'Edit Question',
            'new_item'           => 'New Question',
            'view_item'          => 'View Question',
            'search_items'       => 'Search Questions',
            'not_found'          => 'No questions found',
            'not_found_in_trash' => 'No questions found in trash',
        ),
        'public'            => true,
        'has_archive'       => false,
        'show_in_rest'      => true,
        'supports'          => array( 'title', 'editor' ),
        'menu_icon'         => 'dashicons-list-view',
        'rewrite'           => array( 'slug' => 'quiz-questions' ),
        'show_ui'           => true,
        'show_in_menu'      => true,
        'capability_type'   => 'post',
    ));

    // ----------------------------------
    // CPT 3: Leaderboard Entry
    // ----------------------------------
    register_post_type( 'leaderboard_entry', array(
        'labels' => array(
            'name'               => 'Leaderboard Entries',
            'singular_name'      => 'Leaderboard Entry',
            'add_new'            => 'Add New Entry',
            'add_new_item'       => 'Add New Entry',
            'edit_item'          => 'Edit Entry',
            'new_item'           => 'New Entry',
            'view_item'          => 'View Entry',
            'search_items'       => 'Search Entries',
            'not_found'          => 'No entries found',
            'not_found_in_trash' => 'No entries found in trash',
        ),
        'public'            => false,
        'has_archive'       => false,
        'show_in_rest'      => false,
        'supports'          => array( 'title' ),
        'menu_icon'         => 'dashicons-awards',
        'rewrite'           => array( 'slug' => 'leaderboard-entries' ),
        'show_ui'           => true,
        'show_in_menu'      => true,
        'capability_type'   => 'post',
    ));

    // ----------------------------------
    // CPT 4: Blog Insight
    // ----------------------------------
    register_post_type( 'blog_insight', array(
        'labels' => array(
            'name'               => 'Blog Insights',
            'singular_name'      => 'Blog Insight',
            'add_new'            => 'Add New Insight',
            'add_new_item'       => 'Add New Insight',
            'edit_item'          => 'Edit Insight',
            'new_item'           => 'New Insight',
            'view_item'          => 'View Insight',
            'search_items'       => 'Search Insights',
            'not_found'          => 'No insights found',
            'not_found_in_trash' => 'No insights found in trash',
        ),
        'public'            => true,
        'has_archive'       => true,
        'show_in_rest'      => true,
        'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
        'menu_icon'         => 'dashicons-welcome-write-blog',
        'rewrite'           => array( 'slug' => 'insights' ),
        'show_ui'           => true,
        'show_in_menu'      => true,
        'capability_type'   => 'post',
    ));
}
add_action( 'init', 'gamer_register_post_types' );


// ============================================================
// 4. REGISTER CUSTOM TAXONOMIES
// ============================================================
function gamer_register_taxonomies() {

    // Genre Taxonomy (for Quizzes)
    register_taxonomy( 'quiz_genre', array( 'quiz' ), array(
        'labels' => array(
            'name'              => 'Genres',
            'singular_name'     => 'Genre',
            'search_items'      => 'Search Genres',
            'all_items'         => 'All Genres',
            'edit_item'         => 'Edit Genre',
            'update_item'       => 'Update Genre',
            'add_new_item'      => 'Add New Genre',
            'new_item_name'     => 'New Genre Name',
            'menu_name'         => 'Genres',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'genre' ),
    ));

    // Difficulty Taxonomy (for Quizzes)
    register_taxonomy( 'quiz_difficulty', array( 'quiz' ), array(
        'labels' => array(
            'name'              => 'Difficulty Levels',
            'singular_name'     => 'Difficulty',
            'search_items'      => 'Search Difficulties',
            'all_items'         => 'All Difficulties',
            'edit_item'         => 'Edit Difficulty',
            'update_item'       => 'Update Difficulty',
            'add_new_item'      => 'Add New Difficulty',
            'new_item_name'     => 'New Difficulty Name',
            'menu_name'         => 'Difficulty',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'difficulty' ),
    ));

    // Insight Category Taxonomy (for Blog Insights)
    register_taxonomy( 'insight_category', array( 'blog_insight' ), array(
        'labels' => array(
            'name'              => 'Insight Categories',
            'singular_name'     => 'Insight Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
            'menu_name'         => 'Categories',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'insight-category' ),
    ));
}
add_action( 'init', 'gamer_register_taxonomies' );


// ============================================================
// 5. REGISTER CUSTOM META BOXES (Custom Fields - Pure PHP)
// ============================================================
function gamer_register_meta_boxes() {

    // Quiz Details Meta Box
    add_meta_box(
        'quiz_details',
        'Quiz Details',
        'gamer_quiz_details_callback',
        'quiz',
        'normal',
        'high'
    );

    // Quiz Question Details Meta Box
    add_meta_box(
        'question_details',
        'Question Details',
        'gamer_question_details_callback',
        'quiz_question',
        'normal',
        'high'
    );

    // Leaderboard Entry Details Meta Box
    add_meta_box(
        'leaderboard_details',
        'Leaderboard Entry Details',
        'gamer_leaderboard_details_callback',
        'leaderboard_entry',
        'normal',
        'high'
    );

    // Blog Insight Details Meta Box
    add_meta_box(
        'insight_details',
        'Insight Details',
        'gamer_insight_details_callback',
        'blog_insight',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'gamer_register_meta_boxes' );


// ----------------------------------
// Quiz Details Fields
// ----------------------------------
function gamer_quiz_details_callback( $post ) {
    wp_nonce_field( 'gamer_save_quiz_details', 'gamer_quiz_nonce' );
    $time_limit     = get_post_meta( $post->ID, '_quiz_time_limit', true );
    $total_questions = get_post_meta( $post->ID, '_quiz_total_questions', true );
    $passing_score  = get_post_meta( $post->ID, '_quiz_passing_score', true );
    ?>
    <table class="form-table">
        <tr>
            <th><label for="quiz_time_limit">Time Limit (seconds)</label></th>
            <td>
                <input type="number" id="quiz_time_limit" name="quiz_time_limit"
                    value="<?php echo esc_attr( $time_limit ); ?>"
                    placeholder="e.g. 300 for 5 minutes" style="width:100%;" />
            </td>
        </tr>
        <tr>
            <th><label for="quiz_total_questions">Total Questions</label></th>
            <td>
                <input type="number" id="quiz_total_questions" name="quiz_total_questions"
                    value="<?php echo esc_attr( $total_questions ); ?>"
                    placeholder="e.g. 10" style="width:100%;" />
            </td>
        </tr>
        <tr>
            <th><label for="quiz_passing_score">Passing Score (%)</label></th>
            <td>
                <input type="number" id="quiz_passing_score" name="quiz_passing_score"
                    value="<?php echo esc_attr( $passing_score ); ?>"
                    placeholder="e.g. 70" style="width:100%;" />
            </td>
        </tr>
    </table>
    <?php
}


// ----------------------------------
// Quiz Question Details Fields
// ----------------------------------
function gamer_question_details_callback( $post ) {
    wp_nonce_field( 'gamer_save_question_details', 'gamer_question_nonce' );
    $related_quiz  = get_post_meta( $post->ID, '_question_quiz_id', true );
    $option_a      = get_post_meta( $post->ID, '_question_option_a', true );
    $option_b      = get_post_meta( $post->ID, '_question_option_b', true );
    $option_c      = get_post_meta( $post->ID, '_question_option_c', true );
    $option_d      = get_post_meta( $post->ID, '_question_option_d', true );
    $correct       = get_post_meta( $post->ID, '_question_correct_answer', true );

    // Get all quizzes for the dropdown
    $quizzes = get_posts( array( 'post_type' => 'quiz', 'numberposts' => -1 ) );
    ?>
    <table class="form-table">
        <tr>
            <th><label for="question_quiz_id">Belongs to Quiz</label></th>
            <td>
                <select id="question_quiz_id" name="question_quiz_id" style="width:100%;">
                    <option value="">-- Select Quiz --</option>
                    <?php foreach ( $quizzes as $quiz ) : ?>
                        <option value="<?php echo $quiz->ID; ?>"
                            <?php selected( $related_quiz, $quiz->ID ); ?>>
                            <?php echo esc_html( $quiz->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="question_option_a">Option A</label></th>
            <td><input type="text" id="question_option_a" name="question_option_a"
                value="<?php echo esc_attr( $option_a ); ?>" style="width:100%;" /></td>
        </tr>
        <tr>
            <th><label for="question_option_b">Option B</label></th>
            <td><input type="text" id="question_option_b" name="question_option_b"
                value="<?php echo esc_attr( $option_b ); ?>" style="width:100%;" /></td>
        </tr>
        <tr>
            <th><label for="question_option_c">Option C</label></th>
            <td><input type="text" id="question_option_c" name="question_option_c"
                value="<?php echo esc_attr( $option_c ); ?>" style="width:100%;" /></td>
        </tr>
        <tr>
            <th><label for="question_option_d">Option D</label></th>
            <td><input type="text" id="question_option_d" name="question_option_d"
                value="<?php echo esc_attr( $option_d ); ?>" style="width:100%;" /></td>
        </tr>
        <tr>
            <th><label for="question_correct_answer">Correct Answer</label></th>
            <td>
                <select id="question_correct_answer" name="question_correct_answer" style="width:100%;">
                    <option value="">-- Select Correct Answer --</option>
                    <option value="a" <?php selected( $correct, 'a' ); ?>>Option A</option>
                    <option value="b" <?php selected( $correct, 'b' ); ?>>Option B</option>
                    <option value="c" <?php selected( $correct, 'c' ); ?>>Option C</option>
                    <option value="d" <?php selected( $correct, 'd' ); ?>>Option D</option>
                </select>
            </td>
        </tr>
    </table>
    <?php
}


// ----------------------------------
// Leaderboard Entry Details Fields
// ----------------------------------
function gamer_leaderboard_details_callback( $post ) {
    wp_nonce_field( 'gamer_save_leaderboard_details', 'gamer_leaderboard_nonce' );
    $user_id    = get_post_meta( $post->ID, '_leaderboard_user_id', true );
    $quiz_id    = get_post_meta( $post->ID, '_leaderboard_quiz_id', true );
    $score      = get_post_meta( $post->ID, '_leaderboard_score', true );
    $total      = get_post_meta( $post->ID, '_leaderboard_total', true );
    $percentage = get_post_meta( $post->ID, '_leaderboard_percentage', true );
    $time_taken = get_post_meta( $post->ID, '_leaderboard_time_taken', true );

    $quizzes = get_posts( array( 'post_type' => 'quiz', 'numberposts' => -1 ) );
    $users   = get_users();
    ?>
    <table class="form-table">
        <tr>
            <th><label for="leaderboard_user_id">User</label></th>
            <td>
                <select id="leaderboard_user_id" name="leaderboard_user_id" style="width:100%;">
                    <option value="">-- Select User --</option>
                    <?php foreach ( $users as $user ) : ?>
                        <option value="<?php echo $user->ID; ?>"
                            <?php selected( $user_id, $user->ID ); ?>>
                            <?php echo esc_html( $user->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="leaderboard_quiz_id">Quiz</label></th>
            <td>
                <select id="leaderboard_quiz_id" name="leaderboard_quiz_id" style="width:100%;">
                    <option value="">-- Select Quiz --</option>
                    <?php foreach ( $quizzes as $quiz ) : ?>
                        <option value="<?php echo $quiz->ID; ?>"
                            <?php selected( $quiz_id, $quiz->ID ); ?>>
                            <?php echo esc_html( $quiz->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="leaderboard_score">Score (correct answers)</label></th>
            <td><input type="number" id="leaderboard_score" name="leaderboard_score"
                value="<?php echo esc_attr( $score ); ?>" style="width:100%;" /></td>
        </tr>
        <tr>
            <th><label for="leaderboard_total">Total Questions</label></th>
            <td><input type="number" id="leaderboard_total" name="leaderboard_total"
                value="<?php echo esc_attr( $total ); ?>" style="width:100%;" /></td>
        </tr>
        <tr>
            <th><label for="leaderboard_percentage">Percentage (%)</label></th>
            <td><input type="number" id="leaderboard_percentage" name="leaderboard_percentage"
                value="<?php echo esc_attr( $percentage ); ?>" style="width:100%;" /></td>
        </tr>
        <tr>
            <th><label for="leaderboard_time_taken">Time Taken (seconds)</label></th>
            <td><input type="number" id="leaderboard_time_taken" name="leaderboard_time_taken"
                value="<?php echo esc_attr( $time_taken ); ?>" style="width:100%;" /></td>
        </tr>
    </table>
    <?php
}


// ----------------------------------
// Blog Insight Details Fields
// ----------------------------------
function gamer_insight_details_callback( $post ) {
    wp_nonce_field( 'gamer_save_insight_details', 'gamer_insight_nonce' );
    $related_quiz = get_post_meta( $post->ID, '_insight_related_quiz', true );
    $read_time    = get_post_meta( $post->ID, '_insight_read_time', true );
    $quizzes      = get_posts( array( 'post_type' => 'quiz', 'numberposts' => -1 ) );
    ?>
    <table class="form-table">
        <tr>
            <th><label for="insight_related_quiz">Related Quiz</label></th>
            <td>
                <select id="insight_related_quiz" name="insight_related_quiz" style="width:100%;">
                    <option value="">-- Select Related Quiz --</option>
                    <?php foreach ( $quizzes as $quiz ) : ?>
                        <option value="<?php echo $quiz->ID; ?>"
                            <?php selected( $related_quiz, $quiz->ID ); ?>>
                            <?php echo esc_html( $quiz->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="insight_read_time">Estimated Read Time (minutes)</label></th>
            <td><input type="number" id="insight_read_time" name="insight_read_time"
                value="<?php echo esc_attr( $read_time ); ?>" style="width:100%;" /></td>
        </tr>
    </table>
    <?php
}


// ============================================================
// 6. SAVE ALL META BOX DATA
// ============================================================
function gamer_save_meta_boxes( $post_id ) {

    // ---- Save Quiz Details ----
    if ( isset( $_POST['gamer_quiz_nonce'] ) &&
        wp_verify_nonce( $_POST['gamer_quiz_nonce'], 'gamer_save_quiz_details' ) ) {

        if ( isset( $_POST['quiz_time_limit'] ) )
            update_post_meta( $post_id, '_quiz_time_limit', sanitize_text_field( $_POST['quiz_time_limit'] ) );

        if ( isset( $_POST['quiz_total_questions'] ) )
            update_post_meta( $post_id, '_quiz_total_questions', sanitize_text_field( $_POST['quiz_total_questions'] ) );

        if ( isset( $_POST['quiz_passing_score'] ) )
            update_post_meta( $post_id, '_quiz_passing_score', sanitize_text_field( $_POST['quiz_passing_score'] ) );
    }

    // ---- Save Question Details ----
    if ( isset( $_POST['gamer_question_nonce'] ) &&
        wp_verify_nonce( $_POST['gamer_question_nonce'], 'gamer_save_question_details' ) ) {

        if ( isset( $_POST['question_quiz_id'] ) )
            update_post_meta( $post_id, '_question_quiz_id', sanitize_text_field( $_POST['question_quiz_id'] ) );

        if ( isset( $_POST['question_option_a'] ) )
            update_post_meta( $post_id, '_question_option_a', sanitize_text_field( $_POST['question_option_a'] ) );

        if ( isset( $_POST['question_option_b'] ) )
            update_post_meta( $post_id, '_question_option_b', sanitize_text_field( $_POST['question_option_b'] ) );

        if ( isset( $_POST['question_option_c'] ) )
            update_post_meta( $post_id, '_question_option_c', sanitize_text_field( $_POST['question_option_c'] ) );

        if ( isset( $_POST['question_option_d'] ) )
            update_post_meta( $post_id, '_question_option_d', sanitize_text_field( $_POST['question_option_d'] ) );

        if ( isset( $_POST['question_correct_answer'] ) )
            update_post_meta( $post_id, '_question_correct_answer', sanitize_text_field( $_POST['question_correct_answer'] ) );
    }

    // ---- Save Leaderboard Entry Details ----
    if ( isset( $_POST['gamer_leaderboard_nonce'] ) &&
        wp_verify_nonce( $_POST['gamer_leaderboard_nonce'], 'gamer_save_leaderboard_details' ) ) {

        if ( isset( $_POST['leaderboard_user_id'] ) )
            update_post_meta( $post_id, '_leaderboard_user_id', sanitize_text_field( $_POST['leaderboard_user_id'] ) );

        if ( isset( $_POST['leaderboard_quiz_id'] ) )
            update_post_meta( $post_id, '_leaderboard_quiz_id', sanitize_text_field( $_POST['leaderboard_quiz_id'] ) );

        if ( isset( $_POST['leaderboard_score'] ) )
            update_post_meta( $post_id, '_leaderboard_score', sanitize_text_field( $_POST['leaderboard_score'] ) );

        if ( isset( $_POST['leaderboard_total'] ) )
            update_post_meta( $post_id, '_leaderboard_total', sanitize_text_field( $_POST['leaderboard_total'] ) );

        if ( isset( $_POST['leaderboard_percentage'] ) )
            update_post_meta( $post_id, '_leaderboard_percentage', sanitize_text_field( $_POST['leaderboard_percentage'] ) );

        if ( isset( $_POST['leaderboard_time_taken'] ) )
            update_post_meta( $post_id, '_leaderboard_time_taken', sanitize_text_field( $_POST['leaderboard_time_taken'] ) );
    }

    // ---- Save Blog Insight Details ----
    if ( isset( $_POST['gamer_insight_nonce'] ) &&
        wp_verify_nonce( $_POST['gamer_insight_nonce'], 'gamer_save_insight_details' ) ) {

        if ( isset( $_POST['insight_related_quiz'] ) )
            update_post_meta( $post_id, '_insight_related_quiz', sanitize_text_field( $_POST['insight_related_quiz'] ) );

        if ( isset( $_POST['insight_read_time'] ) )
            update_post_meta( $post_id, '_insight_read_time', sanitize_text_field( $_POST['insight_read_time'] ) );
    }
}
add_action( 'save_post', 'gamer_save_meta_boxes' );


// ============================================================
// 7. REGISTER CUSTOM USER ROLES
// ============================================================
function gamer_register_user_roles() {

    // Remove roles first to avoid duplication on repeated loads
    remove_role( 'quiz_master' );
    remove_role( 'player' );

    // Role 1: Quiz Master - can create and manage quizzes
    add_role( 'quiz_master', 'Quiz Master', array(
        'read'                   => true,
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'publish_posts'          => true,
        'delete_posts'           => true,
        'delete_published_posts' => true,
        'upload_files'           => true,
        'edit_others_posts'      => false,
        'delete_others_posts'    => false,
        'manage_categories'      => true,
    ));

    // Role 2: Player - can take quizzes, comment, view leaderboard
    add_role( 'player', 'Player', array(
        'read'          => true,
        'edit_posts'    => false,
        'delete_posts'  => false,
        'publish_posts' => false,
        'upload_files'  => false,
    ));
}
add_action( 'init', 'gamer_register_user_roles' );


// ============================================================
// 8. FLUSH REWRITE RULES ON THEME SWITCH
// ============================================================
function gamer_flush_rewrite_rules() {
    gamer_register_post_types();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'gamer_flush_rewrite_rules' );
