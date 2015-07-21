<?php
/*
Plugin Name: Gravity Forms PayDock Add-On
Plugin URI: http://www.thepaydock.com
Description: Connect your GravityForms forms to Houston PayDock
Version: 0.3
Author: Rob Lincolne
Author URI: http://www.thepaydock.com

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
if (class_exists("GFForms")) {

    $all_settings = "";

    GFForms::include_addon_framework();

    class GFPayDockAddOn extends GFAddOn {

        protected $_version = "1.1";
        protected $_min_gravityforms_version = "1.7.9999";
        protected $_slug = "gravityformspaydock";
        protected $_path = "gravityformspaydock/gf-paydock.php";
        protected $_full_path = __FILE__;
        protected $_title = "Gravity Forms PayDock Add-On";
        protected $_short_title = "PayDock Add-On";

        public function init(){
            parent::init();
            add_filter("gform_submit_button", array($this, "form_submit_button"), 10, 2);
                    $GLOBALS['all_plugin_settings'] = $this->get_plugin_settings();

        }

        // Add the text in the plugin settings to the bottom of the form if enabled for this form
        function form_submit_button($button, $form){
            $settings = $this->get_form_settings($form);
            if(isset($settings["enabled"]) && true == $settings["enabled"]){
                $text = $this->get_plugin_setting("mytextbox");
                $button = "<div>{$text}</div>" . $button;
            }
            return $button;
        }


        public function plugin_page() {
            ?>
            <h3>PayDock</h3>
            <a href="http://thepaydock.com" target="_blank"><img src="<?php echo plugins_url()  . '/gravityformspaydock/img/paydock_small.png' ?>"/></a>
            <p>PayDock is a revolutionary way to integrate recurring and one-off payments into your website, regardless of gateway and free from hassle.</p>
            <p>PayDock settings are managed on a per-form basis.</p>
            <p><a href="http://docs.thepaydock.com"/>Click here</a> for API documentation or <a href="mailto:support@thepaydock.com">here for support</a>.</p>
        <?php
        }

        public function form_settings_fields($form) {
        
            $api_key = $this->get_plugin_setting('paydock_api_key');
            $api_url = $this->get_plugin_setting('paydock_api_uri') . "gateways";

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
                    "title"  => "PayDock Form Settings",
                    "fields" => array(
                        array(
                            "label"   => "Process Payment",
                            "type"    => "checkbox",
                            "name"    => "pd_process_payment",
                            "tooltip" => "Submitting this form will create a request to PayDock.",
                            "choices" => array(
                                array(
                                    "label" => "Enabled",
                                    "name"  => "pd_process_payment"
                                )
                            )
                        ),
                        array(
                            "label"   => "Select Gateway",
                            "type"    => "select",
                            "name"    => "pd_select_gateway",
                            "tooltip" => "Select which payment gateway you wish to process with.",
                            "choices" => $gateways_select
                        )
                    )
                )
            );
        }

        public function settings_my_custom_field_type(){
            ?>
            <div>
                My custom field contains a few settings:
            </div>
            <?php
                $this->settings_text(
                    array(
                        "label" => "A textbox sub-field",
                        "name" => "subtext",
                        "default_value" => "change me"
                    )
                );
                $this->settings_checkbox(
                    array(
                        "label" => "A checkbox sub-field",
                        "choices" => array(
                            array(
                                "label" => "Activate",
                                "name" => "subcheck",
                                "default_value" => true
                            )

                        )
                    )
                );
        }

        public function plugin_settings_fields() {
            return array(
                array(
                    "title"  => "PayDock Add-On Settings",
                    "fields" => array(
                        array(
                            "name"    => "paydock_api_key",
                            "tooltip" => "You can find this under 'My Account' in PayDock.",
                            "label"   => "PayDock API Key",
                            "type"    => "text",
                            "class"   => "medium",
                            "feedback_callback" => array($this, "is_valid_setting")
                        ),
                        array(
                            "name"    => "paydock_api_uri",
                            "tooltip" => "For example, 'http://api.thepaydock.com'.",
                            "label"   => "PayDock API URI",
                            "type"    => "text",
                            "class"   => "medium"                        
                        ),
                    )
                )
            );
        }

        public function is_valid_setting($value){

            return strlen($value) == 40;
        }

        public function scripts() {
            $scripts = array(
                array("handle"  => "my_script_js",
                      "src"     => $this->get_base_url() . "/js/my_script.js",
                      "version" => $this->_version,
                      "deps"    => array("jquery"),
                      "strings" => array(
                          'first'  => __("First Choice", "simpleaddon"),
                          'second' => __("Second Choice", "simpleaddon"),
                          'third'  => __("Third Choice", "simpleaddon")
                      ),
                      "enqueue" => array(
                          array(
                              "admin_page" => array("form_settings"),
                              "tab"        => "simpleaddon"
                          )
                      )
                ),

            );

            return array_merge(parent::scripts(), $scripts);
        }

        public function styles() {

            $styles = array(
                array("handle"  => "paydock_styles",
                      "src"     => $this->get_base_url() . "/css/gf-paydock.css",
                      "version" => $this->_version,
                      "enqueue" => array(
                          array("field_types" => array("poll"))
                      )
                )
            );

            return array_merge(parent::styles(), $styles);
        }

    }





  // THIS IS OUR FUNCTION FOR CLEANING UP THE PRICING AMOUNTS THAT GF SPITS OUT
        function pd_clean_amount($entry) {
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


         function pd_payment($data) {
            $data_string = json_encode($data);
            $envoyrecharge_key = '3558c3a3fbc646111ecdc5bcba2df9a454eef2c7';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api-sandbox.envoyrecharge.com/v1/charges');
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
            return $result;
        }



    /**
     * Add checkboxes to the 'advanced' settings tab for each field in the gravity forms admin area.
     */
    function pd_gform_field_advanced_settings( $id, $form ) {
        if( $id != -1 )
            return;

        ?>
        <h3>PayDock Field Mapping</h3>
        <p>Select which PayDock setting you'd like to map this field to.<p>
        <li>
            <label><input type="radio" name="echopts" value="1" id="echFullName" onclick="SetFieldProperty('echFullName',this.checked);" />Full Name</label>
            <label><input type="radio" name="echopts" value="1" id="echPaymentTotal" onclick="SetFieldProperty('echPaymentTotal',this.checked);" />Payment Total</label>
            <label><input type="radio" name="echopts" value="1" id="echPaymentFrequency" onclick="SetFieldProperty('echPaymentFrequency',this.checked);" />Payment Frequency</label>
            <label><input type="radio" name="echopts" value="1" id="echEmail" onclick="SetFieldProperty('echEmail',this.checked);" />Email Address</label>
            <label><input type="radio" name="echopts" value="1" id="echBSB" onclick="SetFieldProperty('echBSB',this.checked);" />Bank: BSB</label>
            <label><input type="radio" name="echopts" value="1" id="echBankAcctNumber" onclick="SetFieldProperty('echBankAcctNumber',this.checked);" />Bank: Account Number</label>
            <label><input type="radio" name="echopts" value="1" id="echBankAcctName" onclick="SetFieldProperty('echBankAcctName',this.checked);" />Bank: Account Name</label>
            <label><input type="radio" name="echopts" value="1" id="echTransReference" onclick="SetFieldProperty('echTransReference',this.checked);" />Transaction Reference</label>
            <label><input type="radio" name="echopts" value="1" id="echPaymentType" onclick="SetFieldProperty('echPaymentType',this.checked);" />Payment Type</label>
        </li>
        <?php
        add_action( 'admin_footer', 'pd_handle_field_settings' );
    }

    /**
     * Add javascript to hook into gravity forms' form update code and update the hide if logged in/out values.
     */
    function pd_handle_field_settings() {

        ?>
        <script>
            jQuery(document).ready(function($){
                $(document).bind('gform_load_field_settings',function(event, field, form){
                    var echFullName = (undefined === field.echFullName) ? false : (true === field.echFullName),
                    echEmail = (undefined === field.echEmail) ? false : (true === field.echEmail),
                    echBSB = (undefined === field.echBSB) ? false : (true === field.echBSB),
                    echBankAcctNumber = (undefined === field.echBankAcctNumber) ? false : (true === field.echBankAcctNumber),
                    echBankAcctName = (undefined === field.echBankAcctName) ? false : (true === field.echBankAcctName),
                    echPaymentType = (undefined === field.echPaymentType) ? false : (true === field.echPaymentType),
                    echTransReference = (undefined === field.echTransReference) ? false : (true === field.echTransReference),
                    hili = (undefined === field.echPaymentTotal) ? false : (true === field.echPaymentTotal),
                    hilo = (undefined === field.echPaymentFrequency) ? false : (true === field.echPaymentFrequency);
                    $('#echPaymentTotal')[0].checked = hili;
                    $('#echPaymentFrequency')[0].checked = hilo;
                    $('#echFullName')[0].checked = echFullName;
                    $('#echEmail')[0].checked = echEmail;
                    $('#echBSB')[0].checked = echBSB;
                    $('#echBankAcctNumber')[0].checked = echBankAcctNumber;
                    $('#echBankAcctName')[0].checked = echBankAcctName;
                    $('#echTransReference')[0].checked = echTransReference;
                    $('#echPaymentType')[0].checked = echPaymentType;
                });
            });
        </script>
        <?php
    }

    // add_filter( 'gform_field_content', '_avm_dym_gform_field_content', 10, 5 );
    // add_action( 'init', '_avm_gforms_filter_for_validation' );
    if( is_admin() )
        add_action( 'gform_field_advanced_settings', 'pd_gform_field_advanced_settings', 10, 2 );


    add_filter( 'gform_enable_credit_card_field', 'pd_enable_creditcard', 11 );
    function pd_enable_creditcard( $is_enabled ) {
        return true;
    }



        add_filter( 'gform_validation', 'pd_custom_validation' );
        function pd_custom_validation( $validation_result ) {

            $form = $validation_result["form"];
            $form_id = $form["id"];

            $payment_enabled = $form["gravityformspaydock"]["pd_process_payment"];
            $selected_gateway = $form["gravityformspaydock"]["pd_select_gateway"];

            $fails = 0; foreach($form['fields'] as $field) if ($field['failed_validation']==1) $fails++; 
            if ($fails==0 && $payment_enabled=="1") {



            $data = array();
            $form = $validation_result['form'];
            foreach ($form["fields"] as $field) {


                if($field->echPaymentTotal){
                    // $amount = clean_amount(rgpost("input_".$field->id));
                    $amount = rgpost("input_".$field->id);
                }       
                if($field->echEmail){
                    $email = rgpost("input_".$field->id);
                }
                if($field->echFullName){
                    $firstname = rgpost("input_".$field->id."_3");
                    $lastname = rgpost("input_".$field->id."_6");
                }
                if($field->echPaymentType){
                    if(rgpost("input_".$field->id)=="bsb"){
                        $data["customer"]["payment_source"]["type"] = rgpost("input_".$field->id);
                    }
                }
                if($field->echBSB){
                    $data["customer"]["payment_source"]["account_bsb"] = rgpost("input_".$field->id);
                }
                if($field->echBankAcctNumber){
                    $data["customer"]["payment_source"]["account_number"] = rgpost("input_".$field->id);
                }
                if($field->echBankAcctName){
                    $data["customer"]["payment_source"]["account_name"] = rgpost("input_".$field->id);
                }
                if($field->echTransReference){
                    $data["reference"] = rgpost("input_".$field->id);
                }


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

                        $data["customer"]["first_name"] = $firstname;
                        $data["customer"]["last_name"] = $lastname;
                        $data["email"] = $email;
                        $data["amount"] = $amount;
                        $data["currency"] = (!empty($currency)) ? $currency : GFCommon::get_currency();
                        // $data["interval"] = $the_interval;
                        // $data["frequency"] = $frequency;
                        // if (isset($startdate))
                        // $data["startdate"] = $startdate; //format need to be date('d/m/Y');
                        // if (isset($enddate))
                        // $data["enddate"] = $enddate; // format need to be date('d/m/Y');
                        // if (isset($txtref))
                        // $data["txref"] = $txtref;
                        // if (isset($reference))
                        // $data["customer"]["payment_source"] = $reference;
                        $data["customer"]["payment_source"]["gateway_id"] = $selected_gateway;
                        // $data["address_line1"] = $address_line1;
                        // if (isset($address_line2))
                        // $data["address_line2"] = $address_line2;
                        // $data["address_city"] = $address_city;
                        // $data["address_postcode"] = $address_postcode;
                        // $data["address_state"] = $address_state;
                        // $data["address_country"] = $address_country;

                        $result = pd_payment($data);
                        $result = json_decode($result);


                //supposing we don't want input 1 to be a value of 86
                if ( $result->status > "250") {

                    // set the form validation to false
                    $validation_result['is_valid'] = false;

                    //finding Field with ID of 1 and marking it as failed validation
                    foreach( $form['fields'] as &$field ) {

                        //NOTE: replace 1 with the field you would like to validate
                        if ( $field->id == '9' ) {
                            $field->failed_validation = true;
                            $field->validation_message = 'There was a problem processing your payment, please try again or contact us.';
                            break;
                        }
                    }

                }

            }  // end condition around standard validation

            //Assign modified $form object back to the validation result
            $validation_result['form'] = $form;
            return $validation_result;
    }



    new GFPayDockAddOn();

}