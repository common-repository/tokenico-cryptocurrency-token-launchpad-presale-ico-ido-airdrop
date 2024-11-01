<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="<?php echo esc_attr($endOrStart); ?>-countdown countdown" data-date="<?php echo esc_attr($date); ?>">
    <p class="timer">
        <span class="days"><?php echo esc_html($diff->days); ?> D</span><?php echo wp_kses_post($diff->format('<span class="hours">%H H</span><span class="minutes">%I M</span><span class="seconds">%S S</span>')); ?>
    </p>
</div>