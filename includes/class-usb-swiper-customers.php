<?php

class Usb_Swiper_Customers {

	public string $customer_table = '';
	public string $customer_meta_table = '';

	public function __construct() {
		global $wpdb;

		$this->customer_table = $wpdb->prefix . 'customers';
		$this->customer_meta_table = $wpdb->prefix . 'customer_meta';
	}

	public function get_customer_id_by_email( $customer_email ) {

		global $wpdb;

		$results = $wpdb->get_results($wpdb->prepare(
			"SELECT id FROM $this->customer_table WHERE email LIKE %s",
			'%' . $wpdb->esc_like($customer_email) . '%',
		));

		return !empty($results[0]->id) ? $results[0]->id : 0;
	}

	public function get_customers( $args = [] ) {

		global $wpdb;

		$customers = [];

		$customer = !empty( $args['customer'] ) ? $args['customer'] : '';
		$per_page = !empty( $args['per_page'] ) ? (int) $args['per_page'] : 10;
		$current_page = !empty( $args['current_page'] ) ? (int) $args['current_page'] : 1;
		$order = !empty( $args['order'] ) ? $args['order'] : 'id';
		$order_by = !empty( $args['order_by'] ) ? $args['order_by'] : 'DESC';
        $merchant_id = get_current_user_id();

		$offset = ( $current_page - 1 ) * $per_page;
        $order = 'c.'.$order;
		if( !empty( $customer ) ) {

            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT  c.*  FROM $this->customer_table c INNER JOIN $this->customer_meta_table cm ON c.id = cm.customer_id WHERE cm.meta_key = %s AND cm.meta_value = %d AND (c.email LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s) ORDER BY %s %s LIMIT %d OFFSET %d",
                'merchant_id',
                $merchant_id,
                '%' . $wpdb->esc_like($customer) . '%',
                '%' . $wpdb->esc_like($customer) . '%',
                '%' . $wpdb->esc_like($customer) . '%',
                $order,
                $order_by,
                (int) $per_page,
                (int) $offset
            ));
		} else {


			$results = $wpdb->get_results($wpdb->prepare(
				"SELECT c.* FROM $this->customer_table c INNER JOIN $this->customer_meta_table cm ON c.id = cm.customer_id WHERE cm.meta_key = %s AND cm.meta_value = %d ORDER BY %s %s LIMIT %d OFFSET %d",
                'merchant_id',
                $merchant_id,
                $order,
				$order_by,
				(int) $per_page,
				(int) $offset
			));
		}

		if( !empty( $results ) && is_array( $results ) ) {

			foreach ( $results as $result ) {

				$customer_id = !empty($result->id) ? $result->id : '';

				if( !empty( $customer_id ) && $customer_id > 0 ) {

					$temp_customer = [
						'customer_id' => $customer_id,
						'BillingEmail' => !empty($result->email) ? $result->email : '',
						'BillingFirstName' => !empty($result->first_name) ? $result->first_name : '',
						'BillingLastName' => !empty($result->last_name) ? $result->last_name : '',
						'company' => !empty($result->company) ? $result->company : '',
						'date' => !empty($result->date) ? $result->date : '',
						'modified_date' => !empty($result->modified_date) ? $result->modified_date : '',
					];

					$currency_fields = usb_swiper_get_vt_form_fields( 'currency_info' );

					if( !empty( $currency_fields ) && is_array( $currency_fields ) ) {
						foreach ($currency_fields as $key => $currency_field) {
							$currency_field_id = !empty($currency_field['id']) ? $currency_field['id'] : '';
							$temp_customer[$currency_field_id] = $this->get_customer_meta( $customer_id, $currency_field_id);
						}
					}

					$billing_address_fields = usb_swiper_get_vt_form_fields( 'billing_address' );

					if( !empty( $billing_address_fields ) && is_array( $billing_address_fields ) ) {
						foreach ($billing_address_fields as $bkey => $billing_address_field) {
							$billing_field_id = !empty($billing_address_field['id']) ? $billing_address_field['id'] : '';
							$temp_customer[$billing_field_id] = $this->get_customer_meta( $customer_id, $billing_field_id);
						}
					}

					$shipping_address_fields = usb_swiper_get_vt_form_fields( 'shipping_address' );

					if( !empty( $shipping_address_fields ) && is_array( $shipping_address_fields ) ) {
						foreach ($shipping_address_fields as $skey => $shipping_address_field) {
							$shipping_field_id = !empty($shipping_address_field['id']) ? $shipping_address_field['id'] : '';
							$temp_customer[$shipping_field_id] = $this->get_customer_meta( $customer_id, $shipping_field_id);
						}
					}

					$save_customer_info_fields = usb_swiper_get_vt_form_fields( 'save_customer_info' );

					if( !empty( $save_customer_info_fields ) && is_array( $save_customer_info_fields ) ) {
						foreach ($save_customer_info_fields as $sckey => $save_customer_info_field) {
							$save_customer_field_id = !empty($save_customer_info_field['id']) ? $save_customer_info_field['id'] : '';
							$temp_customer[$save_customer_field_id] = $this->get_customer_meta( $customer_id, $save_customer_field_id);
						}
					}

					$customers[] = $temp_customer;
				}
			}
		}

		$total_customers = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $this->customer_table c INNER JOIN $this->customer_meta_table cm ON c.id = cm.customer_id WHERE cm.meta_key = %s AND cm.meta_value = %d AND (c.email LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s)",
            'merchant_id',
            $merchant_id,
			'%' . $wpdb->esc_like($customer) . '%',
			'%' . $wpdb->esc_like($customer) . '%',
			'%' . $wpdb->esc_like($customer) . '%'
		));

		return [
			'customers' => $customers,
			'total_customers' => $total_customers,
			'total_pages' => !empty( $total_customers ) ? ceil( $total_customers / $per_page) : 0,
			'current_page' => $current_page,
			'per_page' => $per_page,
		];
	}

	public function get_customer_by_id( $customer_id ) {

		global $wpdb;

		$customer = [];

		if( empty( $customer_id ) ) {
			return [];
		}

		$customer_results = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $this->customer_table WHERE id=%d ORDER BY first_name ASC LIMIT 1",
			(int)$customer_id,
		));

		$customer_result = !empty($customer_results[0]) ? $customer_results[0] : [];

		if( !empty( $customer_result ) && ( is_array( $customer_result ) || is_object( $customer_result ) )) {

			$customer_id = !empty($customer_result->id) ? $customer_result->id : '';

			if( !empty( $customer_id ) && $customer_id > 0 ) {

				$customer['customer_id'] = $customer_id;
				$customer['BillingEmail'] = !empty($customer_result->email) ? $customer_result->email : '';
				$customer['BillingFirstName'] = !empty($customer_result->first_name) ? $customer_result->first_name : '';
				$customer['BillingLastName'] = !empty($customer_result->last_name) ? $customer_result->last_name : '';
				$customer['company'] = !empty($customer_result->company) ? $customer_result->company : '';

				$currency_fields = usb_swiper_get_vt_form_fields( 'currency_info' );

				if( !empty( $currency_fields ) && is_array( $currency_fields ) ) {
					foreach ($currency_fields as $key => $currency_field) {
						$currency_field_id = !empty($currency_field['id']) ? $currency_field['id'] : '';
						$customer[$currency_field_id] = $this->get_customer_meta( $customer_id, $currency_field_id);
					}
				}

				$billing_address_fields = usb_swiper_get_vt_form_fields( 'billing_address' );

				if( !empty( $billing_address_fields ) && is_array( $billing_address_fields ) ) {
					foreach ($billing_address_fields as $bkey => $billing_address_field) {
						$billing_field_id = !empty($billing_address_field['id']) ? $billing_address_field['id'] : '';
						$customer[$billing_field_id] = $this->get_customer_meta( $customer_id, $billing_field_id);
					}
				}

				$shipping_address_fields = usb_swiper_get_vt_form_fields( 'shipping_address' );

				if( !empty( $shipping_address_fields ) && is_array( $shipping_address_fields ) ) {
					foreach ($shipping_address_fields as $skey => $shipping_address_field) {
						$shipping_field_id = !empty($shipping_address_field['id']) ? $shipping_address_field['id'] : '';
						$customer[$shipping_field_id] = $this->get_customer_meta( $customer_id, $shipping_field_id);
					}
				}

				$save_customer_info_fields = usb_swiper_get_vt_form_fields( 'save_customer_info' );

				if( !empty( $save_customer_info_fields ) && is_array( $save_customer_info_fields ) ) {
					foreach ($save_customer_info_fields as $sckey => $save_customer_info_field) {
						$save_customer_field_id = !empty($save_customer_info_field['id']) ? $save_customer_info_field['id'] : '';
						$customer[$save_customer_field_id] = $this->get_customer_meta( $customer_id, $save_customer_field_id);
					}
				}
			}
		}

		return $customer;
	}

	public function handle_customer( $customer_data, $customer_id = 0 ) {

		$status = false;
		$message = '';

		if( !empty( $customer_data['save_customer_details'] ) && '1' === $customer_data['save_customer_details'] ) {

			if( !$customer_id ) {
				$email = !empty($customer_data['CustomerEmail']) ? $customer_data['CustomerEmail'] : '';
                if( !empty( $email ) ) {
                    $customer_id = $this->get_customer_id_by_email($email);
                }
			}

			if( !empty( $customer_id ) && $customer_id > 0 ) {
				$status = true;
				$message = __('Customer updated successfully.', 'usb-swiper');
				$customer_id = $this->update_customer($customer_data, $customer_id);
			} else {
				$status = true;
				$message = __('Customer inserted successfully.', 'usb-swiper');
				$customer_id = $this->insert_customer($customer_data);
			}

			if( !empty( $customer_id ) && $customer_id > 0 ) {

				$currency_fields = usb_swiper_get_vt_form_fields( 'currency_info' );

				if( !empty( $currency_fields ) && is_array( $currency_fields ) ) {
					foreach ($currency_fields as $key => $currency_field) {
						$currency_field_id = !empty($currency_field['id']) ? $currency_field['id'] : '';
						$currency_field_value = !empty( $customer_data[$currency_field_id] ) ? $customer_data[$currency_field_id] : '';
						$this->update_customer_meta($customer_id, $currency_field_id, $currency_field_value);
					}
				}

                if( !empty($customer_data['merchant_id']) ){
                    $this->update_customer_meta($customer_id, 'merchant_id', $customer_data['merchant_id']);
                }

				$billing_address_fields = usb_swiper_get_vt_form_fields( 'billing_address' );
				if( !empty( $billing_address_fields ) && is_array( $billing_address_fields ) ) {
					foreach ($billing_address_fields as $bkey => $billing_address_field) {
						$billing_field_id = !empty($billing_address_field['id']) ? $billing_address_field['id'] : '';
						$billing_field_value = !empty( $customer_data[$billing_field_id] ) ? $customer_data[$billing_field_id] : '';
						$this->update_customer_meta($customer_id, $billing_field_id, $billing_field_value);
					}
				}

				$shipping_address_fields = usb_swiper_get_vt_form_fields( 'shipping_address' );
				if( !empty( $shipping_address_fields ) && is_array( $shipping_address_fields ) ) {
					foreach ($shipping_address_fields as $skey => $shipping_address_field) {
						$shipping_field_id = !empty($shipping_address_field['id']) ? $shipping_address_field['id'] : '';
						$shipping_field_value = !empty( $customer_data[$shipping_field_id] ) ? $customer_data[$shipping_field_id] : '';
						$this->update_customer_meta($customer_id, $shipping_field_id, $shipping_field_value);
					}
				}

				$save_customer_info_fields = usb_swiper_get_vt_form_fields( 'save_customer_info' );
				if( !empty( $save_customer_info_fields ) && is_array( $save_customer_info_fields ) ) {
					foreach ($save_customer_info_fields as $sckey => $save_customer_info_field) {
						$save_customer_field_id = !empty($save_customer_info_field['id']) ? $save_customer_info_field['id'] : '';
						$save_customer_field_value = !empty( $customer_data[$save_customer_field_id] ) ? $customer_data[$save_customer_field_id] : '';
						$this->update_customer_meta($customer_id, $save_customer_field_id, $save_customer_field_value);
					}
				}
			}
		}

		return [
			'customer_id' => $customer_id,
			'status' => $status,
			'message' => $message,
		];
	}

	public function update_customer( $customer_data, $customer_id ) {

		global $wpdb;

		if( empty( $customer_data ) || !is_array( $customer_data ) ) {
			return $customer_id;
		}

		$wpdb->update(
			$this->customer_table,
			[
				'email' => !empty( $customer_data['BillingEmail'] ) ? $customer_data['BillingEmail'] : '',
				'first_name' => !empty( $customer_data['BillingFirstName'] ) ? $customer_data['BillingFirstName'] : '',
				'last_name' => !empty( $customer_data['BillingLastName'] ) ? $customer_data['BillingLastName'] : '',
				'company' => !empty( $customer_data['company'] ) ? $customer_data['company'] : '',
				'modified_date' => current_time( 'mysql' ),
			],
			[
				'id' => (int) $customer_id,
			]
		);

		return $customer_id;
	}

	public function insert_customer( $customer_data ) {

		global $wpdb;

		if( empty( $customer_data ) || !is_array( $customer_data ) ) {
			return false;
		}

		$wpdb->insert(
			$this->customer_table,
			[
				'email' => !empty( $customer_data['BillingEmail'] ) ? $customer_data['BillingEmail'] : '',
				'first_name' => !empty( $customer_data['BillingFirstName'] ) ? $customer_data['BillingFirstName'] : '',
				'last_name' => !empty( $customer_data['BillingLastName'] ) ? $customer_data['BillingLastName'] : '',
				'company' => !empty( $customer_data['company'] ) ? $customer_data['company'] : '',
				'date' => current_time( 'mysql' ),
				'modified_date' => current_time( 'mysql' ),
			],
			['%s','%s','%s','%s','%s','%s']
		);

		$customer_id = $wpdb->insert_id;

		return !empty( $customer_id ) ? $customer_id : 0;
	}

	public function get_customer_meta( $customer_id, $customer_key ) {

		global $wpdb;

		$customer_meta = $wpdb->get_results($wpdb->prepare(
			"SELECT meta_value FROM $this->customer_meta_table WHERE customer_id = %d AND meta_key = %s",
			$customer_id,
			$customer_key
		));

		return !empty( $customer_meta[0]->meta_value ) ? $customer_meta[0]->meta_value : '';
	}

	public function insert_customer_meta( $customer_id, $customer_key, $customer_value ) {

		global $wpdb;

		$wpdb->insert(
			$this->customer_meta_table,
			[
				'customer_id' => (int)$customer_id,
				'meta_key' => $customer_key,
				'meta_value' => $customer_value,
			],
			['%d','%s','%s']
		);

		$customer_meta_id = $wpdb->insert_id;

		return !empty( $customer_meta_id ) ? $customer_meta_id : 0;
	}

	public function update_customer_meta( $customer_id, $customer_key, $customer_value ) {

		global $wpdb;

		$is_meta_key = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_key FROM $this->customer_meta_table WHERE meta_key = %s AND customer_id = %d",
				$customer_key,
				$customer_id
			)
		);

		if ( $is_meta_key === null ) {
			$customer_meta_id = $this->insert_customer_meta( $customer_id, $customer_key, $customer_value );
		} else {
			$wpdb->update(
				$this->customer_meta_table,
				[
					'meta_value' => $customer_value
				],
				[
					'customer_id' => $customer_id,
					'meta_key'   => $customer_key,
				]
			);

			$customer_meta_id = $wpdb->insert_id;
		}

		return !empty( $customer_meta_id ) ? $customer_meta_id : 0;
	}

	public function delete_customer( $customer_id ) {
		global $wpdb;

		if( empty( $customer_id )) {
			return [
				'status' => false,
				'customer_id' => 0,
				'is_customer_delete' => false,
				'is_customer_meta_delete' => false,
				'message' => __( 'Customer id is not found.', 'usb-swiper'),
			];
		}

		$customer_data = $this->get_customer_by_id($customer_id);

		$customer_name = !empty(  $customer_data['BillingFirstName'] ) ? $customer_data['BillingFirstName'] : '';

		$customer_result = $wpdb->query($wpdb->prepare("DELETE FROM $this->customer_table WHERE id = %d", (int) $customer_id));
		$customer_meta_result = $wpdb->query($wpdb->prepare("DELETE FROM $this->customer_meta_table WHERE customer_id = %d", (int) $customer_id));

		if( false === $customer_result || false === $customer_meta_result) {

			return [
				'status' => false,
				'customer_id' => $customer_id,
				'is_customer_delete' => $customer_result,
				'is_customer_meta_delete' => $customer_meta_result,
				'message' => sprintf( __( '%s user is not deleted successfully.', 'usb-swiper'), $customer_id),
			];
		}

		return [
			'status' => true,
			'customer_id' => $customer_id,
			'is_customer_delete' => $customer_result,
			'is_customer_meta_delete' => $customer_meta_result,
			'message' => sprintf( __( '%s user(#%s) is deleted successfully.', 'usb-swiper'), $customer_name, $customer_id),
		];
	}
}