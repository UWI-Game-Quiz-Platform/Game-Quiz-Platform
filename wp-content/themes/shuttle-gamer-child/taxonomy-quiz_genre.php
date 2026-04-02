<?php
get_header();

$term = get_queried_object();
?>

<div style="background:#1a1d21; padding:80px 0;">
    <div class="container">

        <h1 style="color:#fff; font-size:40px; font-weight:900; margin-bottom:30px;">
            Genre: <?php echo esc_html($term->name); ?>
        </h1>

        <?php
        $args = array(
            'post_type'      => 'quiz',
            'posts_per_page' => 9,
            'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'quiz_genre',
                    'field'    => 'slug',
                    'terms'    => $term->slug,
                ),
            ),
        );

        $quiz_query = new WP_Query($args);
        ?>

        <?php if ($quiz_query->have_posts()) : ?>

            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:25px;">
                <?php while ($quiz_query->have_posts()) : $quiz_query->the_post(); ?>

                    <div style="background:#212529; border:1px solid rgba(255,255,255,0.15); border-radius:10px; overflow:hidden;">
                        <?php if (has_post_thumbnail()) : ?>
                            <div style="height:180px; overflow:hidden;">
                                <?php the_post_thumbnail('medium', array(
                                    'style' => 'width:100%; height:100%; object-fit:cover;'
                                )); ?>
                            </div>
                        <?php endif; ?>

                        <div style="padding:20px;">
                            <h3 style="color:#fff;"><?php the_title(); ?></h3>
                            <a href="<?php the_permalink(); ?>" style="
                                display:inline-block;
                                margin-top:10px;
                                background:#13aff0;
                                color:#fff;
                                padding:10px 20px;
                                border-radius:6px;
                                text-decoration:none;">
                                Take Quiz →
                            </a>
                        </div>
                    </div>

                <?php endwhile; wp_reset_postdata(); ?>
            </div>

        <?php else : ?>
            <p style="color:rgba(255,255,255,0.6);">No quizzes found in this genre.</p>
        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>