<?php 
    if ( ! defined( 'ABSPATH' ) ) exit; 
    use BeycanPress\TokenicoLite\Settings;
    use BeycanPress\TokenicoLite\PluginHero\Helpers;
?>
<?php $status = $presale->getStatus(); ?>
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
<?php $percent = $presale->totalSales * 100 / $presale->totalSaleLimit; ?>
<div class="progress-bar">
    <div class="bar" style="width: <?php echo esc_html($percent); ?>%"></div>
    <div class="percent">
        <?php echo esc_html(Settings::get('progressShowingStyle') == 'token-based' ? ($presale->getStaticSaleTokenBetween() . ' ' . esc_html__('SOLD', 'tokenico')) : ($presale->getStaticSaleNativeBetween()) . ' ' . esc_html__('RAISED', 'tokenico')); ?>
    </div>
</div>
<div class="infos">
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
            <?php echo esc_html__('Exchange Rate: ', 'tokenico'); ?>
        </div>
        <div class="value">
            <?php echo esc_html($presale->getStaticNativeRate()); ?>
        </div>
    </div>
    <div class="clear"></div>
</div>
<?php if ($status == 'started') : ?>
    <div class="process-area-loading">
        <div><?php echo esc_html__('Loading...', 'tokenico'); ?></div>
    </div>
    <div class="process-area">
        <div class="wrapper">
            <div class="box">
                <label for="coinAmount"><?php echo sprintf(esc_html__('Amount in %s you pay', 'tokenico'), esc_html($presale->getNativeCoinSymbol())) ?></label>
                <input type="number" name="coinAmount" id="coinAmount" value="<?php echo esc_attr($presale->minContribution) ?>" min="<?php echo esc_attr($presale->minContribution) ?>" max="<?php echo esc_attr($presale->maxContribution) ?>">
            </div>
            <div class="box">
                <label for="tokenAmount"><?php echo sprintf(esc_html__('Amount in %s you receive', 'tokenico'), esc_html($presale->getTokenSymbol())) ?></label>
                <input type="number" name="tokenAmount" id="tokenAmount" value="<?php echo esc_attr($presale->calculateMinTokenAmount()) ?>" min="<?php echo esc_attr($presale->calculateMinTokenAmount()) ?>" max="<?php echo esc_attr($presale->calculateMaxTokenAmount()) ?>">
            </div>
        </div>
        <div class="t-button t-buy-now" data-presale-key="<?php echo esc_attr($presale->key) ?>">
            <?php echo esc_html__('Buy now', 'tokenico') ?>
        </div>
    </div>
<?php elseif ($status == 'ended' && !$presale->instantTransfer): ?>
    <div class="process-area-loading">
        <div><?php echo esc_html__('Loading...', 'tokenico'); ?></div>
    </div>
    <div class="process-area">
        <div class="t-button t-claim" data-presale-key="<?php echo esc_attr($presale->key) ?>">
            <?php echo esc_html__('Claim', 'tokenico') ?>
        </div>
    </div>
<?php endif; ?>
<br>
<div>
    <?php echo esc_html__('Times are in UTC time zone!', 'tokenico'); ?>
</div>
<div class="copy-token-address t-button" data-address="<?php echo esc_attr($token->address); ?>">
    <?php echo esc_html__('Copy token address', 'tokenico'); ?>
</div>