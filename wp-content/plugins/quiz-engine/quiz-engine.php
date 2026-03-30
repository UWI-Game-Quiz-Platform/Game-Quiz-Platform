<?php
/**
 * Plugin Name: Quiz Engine
 * Description: Interactive quiz engine with timer, scoring, and leaderboard saving for the Games Quiz Platform.
 * Author: Ijaaz Sisarran
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Quiz_Engine {

    public function __construct() {
        add_shortcode( 'quiz_engine', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    public function register_assets() {
        wp_register_style(
            'quiz-engine-style',
            plugin_dir_url( __FILE__ ) . 'quiz-engine.css',
            array(),
            '1.0.0'
        );
    }

    // ============================================================
    // HANDLE QUIZ SUBMISSION
    // ============================================================
    private function handle_submission() {

        if ( ! isset( $_POST['quiz_submit'] ) ) {
            return null;
        }

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
                'question_text'  => $question->post_title,
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

        return array(
            'quiz_id'         => $quiz_id,
            'questions'       => $questions,
            'results'         => $results,
            'correct_count'   => $correct_count,
            'total_questions' => $total_questions,
            'percentage'      => $percentage,
            'passed'          => $passed,
            'time_taken'      => $time_taken,
            'passing_score'   => $passing_score,
        );
    }

    // ============================================================
    // RENDER RESULTS PAGE
    // ============================================================
    private function render_results( $data ) {
        wp_enqueue_style( 'quiz-engine-style' );
        ob_start();
        ?>
        <div class="qe-results-wrapper">

            <!-- Score Card -->
            <div class="qe-score-card">

                <div class="qe-result-icon">
                    <?php echo $data['passed'] ? '🏆' : '😔'; ?>
                </div>

                <div class="qe-pass-badge <?php echo $data['passed'] ? 'qe-passed' : 'qe-failed'; ?>">
                    <?php echo $data['passed'] ? '✅ PASSED!' : '❌ FAILED'; ?>
                </div>

                <div class="qe-percentage">
                    <?php echo $data['percentage']; ?>%
                </div>

                <div class="qe-score-subtitle">
                    You got <?php echo $data['correct_count']; ?> out of
                    <?php echo $data['total_questions']; ?> questions correct
                </div>

                <!-- Stats Row -->
                <div class="qe-stats-row">
                    <div class="qe-stat">
                        <div class="qe-stat-value qe-correct"><?php echo $data['correct_count']; ?></div>
                        <div class="qe-stat-label">Correct</div>
                    </div>
                    <div class="qe-stat">
                        <div class="qe-stat-value qe-wrong">
                            <?php echo $data['total_questions'] - $data['correct_count']; ?>
                        </div>
                        <div class="qe-stat-label">Wrong</div>
                    </div>
                    <div class="qe-stat">
                        <div class="qe-stat-value qe-time">
                            <?php
                            $mins = floor( $data['time_taken'] / 60 );
                            $secs = $data['time_taken'] % 60;
                            echo $mins . 'm ' . $secs . 's';
                            ?>
                        </div>
                        <div class="qe-stat-label">Time Taken</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="qe-action-buttons">
                    <a href="<?php echo get_permalink( $data['quiz_id'] ); ?>" class="qe-btn qe-btn-primary">
                        🔄 Try Again
                    </a>
                    <a href="<?php echo home_url('/quizzes'); ?>" class="qe-btn qe-btn-secondary">
                        🎮 More Quizzes
                    </a>
                </div>

            </div>

            <!-- Question Review -->
            <h2 class="qe-review-title">📝 Question Review</h2>

            <?php
            $q_num = 1;
            foreach ( $data['questions'] as $question ) :
                $result  = $data['results'][ $question->ID ];
                $options = array(
                    'a' => $result['option_a'],
                    'b' => $result['option_b'],
                    'c' => $result['option_c'],
                    'd' => $result['option_d'],
                );
            ?>
                <div class="qe-review-card <?php echo $result['is_correct'] ? 'qe-review-correct' : 'qe-review-wrong'; ?>">

                    <div class="qe-review-question-header">
                        <span class="qe-review-badge <?php echo $result['is_correct'] ? 'qe-badge-correct' : 'qe-badge-wrong'; ?>">
                            <?php echo $result['is_correct'] ? '✓' : '✗'; ?>
                        </span>
                        <h4 class="qe-review-question-text">
                            Q<?php echo $q_num; ?>: <?php echo esc_html( $question->post_title ); ?>
                        </h4>
                    </div>

                    <div class="qe-review-options">
                        <?php foreach ( $options as $key => $option_text ) :
                            if ( empty( $option_text ) ) continue;
                            $is_correct_option = ( $key === $result['correct_answer'] );
                            $is_user_choice    = ( $key === $result['user_answer'] );

                            $extra_class = '';
                            if ( $is_correct_option ) $extra_class = 'qe-option-correct';
                            elseif ( $is_user_choice && ! $is_correct_option ) $extra_class = 'qe-option-wrong';
                        ?>
                            <div class="qe-review-option <?php echo $extra_class; ?>">
                                <span class="qe-option-key"><?php echo strtoupper($key); ?></span>
                                <span class="qe-option-text"><?php echo esc_html( $option_text ); ?></span>
                                <?php if ( $is_correct_option ) echo '<span class="qe-option-icon">✅</span>'; ?>
                                <?php if ( $is_user_choice && ! $is_correct_option ) echo '<span class="qe-option-icon">❌</span>'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php $q_num++; endforeach; ?>

        </div>
        <?php
        return ob_get_clean();
    }

    // ============================================================
    // RENDER QUIZ ENGINE (Questions Form)
    // ============================================================
    private function render_quiz( $quiz_id ) {
        wp_enqueue_style( 'quiz-engine-style' );

        $time_limit = get_post_meta( $quiz_id, '_quiz_time_limit', true );

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

        if ( empty( $questions ) ) {
            return '<div class="qe-no-questions">
                <h3>No questions found for this quiz yet.</h3>
                <a href="' . get_permalink( $quiz_id ) . '">Go Back</a>
            </div>';
        }

        $total = count( $questions );

        ob_start();
        ?>
        <div class="qe-engine-wrapper">

            <!-- Timer + Progress Bar -->
            <?php if ( $time_limit ) : ?>
            <div class="qe-timer-bar">
                <div>
                    <span class="qe-timer-label">Time Remaining</span>
                    <div id="qe-timer-display" class="qe-timer-display">
                        <?php
                        $mins = floor( $time_limit / 60 );
                        $secs = $time_limit % 60;
                        echo sprintf('%02d:%02d', $mins, $secs);
                        ?>
                    </div>
                </div>
                <div class="qe-progress-right">
                    <span class="qe-timer-label">Progress</span>
                    <div id="qe-progress-text" class="qe-progress-text">
                        1 / <?php echo $total; ?>
                    </div>
                </div>
            </div>

            <div class="qe-progress-track">
                <div id="qe-timer-progress" class="qe-progress-fill"></div>
            </div>
            <?php endif; ?>

            <!-- Quiz Form -->
            <form method="POST" action="<?php echo get_permalink( $quiz_id ); ?>" id="qe-form">
                <?php wp_nonce_field( 'take_quiz_' . $quiz_id, 'quiz_nonce' ); ?>
                <input type="hidden" name="quiz_submit" value="1" />
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>" />
                <input type="hidden" name="time_taken" id="qe-time-taken" value="0" />

                <?php
                $q_num = 1;
                foreach ( $questions as $question ) :
                    $options = array(
                        'a' => get_post_meta( $question->ID, '_question_option_a', true ),
                        'b' => get_post_meta( $question->ID, '_question_option_b', true ),
                        'c' => get_post_meta( $question->ID, '_question_option_c', true ),
                        'd' => get_post_meta( $question->ID, '_question_option_d', true ),
                    );
                ?>
                    <div class="qe-question-card"
                        id="qe-question-<?php echo $q_num; ?>"
                        style="display:<?php echo $q_num === 1 ? 'block' : 'none'; ?>">

                        <div class="qe-question-header">
                            <span class="qe-question-counter">
                                Question <?php echo $q_num; ?> of <?php echo $total; ?>
                            </span>
                            <h3 class="qe-question-text">
                                <?php echo esc_html( $question->post_title ); ?>
                            </h3>
                        </div>

                        <div class="qe-options-grid">
                            <?php foreach ( $options as $key => $option_text ) :
                                if ( empty( $option_text ) ) continue;
                            ?>
                                <label class="qe-option-label">
                                    <input type="radio"
                                        name="answers[<?php echo $question->ID; ?>]"
                                        value="<?php echo esc_attr( $key ); ?>"
                                        class="qe-radio"
                                        style="display:none;" />
                                    <span class="qe-option-letter">
                                        <?php echo strtoupper($key); ?>
                                    </span>
                                    <span class="qe-option-value">
                                        <?php echo esc_html( $option_text ); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Navigation -->
                        <div class="qe-nav-buttons">
                            <?php if ( $q_num > 1 ) : ?>
                                <button type="button" class="qe-prev qe-btn qe-btn-secondary"
                                    data-current="<?php echo $q_num; ?>">
                                    ← Previous
                                </button>
                            <?php else : ?>
                                <div></div>
                            <?php endif; ?>

                            <?php if ( $q_num < $total ) : ?>
                                <button type="button" class="qe-next qe-btn qe-btn-primary"
                                    data-current="<?php echo $q_num; ?>">
                                    Next →
                                </button>
                            <?php else : ?>
                                <button type="submit" class="qe-btn qe-btn-submit">
                                    🏁 Submit Quiz
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php $q_num++; endforeach; ?>
            </form>

        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var totalQ = <?php echo $total; ?>;

            // ---- Timer ----
            <?php if ( $time_limit ) : ?>
            var totalTime = <?php echo intval( $time_limit ); ?>;
            var timeLeft  = totalTime;
            var timeTaken = document.getElementById('qe-time-taken');
            var timerDisp = document.getElementById('qe-timer-display');
            var timerProg = document.getElementById('qe-timer-progress');

            timerProg.style.width = '100%';

            var timerInterval = setInterval(function() {
                timeLeft--;
                timeTaken.value = totalTime - timeLeft;

                var mins = Math.floor(timeLeft / 60);
                var secs = timeLeft % 60;
                timerDisp.textContent =
                    String(mins).padStart(2,'0') + ':' + String(secs).padStart(2,'0');

                var pct = (timeLeft / totalTime) * 100;
                timerProg.style.width = pct + '%';

                if ( timeLeft <= 60 ) {
                    timerProg.style.background = '#dc3545';
                    timerDisp.style.color = '#dc3545';
                } else if ( timeLeft <= 120 ) {
                    timerProg.style.background = '#ffc107';
                    timerDisp.style.color = '#ffc107';
                }

                if ( timeLeft <= 0 ) {
                    clearInterval(timerInterval);
                    document.getElementById('qe-form').submit();
                }
            }, 1000);
            <?php endif; ?>

            // ---- Answer Highlighting ----
            document.querySelectorAll('.qe-option-label').forEach(function(label) {
                label.addEventListener('click', function() {
                    var card = this.closest('.qe-question-card');
                    card.querySelectorAll('.qe-option-label').forEach(function(l) {
                        l.classList.remove('qe-selected');
                    });
                    this.classList.add('qe-selected');
                    this.querySelector('.qe-radio').checked = true;
                });
            });

            // ---- Next ----
            document.querySelectorAll('.qe-next').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var current = parseInt(this.getAttribute('data-current'));
                    document.getElementById('qe-question-' + current).style.display = 'none';
                    document.getElementById('qe-question-' + (current + 1)).style.display = 'block';
                    document.getElementById('qe-progress-text').textContent =
                        (current + 1) + ' / ' + totalQ;
                    window.scrollTo(0, 0);
                });
            });

            // ---- Previous ----
            document.querySelectorAll('.qe-prev').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var current = parseInt(this.getAttribute('data-current'));
                    document.getElementById('qe-question-' + current).style.display = 'none';
                    document.getElementById('qe-question-' + (current - 1)).style.display = 'block';
                    document.getElementById('qe-progress-text').textContent =
                        (current - 1) + ' / ' + totalQ;
                    window.scrollTo(0, 0);
                });
            });

            // ---- Submit Confirmation ----
            document.getElementById('qe-form').addEventListener('submit', function(e) {
                var unanswered = 0;
                document.querySelectorAll('.qe-question-card').forEach(function(card) {
                    if ( ! card.querySelector('.qe-radio:checked') ) unanswered++;
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
        return ob_get_clean();
    }

    // ============================================================
    // MAIN RENDER (shortcode entry point)
    // ============================================================
    public function render( $atts ) {

        $atts = shortcode_atts(
            array( 'id' => 0 ),
            $atts,
            'quiz_engine'
        );

        $quiz_id = $atts['id'] ? intval( $atts['id'] ) : get_queried_object_id();

        // Handle submission first
        $submission = $this->handle_submission();
        if ( $submission !== null ) {
            return $this->render_results( $submission );
        }

        // Must be logged in to take quiz
        if ( ! is_user_logged_in() ) {
            return '<div class="qe-login-notice">
                <p>You must be logged in to take this quiz.</p>
                <a href="' . wp_login_url( get_permalink() ) . '" class="qe-btn qe-btn-primary">Login to Play</a>
                <a href="' . wp_registration_url() . '" class="qe-btn qe-btn-secondary">Register</a>
            </div>';
        }

        return $this->render_quiz( $quiz_id );
    }
}

new Quiz_Engine();
