<?php
/**
 * Template for displaying single seed entries
 * Implements WCAG 2.1 accessibility standards
 */
get_header();
?>

<div class="seed-catalog-single" role="main">
    <div class="seed-catalog-container">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('seed-catalog-single-seed'); ?>>
                <header class="seed-catalog-entry-header">
                    <h1 class="seed-catalog-entry-title"><?php the_title(); ?></h1>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="seed-catalog-featured-image" role="img" aria-label="<?php echo esc_attr(sprintf(__('Featured image for %s', 'seed-catalog'), get_the_title())); ?>">
                        <?php the_post_thumbnail('large', array('alt' => get_the_title())); ?>
                    </div>
                <?php endif; ?>

                <div class="seed-catalog-entry-content">
                    <?php
                    // Display the main content if any
                    the_content();

                    // Display all seed details using the template class
                    Seed_Catalog_Templates::display_seed_details(get_the_ID());
                    ?>
                </div>

                <footer class="seed-catalog-entry-footer">
                    <?php
                    // Navigation
                    $prev_post = get_previous_post(true, '', 'seed_category');
                    $next_post = get_next_post(true, '', 'seed_category');
                    
                    if ($prev_post || $next_post) :
                    ?>
                        <nav class="seed-catalog-navigation" role="navigation" aria-label="<?php esc_attr_e('Seed navigation', 'seed-catalog'); ?>">
                            <?php if ($prev_post) : ?>
                                <div class="seed-catalog-nav-previous">
                                    <span class="nav-subtitle" aria-hidden="true"><?php _e('Previous Seed', 'seed-catalog'); ?></span>
                                    <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" rel="prev">
                                        <span class="screen-reader-text"><?php echo sprintf(__('Previous seed: %s', 'seed-catalog'), $prev_post->post_title); ?></span>
                                        <span aria-hidden="true"><?php echo esc_html($prev_post->post_title); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($next_post) : ?>
                                <div class="seed-catalog-nav-next">
                                    <span class="nav-subtitle" aria-hidden="true"><?php _e('Next Seed', 'seed-catalog'); ?></span>
                                    <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" rel="next">
                                        <span class="screen-reader-text"><?php echo sprintf(__('Next seed: %s', 'seed-catalog'), $next_post->post_title); ?></span>
                                        <span aria-hidden="true"><?php echo esc_html($next_post->post_title); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>

                    <?php
                    // Related seeds in the same category
                    $terms = get_the_terms(get_the_ID(), 'seed_category');
                    if ($terms && !is_wp_error($terms)) {
                        $term_ids = wp_list_pluck($terms, 'term_id');
                        
                        $args = array(
                            'post_type' => 'seed',
                            'posts_per_page' => 3,
                            'post__not_in' => array(get_the_ID()),
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'seed_category',
                                    'field' => 'term_id',
                                    'terms' => $term_ids,
                                ),
                            ),
                        );
                        
                        $related_query = new WP_Query($args);
                        
                        if ($related_query->have_posts()) :
                        ?>
                            <div class="seed-catalog-related" role="complementary" aria-label="<?php esc_attr_e('Related seeds', 'seed-catalog'); ?>">
                                <h2><?php _e('Related Seeds', 'seed-catalog'); ?></h2>
                                <div class="seed-catalog-grid seed-catalog-columns-3" role="list">
                                    <?php
                                    while ($related_query->have_posts()) : $related_query->the_post();
                                        $seed_name = get_post_meta(get_the_ID(), 'seed_name', true);
                                        $seed_variety = get_post_meta(get_the_ID(), 'seed_variety', true);
                                    ?>
                                        <article class="seed-catalog-item" role="listitem">
                                            <div class="seed-catalog-item-inner">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <div class="seed-catalog-item-image">
                                                        <a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(sprintf(__('View details for %s', 'seed-catalog'), get_the_title())); ?>">
                                                            <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="seed-catalog-item-content">
                                                    <h3 class="seed-catalog-item-title">
                                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                    </h3>

                                                    <?php if (!empty($seed_name)) : ?>
                                                        <div class="seed-catalog-item-meta seed-name">
                                                            <span class="meta-label"><?php esc_html_e('Seed:', 'seed-catalog'); ?></span>
                                                            <span class="meta-value"><?php echo esc_html($seed_name); ?></span>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($seed_variety)) : ?>
                                                        <div class="seed-catalog-item-meta seed-variety">
                                                            <span class="meta-label"><?php esc_html_e('Variety:', 'seed-catalog'); ?></span>
                                                            <span class="meta-value"><?php echo esc_html($seed_variety); ?></span>
                                                        </div>
                                                    <?php endif; ?>

                                                    <a href="<?php the_permalink(); ?>" class="seed-catalog-read-more" aria-label="<?php echo esc_attr(sprintf(__('Read more about %s', 'seed-catalog'), get_the_title())); ?>">
                                                        <?php esc_html_e('View Details', 'seed-catalog'); ?>
                                                        <span class="screen-reader-text"><?php echo sprintf(__('about %s', 'seed-catalog'), get_the_title()); ?></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endwhile; ?>
                                </div>
                                <?php wp_reset_postdata(); ?>
                            </div>
                        <?php endif; ?>
                    <?php } ?>
                </footer>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>