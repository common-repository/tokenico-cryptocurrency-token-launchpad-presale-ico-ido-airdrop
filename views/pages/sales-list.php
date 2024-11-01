<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html__('Sale list', 'tokenico'); ?>
    </h1>
    <hr class="wp-header-end">
    <?php $ksesEcho($table->renderWpTable()); ?>
</div>