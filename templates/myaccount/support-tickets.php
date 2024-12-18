<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template: Support Tickets
 * /my-account/support-tickets
 */
?>

<div class="wpsm-support-tickets">
    <!-- Ticket List -->
    <?php if (!empty($tickets)) : ?>
        <h3><?php _e('Your Support Tickets', 'woo-product-support'); ?></h3>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th><?php _e('Ticket', 'woo-product-support'); ?></th>
                    <th><?php _e('Product', 'woo-product-support'); ?></th>
                    <th><?php _e('Status', 'woo-product-support'); ?></th>
                    <th><?php _e('Date', 'woo-product-support'); ?></th>
                    <th><?php _e('Actions', 'woo-product-support'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket) : 
                    $product_id = get_post_meta($ticket->ID, '_ticket_product_id', true);
                    $product = wc_get_product($product_id);
                    $status = get_post_status_object(get_post_status($ticket->ID));
                    ?>
                    <tr>
                        <td>
                            <?php echo esc_html($ticket->post_title); ?>
                        </td>
                        <td>
                            <?php echo $product ? esc_html($product->get_name()) : ''; ?>
                        </td>
                        <td>
                            <?php echo esc_html($status->label); ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($ticket->post_date))); ?>
                        </td>
                        <td>
                        <a href="<?php echo esc_url(add_query_arg('ticket_id', $ticket->ID, wc_get_account_endpoint_url('support-tickets'))); ?>" class="button view">
                            <strong><?php _e('View', 'woo-product-support'); ?></strong>
                        </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php echo $max_num_pages; ?>

        <?php if ($max_num_pages > 1) : ?>
            <nav class="woocommerce-pagination">
                <?php
                    $base = wc_get_account_endpoint_url('support-tickets');
                    echo paginate_links(array(
                        'base' => $base . '%_%',
                        'format' => '?support_ticket_page=%#%',
                        'current' => $current_page,
                        'total' => $max_num_pages,
                        'prev_text' => '&larr;',
                        'next_text' => '&rarr;',
                        'type' => 'list',
                        'end_size' => 3,
                        'mid_size' => 3
                    ));
                ?>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <!-- New Ticket Form -->
    <h3><?php _e('Submit New Support Ticket', 'woo-product-support'); ?></h3>
    
    <?php 
    $purchased_products = WPSM_My_Account::get_customer_purchased_products();
    
    if (empty($purchased_products)) : ?>
        <p><?php _e('You need to purchase products before you can submit a support ticket.', 'woo-product-support'); ?></p>
    <?php else : ?>
        <form method="post" class="wpsm-new-ticket-form">
            <?php wp_nonce_field('wpsm_submit_ticket', 'wpsm_ticket_nonce'); ?>
            
            <p class="form-row">
                <label for="ticket_subject"><?php _e('Subject', 'woo-product-support'); ?> <span class="required">*</span></label>
                <input type="text" name="ticket_subject" id="ticket_subject" class="input-text" required>
            </p>
            
            <p class="form-row">
                <label for="ticket_product"><?php _e('Related Product', 'woo-product-support'); ?> <span class="required">*</span></label>
                <select name="ticket_product" id="ticket_product" class="select" required>
                    <option value=""><?php _e('Select a product', 'woo-product-support'); ?></option>
                    <?php foreach ($purchased_products as $product) : ?>
                        <option value="<?php echo esc_attr($product['id']); ?>">
                            <?php echo esc_html($product['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <p class="form-row">
                <label for="ticket_priority"><?php _e('Priority', 'woo-product-support'); ?></label>
                <select name="ticket_priority" id="ticket_priority" class="select">
                    <option value="low"><?php _e('Low', 'woo-product-support'); ?></option>
                    <option value="medium" selected><?php _e('Medium', 'woo-product-support'); ?></option>
                    <option value="high"><?php _e('High', 'woo-product-support'); ?></option>
                    <option value="urgent"><?php _e('Urgent', 'woo-product-support'); ?></option>
                </select>
            </p>
            
            <p class="form-row">
                <label for="ticket_message"><?php _e('Message', 'woo-product-support'); ?> <span class="required">*</span></label>
                <textarea name="ticket_message" id="ticket_message" class="input-text" rows="5" required></textarea>
            </p>
            
            <p class="form-row">
                <button type="submit" class="button" name="wpsm_submit_ticket" value="submit">
                    <?php _e('Submit Ticket', 'woo-product-support'); ?>
                </button>
            </p>
        </form>
    <?php endif; ?>
</div>