<?php 
    if ( ! defined( 'ABSPATH' ) ) exit; 
    use BeycanPress\TokenicoLite\Entity\Presale;
    use BeycanPress\TokenicoLite\PluginHero\Helpers;
?>

<?php if ($presales->have_posts()) : ?>
    <?php foreach($presales->posts as $presale) : 
        $presale = new Presale(absint($presale->ID));
        $token = json_decode($presale->token);
        $status = $presale->getStatus();
        ?>
        <div class="presale-col">
            <div class="presale-item">
                <div class="title">
                    <h3>
                        <?php echo esc_html(get_the_title($presale->ID)); ?>
                    </h3>
                </div>
                <div class="start-status">
                    <?php if ($status == 'started') { ?>
                        <?php echo esc_html__('Sale Ends In', 'tokenico') ?>
                        <?php echo wp_kses_post(Helpers::runFunc('getTimer', $presale->endDate, 'end', 2)); ?>
                    <?php } elseif ($status == 'ended') {
                            echo esc_html__('Presale: Ended', 'tokenico');   
                    } else { ?>
                        <?php echo esc_html__('Sale Starts In', 'tokenico') ?>
                        <?php echo wp_kses_post(Helpers::runFunc('getTimer', $presale->startDate, 'start', 2)); ?>
                    <?php } ?>
                </div>
                <div class="infos">
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('Network: ', 'tokenico'); ?>
                        </div>
                        <div class="value hide-text">
                            <?php echo esc_html($presale->getNetworkName()); ?>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('Total sale limit: ', 'tokenico'); ?>
                        </div>
                        <div class="value">
                            <?php echo esc_html($presale->getTotalSaleLimit()); ?>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('Remaining limit: ', 'tokenico'); ?>
                        </div>
                        <div class="value">
                            <?php echo esc_html($presale->getRemainingLimit()); ?>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('Min contribution: ', 'tokenico'); ?>
                        </div>
                        <div class="value">
                            <?php echo esc_html($presale->getMinContribution()); ?>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('Max contribution: ', 'tokenico'); ?>
                        </div>
                        <div class="value">
                            <?php echo esc_html($presale->getMaxContribution()); ?>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('Exchange rate: ', 'tokenico'); ?>
                        </div>
                        <div class="value">
                            <?php echo esc_html($presale->getExchangeRate()); ?>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('Start date: ', 'tokenico'); ?>
                        </div>
                        <div class="value">
                            <?php echo esc_html($presale->getStartDate()); ?>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">
                            <?php echo esc_html__('End date: ', 'tokenico'); ?>
                        </div>
                        <div class="value">
                            <?php echo esc_html($presale->getEndDate()); ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <a href="<?php echo esc_url(get_the_permalink($presale->ID)); ?>" class="t-button review-btn">
                    <?php echo esc_html__('Review', 'tokenico'); ?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="not-found">
        <?php echo esc_html__('Not found presale!', 'tokenico'); ?>
    </div>
<?php endif; ?>