<?php
if (!defined('ABSPATH'))
    exit;
if(!class_exists('Hbwf_Subscribe')){

	class Hbwf_Subscribe{

		public function __construct(){
 
			add_action('wpforms_process',array($this,'acwf_sends_data_to_ac'),10,3);
		}

		public function acwf_sends_data_to_ac($fields,$entry,$form_data){
            
            //Global Settings
			$API_KEY = wpforms_setting('hb-apikey');
			$list_ID = wpforms_setting('hb-listid');
			$lifecycle_stage = wpforms_setting('hb-lifecycle-stage');
			$lead_status = wpforms_setting('hb-lead-status');
            
            $entry_id = $form_data['id'];
            $hb_integration = $form_data['settings']['hb-integration'];
			$hbfield = $form_data['settings']['assign-hbfields'];

			$hb_ebable = $hb_integration['enable-hb'];
			$API_KEY = isset($hb_integration['hb-apikey']) ? $hb_integration['hb-apikey'] : $API_KEY;
			$list_ID = isset($hb_integration['list-id']) ? $hb_integration['list-id'] : $list_ID;
			$lifecycle_stage = isset($hb_integration['hb-lifecycle-stage']) ? $hb_integration['hb-lifecycle-stage'] : $lifecycle_stage;
			$lead_status = isset($hb_integration['hb-lead-status']) ? $hb_integration['hb-lead-status'] : $lead_status;

            $email = $this->process_tag( $hbfield['email'], $form_data,$fields,$entry_id );
            $fname = $this->process_tag( $hbfield['first-name'], $form_data,$fields,$entry_id);
            $lname = $this->process_tag( $hbfield['last-name'], $form_data,$fields,$entry_id );
            $phone = $this->process_tag( $hbfield['phone'], $form_data,$fields,$entry_id );
            $company = $this->process_tag( $hbfield['company'], $form_data,$fields,$entry_id );
            
			//Active Campaign starts
			if($hb_ebable == '1' && !empty($email)){

				if(!empty($API_KEY) && !empty($list_ID)){

				    $user_info = [
				    	'user_email' => $email,
				    	'first_name' => $fname,
				    	'last_name' => $lname,
				    	'phone' => $phone,
				    	'company' => $company,
				    	'lifecycle' => $lifecycle_stage,
				    	'lead' => $lead_status,
				    	'extra_fields' => $extra_fields
				    ];
                    
                    //create contact
					$response = $this->contact_create($user_info,$API_KEY);
					$response = json_decode($response,true);

					if(isset($response['status']) && $response['status']=='error'){
						wpforms()->process->errors[ $form_data['id'] ]['header'] = $response['message'];
					}

					//assign contact to list
					if($list_ID){
						$list_assign = $this->list_assign_contact($list_ID, $user_info['user_email'],$API_KEY);
						$list_assign = json_decode($list_assign,true);

						if(isset($list_assign['status'])&&$list_assign['status']=='error'){
							wpforms()->process->errors[ $form_data['id'] ]['header'] = $list_assign['message'];
						}
					}
                
				}else{
					wpforms()->process->errors[ $form_data['id'] ]['header'] = esc_html__( 'You have enabled HubSpot but have not add API Key and List ID.', 'wpop-wpforms-hubspot' );
				}
			}

		}

		public function contact_create($user_info,$api_key){

			$arr = array('properties' => array(

				array(
					'property' => 'email',
					'value' => $user_info['user_email']
				),
				array(
					'property' => 'lastname',
					'value' => $user_info['last_name']
				),
				array(
					'property' => 'firstname',
					'value' => $user_info['first_name']
				),
				array(
					'property' => 'phone',
					'value' => $user_info['phone']
				),
				array(
					'property' => 'company',
					'value' => $user_info['company']
				),
				array(
					'property' => 'lifecyclestage',
					'value' => $user_info['lifecycle']
				),
				array(
					'property' => 'hs_lead_status',
					'value' => $user_info['lead']
				),
			));


			$post_json = json_encode($arr);
			$endpoint = 'https://api.hubapi.com/contacts/v1/contact';
			return $this->http($endpoint,$post_json,$api_key);
		}

		public function list_assign_contact($lid, $email,$api_key){
			(object)$arr = array(
				"emails" => array($email)
			);
			$post_json = json_encode($arr);
			$endpoint = 'https://api.hubapi.com/contacts/v1/lists/'.(int)$lid.'/add';
			return $this->http($endpoint,$post_json,$api_key);
			//die();
		} 

		public function http($endpoint,$post_json,$api_key){

			$args = array(
        		'method' => 'POST',
        		'timeout'     => 15,
        		'redirection' => 15,
    		    'headers'     => array(
    		    	'Content-Type' => 'application/json',
			        'Authorization' => 'Bearer ' . $api_key,
			    ),
        		//'headers'     => "Authorization: application/json",
        		'body' => $post_json,
        	);
        	$response = wp_remote_request( $endpoint, $args);

           	if( is_wp_error( $response ) ) {
           		$msg = ['status'=>'error','messgae'=>'Error!'];
            	return json_encode($msg);
        	}
        	else{
        		$api_resp =  wp_remote_retrieve_body($response);
        		$code = wp_remote_retrieve_response_code( $response );
        		/*print_r($code);
        		print_r($api_resp);
        		die();*/
        		if($code == 401||$code == 403){
        			return $api_resp;
        		}else{
        			$msg = ['status'=>'success','messgae'=>'Success'];
        			return json_encode($msg);
        		}
        	}

		}

		public function process_tag( $string = '', $form_data, $fields, $entry_id ) {

			$tag = apply_filters( 'wpforms_process_smart_tags', $string, $form_data, $fields, $entry_id );

			$tag = wpforms_decode_string( $tag );
			$tag = sanitize_text_field( $tag );

			return $tag;
		}	

	}
	new Hbwf_Subscribe();
}