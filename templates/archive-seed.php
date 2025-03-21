<?php
/**
 * Template for displaying seed archives
 * Implements WCAG 2.1 accessibility standards
 */
get_header();
?>

<div class="seed-catalog-archive" role="main">
    <div class="seed-catalog-container">
        <header class="seed-catalog-archive-header">
            <?php if (is_tax('seed_category')) : ?>
                <h1 class="seed-catalog-archive-title"><?php single_term_title(); ?></h1>
                <?php the_archive_description('<div class="seed-catalog-archive-description" role="complementary">', '</div>'); ?>
            <?php else : ?>
                <h1 class="seed-catalog-archive-title"><?php esc_html_e('Seed Catalog', 'seed-catalog'); ?></h1>
            <?php endif; ?>
        </header>

        <div class="seed-catalog-filter-section" role="search" aria-label="<?php esc_attr_e('Seed catalog filters', 'seed-catalog'); ?>">
            <?php echo do_shortcode('[seed_search]'); ?>
            <?php echo do_shortcode('[seed_categories style="dropdown"]'); ?>
        </div>

        <?php if (have_posts()) : ?>
            <div class="seed-catalog-grid seed-catalog-columns-3" role="list">
                <?php
                while (have_posts()) : the_post();
                    $seed_name = get_post_meta(get_the_ID(), 'seed_name', true);
                    $seed_variety = get_post_meta(get_the_ID(), 'seed_variety', true);
                    $days_to_maturity = get_post_meta(get_the_ID(), 'days_to_maturity', true);
                    $sunlight_needs = get_post_meta(get_the_ID(), 'sunlight_needs', true);
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
                                <h2 class="seed-catalog-item-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

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

                                <?php if (!empty($days_to_maturity)) : ?>
                                    <div class="seed-catalog-item-meta maturity">
                                        <span class="meta-label"><?php esc_html_e('Days to Maturity:', 'seed-catalog'); ?></span>
                                        <span class="meta-value"><?php echo esc_html($days_to_maturity); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($sunlight_needs)) : ?>
                                    <div class="seed-catalog-item-meta sunlight">
                                        <strong><?php esc_html_e('Sunlight:', 'seed-catalog'); ?></strong>
                                        <?php echo esc_html($sunlight_needs); ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $terms = get_the_terms(get_the_ID(), 'seed_category');
                                if (!empty($terms) && !is_wp_error($terms)) :
                                ?>
                                    <div class="seed-catalog-item-categories">
                                        <?php
                                        $term_links = array();
                                        foreach ($terms as $term) {
                                            $term_links[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                                        }
                                        echo implode(', ', $term_links);
                                        ?>
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

            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => sprintf(
                    '<span class="nav-prev-text screen-reader-text">%s</span>%s',
                    __('Previous page', 'seed-catalog'),
                    '<span aria-hidden="true">&larr;</span>'
                ),
                'next_text' => sprintf(
                    '<span class="nav-next-text screen-reader-text">%s</span>%s',
                    __('Next page', 'seed-catalog'),
                    '<span aria-hidden="true">&rarr;</span>'
                ),
            ));
            ?>

        <?php else : ?>
            <p class="seed-catalog-no-seeds" role="alert"><?php esc_html_e('No seeds found.', 'seed-catalog'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>