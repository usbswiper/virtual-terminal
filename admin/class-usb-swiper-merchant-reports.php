<?php

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * The Merchant_Report_Table class is responsible for Users table lists.
 *
 * @since 3.2.2
 */
class Merchant_Report_Table extends WP_List_Table {

    public float $total_volume = 0;
    public float $total_amex_volume = 0;
    public int $per_page = 20;
    public int $current_page = 1;

	public function __construct() {

        $this->current_page = !empty( $_GET['paged'] ) ? (int)$_GET['paged'] : 1;

		parent::__construct();
	}

	/**
	 * Get the columns titles.
	 *
	 * @since 3.2.2
	 *
	 * @return array
	 */
	public function get_columns() {

		return [
			'merchant_name' => __('Merchant Name', 'usb-swiper'),
			'month'         => __('Month', 'usb-swiper'),
			'total_volume'  => __('Total Volume', 'usb-swiper'),
			'amex_volume'   => __('Amex Volume', 'usb-swiper'),
		];
	}

	/**
     * Get default columns value.
     *
     * @since 3.2.2
     *
	 * @param array $item get item data
	 * @param string $column_name get column name
	 * @return int|string|true
	 */
	protected function column_default($item, $column_name) {

		switch ($column_name) {
			case 'merchant_name':
                $merchant_id = !empty( $item[$column_name] ) ? esc_html( $item[$column_name] ) : 0;
                $user_info = get_user_by('id', $merchant_id);
				return !empty( $user_info->display_name ) ? esc_html( $user_info->display_name ) : '';
			case 'month':
				return !empty( $item[$column_name] ) ? esc_html( $item[$column_name] ) : '';
			case 'total_volume':
			case 'amex_volume':
				return !empty( $item[$column_name] ) ? wc_price($item[$column_name]) : 0;
			default:
                return !empty( $item ) ? print_r($item, true) : '';
		}
	}

	/**
     * Get all merchant ids from transactions.
     *
     * @since 3.2.2
     *
	 * @param array $data Get merchant ids.
	 * @param int $paged Get current paged number.
	 * @return array $data.
	 */
    public function get_all_merchants_from_transactions( $data = [], $paged = 1 ) {

	    $transaction_results = new WP_Query([
		    'post_type'   => 'transactions',
		    'posts_per_page' => 20,
		    'paged' => $paged,
		    'post_status' => 'any',
	    ]);

	    if( !empty( $transaction_results->posts ) && is_array( $transaction_results->posts ) ) {

            foreach ( $transaction_results->posts as $transaction_result ) {
	            $data[] = $transaction_result->post_author;
            }
        }

	    if( !empty( $transaction_results->max_num_pages ) && $paged < $transaction_results->max_num_pages ) {
		    $paged = $paged + 1;
		    $data = $this->get_all_merchants_from_transactions( $data, $paged );
	    }

        return !empty( $data ) ? array_values(array_unique($data)) : [];
    }

	/**
     * Get all merchants lists.
     *
     * @since 3.2.2
     *
	 * @param string $fields Get merchants list fields.
	 * @return array $merchant_lists
	 */
	public function get_merchants( $fields ='' ) {

		$merchants = get_users([
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

        if( !empty( $merchants ) && !empty( $merchants_from_transactions )) {
            $merchants = array_merge( $merchants, $merchants_from_transactions );
	        $merchants = array_unique($merchants);
        }

        if( $fields === 'ID' ) {
            return $merchants;
        }

		$merchant_lists = [];
		if ( !empty( $merchants ) ) {
			foreach ($merchants as $merchant_id) {
				$merchant_info = get_user_by('ID', $merchant_id);

				$merchant_lists[] = [
					'id'           => $merchant_id,
					'display_name' => $merchant_info->display_name,
				];
			}
		}

		return $merchant_lists;
	}

	/**
     * Get merchant transactions by merchant id.
     *
     * @string 3.2.2
     *
	 * @param array $data Get transactions.
	 * @param int $paged Get current page number.
	 * @return array|int[]|WP_Post[]
	 */
    public function get_merchant_transaction_by_id( $data, $paged = 1 ) {

        $args = [
	        'post_type'   => 'transactions',
	        'meta_query'  => [
		        [
			        'key'     => '_payment_response',
			        'compare' => 'EXISTS'
		        ]
	        ],
	        'posts_per_page' => 20,
	        'paged' => $paged,
	        'post_status' => ['publish','future'],
        ];

        if( !empty( $_GET['merchant'] ) ) {
            $args['author__in'] = esc_attr( $_GET['merchant'] );
        } else {
	        $args['author__in'] = $this->get_merchants('ID');
        }

        if( !empty( $_GET['start_date'] ) && !empty( $_GET['end_date'] ) ) {

            $args['date_query'] = [
	            [
		            'after'     => esc_attr( $_GET['start_date'] ),
		            'before'    => esc_attr( $_GET['end_date'] ),
                    'compare'   => 'BETWEEN',
		            'inclusive'   => true,
                ]
            ];
        }

	    $transaction_results = new WP_Query($args);

	    $data = !empty( $transaction_results->posts ) ? array_merge( $data, $transaction_results->posts ) : $data;

	    if( !empty( $transaction_results->max_num_pages ) && $paged < $transaction_results->max_num_pages ) {
		    $paged = $paged + 1;
		    $data = $this->get_merchant_transaction_by_id( $data, $paged );
	    }

	    return $data;
    }

	/**
	 * Prepare the table html items.
	 *
	 * @since 3.2.2
	 *
	 * @return void
	 */
	public function prepare_items() {

        $transactions = $this->get_merchant_transaction_by_id([]);

        $items = [];
        if( !empty( $transactions ) && is_array( $transactions ) ){

            foreach ( $transactions as $transaction ) {

	            $transaction_id = !empty($transaction->ID) ? $transaction->ID : 0;
                $post_author = !empty($transaction->post_author) ? $transaction->post_author : 0;
                $post_date = !empty($transaction->post_date) ? $transaction->post_date : '';
	            $transaction_status = usbswiper_get_transaction_status($transaction_id);
	            $grand_total = get_post_meta( $transaction_id, 'GrandTotal', true );
	            $grand_total = !empty( $grand_total ) ? $grand_total : 0;
	            $payment_response = get_post_meta($transaction_id, '_payment_response', true);
	            $payment_data = maybe_unserialize($payment_response);
	            $unique_key = $post_author.'_'.date('Y-m',strtotime($post_date));

                $amex_amount = 0;
	            if (isset($payment_data['payment_source']['card']['brand']) && $payment_data['payment_source']['card']['brand'] === 'AMEX') {
		            $amex_amount = $grand_total;
	            }

                if( !empty( $transaction_status ) &&  in_array( strtolower($transaction_status), ['paid','completed'])  ) {

	                $this->total_volume = floatval($this->total_volume) + floatval($grand_total);
	                $this->total_amex_volume = floatval($this->total_amex_volume) + floatval($amex_amount);

	                if( !empty( $items[$unique_key]  )) {
                        $temp_total_volume = !empty( $items[$unique_key]['total_volume'] ) ? $items[$unique_key]['total_volume'] : 0;
                        $temp_amex_volume = !empty( $items[$unique_key]['amex_volume'] ) ? $items[$unique_key]['amex_volume'] : 0;
		                $items[$unique_key]['total_volume'] = floatval( $temp_total_volume ) + floatval( $grand_total );
		                $items[$unique_key]['amex_volume'] = floatval( $temp_amex_volume ) + floatval( $amex_amount );
	                } else {
		                $items[$unique_key] = [
			                'total_volume' => floatval($grand_total),
			                'amex_volume' => floatval($amex_amount),
			                'month' => date('Y-m',strtotime($post_date)),
			                'merchant_name' => $post_author,
		                ];
	                }
                }
            }
        }

		$columns = $this->get_columns();
		$hidden = [];
		$sortable = [];

        $this->set_pagination_args( [
			'total_items' => !empty( $items ) ? count( $items ) : 0,
			'per_page'    => $this->per_page
        ]);

		$this->_column_headers = [$columns, $hidden, $sortable];

        if( !empty( $items ) && count( $items ) > $this->per_page ) {
	        $offset = $this->per_page * ( $this->current_page - 1);
	        $items = array_slice($items, $offset, $this->per_page);
        }

		$this->items = $items;
	}

	/**
     * Display custom filter.
     *
     * @since 3.2.2
     *
	 * @param string $which Filter position
	 * @return void
	 */
	public function extra_tablenav($which) {

		if ( !empty( $which )  && 'top' === $which ) {

			$get_merchants = $this->get_merchants();
			?>
			<div class="alignleft actions">
				<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="GET">
					<input type="hidden" name="page" value="usb-swiper">
					<input type="hidden" name="tab" value="reports">

					<select id="merchant" name="merchant">
						<option value=""><?php _e('All Merchants', 'usb-swiper'); ?></option>
						<?php
						if( !empty( $get_merchants ) && is_array( $get_merchants ) ) :
							foreach ( $get_merchants as $merchant) : ?>
								<option <?php echo !empty( $_GET['merchant'] ) ? selected( esc_attr( $_GET['merchant'] ), esc_attr( $merchant['id'] ), false) : ''; ?> value="<?php echo esc_attr( $merchant['id'] ); ?>"><?php echo esc_html( $merchant['display_name'] ); ?></option>
							<?php endforeach;
						endif;
						?>
					</select>
					<label for="start_date"><?php _e('Start Date:', 'usb-swiper'); ?></label>
					<input type="text" autocomplete="off" id="start_date" name="start_date" value="<?php echo !empty( $_GET['start_date'] ) ? esc_attr( $_GET['start_date'] ) : ''; ?>" />
					<label for="end_date"><?php _e('End Date:', 'usb-swiper'); ?></label>
					<input type="text" autocomplete="off" id="end_date" name="end_date" value="<?php echo !empty( $_GET['end_date'] ) ? esc_attr( $_GET['end_date'] ) : ''; ?>" />
					<input type="submit" value="<?php _e('Filter', 'usb-swiper'); ?>" class="button button-primary">
					<span class="total-volume">
						<?php echo sprintf( __('Total Volume: <strong>%s</strong>', 'usb-swiper'), wc_price($this->total_volume) ); ?>
                    </span>
					<span class="total-volume amex">
						<?php echo sprintf( __('Total Amex Volume: <strong>%s</strong>', 'usb-swiper'), wc_price($this->total_amex_volume) ); ?>
                    </span>
				</form>
			</div>
			<?php
		}
	}
}
