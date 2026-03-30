<?php
/**
 * Template Name: Home Page
 * Description: Main landing page for the Games Quiz Platform
 */

get_header(); ?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<div style="
    background: linear-gradient(135deg, #0d1117 0%, #1a1a2e 50%, #16213e 100%);
    padding: 100px 0 80px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(255,255,255,0.1);">

    <!-- Background glow effects -->
    <div style="
        position:absolute; top:-100px; left:50%;
        transform:translateX(-50%);
        width:600px; height:600px;
        background:radial-gradient(circle, rgba(19,175,240,0.15) 0%, transparent 70%);
        pointer-events:none;">
    </div>

    <div class="container" style="position:relative; z-index:2;">

        <div style="
            display:inline-block;
            background:rgba(19,175,240,0.15);
            border:1px solid rgba(19,175,240,0.3);
            color:#13aff0;
            padding:6px 20px;
            border-radius:30px;
            font-size:13px;
            font-weight:600;
            letter-spacing:1px;
            text-transform:uppercase;
            margin-bottom:25px;">
            🎮 The Ultimate Gaming Quiz Platform
        </div>

        <h1 style="
            color:#fff;
            font-size:56px;
            font-weight:900;
            line-height:1.1;
            margin-bottom:20px;
            text-transform:none;">
            Test Your <span style="color:#13aff0;">Gaming</span><br>Knowledge
        </h1>

        <p style="
            color:rgba(255,255,255,0.65);
            font-size:18px;
            max-width:550px;
            margin:0 auto 40px;
            line-height:1.7;">
            Challenge yourself with quizzes across Action, RPG, Sports, FPS and more.
            Compete on the leaderboard and prove you're the ultimate gamer.
        </p>

        <!-- CTA Buttons -->
        <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap; margin-bottom:60px;">
            <a href="<?php echo esc_url( home_url('/quizzes') ); ?>" style="
                background:#13aff0;
                color:#fff;
                padding:15px 35px;
                border-radius:8px;
                text-decoration:none;
                font-weight:700;
                font-size:16px;
                transition:all 0.3s;">
                🎮 Browse All Quizzes
            </a>
            <?php if ( ! is_user_logged_in() ) : ?>
            <a href="<?php echo wp_registration_url(); ?>" style="
                background:rgba(255,255,255,0.1);
                color:#fff;
                padding:15px 35px;
                border-radius:8px;
                text-decoration:none;
                font-weight:700;
                font-size:16px;
                border:1px solid rgba(255,255,255,0.2);
                transition:all 0.3s;">
                ✍️ Register Free
            </a>
            <?php else : ?>
            <a href="<?php echo esc_url( home_url('/leaderboard') ); ?>" style="
                background:rgba(255,255,255,0.1);
                color:#fff;
                padding:15px 35px;
                border-radius:8px;
                text-decoration:none;
                font-weight:700;
                font-size:16px;
                border:1px solid rgba(255,255,255,0.2);
                transition:all 0.3s;">
                🏆 View Leaderboard
            </a>
            <?php endif; ?>
        </div>

        <!-- Quick Stats -->
        <?php
        $total_quizzes    = wp_count_posts('quiz')->publish;
        $total_questions  = wp_count_posts('quiz_question')->publish;
        $total_scores     = wp_count_posts('leaderboard_entry')->publish;
        $total_players    = count_users();
        ?>
        <div style="
            display:inline-grid;
            grid-template-columns:repeat(4,1fr);
            gap:1px;
            background:rgba(255,255,255,0.1);
            border-radius:12px;
            overflow:hidden;
            border:1px solid rgba(255,255,255,0.1);">
            <?php
            $stats = array(
                array( 'value' => $total_quizzes,             'label' => 'Quizzes',       'icon' => '🎮' ),
                array( 'value' => $total_questions,           'label' => 'Questions',     'icon' => '❓' ),
                array( 'value' => $total_scores,              'label' => 'Scores Logged', 'icon' => '📊' ),
                array( 'value' => $total_players['total_users'], 'label' => 'Players',    'icon' => '👥' ),
            );
            foreach ( $stats as $stat ) : ?>
                <div style="
                    background:#1f2326;
                    padding:20px 30px;
                    text-align:center;">
                    <div style="font-size:22px; margin-bottom:4px;"><?php echo $stat['icon']; ?></div>
                    <div style="color:#13aff0; font-size:28px; font-weight:800; line-height:1;">
                        <?php echo $stat['value']; ?>
                    </div>
                    <div style="color:rgba(255,255,255,0.5); font-size:12px; margin-top:4px; text-transform:uppercase; letter-spacing:1px;">
                        <?php echo $stat['label']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<!-- ============================================================
     FEATURED QUIZZES SECTION
     ============================================================ -->
<div style="background:#1a1d21; padding:80px 0;">
    <div class="container">

        <div style="text-align:center; margin-bottom:50px;">
            <h2 style="color:#fff; font-size:36px; font-weight:800; margin-bottom:12px; text-transform:none;">
                🔥 Featured Quizzes
            </h2>
            <p style="color:rgba(255,255,255,0.5); font-size:16px;">
                Jump straight into our most popular challenges
            </p>
        </div>

        <?php
        // Query latest 3 published quizzes
        $featured_quizzes = new WP_Query( array(
            'post_type'      => 'quiz',
            'posts_per_page' => 3,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        ?>

        <?php if ( $featured_quizzes->have_posts() ) : ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:25px; margin-bottom:40px;">
            <?php while ( $featured_quizzes->have_posts() ) : $featured_quizzes->the_post();
                $time_limit      = get_post_meta( get_the_ID(), '_quiz_time_limit', true );
                $total_questions = get_post_meta( get_the_ID(), '_quiz_total_questions', true );
                $passing_score   = get_post_meta( get_the_ID(), '_quiz_passing_score', true );
                $difficulties    = get_the_terms( get_the_ID(), 'quiz_difficulty' );
                $genres          = get_the_terms( get_the_ID(), 'quiz_genre' );
                $difficulty_name = $difficulties ? $difficulties[0]->name : 'N/A';

                $diff_color = '#13aff0';
                if ( $difficulty_name == 'Easy' )   $diff_color = '#28a745';
                if ( $difficulty_name == 'Medium' ) $diff_color = '#ffc107';
                if ( $difficulty_name == 'Hard' )   $diff_color = '#dc3545';
            ?>
            <div style="
                background:#212529;
                border:1px solid rgba(255,255,255,0.12);
                border-radius:12px;
                overflow:hidden;
                transition:transform 0.3s, box-shadow 0.3s;
                display:flex;
                flex-direction:column;">

                <!-- Thumbnail -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div style="height:180px; overflow:hidden;">
                        <?php the_post_thumbnail('medium', array('style'=>'width:100%;height:100%;object-fit:cover;')); ?>
                    </div>
                <?php else : ?>
                    <div style="
                        height:180px;
                        background:linear-gradient(135deg,#0d1117,#1a1a2e);
                        display:flex; align-items:center; justify-content:center; font-size:60px;">
                        🎮
                    </div>
                <?php endif; ?>

                <div style="padding:25px; flex:1; display:flex; flex-direction:column;">

                    <!-- Badges -->
                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
                        <span style="background:<?php echo $diff_color; ?>; color:#fff; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                            <?php echo esc_html($difficulty_name); ?>
                        </span>
                        <?php if ($genres) : ?>
                        <span style="background:rgba(255,255,255,0.1); color:#fff; padding:3px 12px; border-radius:20px; font-size:12px;">
                            <?php echo esc_html($genres[0]->name); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <h3 style="color:#fff; font-size:18px; font-weight:700; margin-bottom:10px; text-transform:none; flex:1;">
                        <?php the_title(); ?>
                    </h3>

                    <!-- Mini Stats -->
                    <div style="display:flex; gap:15px; margin-bottom:20px;">
                        <span style="color:rgba(255,255,255,0.5); font-size:13px;">
                            ❓ <?php echo $total_questions ?: '?'; ?> Questions
                        </span>
                        <span style="color:rgba(255,255,255,0.5); font-size:13px;">
                            ⏱️ <?php echo $time_limit ? floor($time_limit/60).'m' : '?'; ?>
                        </span>
                    </div>

                    <a href="<?php the_permalink(); ?>" style="
                        display:block; text-align:center;
                        background:#13aff0; color:#fff;
                        padding:11px; border-radius:6px;
                        text-decoration:none; font-weight:600; font-size:14px;">
                        Take Quiz →
                    </a>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <div style="text-align:center;">
            <a href="<?php echo esc_url( home_url('/quizzes') ); ?>" style="
                display:inline-block;
                background:rgba(255,255,255,0.08);
                color:#fff;
                padding:12px 30px;
                border-radius:8px;
                text-decoration:none;
                font-weight:600;
                border:1px solid rgba(255,255,255,0.15);">
                View All Quizzes →
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ============================================================
     GENRE CATEGORIES SECTION
     ============================================================ -->
<div style="background:#212529; padding:80px 0;">
    <div class="container">

        <div style="text-align:center; margin-bottom:50px;">
            <h2 style="color:#fff; font-size:36px; font-weight:800; margin-bottom:12px; text-transform:none;">
                🕹️ Browse by Genre
            </h2>
            <p style="color:rgba(255,255,255,0.5); font-size:16px;">
                Find quizzes in your favourite gaming genre
            </p>
        </div>

        <?php
        $genre_icons = array(
            'action'     => '⚔️',
            'rpg'        => '🧙',
            'sports'     => '⚽',
            'fps'        => '🔫',
            'strategy'   => '♟️',
            'multiplayer'=> '👥',
        );

        $genres = get_terms( array(
            'taxonomy'   => 'quiz_genre',
            'hide_empty' => false,
        ));
        ?>

        <?php if ( ! empty($genres) && ! is_wp_error($genres) ) : ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:15px;">
            <?php foreach ( $genres as $genre ) :
                $icon  = isset($genre_icons[ strtolower($genre->slug) ]) ? $genre_icons[ strtolower($genre->slug) ] : '🎮';
                $count = $genre->count;
                $link  = add_query_arg( 'genre', $genre->slug, home_url('/quizzes') );
            ?>
            <a href="<?php echo esc_url($link); ?>" style="
                display:block;
                background:#1f2326;
                border:1px solid rgba(255,255,255,0.12);
                border-radius:10px;
                padding:25px 15px;
                text-align:center;
                text-decoration:none;
                transition:all 0.3s;">
                <div style="font-size:36px; margin-bottom:10px;"><?php echo $icon; ?></div>
                <div style="color:#fff; font-weight:700; font-size:15px; margin-bottom:4px;">
                    <?php echo esc_html($genre->name); ?>
                </div>
                <div style="color:rgba(255,255,255,0.4); font-size:12px;">
                    <?php echo $count; ?> quiz<?php echo $count != 1 ? 'zes' : ''; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ============================================================
     HOW IT WORKS SECTION
     ============================================================ -->
<div style="background:#1a1d21; padding:80px 0;">
    <div class="container">

        <div style="text-align:center; margin-bottom:50px;">
            <h2 style="color:#fff; font-size:36px; font-weight:800; margin-bottom:12px; text-transform:none;">
                ⚡ How It Works
            </h2>
            <p style="color:rgba(255,255,255,0.5); font-size:16px;">
                Get started in three simple steps
            </p>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:30px;">
            <?php
            $steps = array(
                array(
                    'number' => '01',
                    'icon'   => '📝',
                    'title'  => 'Create an Account',
                    'desc'   => 'Register for free and set up your gamer profile to track your scores and rankings.',
                ),
                array(
                    'number' => '02',
                    'icon'   => '🎮',
                    'title'  => 'Choose a Quiz',
                    'desc'   => 'Browse quizzes by genre or difficulty. Pick one that matches your gaming knowledge.',
                ),
                array(
                    'number' => '03',
                    'icon'   => '🏆',
                    'title'  => 'Compete & Rank',
                    'desc'   => 'Submit your answers, get your score instantly, and climb the leaderboard.',
                ),
            );
            foreach ( $steps as $step ) : ?>
            <div style="
                background:#212529;
                border:1px solid rgba(255,255,255,0.12);
                border-radius:12px;
                padding:35px 30px;
                text-align:center;
                position:relative;">
                <div style="
                    position:absolute; top:-15px; left:50%; transform:translateX(-50%);
                    background:#13aff0; color:#fff;
                    width:32px; height:32px; border-radius:50%;
                    display:flex; align-items:center; justify-content:center;
                    font-size:12px; font-weight:800;">
                    <?php echo $step['number']; ?>
                </div>
                <div style="font-size:48px; margin-bottom:15px; margin-top:10px;">
                    <?php echo $step['icon']; ?>
                </div>
                <h3 style="color:#fff; font-size:18px; font-weight:700; margin-bottom:12px; text-transform:none;">
                    <?php echo $step['title']; ?>
                </h3>
                <p style="color:rgba(255,255,255,0.55); font-size:14px; line-height:1.7; margin:0;">
                    <?php echo $step['desc']; ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<!-- ============================================================
     LIVE LEADERBOARD PREVIEW SECTION
     ============================================================ -->
<div style="background:#212529; padding:80px 0;">
    <div class="container">

        <div style="text-align:center; margin-bottom:50px;">
            <h2 style="color:#fff; font-size:36px; font-weight:800; margin-bottom:12px; text-transform:none;">
                🏆 Top Scorers This Week
            </h2>
            <p style="color:rgba(255,255,255,0.5); font-size:16px;">
                Can you beat them?
            </p>
        </div>

        <?php
        $top_scores = new WP_Query( array(
            'post_type'      => 'leaderboard_entry',
            'posts_per_page' => 5,
            'meta_key'       => '_leaderboard_percentage',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'date_query'     => array(
                array(
                    'after' => '1 week ago',
                ),
            ),
        ));
        ?>

        <div style="max-width:700px; margin:0 auto;">
            <?php if ( $top_scores->have_posts() ) :
                $rank = 1;
                $medals = array( 1 => '🥇', 2 => '🥈', 3 => '🥉' );
                while ( $top_scores->have_posts() ) : $top_scores->the_post();
                    $user_id    = get_post_meta( get_the_ID(), '_leaderboard_user_id', true );
                    $quiz_id    = get_post_meta( get_the_ID(), '_leaderboard_quiz_id', true );
                    $score      = get_post_meta( get_the_ID(), '_leaderboard_score', true );
                    $total      = get_post_meta( get_the_ID(), '_leaderboard_total', true );
                    $percentage = get_post_meta( get_the_ID(), '_leaderboard_percentage', true );
                    $user       = get_userdata( $user_id );
                    $username   = $user ? $user->display_name : 'Unknown';
                    $quiz_title = get_the_title( $quiz_id );
                    $avatar     = get_avatar_url( $user_id, array('size' => 40) );
            ?>
                <div style="
                    display:flex; align-items:center; gap:15px;
                    background:#1f2326;
                    border:1px solid rgba(255,255,255,0.1);
                    border-radius:10px;
                    padding:15px 20px;
                    margin-bottom:12px;">

                    <!-- Rank -->
                    <div style="font-size:24px; width:36px; text-align:center; flex-shrink:0;">
                        <?php echo isset($medals[$rank]) ? $medals[$rank] : '#'.$rank; ?>
                    </div>

                    <!-- Avatar -->
                    <img src="<?php echo esc_url($avatar); ?>" style="width:40px; height:40px; border-radius:50%; flex-shrink:0;" />

                    <!-- Info -->
                    <div style="flex:1;">
                        <div style="color:#fff; font-weight:700; font-size:15px;">
                            <?php echo esc_html($username); ?>
                        </div>
                        <div style="color:rgba(255,255,255,0.4); font-size:12px;">
                            <?php echo esc_html($quiz_title); ?>
                        </div>
                    </div>

                    <!-- Score -->
                    <div style="text-align:right;">
                        <div style="color:#13aff0; font-weight:800; font-size:22px;">
                            <?php echo $percentage; ?>%
                        </div>
                        <div style="color:rgba(255,255,255,0.4); font-size:12px;">
                            <?php echo $score; ?>/<?php echo $total; ?> correct
                        </div>
                    </div>

                </div>
            <?php $rank++; endwhile; wp_reset_postdata();
            else : ?>
                <div style="text-align:center; padding:40px; background:#1f2326; border-radius:10px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:40px; margin-bottom:15px;">🎮</div>
                    <p style="color:rgba(255,255,255,0.5);">No scores yet this week. Be the first to play!</p>
                    <a href="<?php echo esc_url(home_url('/quizzes')); ?>" style="
                        display:inline-block; margin-top:15px;
                        background:#13aff0; color:#fff;
                        padding:10px 25px; border-radius:5px;
                        text-decoration:none; font-weight:600;">
                        Take a Quiz Now
                    </a>
                </div>
            <?php endif; ?>

            <div style="text-align:center; margin-top:25px;">
                <a href="<?php echo esc_url(home_url('/leaderboard')); ?>" style="
                    display:inline-block;
                    background:rgba(255,255,255,0.08);
                    color:#fff; padding:12px 30px;
                    border-radius:8px; text-decoration:none;
                    font-weight:600; border:1px solid rgba(255,255,255,0.15);">
                    View Full Leaderboard →
                </a>
            </div>
        </div>

    </div>
</div>

<!-- ============================================================
     LATEST BLOG INSIGHTS SECTION
     ============================================================ -->
<div style="background:#1a1d21; padding:80px 0;">
    <div class="container">

        <div style="text-align:center; margin-bottom:50px;">
            <h2 style="color:#fff; font-size:36px; font-weight:800; margin-bottom:12px; text-transform:none;">
                📰 Latest Insights
            </h2>
            <p style="color:rgba(255,255,255,0.5); font-size:16px;">
                Gaming articles to sharpen your quiz knowledge
            </p>
        </div>

        <?php
        $insights = new WP_Query( array(
            'post_type'      => 'blog_insight',
            'posts_per_page' => 3,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        ?>

        <?php if ( $insights->have_posts() ) : ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:25px; margin-bottom:40px;">
            <?php while ( $insights->have_posts() ) : $insights->the_post();
                $read_time       = get_post_meta( get_the_ID(), '_insight_read_time', true );
                $related_quiz_id = get_post_meta( get_the_ID(), '_insight_related_quiz', true );
                $categories      = get_the_terms( get_the_ID(), 'insight_category' );
            ?>
            <div style="
                background:#212529;
                border:1px solid rgba(255,255,255,0.12);
                border-radius:12px;
                overflow:hidden;
                display:flex; flex-direction:column;">

                <?php if ( has_post_thumbnail() ) : ?>
                    <div style="height:160px; overflow:hidden;">
                        <?php the_post_thumbnail('medium', array('style'=>'width:100%;height:100%;object-fit:cover;')); ?>
                    </div>
                <?php else : ?>
                    <div style="height:160px; background:linear-gradient(135deg,#0d1117,#1a1a2e); display:flex; align-items:center; justify-content:center; font-size:50px;">📰</div>
                <?php endif; ?>

                <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
                    <div style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                        <span style="color:rgba(255,255,255,0.4); font-size:12px;">
                            <?php echo get_the_date(); ?>
                        </span>
                        <?php if ( $read_time ) : ?>
                        <span style="color:rgba(255,255,255,0.4); font-size:12px;">
                            · <?php echo $read_time; ?> min read
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if ( $categories && ! is_wp_error($categories) ) : ?>
                    <div style="margin-bottom:10px;">
                        <span style="background:rgba(19,175,240,0.15); color:#13aff0; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            <?php echo esc_html($categories[0]->name); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <h3 style="color:#fff; font-size:16px; font-weight:700; margin-bottom:10px; text-transform:none; flex:1;">
                        <?php the_title(); ?>
                    </h3>

                    <div style="display:flex; gap:10px; margin-top:auto;">
                        <a href="<?php the_permalink(); ?>" style="
                            flex:1; text-align:center;
                            background:#13aff0; color:#fff;
                            padding:9px; border-radius:5px;
                            text-decoration:none; font-weight:600; font-size:13px;">
                            Read More
                        </a>
                        <?php if ( $related_quiz_id ) : ?>
                        <a href="<?php echo get_permalink($related_quiz_id); ?>" style="
                            flex:1; text-align:center;
                            background:rgba(255,255,255,0.08); color:#fff;
                            padding:9px; border-radius:5px;
                            text-decoration:none; font-weight:600; font-size:13px;
                            border:1px solid rgba(255,255,255,0.15);">
                            Take Quiz
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <div style="text-align:center;">
            <a href="<?php echo esc_url(home_url('/blog-insights')); ?>" style="
                display:inline-block;
                background:rgba(255,255,255,0.08); color:#fff;
                padding:12px 30px; border-radius:8px;
                text-decoration:none; font-weight:600;
                border:1px solid rgba(255,255,255,0.15);">
                View All Insights →
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ============================================================
     CALL TO ACTION SECTION
     ============================================================ -->
<?php if ( ! is_user_logged_in() ) : ?>
<div style="
    background:linear-gradient(135deg,#0d1117,#1a1a2e);
    padding:80px 0;
    text-align:center;
    border-top:1px solid rgba(255,255,255,0.1);">
    <div class="container">
        <h2 style="color:#fff; font-size:40px; font-weight:900; margin-bottom:15px; text-transform:none;">
            Ready to Prove Your Skills?
        </h2>
        <p style="color:rgba(255,255,255,0.6); font-size:17px; margin-bottom:35px; max-width:500px; margin-left:auto; margin-right:auto;">
            Join thousands of gamers competing on the leaderboard. Register free and start playing today.
        </p>
        <a href="<?php echo wp_registration_url(); ?>" style="
            display:inline-block;
            background:#13aff0; color:#fff;
            padding:16px 45px; border-radius:8px;
            text-decoration:none; font-weight:800; font-size:17px;">
            🎮 Get Started Free
        </a>
    </div>
</div>
<?php endif; ?>

<?php get_footer(); ?>
