<?php
if (!defined('ABSPATH')) {
    exit;
}

$ticket_id = isset($_GET['ticket_id']) ? absint($_GET['ticket_id']) : 0;
$ticket = get_post($ticket_id);

// Security checks
if (!$ticket 
    || get_post_type($ticket) !== 'support_ticket' 
    || $ticket->post_author != get_current_user_id()) {
    ?>
    <div class="woocommerce-error">
        <?php _e('Invalid ticket or you don\'t have permission to view this ticket.', 'woo-product-support'); ?>
    </div>
    <?php
    return;
}

// Get ticket details
$product_id = get_post_meta($ticket_id, '_ticket_product_id', true);
$product = wc_get_product($product_id);
$priority = get_post_meta($ticket_id, '_ticket_priority', true);
$status = get_post_status_object(get_post_status($ticket));
$replies = get_comments(array(
    'post_id' => $ticket_id,
    'order' => 'ASC'
));
?>

<div class="wpsm-single-ticket">
    <!-- Back to tickets list -->
    <p class="wpsm-back-link">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('support-tickets')); ?>" class="button">
            <?php _e('← Back to Tickets', 'woo-product-support'); ?>
        </a>
    </p>

    <!-- Ticket header -->
    <div class="wpsm-ticket-header">
        <h2><?php echo esc_html($ticket->post_title); ?></h2>
        <div class="wpsm-ticket-meta">
            <span class="wpsm-ticket-status status-<?php echo esc_attr($status->name); ?>">
                <?php echo esc_html($status->label); ?>
            </span>
            <span class="wpsm-ticket-priority priority-<?php echo esc_attr($priority); ?>">
                <?php echo esc_html(ucfirst($priority)); ?>
            </span>
            <span class="wpsm-ticket-date">
                <?php 
                printf(
                    __('Created on %s', 'woo-product-support'),
                    date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($ticket->post_date))
                );
                ?>
            </span>
        </div>
    </div>

    <!-- Product information -->
    <?php if ($product) : ?>
    <div class="wpsm-product-info">
        <h3><?php _e('Related Product', 'woo-product-support'); ?></h3>
        <p>
            <a href="<?php echo esc_url($product->get_permalink()); ?>" target="_blank">
                <?php echo esc_html($product->get_name()); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>

    <!-- Original ticket message -->
    <div class="wpsm-ticket-message">
        <div class="wpsm-message-header">
            <span class="wpsm-message-author">
                <?php 
                $author = get_user_by('id', $ticket->post_author);
                echo get_avatar($author->ID, 40);
                echo esc_html($author->display_name);
                ?>
            </span>
            <span class="wpsm-message-date">
                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($ticket->post_date)); ?>
            </span>
        </div>
        <div class="wpsm-message-content">
            <?php echo wpautop(wp_kses_post($ticket->post_content)); ?>
        </div>
    </div>

    <!-- Replies -->
    <?php if (!empty($replies)) : ?>
        <div class="wpsm-ticket-replies">
            <h3><?php _e('Replies', 'woo-product-support'); ?></h3>
            <?php foreach ($replies as $reply) : ?>
                <div class="wpsm-reply <?php echo $reply->user_id ? 'customer-reply' : 'staff-reply'; ?>">
                    <div class="wpsm-message-header">
                        <span class="wpsm-message-author">
                            <?php 
                            echo get_avatar($reply->user_id, 40);
                            echo esc_html($reply->comment_author);
                            if (!$reply->user_id) {
                                echo ' <span class="staff-badge">' . __('Staff', 'woo-product-support') . '</span>';
                            }
                            ?>
                        </span>
                        <span class="wpsm-message-date">
                            <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reply->comment_date)); ?>
                        </span>
                    </div>
                    <div class="wpsm-message-content">
                        <?php echo wpautop(wp_kses_post($reply->comment_content)); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Reply form -->
    <?php if ($status->name !== 'ticket_resolved') : ?>
        <div class="wpsm-reply-form">
            <h3><?php _e('Add Reply', 'woo-product-support'); ?></h3>
            <form method="post">
                <?php wp_nonce_field('wpsm_add_reply', 'wpsm_reply_nonce'); ?>
                <input type="hidden" name="ticket_id" value="<?php echo esc_attr($ticket_id); ?>">
                <p class="form-row">
                    <label for="ticket_reply"><?php _e('Your Reply', 'woo-product-support'); ?> <span class="required">*</span></label>
                    <textarea name="ticket_reply" id="ticket_reply" class="input-text" rows="5" required></textarea>
                </p>
                <p class="form-row">
                    <button type="submit" class="button" name="wpsm_add_reply" value="true">
                        <?php _e('Submit Reply', 'woo-product-support'); ?>
                    </button>
                </p>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
.wpsm-single-ticket {
    max-width: 800px;
    margin: 0 auto;
}
.wpsm-ticket-header {
    margin-bottom: 30px;
}
.wpsm-ticket-meta {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}
.wpsm-ticket-status,
.wpsm-ticket-priority {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.9em;
}
.wpsm-ticket-status.status-ticket_open {
    background: #ffeeba;
    color: #856404;
}
.wpsm-ticket-status.status-ticket_in_progress {
    background: #b8daff;
    color: #004085;
}
.wpsm-ticket-status.status-ticket_resolved {
    background: #c3e6cb;
    color: #155724;
}
.wpsm-ticket-priority.priority-urgent {
    background: #f8d7da;
    color: #721c24;
}
.wpsm-ticket-priority.priority-high {
    background: #ffeeba;
    color: #856404;
}
.wpsm-message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.wpsm-message-author {
    display: flex;
    align-items: center;
    gap: 10px;
}
.wpsm-message-author img {
    border-radius: 50%;
}
.wpsm-message-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}
.staff-badge {
    background: #6c757d;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8em;
}
.staff-reply .wpsm-message-content {
    background: #e9ecef;
}
.wpsm-reply-form {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
}
.wpsm-reply-form textarea {
    width: 100%;
    min-height: 150px;
}
.wpsm-back-link {
    margin-bottom: 20px;
}
</style>