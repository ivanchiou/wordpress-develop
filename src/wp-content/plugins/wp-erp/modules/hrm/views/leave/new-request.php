<div class="wrap erp erp-hr-leave-request-new erp-hr-leave-reqs-wrap">
    <div class="postbox">
        <h3 class="hndle">
            <?php esc_html_e( 'New Leave Request', 'erp' ); ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=leave&sub-section=leave-requests' ) ); ?>" id="erp-new-leave-request" class="add-new-h2" style="top: 0px;"><?php esc_html_e( 'Back to Leave Requests', 'erp' ); ?></a>
        </h3>
        <div class="inside">
            <?php
            use WeDevs\ERP\HRM\Models\Financial_Year;
            use WeDevs\ERP\HRM\Models\Leave_Request;

            if ( isset( $_GET['insert_error'] ) && $_GET['insert_error'] !== '' ) {
                $errors = new \WeDevs\ERP\ERP_Errors( sanitize_text_field( wp_unslash( $_GET['insert_error'] ) ) );
                echo $errors->display();
            } elseif ( isset( $_GET['msg'] ) && $_GET['msg'] == 'submitted' ) {
                erp_html_show_notice( __( 'Leave request has been submitted successfully.', 'erp' ), 'updated', true );
            }
            $financial_years = [];
            $current_f_year  = erp_hr_get_financial_year_from_date();
            $substitute_required = array(1 => "Yes", 0 => "No");
            $substitute_type    = Leave_Request::get_substitute_type_enum();
            $total_taken_year   = Leave_Request::get_total_taken_year();

            foreach ( Financial_Year::all() as $f_year ) {
                if ( $f_year['start_date'] < $current_f_year->start_date ) {
                    continue;
                }
                $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
            }
            $f_year_help_text = '';

            if ( current_user_can( 'erp_leave_create_request' ) ) {
                $f_year_help_text .= ' ' . sprintf( '<a href="?page=erp-settings&tab=erp-hr&section=financial">%s</a>', __( 'Add New', 'erp' ) );
            }
            ?>

            <form action="" method="post" class="new-leave-request-form" enctype="multipart/form-data">
                <?php
                if ( count( $financial_years ) === 1 ) { ?>
                    <input type="hidden" name="f_year" id="f_year" class="f_year" value="<?php echo key( $financial_years ); ?>" />
                    <?php
                } else {
                    echo '<div class="row">';
                    erp_html_form_input( [
                        'label'    => esc_html__( 'Year', 'erp' ),
                        'name'     => 'f_year',
                        'value'    => '',
                        'required' => true,
                        'class'    => 'f_year',
                        'type'     => 'select',
                        'options'  => $financial_years,
                    ] );
                    echo '</div>';
                }?>

                <?php if ( current_user_can( 'erp_leave_create_request' ) ) { ?>
                    <div class="row two-col">
                        <div class="cols">
                            <?php erp_html_form_input( [
                                'label'    => __( 'Employee', 'erp' ),
                                'name'     => 'employee_id',
                                'id'       => 'erp-hr-leave-req-employee-id',
                                'value'    => '',
                                'class'    => 'erp-select2',
                                'required' => true,
                                'type'     => 'select',
                                'options'  => erp_hr_get_employees_dropdown_raw(),
                            ] ); ?>
                        </div>
                        <div class="cols">  
                            <?php erp_html_form_input( [
                                'label'       => __( 'Date', 'erp' ),
                                'name'        => 'date',
                                'value'       => erp_current_datetime()->format( 'Y-m-d' ),
                                'required'    => true,
                                'disabled'    => true,
                                'readonly'    => true
                            ] ); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="row erp-hide erp-hr-leave-type-wrapper"></div>

                <?php do_action( 'erp_hr_leave_request_form_middle' ); ?>                
                <?php if ( current_user_can( 'erp_leave_create_request' ) ) { ?>
                    <div class="row two-col">
                        <div class="cols">
                            <?php erp_html_form_input( [
                                'label'       => __( 'Post', 'erp' ),
                                'name'        => 'employee_position',
                                'id'          => 'employee_position',
                                'required'    => true,
                                'disabled'    => true,
                                'readonly'    => true
                            ] ); ?>
                        </div>
                        <div class="cols">
                            <?php erp_html_form_input( [
                                'label'       => __( 'Ministry/Department', 'erp' ),
                                'name'        => 'employee_department',
                                'id'          => 'employee_department',
                                'required'    => true,
                                'disabled'    => true,
                                'readonly'    => true,                   
                            ] ); ?>
                        </div>             
                    </div>              
                <?php } ?>

                <div class="row two-col">
                    <div>
                        <?php erp_html_form_input( [
                            'label'       => __( 'Taken for the year', 'erp' ),
                            'name'        => 'taken_year',
                            'value'       => $total_taken_year,
                            'type'        => 'number',
                            'required'    => true,
                            'disabled'    => true,
                            'readonly'    => true
                        ] ); ?>
                    </div>                    
                    <div class="cols">
                        <?php erp_html_form_input( [
                            'label'       => __( 'From', 'erp' ),
                            'name'        => 'leave_from',
                            'id'          => 'erp-hr-leave-req-from-date',
                            'value'       => '',
                            'required'    => true,
                            'class'       => 'erp-leave-date-field',
                            'custom_attr' => [
                                'disabled'     => 'disabled',
                                'autocomplete' => 'off',
                            ],
                        ] ); ?>
                    </div>

                    <div class="cols last erp-leave-to-date">
                        <?php erp_html_form_input( [
                            'label'       => __( 'To', 'erp' ),
                            'name'        => 'leave_to',
                            'id'          => 'erp-hr-leave-req-to-date',
                            'value'       => '',
                            'required'    => true,
                            'class'       => 'erp-leave-date-field',
                            'custom_attr' => [
                                'disabled'     => 'disabled',
                                'autocomplete' => 'off',
                            ],
                        ] ); ?>
                    </div>
                </div>

                <div class="row erp-hr-leave-req-show-days show-days"></div>

                <div class="row two-col">
                    <div class="cols">
                        <?php erp_html_form_input( [
                            'label'       => __( 'in the Federation', 'erp' ),
                            'name'        => 'days_in_federation',
                            'type'        => 'number',
                            'required'    => false,
                            'disabled'    => true,
                        ] ); ?>
                    </div>
                    <div class="cols">
                        <?php erp_html_form_input( [
                            'label'       => __( 'outside the Federation', 'erp' ),
                            'name'        => 'days_out_federation',
                            'type'        => 'number',
                            'required'    => false,
                            'disabled'    => true,
                        ] ); ?>
                    </div>
                    <div>
                        <?php erp_html_form_input( [
                            'label'       => __( 'Contact No.', 'erp' ),
                            'name'        => 'contact_no',
                            'required'    => false,
                            'disabled'    => true,
                        ] ); ?>
                    </div>        
                </div>
                
                <div class="row">
                    <?php erp_html_form_input( [
                        'label'       => __( 'Address while on leave', 'erp' ),
                        'tooltip'     => 'if outside the Federation',
                        'name'        => 'address_on_leave',
                        'type'        => 'textarea',
                        'required'    => false,
                        'custom_attr' => [ 'cols' => 30, 'rows' => 3, 'disabled' => 'disabled'],
                    ] ); ?>
                </div>

                <div class="row two-col">
                    <div class="cols">
                        <?php erp_html_form_input( [
                            'label'       => __( 'Substitute required?', 'erp' ),
                            'name'        => 'substitute_required',
                            'type'        => 'radio',
                            'required'    => false,
                            'options'     => $substitute_required,
                            'disabled'    => true,
                        ] ); ?>
                    </div>
                    <div class="cols">
                        <?php erp_html_form_input( [
                            'label'       => __( 'Substitute Type', 'erp' ),
                            'name'     => 'substitute_type',
                            'value'    => '',
                            'required' => false,
                            'type'     => 'select',
                            'options'  => $substitute_type,
                            'disabled'    => true,
                        ] ); ?>
                    </div>      
                </div>                

                <div class="row">
                    <?php erp_html_form_input( [
                        'label'       => __( 'Reason', 'erp' ),
                        'name'        => 'leave_reason',
                        'type'        => 'textarea',
                        'required'    => false,
                        'custom_attr' => [ 'cols' => 30, 'rows' => 3, 'disabled' => 'disabled' ],
                    ] ); ?>
                </div>

                <div class="row">
                    <label for="leave_document"><?php echo esc_html__( 'Document', 'wp-erp' ); ?></label>
                    <input type="file" name="leave_document[]" id="leave_document" multiple>
                </div>

                <input type="hidden" name="erp-action" value="hr-leave-req-new">
                <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
                <?php submit_button( __( 'Submit Request', 'erp' ), 'primary', 'submit', true, [ 'disabled' => 'disabled' ]  ); ?>

            </form>
        </div><!-- .inside-->
    </div><!-- .postbox-->
</div><!-- .wrap -->

<?php erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-days.php', 'erp-leave-days' ); ?>
