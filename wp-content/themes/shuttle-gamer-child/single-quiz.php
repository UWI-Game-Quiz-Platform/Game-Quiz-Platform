<?php
/**
 * Single Quiz Template
 * Handles both quiz info display AND the interactive quiz engine
 */

// ============================================================
// HANDLE QUIZ SUBMISSION (POST request)
// ============================================================
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['quiz_submit'] ) ) {

    if ( ! wp_verify_nonce( $_POST['quiz_nonce'], 'take_quiz_' . $_POST['quiz_id'] ) ) {
        wp_die( 'Security check failed.' );
    }

    $quiz_id    = intval( $_POST['quiz_id'] );
    $user_id    = get_current_user_id();
    $answers    = isset( $_POST['answers'] ) ? $_POST['answers'] : array();
    $time_taken = isset( $_POST['time_taken'] ) ? intval( $_POST['time_taken'] ) : 0;

    // Get all questions for this quiz
    $questions = get_posts( array(
        'post_type'      => 'quiz_question',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_question_quiz_id',
                'value'   => $quiz_id,
                'compare' => '='
            )
        )
    ));

    $total_questions = count( $questions );
    $correct_count   = 0;
    $results         = array();

    foreach ( $questions as $question ) {
        $correct_answer = get_post_meta( $question->ID, '_question_correct_answer', true );
        $user_answer    = isset( $answers[ $question->ID ] ) ? sanitize_text_field( $answers[ $question->ID ] ) : '';
        $is_correct     = ( $user_answer === $correct_answer );

        if ( $is_correct ) $correct_count++;

        $results[ $question->ID ] = array(
            'user_answer'    => $user_answer,
            'correct_answer' => $correct_answer,
            'is_correct'     => $is_correct,
            'option_a'       => get_post_meta( $question->ID, '_question_option_a', true ),
            'option_b'       => get_post_meta( $question->ID, '_question_option_b', true ),
            'option_c'       => get_post_meta( $question->ID, '_question_option_c', true ),
            'option_d'       => get_post_meta( $question->ID, '_question_option_d', true ),
        );
    }

    $percentage    = $total_questions > 0 ? round( ( $correct_count / $total_questions ) * 100 ) : 0;
    $passing_score = get_post_meta( $quiz_id, '_quiz_passing_score', true );
    $passed        = $percentage >= intval( $passing_score );

    // Save leaderboard entry
    $entry_id = wp_insert_post( array(
        'post_type'   => 'leaderboard_entry',
        'post_title'  => get_the_title( $quiz_id ) . ' - ' . wp_get_current_user()->display_name . ' - ' . date('Y-m-d H:i:s'),
        'post_status' => 'publish',
    ));

    if ( $entry_id ) {
        update_post_meta( $entry_id, '_leaderboard_user_id',    $user_id );
        update_post_meta( $entry_id, '_leaderboard_quiz_id',    $quiz_id );
        update_post_meta( $entry_id, '_leaderboard_score',      $correct_count );
        update_post_meta( $entry_id, '_leaderboard_total',      $total_questions );
        update_post_meta( $entry_id, '_leaderboard_percentage', $percentage );
        update_post_meta( $entry_id, '_leaderboard_time_taken', $time_taken );
    }

    // ---- Show Results Page ----
    get_header();
    ?>

    <div id="intro">
        <div class="container">
            <h1 class="page-title">Quiz Results</h1>
        </div>
    </div>

    <div id="content">
        <div class="container" style="padding:60px 0; max-width:800px; margin:0 auto;">

            <!-- Score Card -->
            <div style="
                background:#212529;
                border:1px solid rgba(255,255,255,0.15);
                border-radius:15px;
                padding:40px;
                text-align:center;
                margin-bottom:40px;">

                <div style="font-size:80px; margin-bottom:20px;">
                    <?php echo $passed ? '🏆' : '😔'; ?>
                </div>

                <div style="
                    display:inline-block;
                    background:<?php echo $passed ? '#28a745' : '#dc3545'; ?>;
                    color:#fff;
                    padding:8px 30px;
                    border-radius:30px;
                    font-size:18px;
                    font-weight:700;
                    margin-bottom:25px;">
                    <?php echo $passed ? '✅ PASSED!' : '❌ FAILED'; ?>
                </div>

                <div style="
                    font-size:72px;
                    font-weight:900;
                    color:#13aff0;
                    line-height:1;
                    margin-bottom:10px;">
                    <?php echo $percentage; ?>%
                </div>

                <div style="color:rgba(255,255,255,0.6); font-size:16px; margin-bottom:30px;">
                    You got <?php echo $correct_count; ?> out of <?php echo $total_questions; ?> questions correct
                </div>

                <!-- Stats -->
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:30px;">
                    <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:15px;">
                        <div style="color:#28a745; font-size:24px; font-weight:700;"><?php echo $correct_count; ?></div>
                        <div style="color:rgba(255,255,255,0.5); font-size:12px;">Correct</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:15px;">
                        <div style="color:#dc3545; font-size:24px; font-weight:700;"><?php echo $total_questions - $correct_count; ?></div>
                        <div style="color:rgba(255,255,255,0.5); font-size:12px;">Wrong</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:15px;">
                        <div style="color:#ffc107; font-size:24px; font-weight:700;">
                            <?php
                            $mins = floor( $time_taken / 60 );
                            $secs = $time_taken % 60;
                            echo $mins . 'm ' . $secs . 's';
                            ?>
                        </div>
                        <div style="color:rgba(255,255,255,0.5); font-size:12px;">Time Taken</div>
                    </div>
                </div>

                <!-- Buttons -->
                <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                    <a href="<?php echo get_permalink( $quiz_id ); ?>" style="
                        background:#13aff0;
                        color:#fff;
                        padding:12px 25px;
                        border-radius:5px;
                        text-decoration:none;
                        font-weight:600;">
                        🔄 Try Again
                    </a>
                    <a href="<?php echo home_url('/quizzes'); ?>" style="
                        background:rgba(255,255,255,0.1);
                        color:#fff;
                        padding:12px 25px;
                        border-radius:5px;
                        text-decoration:none;
                        font-weight:600;">
                        🎮 More Quizzes
                    </a>
                </div>
            </div>

            <!-- Question Review -->
            <h2 style="color:#fff; font-size:22px; font-weight:700; margin-bottom:25px;">
                📝 Question Review
            </h2>

            <?php
            $q_num = 1;
            foreach ( $questions as $question ) :
                $result  = $results[ $question->ID ];
                $options = array(
                    'a' => $result['option_a'],
                    'b' => $result['option_b'],
                    'c' => $result['option_c'],
                    'd' => $result['option_d'],
                );
            ?>
                <div style="
                    background:#212529;
                    border:1px solid <?php echo $result['is_correct'] ? '#28a745' : '#dc3545'; ?>;
                    border-radius:10px;
                    padding:25px;
                    margin-bottom:20px;">

                    <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:15px;">
                        <span style="
                            background:<?php echo $result['is_correct'] ? '#28a745' : '#dc3545'; ?>;
                            color:#fff;
                            width:28px; height:28px;
                            border-radius:50%;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            font-size:12px;
                            font-weight:700;
                            flex-shrink:0;">
                            <?php echo $result['is_correct'] ? '✓' : '✗'; ?>
                        </span>
                        <h4 style="color:#fff; margin:0; font-size:15px; font-weight:600;">
                            Q<?php echo $q_num; ?>: <?php echo esc_html( $question->post_title ); ?>
                        </h4>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; padding-left:40px;">
                        <?php foreach ( $options as $key => $option_text ) :
                            if ( empty( $option_text ) ) continue;
                            $is_correct_option = ( $key === $result['correct_answer'] );
                            $is_user_choice    = ( $key === $result['user_answer'] );

                            $bg     = 'rgba(255,255,255,0.05)';
                            $border = 'rgba(255,255,255,0.1)';
                            $color  = 'rgba(255,255,255,0.7)';

                            if ( $is_correct_option ) {
                                $bg     = 'rgba(40,167,69,0.2)';
                                $border = '#28a745';
                                $color  = '#28a745';
                            } elseif ( $is_user_choice && ! $is_correct_option ) {
                                $bg     = 'rgba(220,53,69,0.2)';
                                $border = '#dc3545';
                                $color  = '#dc3545';
                            }
                        ?>
                            <div style="
                                background:<?php echo $bg; ?>;
                                border:1px solid <?php echo $border; ?>;
                                border-radius:5px;
                                padding:10px 12px;
                                display:flex;
                                align-items:center;
                                gap:8px;">
                                <span style="color:<?php echo $color; ?>; font-weight:700; font-size:13px; text-transform:uppercase;">
                                    <?php echo $key; ?>
                                </span>
                                <span style="color:rgba(255,255,255,0.8); font-size:13px;">
                                    <?php echo esc_html( $option_text ); ?>
                                </span>
                                <?php if ( $is_correct_option ) echo '<span style="margin-left:auto;">✅</span>'; ?>
                                <?php if ( $is_user_choice && ! $is_correct_option ) echo '<span style="margin-left:auto;">❌</span>'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php $q_num++; endforeach; ?>

        </div>
    </div>

    <?php
    get_footer();
    exit;
}


// ============================================================
// SHOW QUIZ ENGINE (when ?take=1 in URL)
// ============================================================
if ( isset( $_GET['take'] ) && $_GET['take'] == '1' ) {

    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url( get_permalink() ) );
        exit;
    }

    // Get quiz data
    $quiz_id         = get_queried_object_id();
    $time_limit      = get_post_meta( $quiz_id, '_quiz_time_limit', true );

    // Get questions
    $questions = get_posts( array(
        'post_type'      => 'quiz_question',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_question_quiz_id',
                'value'   => $quiz_id,
                'compare' => '='
            )
        )
    ));

    get_header();
    ?>

    <div id="intro">
        <div class="container">
            <h1 class="page-title"><?php echo get_the_title( $quiz_id ); ?></h1>
        </div>
    </div>

    <div id="content">
        <div class="container" style="padding:60px 0; max-width:800px; margin:0 auto;">

            <?php if ( empty( $questions ) ) : ?>
                <div style="text-align:center; padding:60px; color:#fff;">
                    <h2>No questions found for this quiz yet.</h2>
                    <a href="<?php echo get_permalink( $quiz_id ); ?>" style="color:#13aff0;">Go Back</a>
                </div>
            <?php else : ?>

            <!-- Timer Bar -->
            <?php if ( $time_limit ) : ?>
            <div style="
                background:#212529;
                border:1px solid rgba(255,255,255,0.15);
                border-radius:10px;
                padding:20px 25px;
                margin-bottom:20px;
                display:flex;
                align-items:center;
                justify-content:space-between;">
                <div>
                    <span style="color:rgba(255,255,255,0.6); font-size:13px;">Time Remaining</span>
                    <div id="timer-display" style="color:#13aff0; font-size:32px; font-weight:700; font-family:monospace;">
                        <?php
                        $mins = floor( $time_limit / 60 );
                        $secs = $time_limit % 60;
                        echo sprintf('%02d:%02d', $mins, $secs);
                        ?>
                    </div>
                </div>
                <div style="text-align:right;">
                    <span style="color:rgba(255,255,255,0.6); font-size:13px;">Progress</span>
                    <div id="progress-text" style="color:#fff; font-size:20px; font-weight:700;">
                        1 / <?php echo count( $questions ); ?>
                    </div>
                </div>
            </div>

            <!-- Timer Progress Bar -->
            <div style="background:rgba(255,255,255,0.1); border-radius:5px; height:6px; margin-bottom:30px; overflow:hidden;">
                <div id="timer-bar" style="background:#13aff0; height:100%; width:100%; transition:width 1s linear; border-radius:5px;"></div>
            </div>
            <?php endif; ?>

            <!-- Quiz Form -->
            <form method="POST" action="<?php echo get_permalink( $quiz_id ); ?>" id="quiz-form">
                <?php wp_nonce_field( 'take_quiz_' . $quiz_id, 'quiz_nonce' ); ?>
                <input type="hidden" name="quiz_submit" value="1" />
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>" />
                <input type="hidden" name="time_taken" id="time-taken-input" value="0" />

                <?php
                $q_num = 1;
                foreach ( $questions as $question ) :
                    $option_a = get_post_meta( $question->ID, '_question_option_a', true );
                    $option_b = get_post_meta( $question->ID, '_question_option_b', true );
                    $option_c = get_post_meta( $question->ID, '_question_option_c', true );
                    $option_d = get_post_meta( $question->ID, '_question_option_d', true );
                    $options  = array( 'a' => $option_a, 'b' => $option_b, 'c' => $option_c, 'd' => $option_d );
                ?>

                    <div class="question-card"
                        id="question-<?php echo $q_num; ?>"
                        style="
                            background:#212529;
                            border:1px solid rgba(255,255,255,0.15);
                            border-radius:10px;
                            padding:30px;
                            margin-bottom:20px;
                            display:<?php echo $q_num === 1 ? 'block' : 'none'; ?>;">

                        <div style="margin-bottom:25px;">
                            <span style="color:#13aff0; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                                Question <?php echo $q_num; ?> of <?php echo count( $questions ); ?>
                            </span>
                            <h3 style="color:#fff; font-size:18px; font-weight:600; margin-top:10px; margin-bottom:0; line-height:1.5; text-transform:none;">
                                <?php echo esc_html( $question->post_title ); ?>
                            </h3>
                        </div>

                        <div class="answer-options" style="display:grid; gap:12px;">
                            <?php foreach ( $options as $key => $option_text ) :
                                if ( empty( $option_text ) ) continue;
                            ?>
                                <label class="answer-option" style="
                                    display:flex;
                                    align-items:center;
                                    gap:15px;
                                    background:rgba(255,255,255,0.05);
                                    border:2px solid rgba(255,255,255,0.1);
                                    border-radius:8px;
                                    padding:15px 20px;
                                    cursor:pointer;
                                    transition:all 0.2s;">
                                    <input type="radio"
                                        name="answers[<?php echo $question->ID; ?>]"
                                        value="<?php echo esc_attr( $key ); ?>"
                                        style="display:none;"
                                        class="answer-radio" />
                                    <span style="
                                        width:32px; height:32px;
                                        border-radius:50%;
                                        background:rgba(255,255,255,0.1);
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        font-weight:700;
                                        color:#fff;
                                        font-size:13px;
                                        flex-shrink:0;
                                        text-transform:uppercase;">
                                        <?php echo $key; ?>
                                    </span>
                                    <span style="color:rgba(255,255,255,0.85); font-size:15px;">
                                        <?php echo esc_html( $option_text ); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Navigation -->
                        <div style="display:flex; justify-content:space-between; margin-top:25px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.1);">

                            <?php if ( $q_num > 1 ) : ?>
                                <button type="button" class="prev-btn" data-current="<?php echo $q_num; ?>" style="
                                    background:rgba(255,255,255,0.1);
                                    color:#fff; border:none;
                                    border-radius:5px;
                                    padding:10px 20px;
                                    cursor:pointer;
                                    font-weight:600;">
                                    ← Previous
                                </button>
                            <?php else : ?>
                                <div></div>
                            <?php endif; ?>

                            <?php if ( $q_num < count( $questions ) ) : ?>
                                <button type="button" class="next-btn" data-current="<?php echo $q_num; ?>" style="
                                    background:#13aff0;
                                    color:#fff;
                                    border:none;
                                    border-radius:5px;
                                    padding:10px 20px;
                                    cursor:pointer;
                                    font-weight:600;">
                                    Next →
                                </button>
                            <?php else : ?>
                                <button type="submit" style="
                                    background:#28a745;
                                    color:#fff;
                                    border:none;
                                    border-radius:5px;
                                    padding:10px 25px;
                                    cursor:pointer;
                                    font-weight:700;
                                    font-size:15px;">
                                    🏁 Submit Quiz
                                </button>
                            <?php endif; ?>

                        </div>
                    </div>

                <?php $q_num++; endforeach; ?>
            </form>

            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // ---- Timer ----
        <?php if ( $time_limit ) : ?>
        var totalTime = <?php echo intval( $time_limit ); ?>;
        var timeLeft  = totalTime;
        var timeTaken = document.getElementById('time-taken-input');
        var timerBar  = document.getElementById('timer-bar');
        var timerDisp = document.getElementById('timer-display');

        var timerInterval = setInterval(function() {
            timeLeft--;
            timeTaken.value = totalTime - timeLeft;

            var mins = Math.floor(timeLeft / 60);
            var secs = timeLeft % 60;
            timerDisp.textContent = String(mins).padStart(2,'0') + ':' + String(secs).padStart(2,'0');

            var pct = (timeLeft / totalTime) * 100;
            timerBar.style.width = pct + '%';

            if ( timeLeft <= 60 )  timerBar.style.background = '#dc3545';
            else if ( timeLeft <= 120 ) timerBar.style.background = '#ffc107';

            if ( timeLeft <= 0 ) {
                clearInterval(timerInterval);
                document.getElementById('quiz-form').submit();
            }
        }, 1000);
        <?php endif; ?>

        // ---- Answer Highlighting ----
        document.querySelectorAll('.answer-option').forEach(function(label) {
            label.addEventListener('click', function() {
                var card = this.closest('.question-card');
                card.querySelectorAll('.answer-option').forEach(function(l) {
                    l.style.borderColor = 'rgba(255,255,255,0.1)';
                    l.style.background  = 'rgba(255,255,255,0.05)';
                });
                this.style.borderColor = '#13aff0';
                this.style.background  = 'rgba(19,175,240,0.15)';
                this.querySelector('.answer-radio').checked = true;
            });
        });

        // ---- Next Button ----
        document.querySelectorAll('.next-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var current = parseInt(this.getAttribute('data-current'));
                document.getElementById('question-' + current).style.display = 'none';
                document.getElementById('question-' + (current + 1)).style.display = 'block';
                document.getElementById('progress-text').textContent = (current + 1) + ' / <?php echo count($questions); ?>';
                window.scrollTo(0, 0);
            });
        });

        // ---- Previous Button ----
        document.querySelectorAll('.prev-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var current = parseInt(this.getAttribute('data-current'));
                document.getElementById('question-' + current).style.display = 'none';
                document.getElementById('question-' + (current - 1)).style.display = 'block';
                document.getElementById('progress-text').textContent = (current - 1) + ' / <?php echo count($questions); ?>';
                window.scrollTo(0, 0);
            });
        });

        // ---- Submit Confirmation ----
        document.getElementById('quiz-form').addEventListener('submit', function(e) {
            var unanswered = 0;
            document.querySelectorAll('.question-card').forEach(function(card) {
                if ( ! card.querySelector('input[type="radio"]:checked') ) unanswered++;
            });
            if ( unanswered > 0 ) {
                if ( ! confirm(unanswered + ' question(s) unanswered. Submit anyway?') ) {
                    e.preventDefault();
                }
            }
        });

    });
    </script>

    <?php
    get_footer();
    exit;
}


// ============================================================
// SHOW QUIZ INFO PAGE (default - no ?take=1)
// ============================================================
get_header(); ?>

<div id="intro">
    <div class="container">
        <h1 class="page-title"><?php the_title(); ?></h1>
        <?php if ( function_exists('shuttle_breadcrumbs') ) shuttle_breadcrumbs(); ?>
    </div>
</div>

<div id="content">
    <div class="container" style="padding:60px 0;">

        <?php while ( have_posts() ) : the_post();

            $time_limit      = get_post_meta( get_the_ID(), '_quiz_time_limit', true );
            $total_questions = get_post_meta( get_the_ID(), '_quiz_total_questions', true );
            $passing_score   = get_post_meta( get_the_ID(), '_quiz_passing_score', true );
            $genres          = get_the_terms( get_the_ID(), 'quiz_genre' );
            $difficulties    = get_the_terms( get_the_ID(), 'quiz_difficulty' );
            $difficulty_name = $difficulties ? $difficulties[0]->name : 'N/A';

            $diff_color = '#13aff0';
            if ( $difficulty_name == 'Easy' )   $diff_color = '#28a745';
            if ( $difficulty_name == 'Medium' ) $diff_color = '#ffc107';
            if ( $difficulty_name == 'Hard' )   $diff_color = '#dc3545';

            // Mini leaderboard query
            $leaderboard = new WP_Query( array(
                'post_type'      => 'leaderboard_entry',
                'posts_per_page' => 5,
                'meta_key'       => '_leaderboard_percentage',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
                'meta_query'     => array(
                    array(
                        'key'     => '_leaderboard_quiz_id',
                        'value'   => get_the_ID(),
                        'compare' => '='
                    )
                )
            ));
        ?>

        <div style="display:grid; grid-template-columns:2fr 1fr; gap:30px;">

            <!-- LEFT COLUMN -->
            <div>
                <div style="
                    background:#212529;
                    border:1px solid rgba(255,255,255,0.15);
                    border-radius:10px;
                    overflow:hidden;
                    margin-bottom:30px;">

                    <?php if ( has_post_thumbnail() ) : ?>
                        <div style="height:300px; overflow:hidden;">
                            <?php the_post_thumbnail('large', array('style'=>'width:100%;height:100%;object-fit:cover;')); ?>
                        </div>
                    <?php else : ?>
                        <div style="height:250px; background:linear-gradient(135deg,#1a1a2e,#16213e); display:flex; align-items:center; justify-content:center; font-size:80px;">🎮</div>
                    <?php endif; ?>

                    <div style="padding:30px;">

                        <!-- Badges -->
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:15px;">
                            <span style="background:<?php echo $diff_color; ?>; color:#fff; padding:4px 14px; border-radius:20px; font-size:13px; font-weight:600;">
                                <?php echo esc_html( $difficulty_name ); ?>
                            </span>
                            <?php if ( $genres ) foreach ( $genres as $genre ) : ?>
                                <span style="background:rgba(255,255,255,0.1); color:#fff; padding:4px 14px; border-radius:20px; font-size:13px;">
                                    <?php echo esc_html( $genre->name ); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Description -->
                        <div style="color:rgba(255,255,255,0.8); line-height:1.7; margin-bottom:25px;">
                            <?php the_content(); ?>
                        </div>

                        <!-- Stats -->
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:30px;">
                            <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:20px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#13aff0;">
                                    <?php echo $total_questions ? esc_html($total_questions) : '?'; ?>
                                </div>
                                <div style="color:rgba(255,255,255,0.5); font-size:13px; margin-top:5px;">Questions</div>
                            </div>
                            <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:20px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#13aff0;">
                                    <?php echo $time_limit ? floor($time_limit/60).'m' : '?'; ?>
                                </div>
                                <div style="color:rgba(255,255,255,0.5); font-size:13px; margin-top:5px;">Time Limit</div>
                            </div>
                            <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:20px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#13aff0;">
                                    <?php echo $passing_score ? esc_html($passing_score).'%' : '?'; ?>
                                </div>
                                <div style="color:rgba(255,255,255,0.5); font-size:13px; margin-top:5px;">Pass Score</div>
                            </div>
                        </div>

                        <!-- Start Button -->
                        <?php if ( is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'take', '1', get_permalink() ) ); ?>" style="
                                display:block;
                                text-align:center;
                                background:#13aff0;
                                color:#fff;
                                padding:15px;
                                border-radius:8px;
                                text-decoration:none;
                                font-weight:700;
                                font-size:16px;">
                                🎮 Start Quiz Now
                            </a>
                        <?php else : ?>
                            <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:20px; text-align:center;">
                                <p style="color:#fff; margin-bottom:15px;">You must be logged in to take this quiz.</p>
                                <a href="<?php echo wp_login_url( get_permalink() ); ?>" style="
                                    display:inline-block;
                                    background:#13aff0;
                                    color:#fff;
                                    padding:10px 25px;
                                    border-radius:5px;
                                    text-decoration:none;
                                    font-weight:600;">
                                    Login to Play
                                </a>
                                <a href="<?php echo wp_registration_url(); ?>" style="
                                    display:inline-block;
                                    background:rgba(255,255,255,0.1);
                                    color:#fff;
                                    padding:10px 25px;
                                    border-radius:5px;
                                    text-decoration:none;
                                    font-weight:600;
                                    margin-left:10px;">
                                    Register
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Comments -->
                <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:30px;">
                    <?php comments_template(); ?>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div>

                <!-- Mini Leaderboard -->
                <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:25px; margin-bottom:25px;">
                    <h3 style="color:#fff; font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.1);">
                        🏆 Top Players
                    </h3>

                    <?php if ( $leaderboard->have_posts() ) :
                        $rank = 1;
                        while ( $leaderboard->have_posts() ) : $leaderboard->the_post();
                            $lb_user_id  = get_post_meta( get_the_ID(), '_leaderboard_user_id', true );
                            $lb_score    = get_post_meta( get_the_ID(), '_leaderboard_score', true );
                            $lb_total    = get_post_meta( get_the_ID(), '_leaderboard_total', true );
                            $lb_pct      = get_post_meta( get_the_ID(), '_leaderboard_percentage', true );
                            $lb_user     = get_userdata( $lb_user_id );
                            $lb_username = $lb_user ? $lb_user->display_name : 'Unknown';
                            $medal = '';
                            if ( $rank == 1 ) $medal = '🥇';
                            if ( $rank == 2 ) $medal = '🥈';
                            if ( $rank == 3 ) $medal = '🥉';
                    ?>
                        <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.07);">
                            <span style="font-size:20px; width:28px;"><?php echo $medal ? $medal : '#'.$rank; ?></span>
                            <div style="flex:1;">
                                <div style="color:#fff; font-weight:600; font-size:14px;"><?php echo esc_html($lb_username); ?></div>
                                <div style="color:rgba(255,255,255,0.4); font-size:12px;"><?php echo $lb_score; ?>/<?php echo $lb_total; ?> correct</div>
                            </div>
                            <span style="color:#13aff0; font-weight:700; font-size:16px;"><?php echo $lb_pct; ?>%</span>
                        </div>
                    <?php $rank++; endwhile; wp_reset_postdata();
                    else : ?>
                        <p style="color:rgba(255,255,255,0.4); text-align:center; padding:20px 0;">No scores yet. Be the first!</p>
                    <?php endif; ?>
                </div>

                <!-- Quiz Rules -->
                <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:25px;">
                    <h3 style="color:#fff; font-size:16px; font-weight:700; margin-bottom:15px;">📋 Quiz Rules</h3>
                    <ul style="list-style:none; padding:0; margin:0;">
                        <?php
                        $rules = array(
                            '✅ Read each question carefully',
                            '⏱️ Timer starts when you begin',
                            '❌ No going back once submitted',
                            '🏆 Score is saved to leaderboard',
                            '📊 You need ' . ($passing_score ? $passing_score : '?') . '% to pass',
                        );
                        foreach ( $rules as $rule ) : ?>
                            <li style="color:rgba(255,255,255,0.7); font-size:14px; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.07);">
                                <?php echo $rule; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </div>

        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>
