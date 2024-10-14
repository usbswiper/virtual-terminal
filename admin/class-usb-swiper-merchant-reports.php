<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * The Merchant_Report_Table class is responsible for displaying the merchants table.
 *
 * @since 3.2.2
 */
class Merchant_Report_Table extends WP_List_Table {

    public float $total_volume = 0;
    public float $total_amex_volume = 0;
    public int $per_page = 20;
    public int $current_page;

    public function __construct() {
        // Set the current page for pagination.
        $this->current_page = !empty($_GET['paged']) ? (int)$_GET['paged'] : 1;
        parent::__construct(); // Call parent constructor.
    }

    /**
     * Get the columns for the table.
     *
     * @since 3.2.2
     * @return array
     */
    public function get_columns() {
        return [
            'merchant_name' => __('Merchant Name', 'usb-swiper'),
            'total_volume'  => __('Total Volume', 'usb-swiper'),
            'amex_volume'   => __('Amex Volume', 'usb-swiper'),
        ];
    }

    /**
     * Handle the default column values.
     *
     * @since 3.2.2
     * @param array $item The item data.
     * @param string $column_name The column name.
     * @return string
     */
    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'merchant_name':
                return esc_html($item['merchant_name']);
            case 'total_volume':
            case 'amex_volume':
                return !empty($item[$column_name]) ? wc_price($item[$column_name]) : wc_price(0);
            default:
                return print_r($item, true);
        }
    }

    /**
     * Get all merchants involved in transactions.
     *
     * @since 3.2.2
     * @param array $data The merchant data.
     * @param int $paged The current page number.
     * @return array
     */
    public function get_all_merchants_from_transactions($data = [], $paged = 1) {
        $transaction_results = new WP_Query([
            'post_type'   => 'transactions',
            'posts_per_page' => 20,
            'paged' => $paged,
            'post_status' => 'any',
        ]);

        if (!empty($transaction_results->posts) && is_array($transaction_results->posts)) {
            foreach ($transaction_results->posts as $transaction_result) {
                $data[] = $transaction_result->post_author;
            }
        }

        if ($paged < $transaction_results->max_num_pages) {
            $paged++;
            $data = $this->get_all_merchants_from_transactions($data, $paged);
        }

        return !empty($data) ? array_values(array_unique($data)) : [];
    }

    /**
     * Get all merchants.
     *
     * @since 3.2.2
     * @param string $fields The fields to retrieve.
     * @return array
     */
    public function get_merchants($fields = '') {
        $merchants = get_users([ // Get merchants with onboarding response.
            'meta_query' => [
                [
                    'key'     => '_merchant_onboarding_response',
                    'value'   => '',
                    'compare' => '!='
                ]
            ],
            'fields' => 'ID'
        ]);

        $merchants = array_unique($merchants);
        $merchants_from_transactions = $this->get_all_merchants_from_transactions();

        if (!empty($merchants_from_transactions)) {
            $merchants = array_merge($merchants, $merchants_from_transactions);
            $merchants = array_unique($merchants);
        }

        // Prepare merchant lists with display names.
        if ($fields === 'ID') {
            return $merchants;
        }

        $merchant_lists = [];
        foreach ($merchants as $merchant_id) {
            $merchant_info = get_user_by('ID', $merchant_id);
            $merchant_lists[] = [
                'id'           => $merchant_id,
                'display_name' => !empty($merchant_info->display_name) ? $merchant_info->display_name : '',
            ];
        }

        return $merchant_lists;
    }

    /**
     * Get transactions for merchants by ID.
     *
     * @since 3.2.2
     * @return array
     */
    public function get_merchant_transaction_by_id($merchant_id = '', $start_date = '', $end_date = '') {
        global $wpdb;

        $query = $wpdb->prepare("SELECT 
            u.ID AS user_id, 
            SUBSTRING_INDEX(SUBSTRING_INDEX(onboard.meta_value, 's:11:\"merchant_id\";s:13:\"', -1), '\";', 1) AS merchant_id,
            u.display_name, 
            SUM(pm.meta_value) AS total_transactions,  -- Total transaction volume
            SUM(CASE WHEN amex.meta_value = 'AMEX' THEN pm.meta_value ELSE 0 END) AS amex_transactions  -- AMEX-specific volume
        FROM $wpdb->users u
        JOIN $wpdb->usermeta um ON u.ID = um.user_id
        JOIN $wpdb->posts p ON u.ID = p.post_author
        JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        JOIN $wpdb->usermeta onboard ON u.ID = onboard.user_id AND onboard.meta_key = '_merchant_onboarding_response'
        JOIN (
            SELECT post_id 
            FROM $wpdb->postmeta 
            WHERE meta_key = '_payment_status' 
              AND meta_value IN ('completed', 'paid')
        ) AS payment_statuses ON p.ID = payment_statuses.post_id
        JOIN (
            SELECT post_id
            FROM $wpdb->postmeta
            WHERE meta_key = '_transaction_type'
              AND meta_value != 'Zettle'
        ) AS zettle_transactions ON p.ID = zettle_transactions.post_id
        LEFT JOIN $wpdb->postmeta amex ON p.ID = amex.post_id AND amex.meta_key = '_payment_card_brand' AND amex.meta_value = 'AMEX'
        WHERE um.meta_key = '_merchant_onboarding_response'
          AND um.meta_value LIKE '%\"country\";s:2:\"US\"%'
          AND p.post_type = 'transactions'
          AND p.post_status = 'publish'
          AND p.post_date BETWEEN '$start_date' AND '$end_date'
          AND pm.meta_key = 'GrandTotal'
        " . ($merchant_id !== '' ? "AND u.ID = $merchant_id" : "") . "
        GROUP BY u.ID, merchant_id
        ORDER BY amex_transactions DESC, total_transactions DESC");

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Prepare items for the table.
     *
     * @since 3.2.2
     */
    public function prepare_items() {
        $merchant_id = !empty($_GET['merchant']) ? (int)sanitize_text_field($_GET['merchant']) : '';
        $current_date = date('Y-m-d');
        $last_30_days_date = date('Y-m-d', strtotime('-30 days', strtotime($current_date)));
        $start_date = !empty($_GET['start_date']) ? date('Y-m-d', strtotime(sanitize_text_field($_GET['start_date']))) : $last_30_days_date;
        $end_date = !empty($_GET['end_date']) ? date('Y-m-d', strtotime(sanitize_text_field($_GET['end_date']))) : $current_date;

        $transactions = $this->get_merchant_transaction_by_id($merchant_id, $start_date, $end_date);

        $items = [];
        if( !empty($transactions) ){
            foreach ($transactions as $transaction) {
                $date = !empty($transaction['post_date']) ? date('Y-m', strtotime($transaction['post_date'])) : date('Y-m');

                $unique_key = $transaction['user_id'] . '_' . $date;
                $total_volume = $transaction['total_transactions'] ?: 0;
                $amex_volume = $transaction['amex_transactions'] ?: 0;

                $this->total_volume += $total_volume;
                $this->total_amex_volume += $amex_volume;

                $items[$unique_key] = [
                    'merchant_name' => $transaction['display_name'],
                    'total_volume'  => $total_volume,
                    'amex_volume'   => $amex_volume,
                ];
            }
        }

        $this->items = $items;

        // Setup pagination.
        $columns  = $this->get_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, [], $sortable];

        $this->set_pagination_args([
            'total_items' => count($this->items),
            'per_page'    => $this->per_page,
            'total_pages' => ceil(count($this->items) / $this->per_page)
        ]);

        // Slice items for pagination.
        $this->items = array_slice($this->items, ($this->current_page - 1) * $this->per_page, $this->per_page);
    }

    /**
     * Display filters above the table.
     *
     * @since 3.2.2
     * @param string $which Position of the tablenav.
     */
    public function extra_tablenav($which) {
        if ($which === 'top') {
            $current_date = date('Y-m-d');
            $last_30_days_date = date('Y-m-d', strtotime('-30 days', strtotime($current_date)));
            $start_date = !empty($_GET['start_date']) ? date('Y-m-d', strtotime(sanitize_text_field($_GET['start_date']))) : $last_30_days_date;
            $end_date = !empty($_GET['end_date']) ? date('Y-m-d', strtotime(sanitize_text_field($_GET['end_date']))) : $current_date;
            $merchants = $this->get_merchants();
            ?>
            <form class="alignleft actions">
                <input name="page" type="hidden" value="usb-swiper">
                <input name="tab" type="hidden" value="reports">
                <input name="subtab" type="hidden" value="amex">
                <select name="merchant">
                    <option value=""><?php esc_html_e('All Merchants', 'usb-swiper'); ?></option>
                    <?php foreach ($merchants as $merchant) : ?>
                        <option value="<?php echo esc_attr($merchant['id']); ?>" <?php selected($merchant['id'], sanitize_text_field($_GET['merchant'])); ?>>
                            <?php echo esc_html($merchant['display_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="report_start_date"><?php esc_html_e('Start Date', 'usb-swiper'); ?></label>
                <input type="text" autocomplete="off" id="report_start_date" name="start_date" value="<?php echo !empty( $start_date ) ? esc_attr( $start_date ) : ''; ?>" />
                <label for="report_end_date"><?php esc_html_e('End Date', 'usb-swiper'); ?></label>
                <input type="text" autocomplete="off" id="report_end_date" name="end_date" value="<?php echo !empty( $end_date ) ? esc_attr( $end_date ) : ''; ?>" />
                <?php submit_button(__('Filter', 'usb-swiper'), 'button', 'filter_action', false); ?>
                <label for="total_volume"><strong><?php esc_html_e('Total Volume : ', 'usb-swiper'); ?></strong><?php echo wc_price($this->total_volume); ?></label>
                <label for="total_volume"><strong><?php esc_html_e('Total AMEX Volume : ', 'usb-swiper'); ?></strong><?php echo wc_price($this->total_amex_volume); ?></label>
            </form>
            <?php
        }
    }

    /**
     * Get the sortable columns.
     *
     * @since 3.2.2
     * @return array
     */
    protected function get_sortable_columns() {
        return [
            'merchant_name' => ['merchant_name', true],
            'total_volume'  => ['total_volume', false],
            'amex_volume'   => ['amex_volume', false],
        ];
    }
}
