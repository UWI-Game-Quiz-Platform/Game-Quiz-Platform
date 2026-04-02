<?php
/**
 * Games Quiz Platform - INFO3602
 * Team: Varune Rampersad, Josiah Phillip, Ijaaz Sisarran
 * File: single-quiz.php
 */
get_header(); ?>

<div id="intro">
    <div class="container">
        <h1 class="page-title"><?php the_title(); ?></h1>
        <?php if ( function_exists('shuttle_breadcrumbs') ) shuttle_breadcrumbs(); ?>
    </div>
</div>

<div id="content">
    <div class="container" style="padding:60px 0;">

        <?php
        // show the quiz engine plugin
        if ( isset($_GET['take']) && $_GET['take'] == '1' ) :
            $quiz_id = get_queried_object_id();
            echo do_shortcode('[quiz_engine id="' . $quiz_id . '"]');

        // If POST submission, handle via plugin
        elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_submit']) ) :
            $quiz_id = intval($_POST['quiz_id']);
            echo do_shortcode('[quiz_engine id="' . $quiz_id . '"]');

        // Otherwise show quiz info page
        else :
            while ( have_posts() ) : the_post();

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

                
                <div>
                    <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; overflow:hidden; margin-bottom:30px;">

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div style="height:300px; overflow:hidden;">
                                <?php the_post_thumbnail('large', array('style'=>'width:100%;height:100%;object-fit:cover;')); ?>
                            </div>
                        <?php else : ?>
                            <div style="height:250px; background:linear-gradient(135deg,#1a1a2e,#16213e); display:flex; align-items:center; justify-content:center; font-size:80px;">🎮</div>
                        <?php endif; ?>

                        <div style="padding:30px;">

                            <!-- enables Badges -->
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

                            <!-- enables Description -->
                            <div style="color:rgba(255,255,255,0.8); line-height:1.7; margin-bottom:25px;">
                                <?php the_content(); ?>
                            </div>

                            <!-- enables Stats -->
                            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:30px;">
                                <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:20px; text-align:center;">
                                    <div style="font-size:28px; font-weight:700; color:#13aff0;"><?php echo $total_questions ?: '?'; ?></div>
                                    <div style="color:rgba(255,255,255,0.5); font-size:13px; margin-top:5px;">Questions</div>
                                </div>
                                <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:20px; text-align:center;">
                                    <div style="font-size:28px; font-weight:700; color:#13aff0;"><?php echo $time_limit ? floor($time_limit/60).'m' : '?'; ?></div>
                                    <div style="color:rgba(255,255,255,0.5); font-size:13px; margin-top:5px;">Time Limit</div>
                                </div>
                                <div style="background:rgba(255,255,255,0.05); border-radius:8px; padding:20px; text-align:center;">
                                    <div style="font-size:28px; font-weight:700; color:#13aff0;"><?php echo $passing_score ? $passing_score.'%' : '?'; ?></div>
                                    <div style="color:rgba(255,255,255,0.5); font-size:13px; margin-top:5px;">Pass Score</div>
                                </div>
                            </div>

                            <!-- Start Button look and functionality -->
                            <?php if ( is_user_logged_in() ) : ?>
                                <a href="<?php echo esc_url( add_query_arg('take','1',get_permalink()) ); ?>" style="
                                    display:block; text-align:center;
                                    background:#13aff0; color:#fff;
                                    padding:15px; border-radius:8px;
                                    text-decoration:none; font-weight:700; font-size:16px;">
                                    🎮 Start Quiz Now
                                </a>
                            <?php else : ?>
                                <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:20px; text-align:center;">
                                    <p style="color:#fff; margin-bottom:15px;">You must be logged in to take this quiz.</p>
                                    <a href="<?php echo wp_login_url(get_permalink()); ?>" style="display:inline-block; background:#13aff0; color:#fff; padding:10px 25px; border-radius:5px; text-decoration:none; font-weight:600;">Login to Play</a>
                                    <a href="<?php echo wp_registration_url(); ?>" style="display:inline-block; background:rgba(255,255,255,0.1); color:#fff; padding:10px 25px; border-radius:5px; text-decoration:none; font-weight:600; margin-left:10px;">Register</a>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- enables Comments -->
                    <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:30px;">
                        <?php comments_template(); ?>
                    </div>
                </div>

                
                <div>
                    <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:25px; margin-bottom:25px;">
                        <h3 style="color:#fff; font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.1);">🏆 Top Players</h3>

                        <?php if ( $leaderboard->have_posts() ) :
                            $rank = 1;
                            while ( $leaderboard->have_posts() ) : $leaderboard->the_post();
                                $lb_user = get_userdata( get_post_meta(get_the_ID(),'_leaderboard_user_id',true) );
                                $medal = array(1=>'🥇',2=>'🥈',3=>'🥉');
                        ?>
                            <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.07);">
                                <span style="font-size:20px; width:28px;"><?php echo isset($medal[$rank]) ? $medal[$rank] : '#'.$rank; ?></span>
                                <div style="flex:1;">
                                    <div style="color:#fff; font-weight:600; font-size:14px;"><?php echo $lb_user ? esc_html($lb_user->display_name) : 'Unknown'; ?></div>
                                    <div style="color:rgba(255,255,255,0.4); font-size:12px;">
                                        <?php echo get_post_meta(get_the_ID(),'_leaderboard_score',true); ?>/<?php echo get_post_meta(get_the_ID(),'_leaderboard_total',true); ?> correct
                                    </div>
                                </div>
                                <span style="color:#13aff0; font-weight:700; font-size:16px;"><?php echo get_post_meta(get_the_ID(),'_leaderboard_percentage',true); ?>%</span>
                            </div>
                        <?php $rank++; endwhile; wp_reset_postdata();
                        else : ?>
                            <p style="color:rgba(255,255,255,0.4); text-align:center; padding:20px 0;">No scores yet. Be the first!</p>
                        <?php endif; ?>
                    </div>

                    <!-- Rules description -->
                    <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:25px;">
                        <h3 style="color:#fff; font-size:16px; font-weight:700; margin-bottom:15px;">📋 Quiz Rules</h3>
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach ( array(
                                '✅ Read each question carefully',
                                '⏱️ Timer starts when you begin',
                                '❌ No going back once submitted',
                                '🏆 Score is saved to leaderboard',
                                '📊 You need ' . ($passing_score ?: '?') . '% to pass',
                            ) as $rule ) : ?>
                                <li style="color:rgba(255,255,255,0.7); font-size:14px; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.07);"><?php echo $rule; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

            </div>

            <?php endwhile;
        endif; ?>

    </div>
</div>

<?php get_footer(); ?>
