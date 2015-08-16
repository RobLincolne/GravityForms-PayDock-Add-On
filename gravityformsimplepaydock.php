<?php
/*
Plugin Name: Rob's PayDock Feed Add-On
Plugin URI: http://www.gravityforms.com
Description: A simple add-on to demonstrate the use of the Add-On Framework
Version: 1.0
Author: Rocketgenius
Author URI: http://www.rocketgenius.com

------------------------------------------------------------------------
Copyright 2012-2013 Rocketgenius Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/


//------------------------------------------
if ( method_exists( 'GFForms', 'include_payment_addon_framework' ) ) {
    GFForms::include_payment_addon_framework();


    class GFPaydocKfeeDadDon extends GFPaymentAddOn {



                protected $_version = "1.00"; 
                protected $_min_gravityforms_version = "1.9.11.18";
                protected $_slug = "paydockfeedaddon";
                protected $_path = "paydockfeedaddon/paydockfeedaddon.php";
                protected $_full_path = __FILE__;
                protected $_title = "Rob's Gravity Forms PayDock Add-On";
                protected $_short_title = "PayDock Add-On";
                protected $_supports_callbacks = true;
                protected $_requires_credit_card = false;
     



        public function plugin_page() {
            ?>
            This page appears in the Forms menu
        <?php
        }

        public function feed_settings_fields() {


            $pd_options = get_option('gravityformsaddon_gravityformspaydock_settings');
            $api_key = $pd_options['paydock_api_key'];
            $api_url = $pd_options['paydock_api_uri'] . 'gateways/';



            // $GLOBALS['paydock_api_key'] = $api_key;
            // $GLOBALS['paydock_api_uri'] = $this->get_plugin_setting('paydock_api_uri');


            $curl_header = array();
            $curl_header[] = 'x-user-token:'.$api_key;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // this one is important
            curl_setopt($ch, CURLOPT_HEADER, false);
            $result = curl_exec($ch);
            curl_close($ch);
 
            $json_string = json_decode($result, true);
            $gateways = $json_string['resource']['data'];
            $gateways_select = array();
            foreach ($gateways as $gateway) {
                $gateways_select[] = array("label" => $gateway['name'], "value" => $gateway['_id']);
            }
         

            return array(
                array(
                    "title"  => "PayDock Feed Settings",
                    "fields" => array(
                        array(
                            "label"   => "Feed name",
                            "type"    => "text",
                            "name"    => "feedName",
                            "tooltip" => "Why not give this feed a helpful name",
                            "class"   => "medium"
                        ),
                         array(
                            "label"   => "Select Gateway",
                            "type"    => "select",
                            "name"    => "pd_select_gateway",
                            "tooltip" => "Select which gateway you wish to push this feed to",
                            "choices" => $gateways_select
                        ),
                        array(
                            "label"   => "Feed Reference",
                            "type"    => "text",
                            "name"    => "pd_reference",
                            "tooltip" => "Use this to add a reference to your submission.",
                            "class"   => "medium"
                        ),
                        // array(
                        //     "label"   => "My checkbox",
                        //     "type"    => "checkbox",
                        //     "name"    => "mycheckbox",
                        //     "tooltip" => "This is the tooltip",
                        //     "choices" => array(
                        //         array(
                        //             "label" => "Enabled",
                        //             "name"  => "mycheckbox"
                        //         )
                        //     )
                        // ),
                        array(
                            "name" => "pd_personal_mapped_details",
                            "label" => "Personal Details",
                            "type" => "field_map",
                            "field_map" => array(   array("name" => "pd_email", "label" => "Email", "required" => 0),
                                                    array("name" => "pd_first_name", "label" => "First Name", "required" => 0),
                                                    array("name" => "pd_last_name", "label" => "Last Name", "required" => 0)
                            )
                        ),
                        array(
                            "name" => "pd_payment_mapped_details",
                            "label" => "Payment Details",
                            "type" => "field_map",
                            "field_map" => array(   
                                                    array("name" => "pd_transaction_reference", "label" => "Transaction Reference", "required" => 0),
                                                    array("name" => "pd_payment_type", "label" => "Payment Type", "required" => 0),
                                                    array("name" => "pd_total_payable", "label" => "Total Amount", "required" => 0),
                                                    array("name" => "pd_payment_source", "label" => "Payment Source", "required" => 0),
                                                    array("name" => "pd_account_name", "label" => "Account Name", "required" => 0),
                                                    array("name" => "pd_account_bsb", "label" => "Account BSB", "required" => 0),
                                                    array("name" => "pd_account_number", "label" => "Account Number", "required" => 0)                      )
                        ),
                        array(
                            "name" => "condition",
                            "label" => __("Condition", "paydockfeedaddon"),
                            "type" => "feed_condition",
                            "checkbox_label" => __('Enable Condition', 'paydockfeedaddon'),
                            "instructions" => __("Process this PayDock feed if", "paydockfeedaddon")
                        ),
                    )
                )
            );
        }

        protected function feed_list_columns() {
            return array(
                'feedName' => __('Name', 'paydockfeedaddon'),
                'mytextbox' => __('My Textbox', 'paydockfeedaddon')
            );
        }

        // customize the value of mytextbox before it's rendered to the list
        public function get_column_value_mytextbox($feed){
            return "<b>" . $feed["meta"]["mytextbox"] ."</b>";
        }

        public function plugin_settings_fields() {
            return array(
                array(
                    "title"  => "Simple Add-On Settings",
                    "fields" => array(
                        array(
                            "name"    => "textbox",
                            "tooltip" => "This is the tooltip",
                            "label"   => "PayDock API Key",
                            "type"    => "text",
                            "class"   => "medium"
                        ),
                         array(
                            "label"   => "My Dropdown",
                            "type"    => "select",
                            "name"    => "mydropdown",
                            "tooltip" => "This is the tooltip",
                            "choices" => array(
                                array(
                                    "label" => "Production (https://api.thepaydock.com)",
                                    "value" => "https://api.thepaydock.com"
                                ),
                                array(
                                    "label" => "Sandbox (https://api-sandbox.thepaydock.com)",
                                    "value" => "https://api-sandbox.thepaydock.com"
                                )
                            )
                        ),
                    )
                )
            );
        }

        public function scripts() {
            $scripts = array(
                array("handle"  => "my_script_js",
                      "src"     => $this->get_base_url() . "/js/my_script.js",
                      "version" => $this->_version,
                      "deps"    => array("jquery"),
                      "strings" => array(
                          'first'  => __("First Choice", "paydockfeedaddon"),
                          'second' => __("Second Choice", "paydockfeedaddon"),
                          'third'  => __("Third Choice", "paydockfeedaddon")
                      ),
                      "enqueue" => array(
                          array(
                              "admin_page" => array("form_settings"),
                              "tab"        => "paydockfeedaddon"
                          )
                      )
                ),

            );

            return array_merge(parent::scripts(), $scripts);
        }

        public function styles() {

            $styles = array(
                array("handle"  => "my_styles_css",
                      "src"     => $this->get_base_url() . "/css/my_styles.css",
                      "version" => $this->_version,
                      "enqueue" => array(
                          array("field_types" => array("poll"))
                      )
                )
            );

            return array_merge(parent::styles(), $styles);
        }



		public function authorize( $feed, $submission_data, $form, $entry ) {
        // public function process_feed($feed, $entry, $form){
          $data = array();
          foreach ($form["fields"] as $field) {
            if ($field['type'] == 'creditcard' && !RGFormsModel::is_field_hidden($form, $field, array())) {
                    $ccnumber = rgpost('input_' . $field['id'] . '_1');
                    $ccdate_array = rgpost('input_' . $field['id'] . '_2');
                    $ccdate_month = $ccdate_array[0];
                    if (strlen($ccdate_month) < 2)
                        $ccdate_month = '0'.$ccdate_month;
                    $ccdate_year = $ccdate_array[1];
                    if (strlen($ccdate_year) > 2)
                        $ccdate_year = substr($ccdate_year, -2); // Only want last 2 digits
                    $ccv = rgpost('input_' . $field['id'] . '_3');
                    $ccname = rgpost('input_' . $field['id'] . '_5');
                    $is_creditcard = true;
                        $data["customer"]["payment_source"]["card_name"] = $ccname;
                        $data["customer"]["payment_source"]["card_number"]= $ccnumber;
                        $data["customer"]["payment_source"]["expire_month"] = $ccdate_month;
                        $data["customer"]["payment_source"]["expire_year"] = $ccdate_year;
                        $data["customer"]["payment_source"]["card_ccv"] = $ccv;
                } 
            }
          

            	$payment_type = $entry[$feed["meta"]["pd_payment_mapped_details_pd_payment_type"]];
            	if($payment_type=="bsb"){
            		 $data["customer"]["payment_source"]["type"] = "bsb";
            		 $data["customer"]["payment_source"]["account_name"] = $entry[$feed["meta"]["pd_payment_mapped_details_pd_account_name"]];
            		 $data["customer"]["payment_source"]["account_bsb"] = $entry[$feed["meta"]["pd_payment_mapped_details_pd_account_bsb"]];
            		 $data["customer"]["payment_source"]["account_number"] = $entry[$feed["meta"]["pd_payment_mapped_details_pd_account_number"]];
            	}

		        $data["customer"]["payment_source"]["gateway_id"] = $feed["meta"]["pd_select_gateway"];

                $data["customer"]["first_name"] = $entry[$feed["meta"]["pd_personal_mapped_details_pd_first_name"]];
                $data["customer"]["last_name"] = $entry[$feed["meta"]["pd_personal_mapped_details_pd_last_name"]];
                $data["customer"]["email"] = $entry[$feed["meta"]["pd_personal_mapped_details_pd_email"]];
                $data["reference"] = $entry[$feed["meta"]["pd_payment_mapped_details_pd_transaction_reference"]];
                $data["amount"] = $entry[$feed["meta"]["pd_payment_mapped_details_pd_total_payable"]];
                $data["currency"] = (!empty($currency)) ? $currency : GFCommon::get_currency();


       
		            $pd_options = get_option('gravityformsaddon_gravityformspaydock_settings');
		            $api_key = $pd_options['paydock_api_key'];
		            $api_url = $pd_options['paydock_api_uri'] . 'charges/';


		            $data_string = json_encode($data);
		     
		            $envoyrecharge_key = $api_key;
		            $ch = curl_init();
		            curl_setopt($ch, CURLOPT_URL, $api_url);
		            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		                    'x-user-token:' . $envoyrecharge_key,
		                    'Content-Type: application/json',
		                    'Content-Length: ' . strlen($data_string)
		            ));
		            $result = curl_exec($ch);
		            curl_close($ch);
		    	
			    	$response = json_decode($result);


			
		         if ( $response->status > "250") {

                    // set the form validation to false
                    $auth = array(
					'is_authorized'  => false,
					'transaction_id' => $response->resource->data->_id,
					'error_message'  => "There was an error with your transaction please try again."
					);

                    foreach( $form['fields'] as &$field ) {
                        if ( $field->id == '9' ) {
                            $field->failed_validation = true;
                            $field->validation_message = 'There was a problem processing your payment, please try again or contact us.';
                            break;
                        }
                    }
                }else{
                	$auth = array( 'is_authorized' => true, 'transaction_id' => $response->resource->data->_id );

				}



			return $auth;


            // $feedName = $feed["meta"]["feedName"];
            // $mytextbox = $feed["meta"]["mytextbox"];
            // $checkbox = $feed["meta"]["mycheckbox"];
            // $mapped_email = $feed["meta"]["mappedFields_email"];
            // $mapped_name = $feed["meta"]["mappedFields_name"];

            // $email = $entry[$mapped_email];
            // $name = $entry[$mapped_name];

        }




          // THIS IS OUR FUNCTION FOR CLEANING UP THE PRICING AMOUNTS THAT GF SPITS OUT
        public function pd_clean_amount($entry) {
            $entry = preg_replace("/\|(.*)/", '', $entry); // replace everything from the pipe symbol forward
            if (strpos($entry, '.') === false) {
                $entry .= ".00";
            }
            if (strpos($entry, '$') !== false) {
                $startsAt = strpos($entry, "$") + strlen("$");
                $endsAt = strlen($entry);
                $amount = substr($entry, 0, $endsAt);
                $amount = preg_replace("/[^0-9,.]/", "", $amount);
            } else {
                $amount = preg_replace("/[^0-9,.]/", "", sprintf("%.2f", $entry));
            }

            $amount = str_replace('.', '', $amount);
            $amount = str_replace(',', '', $amount);
            return $amount;
        }

              // add_filter('gform_validation', array($this, 'pdFormValidation'));
            // public function pdFormValidation($data) {
            //     $fails = 0; foreach($form['fields'] as $field) if ($field['failed_validation']==1) $fails++; 
            //     if ($fails==0) {

            //             $response = $this->capture();
            //             echo "hi";
            //             exit();
            //     }
            // }


   

    }

    new GFPaydocKfeeDadDon();
}



add_filter("gform_field_value_feed_reference", "generate_random_number");
function generate_random_number($value){

	// $pd_options = get_option('gravityformsaddon_gravityformspaydock_settings');
	// $reference = $pd_options['pd_reference'];
	// var_dump($feed);
	// exit();

	$to_return = "stfcf-" . mt_rand();
   return $to_return;
}