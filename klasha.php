<?php
//require __DIR__.'/vendor/autoload.php';
//use Unirest\Request\Body;
//use Unirest\Request;
/*
Plugin Name: Klasha Payment Gateway for WooCommerce
Plugin URI: https://www.klasha.com
Description: Klasha Payment Gateway for WooCommerce
Version: 2.0.8
Author: Klasha
Author URI: https://www.klasha.com
License: GPLv2 or later
*/

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */

add_filter("woocommerce_payment_gateways", "klasha_add_gateway_class");
function klasha_add_gateway_class($gateways)
{
    $gateways[] = "KL_WC_Payment_Gateway"; // your class name is here
    return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action("plugins_loaded", "klasha_init_gateway_class");
function klasha_init_gateway_class()
{
    class KL_WC_Payment_Gateway extends WC_Payment_Gateway
    {
        private $error_occured;
        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct()
        {
			$image_url = plugins_url('/klasha-gateway/css/pay-logo.png');
			
            $this->id = "klasha"; // payment gateway plugin ID
            $this->icon =
                esc_url($image_url); // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = "Klasha";
            $this->method_description = ""; // will be displayed on the options page
            $this->error_occured = "";

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = ["products"];

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = "";
            $this->business_id = $this->get_option("business_id");
            $this->description = $this->get_option("description");
            $this->enabled = $this->get_option("enabled");
            $this->testmode = "yes" === $this->get_option("testmode");
            $this->private_key = $this->testmode
                ? $this->get_option("test_private_key")
                : $this->get_option("private_key");
            $this->publishable_key = $this->testmode
                ? $this->get_option("test_publishable_key")
                : $this->get_option("publishable_key");
            $this->callback = $this->get_option("webhook");

            // This action hook saves the settings
            add_action(
                "woocommerce_update_options_payment_gateways_" . $this->id,
                [$this, "process_admin_options"]
            );

            // We need custom JavaScript to obtain a token
            add_action("wp_enqueue_scripts", [$this, "payment_scripts"]);

            add_action("woocommerce_receipt_" . $this->id, [
                $this,
                "receipt_page",
            ]);

            add_action("woocommerce_api_kl_wc_payment_gateway", [
                $this,
                "validate_payment",
            ]);

            //add_filter( 'user_has_cap' .$this->id, array( $this, 'your_custom_function_name', 10, 3));
            add_action("wp_enqueue_scripts", [$this, "my_custom_styles"]);
            add_action("woocommerce_api_klasha-payment-complete", [
                $this,
                "webhook",
            ]);
        }

        public function my_custom_styles()
        {
			$css_url = plugins_url('/klasha-gateway/css/style.css');
            wp_enqueue_style("custom-styles", esc_url($css_url));
        }
        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields()
        {
            $this->form_fields = [
                "enabled" => [
                    "title" => "Enable/Disable",
                    "label" => "Klasha Payment Gateway",
                    "type" => "checkbox",
                    "description" => "",
                    "default" => "no",
                ],
                "description" => [
                    "title" => "Description",
                    "type" => "textarea",
                    "description" =>
                        "This controls the description which the user sees during checkout.",
                    "default" => "",
                ],
                "testmode" => [
                    "title" => "Test mode",
                    "label" => "Enable Test Mode",
                    "type" => "checkbox",
                    "description" =>
                        "Place the payment gateway in test mode using test API keys.",
                    "default" => "yes",
                    "desc_tip" => true,
                ],
                "test_publishable_key" => [
                    "title" => "Test Publishable Key",
                    "type" => "text",
                ],
                "test_private_key" => [
                    "title" => "Test Private Key",
                    "type" => "password",
                ],
                "publishable_key" => [
                    "title" => "Live Publishable Key",
                    "type" => "text",
                ],
                "private_key" => [
                    "title" => "Live Private Key",
                    "type" => "password",
                ],
                "webhook" => [
                    "title" => "Your Web Hook (for redirected)",
                    "type" => "text",
                    "default" => esc_url_raw(
                        get_site_url() . "/wc-api/klasha-payment-complete/"
                    ),
                ],
            ];
        }

        /**
         * You will need it if you want your custom credit card form, Step 4 is about it
         */
        public function payment_fields()
        {
            if ($this->description) {
                // you can instructions for test mode, I mean test card numbers etc.

                if ($this->testmode) {
                    $this->description .= " ";
                    $this->description = trim($this->description);
                }
                // display the description with <p> tags etc.
                echo wpautop(wp_kses_post($this->description));
            }

            echo '             
                    <input id="txnRef" name="txnRef" type="hidden" value="hi" />  
                    <div id="pot">
                    </div>
                ';
        }

        /*
         * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
         */
        public function payment_scripts()
        {
            if (
                !is_cart() &&
                !is_checkout() &&
                !isset($_GET["pay_for_order"])
            ) {
                $this->error_occured = esc_html("An error has occurred");
                echo "This is the error: " . esc_html($this->error_occured);
                return;
            }

            // if our payment gateway is disabled, we do not have to enqueue JS too
            if ("no" === $this->enabled) {
                return;
            }

            // no reason to enqueue JavaScript if API keys are not set
            if (empty($this->publishable_key) || empty($this->private_key)) {
                $this->error_occured = esc_html(
                    "An error has occurred, public key or private key missing or placed wrongly."
                );
                echo "This is the error: " . esc_html($this->error_occured);
                return;
            }

            if (!$this->testmode && !is_ssl()) {
                $this->error_occured = esc_html(
                    "Test mode not enabled, therefore SSL is required."
                );
                echo "This is the error: " . esc_html($this->error_occured);
                return;
            }

            //$order_key = urldecode(sanitize_text_field($_GET["key"]));
            $order_id = absint(get_query_var("order-pay"));

            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }

            $payment_method = method_exists($order, "get_payment_method")
                ? $order->get_payment_method()
                : $order->payment_method;

            if ($this->id !== $payment_method) {
                return;
            }

            $paystack_params = [];
            $suffix = defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? "" : ".min";

            wp_enqueue_script("klasha", "https://checkoutjs.s3.eu-west-1.amazonaws.com/pay.js");
            wp_enqueue_script(
                "wc_klasha",
                plugins_url("js/wc_klasha.js", __FILE__),
                ["jquery", "klasha"],
                "",
                false
            );

            $amount = WC_Payment_Gateway::get_order_total();

            $currency = get_woocommerce_currency();

            $klasha_params["amount"] = $amount;
            $klasha_params["merchantKey"] = $this->publishable_key;
            $klasha_params["businessId"] = $this->business_id;
            $klasha_params["currency"] = $currency;
            $klasha_params["callback"] = $this->callback;
            $klasha_params["testmode"] = $this->testmode;
            $klasha_params["containerId"] = "pot";

            $txnref = "WOOC_" . $order_id . "_" . time();
            $txnref = filter_var($txnref, FILTER_SANITIZE_STRING);
            $klasha_params["firstname"] = $order->get_billing_first_name();
            $klasha_params["lastname"] = $order->get_billing_last_name();
            $klasha_params["email"] = $order->get_billing_email();
            $klasha_params["currency"] = $order->get_currency();
            $klasha_params["phone"] = $order->get_billing_phone();
            $klasha_params["country"] = $order->get_billing_country();
            $klasha_params["txnref"] = $txnref;

            $cb_url = WC()->api_request_url("KL_WC_Payment_Gateway");
            $klasha_params["cb_url"] = $cb_url;

            wp_localize_script("wc_klasha", "wc_klasha_params", $klasha_params);
        }

        /*
         * Fields validation, more in Step 5
         */
        public function validate_fields()
        {
        }

        public function receipt_page($order_id)
        {
            $order = wc_get_order($order_id);

            echo '<p>Thank you for your order. Click the \'Make payment\' button below to pay with Klasha.</p>';

            echo '<div id="klasha_form"><button class="button alt" id="klasha-payment-button">Make payment</button> <a class="button cancel" href="' .
                esc_url($order->get_cancel_order_url()) .
                '">Cancel order &amp; restore cart</a></div>
			';
        }
        /*
         * We're processing the payments here, everything about it is in Step 5
         */
        public function process_payment($order_id)
        {
            if (
                isset($_POST["wc-klasha-payment-token"]) &&
                "new" !== $_POST["wc-klasha-payment-token"]
            ) {
                try {
                    global $woocommerce;
                    $order = wc_get_order($order_id);
                    $txnRef = sanitize_text_field($_POST["txnRef"]);
                    if (!$txnRef) {
                        wc_add_notice(
                            "We could not verify your payment! Check your tnx status",
                            "error"
                        );
                        return;
                    }

                    $data = [
                        "txnRef" => strip_tags($txnRef),
                        "orderId" => $order_id,
                    ];
                    $header = [
                        "Content-Type" => "application/json",
                    ];
                    if ($this->testmode) {
                        $url =
                            "https://dev.kcookery.com/nucleus/wordpressstatus/";
                    } else {
                        $url =
                            "https://gate.klasapps.com/nucleus/wordpressstatus/";
                    }

                    $response = wp_remote_post(
                        $url . $txnRef . "/" . $order_id,
                        [
                            "headers" => [
                                "Content-Type" =>
                                    "application/json; charset=utf-8",
                            ],
                            "method" => "GET",
                            "timeout" => 100,
                        ]
                    );
                    if (!is_wp_error($response)) {
                        $body = json_decode($response["body"], true);

                        echo esc_html($body);

                        if (strtolower($body["txnStatus"]) == "successful") {
                            // we received the payment
                            $order->payment_complete();

                            // some notes to customer (replace true with false to make it private)
                            $order->add_order_note(
                                "Hey, your order is paid! Thank you!",
                                true
                            );
                            $order->add_order_note(
                                "Charged Amount: " . $body["amount"],
                                false
                            );
                            $order->add_order_note(
                                "Transaction Reference Number: " .
                                    $body["tnxRef"],
                                false
                            );
                            $order->add_order_note(
                                "Complete Gateway Response: " .
                                    $response["body"],
                                false
                            );

                            // Empty cart
                            $woocommerce->cart->empty_cart();

                            // Redirect to the thank you page
                            return [
                                "result" => "success",
                                "redirect" => $this->get_return_url($order),
                            ];
                        } else {
                            var_dump($this->error_occured);
                            wc_add_notice(
                                "Error: " .
                                    "This could be due to server, network connectivity or setup error." .
                                    "-" .
                                    $body["response"]["message"] .
                                    ", Please try again.",
                                "error"
                            );
                            return;
                        }
                    } else {
                        wc_add_notice(
                            "We could not verify your payment, If you have been charged, please contact our support.",
                            "error"
                        );
                        return;
                    }
                } catch (Exception $e) {
                    wc_add_notice($e->getMessage(), "error");
                }
            } else {
                $order = wc_get_order($order_id);

                return [
                    "result" => "success",
                    "redirect" => $order->get_checkout_payment_url(true),
                ];
            }
        }
        public function validate_payment()
        {
            @ob_clean();

            echo "I got here.";
            $txnRef = sanitize_text_field($_POST["txnRef"]);
            // Validate and sanitize the input
            if (isset($txnRef)) {
                $sanitizedTxnRef = filter_var($txnRef, FILTER_SANITIZE_STRING);

                // Validate the sanitized input
                if (!empty($sanitizedTxnRef)) {
                    // Assign sanitized value to $txnRef
                    $txnRef = $sanitizedTxnRef;
                } else {
                    throw new Exception("Invalid input: tx_ref is empty");
                }
            } else {
                throw new Exception("Missing input: tx_ref is not set");
            }

            $order_details = explode("_", $txnRef);

            $order_id = (int) $order_details[1];
            $order = wc_get_order($order_id);

            try {
                //                 global $woocommerce;
                if (!$txnRef) {
                    wc_add_notice(
                        "We could not verify your payment! Check your tnx status",
                        "error"
                    );
                    wp_redirect(wc_get_page_permalink("checkout"));

                    exit();
                }

                $data = [
                    "txnRef" => strip_tags($txnRef),
                    "orderId" => $order_id,
                ];

                $header = [
                    "Content-Type" => "application/json",
                ];
                if ($this->testmode) {
                    $url = "https://dev.kcookery.com/nucleus/wordpressstatus/";
                } else {
                    $url = "https://gate.klasapps.com/nucleus/wordpressstatus/";
                }

                $response = wp_remote_post($url . $txnRef . "/" . $order_id, [
                    "headers" => [
                        "Content-Type" => "application/json; charset=utf-8",
                    ],
                    "method" => "GET",
                    "timeout" => 100,
                ]);

                if (!is_wp_error($response)) {
                    $body = json_decode($response["body"], true);

                    if (strtolower($body["txnStatus"]) == "successful") {
                        // we received the payment
                        $order->payment_complete();

                        // some notes to customer (replace true with false to make it private)
                        $order->add_order_note(
                            "Hey, your order is paid! Thank you!",
                            true
                        );
                        $order->add_order_note(
                            "Charged Amount: " . $body["amount"],
                            false
                        );
                        $order->add_order_note(
                            "Transaction Reference Number: " . $body["tnxRef"],
                            false
                        );
                        $order->add_order_note(
                            "Complete Gateway Response: " . $response["body"],
                            false
                        );

                        // Empty cart
                        wc_empty_cart();

                        wp_redirect($this->get_return_url($order));

                        exit();
                    } else {
                        var_dump($this->error_occured);
                        wc_add_notice(
                            "Error: " .
                                "This could be due to server, network connectivity or setup error." .
                                "-" .
                                $body["response"]["message"] .
                                ", Please try again.",
                            "error"
                        );
                        wp_redirect(wc_get_page_permalink("checkout"));

                        exit();
                    }
                } else {
                    wc_add_notice(
                        "We could not verify your payment, If you have been charged, please contact our support.",
                        "error"
                    );
                    wp_redirect(wc_get_page_permalink("checkout"));

                    exit();
                }
            } catch (Exception $e) {
                wc_add_notice($e->getMessage(), "error");
            }
        }

        /*
         * In case you need a webhook, like PayPal IPN etc
         */
        public function webhook()
        {
            global $woocommerce;
            $pet = sanitize_text_field($_GET["id"]);
            $order = wc_get_order(sanitize_text_field($_GET["id"]));
            $txnStatus = sanitize_text_field($_GET["txnStatus"]);

            if ($txnStatus == "successful") {
                // we received the payment
                $order->payment_complete();
                // some notes to customer (replace true with false to make it private)
                $order->add_order_note(
                    "Hey, your order is paid! Thank you! callback",
                    true
                );
            }

            update_option("webhook_debug", $_GET);
        }

        function your_custom_function_name($allcaps, $caps, $args)
        {
            if (isset($caps[0])) {
                switch ($caps[0]) {
                    case "pay_for_order":
                        $order_id = isset($args[2]) ? $args[2] : null;
                        $order = wc_get_order($order_id);
                        $user = $order->get_user();
                        $user_id = $user->ID;

                        // When no order ID, we assume it's a new order
                        // and thus, customer can pay for it
                        if (!$order_id) {
                            $allcaps["pay_for_order"] = true;
                            break;
                        }

                        $order = wc_get_order($order_id);

                        if (
                            $order &&
                            ($user_id == $order->get_user_id() ||
                                !$order->get_user_id())
                        ) {
                            $allcaps["pay_for_order"] = true;
                        }
                        break;
                }
            }

            return $allcaps;
        }
    }
}

add_filter(
    "woocommerce_checkout_posted_data",
    "ftm_filter_checkout_posted_data",
    10,
    1
);
function ftm_filter_checkout_posted_data($data)
{
    $email = $data["billing_email"];
    if (email_exists($email)) {
        $user = get_user_by(
            "email",
            sanitize_text_field($_POST["billing_email"])
        );
        if ($user) {
            wp_set_current_user($user->ID);
        }
    }
    return $data;
}
