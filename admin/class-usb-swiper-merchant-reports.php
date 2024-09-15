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
					'display_name' => !empty( $merchant_info->display_name ) ? $merchant_info->display_name : '',
				];
			}
		}

		return $merchant_lists;
	}

	/**
     * Get merchant transactions by merchant id.
     *
     * @since 3.2.2
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
            'fields' => 'ids'
        ];

        if( !empty( $_GET['merchant'] ) ) {
            $args['author__in'] = esc_attr( $_GET['merchant'] );
        } else {
	        $args['author__in'] = $this->get_merchants('ID');
        }

        $current_date = date('Y-m-d');
        $last_30_days_date = date('Y-m-d', strtotime('-30 days', strtotime($current_date)));
        $start_date = !empty($_GET['start_date']) ? date('Y-m-d', strtotime(esc_attr($_GET['start_date']))) : $last_30_days_date;
        $end_date = !empty($_GET['end_date']) ? date('Y-m-d', strtotime(esc_attr($_GET['end_date']))) : $current_date;

        $args['date_query'] = [
            [
                'after'     => $start_date,
                'before'    => $end_date,
                'compare'   => 'BETWEEN',
                'inclusive' => true,
            ],
        ];

        $transaction_results = new WP_Query($args);

        $data = !empty( $transaction_results->posts ) ? array_merge( $data, $transaction_results->posts ) : $data;

        if ( !empty( $transaction_results->max_num_pages ) && 10 < $transaction_results->max_num_pages ) {
            $paged++;
            //$data = $this->get_merchant_transaction_by_id( $data, $paged );
        }

        return $data;
    }

    /**
     * Get merchant transactions by merchant id.
     *
     * @since 3.2.2
     *
     * @param array $data Get transactions.
     * @param int $paged Get current page number.
     * @return array|int[]|WP_Post[]
     */
    public function get_ajax_merchant_transaction_by_id( $data, $paged = 1 ) {
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
            'fields' => 'ids'
        ];

        if( !empty( $data['merchant'] ) ) {
            $args['author__in'] = esc_attr( $data['merchant'] );
        }

        $current_date = date('Y-m-d');
        $last_30_days_date = date('Y-m-d', strtotime('-30 days', strtotime($current_date)));
        $start_date = !empty($data['start_date']) ? date('Y-m-d', strtotime(esc_attr($data['start_date']))) : $last_30_days_date;
        $end_date = !empty($data['end_date']) ? date('Y-m-d', strtotime(esc_attr($data['end_date']))) : $current_date;

        $args['date_query'] = [
            [
                'after'     => $start_date,
                'before'    => $end_date,
                'compare'   => 'BETWEEN',
                'inclusive' => true,
            ],
        ];

        $transaction_results = new WP_Query($args);

        $response['ids'] = !empty( $transaction_results->posts ) ? $transaction_results->posts : [];
        $response['max_page'] = $transaction_results->max_num_pages;

        return $response;
    }

    /**
     * Prepare items html.
     *
     * @since 3.2.2
     *
     * @param array $data Get the data.
     * @param int $page Get the page number.
     * @return array
     */
    public function prepare_ajax_items( $data, $page ) {

        $response = [
            'total_volume' => !empty($data['total_volume']) ? floatval($data['total_volume']) : 0,
            'amex_volume' => !empty($data['amex_volume']) ? floatval($data['amex_volume']) : 0,
            'max_page'  => 1,
            'found' => !empty($data['found']) ? $data['found'] : 0,
            'items' => !empty($data['items']) ? (array)$data['items'] : [],
        ];

        $end_count = $data['page'] * 20;
        $start_count = $end_count - 20;

        $merchant_report = $this->get_ajax_merchant_transaction_by_id($data, $page);
        $response['max_page'] = $merchant_report['max_page'];
        $html = '';
        if( !empty( $merchant_report ) && is_array( $merchant_report ) ){

            foreach ( $merchant_report['ids'] as $transaction_id ) {

                $transaction_status = usbswiper_get_transaction_status($transaction_id);
                $grand_total = get_post_meta( $transaction_id, 'GrandTotal', true );
                $grand_total = !empty( $grand_total ) ? $grand_total : 0;
                $payment_response = get_post_meta($transaction_id, '_payment_response', true);
                $payment_data = maybe_unserialize($payment_response);

                $amex_amount = 0;
                if (isset($payment_data['payment_source']['card']['brand']) && $payment_data['payment_source']['card']['brand'] === 'AMEX') {
                    $amex_amount = $grand_total;
                }

                if( !empty( $transaction_status ) &&  in_array( strtolower($transaction_status), ['paid','completed'])  ) {
                    $transaction = get_post($transaction_id);
                    $post_author_id = !empty($transaction->post_author) ? $transaction->post_author : 0;
                    $post_date = !empty($transaction->post_date) ? $transaction->post_date : '';
                    $user_info = get_user_by('id', $post_author_id);
                    $post_author = !empty( $user_info->display_name ) ? esc_html( $user_info->display_name ) : '';
                    $unique_key = $post_author.'_'.date('Y-m',strtotime($post_date));
                    $response['total_volume'] = floatval($response['total_volume']) + floatval($grand_total);
                    $response['amex_volume'] = floatval($response['amex_volume']) + floatval($amex_amount);
                    $response['found'] = (int)$response['found'] + 1;
                    $count = $count + 1;

                    if( !empty( $response['items'][$unique_key]  )) {
                        $temp_total_volume = !empty( $response['items'][$unique_key]['total_volume'] ) ? $response['items'][$unique_key]['total_volume'] : 0;
                        $temp_amex_volume = !empty( $response['items'][$unique_key]['amex_volume'] ) ? $response['items'][$unique_key]['amex_volume'] : 0;
                        $response['items'][$unique_key]['total_volume'] = floatval( $temp_total_volume ) + floatval( $grand_total );
                        $response['items'][$unique_key]['amex_volume'] = floatval( $temp_amex_volume ) + floatval( $amex_amount );
                    } else {
                        $response['items'][$unique_key] = [
                            'total_volume' => floatval($grand_total),
                            'amex_volume' => floatval($amex_amount),
                            'month' => date('Y-m',strtotime($post_date)),
                            'merchant_name' => $post_author,
                        ];
                    }
                }
            }
        }
        if( $page === $response['max_page'] ) {
            $response['total_volume'] = wc_price($response['total_volume']);
            $response['amex_volume'] = wc_price($response['amex_volume']);

            if( !empty($response['items']) && is_array($response['items']) ) {
                $count = 1;
                foreach($response['items'] as $item => $value) {
                    if( $count  >= $start_count && $count <= $end_count ) {
                        $month = !empty($value['month']) ? $value['month'] : '';
                        $total_volume = !empty($value['total_volume']) ? $value['total_volume'] : '';
                        $amex_volume = !empty($value['amex_volume']) ? $value['amex_volume'] : '';
                        $merchant_name = !empty($value['merchant_name']) ? $value['merchant_name'] : '';
                        $html .= '<tr data-id="'.$transaction_id.'" >';
                        $html .= '<td class="merchant_name column-merchant_name has-row-actions column-primary" data-colname="Merchant Name">'.$merchant_name.'</td>';
                        $html .= '<td class="month column-month" data-colname="Month">'.$month.'</td>';
                        $html .= '<td class="total_volume column-total_volume" data-colname="Total Volume">'. wc_price($total_volume).'</td>';
                        $html .= '<td class="amex_volume column-amex_volume" data-colname="Amex Volume">'. wc_price($amex_volume).'</td>';
                        $html .= '</tr>';
                    }

                    $count++;
                }
                $response['total_item'] = count($response['items']);
                $response['pagination'] = $this->get_report_pagination( $data['page'], $response['total_item'] );
            }

        }
        $response['html'] = $html;

        return $response;
    }

    /**
     * Get Report page pagination.
     *
     * @since 3.2.2
     *
     * @param int $page Get the page number.
     * @param int $total_items Get the total items number.
     *
     * @return false|string
     */
    public function get_report_pagination($page, $total_items){

        $total_page = 1;

        if( $total_items > 20 ) {
            $total_page = ceil($total_items / 20);
        }

        ob_start();
        if( $total_page > 1 ){
            if ($page === 1) { ?>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
            <?php } else { ?>
                <a class="prev-page button report-pagination" href="javascript:void(0);" data-page="1">
                    <span class="screen-reader-text"><?php esc_html_e('First page','usb-swiper'); ?></span><span aria-hidden="true">«</span>
                </a>
                <a class="prev-page button report-pagination" href="javascript:void(0);" data-page="<?php echo !empty($page) ? ($page - 1) : 1; ?>">
                    <span class="screen-reader-text"><?php esc_html_e('Previous page','usb-swiper'); ?></span><span aria-hidden="true">‹</span>
                </a>
            <?php } ?>
            <span id="table-paging" class="paging-input">
                <span class="tablenav-paging-text"><?php echo $page; ?> <?php esc_html_e('of','usb-swiper'); ?> <span class="total-pages"><?php echo $total_page; ?></span></span>
            </span>
            <?php
            if ((int)$page === (int)$total_page) { ?>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
            <?php } else { ?>
                <a class="prev-page button report-pagination" href="javascript:void(0);" data-page="<?php echo !empty($page) ? ($page + 1) : 1; ?>">
                    <span class="screen-reader-text"><?php esc_html_e('Next page','usb-swiper'); ?></span><span aria-hidden="true">›</span>
                </a>
                <a class="prev-page button report-pagination" href="javascript:void(0);" data-page="<?php echo !empty($total_page) ? $total_page : 1; ?>">
                    <span class="screen-reader-text"><?php esc_html_e('Last page','usb-swiper'); ?></span><span aria-hidden="true">»</span>
                </a>
            <?php }
        }
        return ob_get_clean();
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

            foreach ( $transactions as $transaction_id ) {

                $transaction_status = usbswiper_get_transaction_status($transaction_id);

                if( !empty( $transaction_status ) &&  in_array( strtolower($transaction_status), ['paid','completed'])  ) {

                    $transaction = get_post($transaction_id);
                    $grand_total = get_post_meta( $transaction_id, 'GrandTotal', true );
                    $grand_total = !empty( $grand_total ) ? $grand_total : 0;
                    $payment_response = get_post_meta($transaction_id, '_payment_response', true);
                    $payment_data = maybe_unserialize($payment_response);

                    $amex_amount = 0;
                    if (isset($payment_data['payment_source']['card']['brand']) && $payment_data['payment_source']['card']['brand'] === 'AMEX') {
                        $amex_amount = $grand_total;
                    }
                    $post_author = !empty($transaction->post_author) ? $transaction->post_author : 0;
                    $post_date = !empty($transaction->post_date) ? $transaction->post_date : '';
                    $unique_key = $post_author.'_'.date('Y-m',strtotime($post_date));

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
            $current_date = date('Y-m-d');
            $last_30_days_date = date('Y-m-d', strtotime('-30 days', strtotime($current_date)));
            $start_date = !empty($_GET['start_date']) ? date('Y-m-d', strtotime(esc_attr($_GET['start_date']))) : $last_30_days_date;
            $end_date = !empty($_GET['end_date']) ? date('Y-m-d', strtotime(esc_attr($_GET['end_date']))) : $current_date;

			?>
			<div class="alignleft actions">
				<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="GET">
					<input type="hidden" name="page" value="usb-swiper">
					<input type="hidden" name="tab" value="reports">

                    <input type="hidden" name="report_nonce" id="report_nonce" value="<?php echo wp_create_nonce('report_table')?>">
                    <input type="hidden" name="current_page" id="current_page" value="1">
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
					<label for="report_start_date"><?php _e('Start Date:', 'usb-swiper'); ?></label>
					<input type="text" autocomplete="off" id="report_start_date" name="start_date" value="<?php echo !empty( $start_date ) ? esc_attr( $start_date ) : ''; ?>" />
					<label for="report_end_date"><?php _e('End Date:', 'usb-swiper'); ?></label>
					<input type="text" autocomplete="off" id="report_end_date" name="end_date" value="<?php echo !empty( $end_date ) ? esc_attr( $end_date ) : ''; ?>" />
					<input type="submit" value="<?php _e('Filter', 'usb-swiper'); ?>" class="button submit-report-filter button-primary">
					<span class="total-volume merchant-total-volume">
						<?php echo sprintf( __('Total Volume: <strong>%s</strong>', 'usb-swiper'), wc_price($this->total_volume) ); ?>
                    </span>
					<span class="total-volume amex merchant-total-amex">
						<?php echo sprintf( __('Total Amex Volume: <strong>%s</strong>', 'usb-swiper'), wc_price($this->total_amex_volume) ); ?>
                    </span>
				</form>
			</div>
			<?php
		}
	}
}
