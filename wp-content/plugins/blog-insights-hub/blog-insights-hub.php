<?php
/**
 * Plugin Name: Blog Insights Hub
 * Description: Adds searchable, filterable Blog Insights sections for the Games Quiz Platform, including top insights and related quiz links.
 * Version: 1.0.0
 * Author: Josiah Phillip
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Blog_Insights_Hub {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
        add_shortcode( 'blog_insights_hub', array( $this, 'render_blog_insights_hub' ) );
        add_shortcode( 'top_quiz_insights', array( $this, 'render_top_quiz_insights' ) );
    }

    public function register_assets() {
        wp_register_style(
            'blog-insights-hub-style',
            plugin_dir_url( __FILE__ ) . 'assets/blog-insights-hub.css',
            array(),
            '1.0.0'
        );
    }

    private function enqueue_assets() {
        wp_enqueue_style( 'blog-insights-hub-style' );
    }

    private function get_read_time( $post_id ) {
        $read_time = get_post_meta( $post_id, '_insight_read_time', true );
        $read_time = absint( $read_time );
        return $read_time > 0 ? $read_time : '';
    }

    private function get_related_quiz_id( $post_id ) {
        return absint( get_post_meta( $post_id, '_insight_related_quiz', true ) );
    }

    private function get_filter_value( $key ) {
        return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
    }

    public function render_blog_insights_hub( $atts ) {
        $this->enqueue_assets();

        $atts = shortcode_atts(
            array(
                'posts_per_page' => 6,
                'show_top'       => 'yes',
                'title'          => 'Blog Insights Hub',
            ),
            $atts,
            'blog_insights_hub'
        );

        $current_page = max( 1, absint( get_query_var( 'paged' ) ) ?: absint( get_query_var( 'page' ) ) );
        $search       = $this->get_filter_value( 'bih_search' );
        $category     = $this->get_filter_value( 'bih_category' );
        $sort         = $this->get_filter_value( 'bih_sort' );

        if ( empty( $sort ) ) {
            $sort = 'newest';
        }

        $args = array(
            'post_type'      => 'blog_insight',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $atts['posts_per_page'] ),
            'paged'          => $current_page,
            's'              => $search,
        );

        if ( ! empty( $category ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'insight_category',
                    'field'    => 'slug',
                    'terms'    => $category,
                ),
            );
        }

        switch ( $sort ) {
            case 'oldest':
                $args['orderby'] = 'date';
                $args['order']   = 'ASC';
                break;
            case 'title':
                $args['orderby'] = 'title';
                $args['order']   = 'ASC';
                break;
            case 'read_time':
                $args['meta_key'] = '_insight_read_time';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;
            case 'longest_read':
                $args['meta_key'] = '_insight_read_time';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'popular':
                $args['orderby'] = array(
                    'comment_count' => 'DESC',
                    'date'          => 'DESC',
                );
                break;
            case 'newest':
            default:
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
        }

        $query = new WP_Query( $args );
        $categories = get_terms(
            array(
                'taxonomy'   => 'insight_category',
                'hide_empty' => false,
            )
        );

        ob_start();
        ?>
        <div class="bih-wrapper">
            <div class="bih-header">
                <div>
                    <h2 class="bih-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                    <p class="bih-subtitle">Search, filter, and explore gaming articles linked to your quizzes.</p>
                </div>
            </div>

            <form class="bih-filter-bar" method="get">
                <div class="bih-filter-field">
                    <label for="bih_search">Search</label>
                    <input type="text" id="bih_search" name="bih_search" value="<?php echo esc_attr( $search ); ?>" placeholder="Search insights...">
                </div>

                <div class="bih-filter-field">
                    <label for="bih_category">Category</label>
                    <select id="bih_category" name="bih_category">
                        <option value="">All Categories</option>
                        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                            <?php foreach ( $categories as $term ) : ?>
                                <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $category, $term->slug ); ?>>
                                    <?php echo esc_html( $term->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="bih-filter-field">
                    <label for="bih_sort">Sort By</label>
                    <select id="bih_sort" name="bih_sort">
                        <option value="newest" <?php selected( $sort, 'newest' ); ?>>Newest</option>
                        <option value="oldest" <?php selected( $sort, 'oldest' ); ?>>Oldest</option>
                        <option value="popular" <?php selected( $sort, 'popular' ); ?>>Most Popular</option>
                        <option value="read_time" <?php selected( $sort, 'read_time' ); ?>>Shortest Read</option>
                        <option value="longest_read" <?php selected( $sort, 'longest_read' ); ?>>Longest Read</option>
                        <option value="title" <?php selected( $sort, 'title' ); ?>>A to Z</option>
                    </select>
                </div>

                <div class="bih-filter-actions">
                    <button type="submit">Apply</button>
                    <a href="<?php echo esc_url( get_permalink() ); ?>">Reset</a>
                </div>
            </form>

            <?php if ( 'yes' === strtolower( $atts['show_top'] ) ) : ?>
                <?php echo $this->get_top_section_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>

            <?php if ( $query->have_posts() ) : ?>
                <div class="bih-results-meta">
                    <span><?php echo esc_html( intval( $query->found_posts ) ); ?> insight(s) found</span>
                </div>

                <div class="bih-grid">
                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                        <?php
                        $post_id         = get_the_ID();
                        $read_time       = $this->get_read_time( $post_id );
                        $related_quiz_id = $this->get_related_quiz_id( $post_id );
                        $categories_list = get_the_terms( $post_id, 'insight_category' );
                        ?>
                        <article class="bih-card">
                            <div class="bih-card-media">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
                                <?php else : ?>
                                    <div class="bih-placeholder">Gaming Insight</div>
                                <?php endif; ?>
                            </div>

                            <div class="bih-card-body">
                                <div class="bih-meta-row">
                                    <span><?php echo esc_html( get_the_date() ); ?></span>
                                    <?php if ( $read_time ) : ?>
                                        <span><?php echo esc_html( $read_time ); ?> min read</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ( ! empty( $categories_list ) && ! is_wp_error( $categories_list ) ) : ?>
                                    <div class="bih-tags">
                                        <?php foreach ( $categories_list as $term ) : ?>
                                            <span><?php echo esc_html( $term->name ); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h3 class="bih-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <div class="bih-excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 22 ) ); ?></div>

                                <div class="bih-card-footer">
                                    <a class="bih-read-more" href="<?php the_permalink(); ?>">Read Insight</a>

                                    <?php if ( $related_quiz_id && 'quiz' === get_post_type( $related_quiz_id ) ) : ?>
                                        <a class="bih-related-quiz" href="<?php echo esc_url( get_permalink( $related_quiz_id ) ); ?>">
                                            Take Related Quiz
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php
                $pagination_base = remove_query_arg( 'paged' );
                echo '<div class="bih-pagination">';
                echo wp_kses_post(
                    paginate_links(
                        array(
                            'total'     => $query->max_num_pages,
                            'current'   => $current_page,
                            'type'      => 'list',
                            'base'      => trailingslashit( $pagination_base ) . '%_%',
                            'format'    => '?paged=%#%',
                            'add_args'  => array_filter(
                                array(
                                    'bih_search'   => $search,
                                    'bih_category' => $category,
                                    'bih_sort'     => $sort,
                                )
                            ),
                        )
                    )
                );
                echo '</div>';
                ?>

            <?php else : ?>
                <div class="bih-empty-state">
                    <h3>No blog insights found</h3>
                    <p>Try changing the search, category, or sorting options.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        wp_reset_postdata();

        return ob_get_clean();
    }

    private function get_top_section_markup() {
        $top_query = new WP_Query(
            array(
                'post_type'      => 'blog_insight',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'orderby'        => array(
                    'comment_count' => 'DESC',
                    'date'          => 'DESC',
                ),
            )
        );

        ob_start();

        if ( $top_query->have_posts() ) :
            ?>
            <section class="bih-top-section">
                <div class="bih-top-header">
                    <h3>Top Insights</h3>
                    <p>Highlighted articles readers may want to start with first.</p>
                </div>
                <div class="bih-top-grid">
                    <?php while ( $top_query->have_posts() ) : $top_query->the_post(); ?>
                        <?php
                        $post_id         = get_the_ID();
                        $read_time       = $this->get_read_time( $post_id );
                        $related_quiz_id = $this->get_related_quiz_id( $post_id );
                        ?>
                        <article class="bih-top-card">
                            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                            <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>
                            <div class="bih-top-meta">
                                <span><?php echo esc_html( get_the_date() ); ?></span>
                                <?php if ( $read_time ) : ?>
                                    <span><?php echo esc_html( $read_time ); ?> min read</span>
                                <?php endif; ?>
                                <span><?php echo esc_html( get_comments_number() ); ?> comment(s)</span>
                            </div>
                            <div class="bih-top-links">
                                <a href="<?php the_permalink(); ?>">Read</a>
                                <?php if ( $related_quiz_id && 'quiz' === get_post_type( $related_quiz_id ) ) : ?>
                                    <a href="<?php echo esc_url( get_permalink( $related_quiz_id ) ); ?>">Related Quiz</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php
        endif;

        wp_reset_postdata();
        return ob_get_clean();
    }

    public function render_top_quiz_insights( $atts ) {
        $this->enqueue_assets();

        $atts = shortcode_atts(
            array(
                'posts_per_page' => 4,
                'title'          => 'Insights Linked to Quizzes',
            ),
            $atts,
            'top_quiz_insights'
        );

        $query = new WP_Query(
            array(
                'post_type'      => 'blog_insight',
                'post_status'    => 'publish',
                'posts_per_page' => absint( $atts['posts_per_page'] ),
                'meta_query'     => array(
                    array(
                        'key'     => '_insight_related_quiz',
                        'value'   => 0,
                        'compare' => '>',
                        'type'    => 'NUMERIC',
                    ),
                ),
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );

        ob_start();
        ?>
        <div class="bih-linked-wrapper">
            <div class="bih-top-header">
                <h3><?php echo esc_html( $atts['title'] ); ?></h3>
                <p>Articles that connect directly to available quizzes on the platform.</p>
            </div>

            <?php if ( $query->have_posts() ) : ?>
                <div class="bih-linked-list">
                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                        <?php
                        $post_id         = get_the_ID();
                        $related_quiz_id = $this->get_related_quiz_id( $post_id );
                        $quiz_title      = $related_quiz_id ? get_the_title( $related_quiz_id ) : '';
                        ?>
                        <article class="bih-linked-item">
                            <div>
                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                                <?php if ( $quiz_title ) : ?>
                                    <div class="bih-linked-quiz-name">Related quiz: <?php echo esc_html( $quiz_title ); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="bih-linked-actions">
                                <a href="<?php the_permalink(); ?>">Read Article</a>
                                <?php if ( $related_quiz_id && 'quiz' === get_post_type( $related_quiz_id ) ) : ?>
                                    <a href="<?php echo esc_url( get_permalink( $related_quiz_id ) ); ?>">Take Quiz</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <div class="bih-empty-state">
                    <h3>No quiz-linked insights yet</h3>
                    <p>Add a related quiz in the Blog Insight custom field area to populate this section.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
}

new Blog_Insights_Hub();
