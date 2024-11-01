<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="<?php echo esc_attr($endOrStart); ?>-countdown countdown" data-date="<?php echo esc_attr($date); ?>">
    <p class="timer">
        <span class="days"><?php echo esc_html($diff->days); ?></span>:<?php echo wp_kses_post($diff->format('<span class="hours">%H</span>:<span class="minutes">%I</span>:<span class="seconds">%S</span>')); ?>
    </p>
</div>