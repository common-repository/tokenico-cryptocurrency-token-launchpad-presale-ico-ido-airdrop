<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="presale-row">
    <div class="presale-col presale-col-content">
        <div class="presale-content" data-presale-key="<?php echo esc_attr($presale->key) ?>">
            <?php $viewEcho('presale/real-content', compact('presale', 'token')); ?>
        </div>
        <t-powered-by></t-powered-by>  
    </div>
</div>