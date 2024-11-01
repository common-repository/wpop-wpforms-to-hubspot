<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if(!class_exists('HBwpf_Register_Settings')){
	class HBwpf_Register_Settings {

        private $lifecycle_stages;
        private $lead_status;

		public function __construct() {

			$this->lifecycle_stages = array(
	        	'subscriber' => esc_html('Subscriber','wpop-wpforms-hubspot'),
	        	'lead' => esc_html('Lead','wpop-wpforms-hubspot'),
	        	'marketingqualifiedlead' => esc_html('Marketing qualified lead','wpop-wpforms-hubspot'),
	        	'salesqualifiedlead' => esc_html('Sales qualified lead','wpop-wpforms-hubspot'),
	        	'opportunity' => esc_html('Opportunity','wpop-wpforms-hubspot'),
	        	'customer' => esc_html('Customer','wpop-wpforms-hubspot'),
	        	'evangelist' =>  esc_html('Evangelist','wpop-wpforms-hubspot'),
	        	'other' => esc_html('Other','wpop-wpforms-hubspot'),
	        );
	        $this->lead_status = array(
	        	'' => esc_html('Choose Status','wpop-wpforms-hubspot'),
	        	'NEW' => esc_html('New','wpop-wpforms-hubspot'),
	        	'OPEN' => esc_html('Open','wpop-wpforms-hubspot'),
	        	'IN_PROGRESS' => esc_html('In Progress','wpop-wpforms-hubspot'),
	        	'OPEN_DEAL' => esc_html('Open Deal','wpop-wpforms-hubspot'),
	        	'UNQUALIFIED' => esc_html('Unqualified','wpop-wpforms-hubspot'),
	        	'ATTEMPTED_TO_CONTACT' => esc_html('Attempted to Contact','wpop-wpforms-hubspot'),
	        	'CONNECTED' => esc_html('Connected','wpop-wpforms-hubspot'),
	        	'BAD_TIMING' => esc_html('Bad timing','wpop-wpforms-hubspot'),
	        );

			// Let's make some menus.
			add_filter( 'wpforms_settings_tabs', array( $this, 'add_hb_setting_tab' ), 9 );

			add_filter( 'wpforms_settings_defaults', array( $this, 'add_fields' ), 9 );
            
            add_filter( 'wpforms_builder_settings_sections', array( $this, 'assign_hb_fields_menu' ), 9 );

            add_action('wpforms_form_settings_panel_content',array($this,'assign_ac_fields'),10);

		}

		public function add_hb_setting_tab($tabs){
			$tabs['hb-integration'] = array(
				'name'   => esc_html__( 'HubSpot Integration', 'wpop-wpforms-hubspot' ),
				'form'   => true,
				'submit' => esc_html__( 'Save Settings', 'wpop-wpforms-hubspot' ),
			);

			return $tabs;
		}

		public function add_fields($defaults){

			$defaults['hb-integration'] = array(
				'hb-heading' => array(
					'id'       => 'hb-heading',
					'content'  => '<h4>' . esc_html__( 'HubSpot Settings', 'wpop-wpforms-hubspot' ) . '</h4><p>' . esc_html__( 'Add your HubSpot API credentials here.This is a global setting area,you can also add Access Token seperately for each forms.', 'wpop-wpforms-hubspot' ) . '</p>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => array( 'section-heading' ),
				),
				'hb-apikey'            => array(
					'id'      => 'hb-apikey',
					'name'    => esc_html__( 'HubSpot Access Token', 'wpop-wpforms-hubspot' ),
					'type'    => 'text',
					'desc' => sprintf(esc_html__( 'You can get Access Token like %s.', 'wpop-wpforms-hubspot' ),'<a href="https://developers.hubspot.com/docs/api/private-apps" target="_blank">this</a>'),
				),
				'hb-listid'            => array(
					'id'      => 'hb-listid',
					'name'    => esc_html__( 'List ID', 'wpop-wpforms-hubspot' ),
					'type'    => 'text',
					'desc'    => esc_html__( 'You Must Add List Id to add contacts in your Hubspot Lists. Works only for Static Lists.', 'wpop-wpforms-hubspot' ),
				),
				'hb-lifecycle-stage'            => array(
					'id'      => 'hb-lifecycle-stage',
					'name'    => esc_html__( 'Lifecycle Stage', 'wpop-wpforms-hubspot' ),
					'type'    => 'select',
					'default'     => 'subscriber',
					'options'     => $this->lifecycle_stages,

				),
				'hb-lead-status'            => array(
					'id'      => 'hb-lead-status',
					'name'    => esc_html__( 'Lead Status', 'wpop-wpforms-hubspot' ),
					'type'    => 'select',
					'default'     => '',
					'options'     => $this->lead_status,

				),
			);

			return $defaults;

		}

		public function assign_hb_fields_menu($sections){
			$sections['hb-integration'] = __('HubSpot Integration','wpop-wpforms-hubspot');
			$sections['assign-hbfields'] = __('Assign Form Fields to HubSpot','wpop-wpforms-hubspot');

			return $sections;
		}

		public function assign_ac_fields($settings){

			//AC Integration Tab
			echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-hb-integration">';
			echo '<div class="wpforms-panel-content-section-title">';
				esc_html_e( 'HubSpot Integration', 'wpop-wpforms-hubspot' );
			echo '</div>';
			echo '<br><em class="field-desc">This will replace the global settings.</em>';	

			echo '<div class="wpforms-builder-settings-block-content">';
				wpforms_panel_field(
					'checkbox',
					'hb-integration',
					'enable-hb',
					$settings->form_data,
					esc_html__( 'Enable HubSpot', 'wpop-wpforms-hubspot' ),
					array(
						'default'    => '0',
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);
				wpforms_panel_field(
					'text',
					'hb-integration',
					'hb-apikey',
					$settings->form_data,
					esc_html__( 'HubSpot Access Token', 'wpop-wpforms-hubspot' ),
					array(
						'default'    => '',
						'tooltip'    => sprintf(esc_html__( 'You can get Access Token like %s.', 'wpop-wpforms-hubspot' ),'<a href="https://developers.hubspot.com/docs/api/private-apps" target="_blank">this</a>'),
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);
				$form_data = $settings->form_data;
				$list_id = isset($form_data['settings']['hb-integration']['list-id']) ? $form_data['settings']['hb-integration']['list-id'] : '';
				?>
				<div id="wpforms-panel-field-hb-integration-list_ids-wrap" class="wpforms-panel-field email-recipient wpforms-panel-field-text">
	            <label for="acwf_list_id"><?php echo __("HubSpot Email List ID","wpop-wpforms-hubspot"); ?></label>
			    <em style="padding:0"><?php echo __("You Must Add List Id to add contacts in your Hubspot Lists. Works only for Static Lists.","wpop-wpforms-hubspot"); ?></em>

		        <input type="number" min="0" name="settings[hb-integration][list-id]" value="<?php echo esc_attr($list_id); ?>" />
		        <span class="add-button table-contacts"><a href="javascript:void(0)" class="docopy-table-list button"><?php esc_html_e('Add List','wpop-wpforms-hubspot'); ?></a></span>
			    </div>
			    <div style="display:inline-block;margin-bottom: 20px;">
		    	<em class="field-desc" style="color:red;"><?php echo __("Available in Premium Version.","wpop-wpforms-hubspot"); ?>
		    		<a href="https://wpoperation.com/plugins/wpop-wpforms-hubspot-pro/" target="_blank"><?php esc_html_e('Get Pro Version','wpop-wpforms-hubspot'); ?></a>
		    	</em>
		    	</div>
				<?php
				wpforms_panel_field(
					'select',
					'hb-integration',
					'hb-lifecycle-stage',
					$settings->form_data,
					esc_html__( 'Lifecycle Stage', 'wpop-wpforms-hubspot' ),
					array(
						'default'     => 'subscriber',
						'options'     => $this->lifecycle_stages,
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);
				wpforms_panel_field(
					'select',
					'hb-integration',
					'hb-lead-status',
					$settings->form_data,
					esc_html__( 'Lead Status', 'wpop-wpforms-hubspot' ),
					array(
						'default'     => '',
						'options'     => $this->lead_status,
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);

			echo '</div>';
			echo '</div>';

			//AC Field Assign Tab
			echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-assign-hbfields">';

			echo '<div class="wpforms-panel-content-section-title">';
				esc_html_e( 'Assign Form Fields', 'wpop-wpforms-hubspot' );
			echo '</div>';
            $id = 1;
			echo '<div class="wpforms-builder-settings-block-content">';
				wpforms_panel_field(
					'text',
					'assign-hbfields',
					'email',
					$settings->form_data,
					esc_html__( 'Email Address*', 'wpop-wpforms-hubspot' ),
					array(
						'default'    => '',
						'tooltip'    => esc_html__( 'Enter the email address to save in your Active Campaign Contact Lists.You can also use the smart tags from your form fields.', 'wpop-wpforms-hubspot' ),
						'smarttags'  => array(
							'type'   => 'fields',
							'fields' => 'email',
						),
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);
			echo '</div>';

			echo '<span><b>';
			echo esc_html__('Following fields are optional select if available in form, otherwise leave unselected. Only email field is required.','wpop-wpforms-hubspot');
			echo '</b></span><br>';

			echo '<div class="wpforms-builder-settings-block-content">';
				wpforms_panel_field(
					'text',
					'assign-hbfields',
					'first-name',
					$settings->form_data,
					esc_html__( 'First Name', 'wpop-wpforms-hubspot' ),
					array(
						'default'    => '',
						'tooltip'    => esc_html__( 'You can use the smart tags from your form fields.', 'wpop-wpforms-hubspot' ),
						'smarttags'  => array(
							'type' => 'all',
						),
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);
				wpforms_panel_field(
					'text',
					'assign-hbfields',
					'last-name',
					$settings->form_data,
					esc_html__( 'Last Name', 'wpop-wpforms-hubspot' ),
					array(
						'default'    => '',
						'tooltip'    => esc_html__( 'You can use the smart tags from your form fields.', 'wpop-wpforms-hubspot' ),
						'smarttags'  => array(
							'type' => 'all',
						),
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);

				echo '<em class="field-desc">';
				echo esc_html__('Note:If you have Name field containing first and last name then you can skip this Last Name field','wpop-wpforms-hubspot');
				echo'</em><br><br>';

				wpforms_panel_field(
					'text',
					'assign-hbfields',
					'phone',
					$settings->form_data,
					esc_html__( 'Phone', 'wpop-wpforms-hubspot' ),
					array(
						'default'    => '',
						'tooltip'    => esc_html__( 'You can use the smart tags from your form fields.', 'wpop-wpforms-hubspot' ),
						'smarttags'  => array(
							'type' => 'all',
						),
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);

				wpforms_panel_field(
					'text',
					'assign-hbfields',
					'company',
					$settings->form_data,
					esc_html__( 'Company', 'wpop-wpforms-hubspot' ),
					array(
						'default'    => '',
						'tooltip'    => esc_html__( 'You can use the smart tags from your form fields.', 'wpop-wpforms-hubspot' ),
						'smarttags'  => array(
							'type' => 'all',
						),
						'parent'     => 'settings',
						'class'      => 'email-recipient',
					)
				);

				?>

                <div class="wpforms-panel-field" style="margin-top:40px;">
                <label><?php echo __("Add Custom Properties","wpop-wpforms-hubspot"); ?></label>
			    <div class="contacts-meta-section-wrapper">
			    	<span class="add-button table-contacts"><a href="javascript:void(0)" class="docopy-table-contact button"><?php esc_html_e('Add Field','wpop-wpforms-hubspot'); ?></a></span>
			    </div>
			    </div>
		    	<em class="field-desc" style="color:red"><?php echo __("Available in Premium Version.","wpop-wpforms-hubspot"); ?>
		    		<a href="https://wpoperation.com/plugins/wpop-wpforms-hubspot-pro/" target="_blank"><?php esc_html_e('Get Pro Version','wpop-wpforms-hubspot'); ?></a>
		    	</em>
			    <?php	    
			echo '</div>';

			echo '</div>';
		}
	}
} 

new HBwpf_Register_Settings();