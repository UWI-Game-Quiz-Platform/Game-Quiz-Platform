<?php
/**
 * Games Quiz Platform - INFO3602
 * Team: Varune Rampersad, Josiah Phillip, Ijaaz Sisarran
 * File: page-all-quizzes.php
 */

get_header(); ?>

<style>
    /* Fix container sizing issues (prevents right overflow) */
    .container {
        width: 100% !important;
        max-width: 1200px;
        margin: 0 auto;
        padding-left: 15px;
        padding-right: 15px;
        box-sizing: border-box;
    }

    .container * {
        box-sizing: border-box;
    }

    /* Fix grid spacing + overflow */
    .quiz-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        width: 100%;
        margin-bottom: 50px;
    }
</style>

<div id="content">
    <div class="container" style="padding: 60px 0;">

        <!-- enables filtering of quizzes -->
        <div class="quiz-filter-bar" style="
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 25px 30px;
            margin-bottom: 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;">

            <form method="GET" action="" style="display:flex; flex-wrap:wrap; gap:15px; width:100%;">

                <!-- allows the user to filter by genre -->
                <div style="flex:1; min-width:180px;">
                    <label style="color:#fff; display:block; margin-bottom:6px; font-weight:600;">
                        Genre
                    </label>
                    <select name="genre" style="
                        width:100%;
                        background:#1f2326;
                        color:#fff;
                        border:1px solid rgba(255,255,255,0.2);
                        border-radius:5px;
                        padding:8px 12px;">
                        <option value="">All Genres</option>

                        <?php
                        $genres = get_terms(array(
                            'taxonomy'   => 'quiz_genre',
                            'hide_empty' => false,
                        ));

                        if ( ! empty($genres) && ! is_wp_error($genres) ) :
                            foreach ( $genres as $genre ) :
                                $selected = ( isset($_GET['genre']) && $_GET['genre'] == $genre->slug ) ? 'selected' : '';
                        ?>
                            <option value="<?php echo esc_attr($genre->slug); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($genre->name); ?>
                            </option>
                        <?php
                            endforeach;
                        endif;
                        ?>

                    </select>
                </div>

                <!-- allows you to filter by difficulty -->
                <div style="flex:1; min-width:180px;">
                    <label style="color:#fff; display:block; margin-bottom:6px; font-weight:600;">
                        Difficulty
                    </label>
                    <select name="difficulty" style="
                        width:100%;
                        background:#1f2326;
                        color:#fff;
                        border:1px solid rgba(255,255,255,0.2);
                        border-radius:5px;
                        padding:8px 12px;">
                        <option value="">All Difficulties</option>

                        <?php
                        $difficulties = get_terms(array(
                            'taxonomy'   => 'quiz_difficulty',
                            'hide_empty' => false,
                        ));

                        if ( ! empty($difficulties) && ! is_wp_error($difficulties) ) :
                            foreach ( $difficulties as $diff ) :
                                $selected = ( isset($_GET['difficulty']) && $_GET['difficulty'] == $diff->slug ) ? 'selected' : '';
                        ?>
                            <option value="<?php echo esc_attr($diff->slug); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($diff->name); ?>
                            </option>
                        <?php
                            endforeach;
                        endif;
                        ?>

                    </select>
                </div>

                <!-- Search implementation -->
                <div style="flex:2; min-width:200px;">
                    <label style="color:#fff; display:block; margin-bottom:6px; font-weight:600;">
                        Search
                    </label>
                    <input type="text" name="quiz_search"
                        value="<?php echo isset($_GET['quiz_search']) ? esc_attr($_GET['quiz_search']) : ''; ?>"
                        placeholder="Search quizzes..."
                        style="
                            width:100%;
                            background:#1f2326;
                            color:#fff;
                            border:1px solid rgba(255,255,255,0.2);
                            border-radius:5px;
                            padding:8px 12px;
                            box-sizing:border-box;" />
                </div>

                <!-- sets up our submit button -->
                <div style="display:flex; align-items:flex-end;">
                    <button type="submit" style="
                        background:#13aff0;
                        color:#fff;
                        border:none;
                        border-radius:5px;
                        padding:9px 25px;
                        font-weight:600;
                        cursor:pointer;
                        transition:all 0.3s;">
                        Filter
                    </button>
                </div>

                <!-- allows reset -->
                <div style="display:flex; align-items:flex-end;">
                    <a href="<?php echo esc_url(get_permalink()); ?>" style="
                        background:rgba(255,255,255,0.1);
                        color:#fff;
                        border-radius:5px;
                        padding:9px 20px;
                        text-decoration:none;
                        font-weight:600;
                        transition:all 0.3s;">
                        Reset
                    </a>
                </div>

            </form>
        </div>

        <?php
        // builds query args based on filters and search, then runs WP_Query to fetch quizzes
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

        
        if ( isset($_GET['paged']) ) {
            $paged = intval($_GET['paged']);
        }

        $tax_query = array( 'relation' => 'AND' );

        if ( ! empty($_GET['genre']) ) {
            $tax_query[] = array(
                'taxonomy' => 'quiz_genre',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['genre']),
            );
        }

        if ( ! empty($_GET['difficulty']) ) {
            $tax_query[] = array(
                'taxonomy' => 'quiz_difficulty',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['difficulty']),
            );
        }

        $args = array(
            'post_type'      => 'quiz',
            'posts_per_page' => 6,
            'paged'          => $paged,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ( ! empty($_GET['quiz_search']) ) {
            $args['s'] = sanitize_text_field($_GET['quiz_search']);
        }

        if ( count($tax_query) > 1 ) {
            $args['tax_query'] = $tax_query;
        }

        $quiz_query = new WP_Query($args);
        ?>

        <?php if ( $quiz_query->have_posts() ) : ?>

            <p style="color:rgba(255,255,255,0.6); margin-bottom:30px;">
                Showing <?php echo esc_html($quiz_query->found_posts); ?> quiz<?php echo $quiz_query->found_posts != 1 ? 'zes' : ''; ?>
            </p>

            
            <div class="quiz-grid">

                <?php while ( $quiz_query->have_posts() ) : $quiz_query->the_post();

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
                ?>

                <div class="quiz-card" style="
                    background: #212529;
                    border: 1px solid rgba(255,255,255,0.15);
                    border-radius: 10px;
                    overflow: hidden;
                    transition: transform 0.3s, box-shadow 0.3s;
                    display: flex;
                    flex-direction: column;">

                    <!-- controls our thumbnails -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div style="height:180px; overflow:hidden;">
                            <?php the_post_thumbnail('medium', array(
                                'style' => 'width:100%; height:100%; object-fit:cover;'
                            )); ?>
                        </div>
                    <?php else : ?>
                        <div style="
                            height:180px;
                            background: linear-gradient(135deg, #1a1a2e, #16213e);
                            display:flex;
                            align-items:center;
                            justify-content:center;">
                            <span style="font-size:60px;">🎮</span>
                        </div>
                    <?php endif; ?>

                    <div style="padding:25px; flex:1; display:flex; flex-direction:column;">

                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
                            <span style="
                                background:<?php echo esc_attr($diff_color); ?>;
                                color:#fff;
                                padding:3px 12px;
                                border-radius:20px;
                                font-size:12px;
                                font-weight:600;">
                                <?php echo esc_html($difficulty_name); ?>
                            </span>

                            <?php if ( $genres && ! is_wp_error($genres) ) : ?>
                                <span style="
                                    background:rgba(255,255,255,0.1);
                                    color:#fff;
                                    padding:3px 12px;
                                    border-radius:20px;
                                    font-size:12px;">
                                    <?php echo esc_html($genres[0]->name); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 style="
                            color:#fff;
                            font-size:18px;
                            font-weight:700;
                            margin-bottom:10px;
                            text-transform:none;">
                            <?php the_title(); ?>
                        </h3>

                        <p style="
                            color:rgba(255,255,255,0.6);
                            font-size:14px;
                            margin-bottom:20px;
                            flex:1;">
                            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 15, '...')); ?>
                        </p>

                        <div style="
                            display:flex;
                            gap:15px;
                            margin-bottom:20px;
                            padding:12px;
                            background:rgba(255,255,255,0.05);
                            border-radius:5px;">

                            <div style="text-align:center; flex:1;">
                                <div style="color:#13aff0; font-size:18px; font-weight:700;">
                                    <?php echo $total_questions ? esc_html($total_questions) : '?'; ?>
                                </div>
                                <div style="color:rgba(255,255,255,0.5); font-size:11px;">Questions</div>
                            </div>

                            <div style="text-align:center; flex:1;">
                                <div style="color:#13aff0; font-size:18px; font-weight:700;">
                                    <?php echo $time_limit ? esc_html(floor($time_limit/60).'m') : '?'; ?>
                                </div>
                                <div style="color:rgba(255,255,255,0.5); font-size:11px;">Time Limit</div>
                            </div>

                            <div style="text-align:center; flex:1;">
                                <div style="color:#13aff0; font-size:18px; font-weight:700;">
                                    <?php echo $passing_score ? esc_html($passing_score).'%' : '?'; ?>
                                </div>
                                <div style="color:rgba(255,255,255,0.5); font-size:11px;">Pass Score</div>
                            </div>
                        </div>

                        <a href="<?php the_permalink(); ?>" style="
                            display:block;
                            text-align:center;
                            background:#13aff0;
                            color:#fff;
                            padding:12px;
                            border-radius:5px;
                            text-decoration:none;
                            font-weight:600;
                            font-size:14px;">
                            Take Quiz →
                        </a>

                    </div>
                </div>

                <?php endwhile; ?>
            </div>

            <!-- display our pagination -->
            <?php if ( $quiz_query->max_num_pages > 1 ) : ?>
                <div class="navigation pagination" style="text-align:center;">
                    <div class="nav-links">
                        <?php
                        echo paginate_links(array(
                            'base'      => add_query_arg('paged', '%#%'),
                            'format'    => '',
                            'current'   => max(1, $paged),
                            'total'     => $quiz_query->max_num_pages,
                            'prev_text' => '← Prev',
                            'next_text' => 'Next →',
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>

            <div style="
                text-align:center;
                padding:80px 20px;
                background:rgba(255,255,255,0.03);
                border-radius:10px;
                border:1px solid rgba(255,255,255,0.1);">
                <div style="font-size:60px; margin-bottom:20px;">🎮</div>
                <h3 style="color:#fff; margin-bottom:10px;">No Quizzes Found</h3>
                <p style="color:rgba(255,255,255,0.5);">
                    Try adjusting your filters or check back later.
                </p>
                <a href="<?php echo esc_url(get_permalink()); ?>" style="
                    display:inline-block;
                    margin-top:20px;
                    background:#13aff0;
                    color:#fff;
                    padding:10px 25px;
                    border-radius:5px;
                    text-decoration:none;
                    font-weight:600;">
                    View All Quizzes
                </a>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>