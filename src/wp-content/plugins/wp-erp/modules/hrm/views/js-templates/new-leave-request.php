<?php

use WeDevs\ERP\HRM\Models\Financial_Year;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\HRM\Models\Employee;
use WeDevs\ERP\HRM\Models\Leave_Request;
use WeDevs\ORM\WP\User;

$erp_user = WeDevs\ORM\WP\User::where( 'ID', get_current_user_id())->first();
$erp_employee_user = WeDevs\ERP\HRM\Models\Employee::where( 'user_id', get_current_user_id())->first();
$employee_types     = erp_hr_get_assign_policy_from_entitlement( get_current_user_id() );
$types              = $employee_types ? array_unique( $employee_types ) : [];
$financial_years    = [];
$department = '';
$designation = '';
$substitute_required = array(1 => "Yes", 0 => "No");
$substitute_type    = Leave_Request::get_substitute_type_enum();

$current_f_year = erp_hr_get_financial_year_from_date();

if ( null === $current_f_year ) {
    erp_html_show_notice( __( 'No leave assigned for current year. Please contact HR.', 'erp' ), 'error', true );

    return;
}

foreach ( Financial_Year::all() as $f_year ) {
    if ( $f_year['start_date'] < $current_f_year->start_date ) {
        continue;
    }
    $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
}

foreach ( Department::all() as $f_department ) {
    if($f_department['id'] == $erp_employee_user->department) {
        $department = $f_department['title'];
    }
}

foreach ( Designation::all() as $f_designation ) {
    if($f_designation['id'] == $erp_employee_user->designation) {
        $designation = $f_designation['title'];
    }
}

?>
<div class="erp-hr-leave-request-new erp-hr-leave-reqs-wrap">
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

    <div class="row erp-hide erp-hr-leave-type-wrapper"></div>

    <?php do_action( 'erp_hr_leave_request_form_middle' ); ?>

    <div class="row erp-leave-flex">
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'Name', 'erp' ),
                'name'        => 'name',
                'value'       => $erp_user->display_name,
                'required'    => true,
                'disabled'    => true,
                'readonly'    => true
            ] ); ?>
        </span>
        <span>     
            <?php erp_html_form_input( [
                'label'       => __( 'Date', 'erp' ),
                'name'        => 'date',
                'value'       => erp_current_datetime()->format( 'Y-m-d' ),
                'required'    => true,
                'disabled'    => true,
                'readonly'    => true
            ] ); ?>
        </span>     
    </div>

    <div class="row erp-leave-flex">
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'Post', 'erp' ),
                'name'        => 'employee_position',
                'value'       => $designation,
                'required'    => true,
                'disabled'    => true,
                'readonly'    => true    
            ] ); ?>
        </span>
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'Ministry/Department', 'erp' ),
                'name'        => 'employee_department',
                'value'       => $department,
                'required'    => true,
                'disabled'    => true,
                'readonly'    => true                          
            ] ); ?>
        </span>             
    </div>    

    <div class="row erp-leave-flex">
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'Taken for the year', 'erp' ),
                'name'        => 'taken_year',
                'value'       => date("Y"),
                'type'        => 'number',
                'required'    => true
            ] ); ?>
        </span>        
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'From', 'erp' ),
                'name'        => 'leave_from',
                'id'          => 'erp-hr-leave-req-from-date',
                'value'       => '',
                'required'    => true,
                'class'       => 'erp-leave-date-field',
                'custom_attr' => [
                    'autocomplete' => 'off',
                ],
            ] ); ?>
        </span>
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'To', 'erp' ),
                'name'        => 'leave_to',
                'id'          => 'erp-hr-leave-req-to-date',
                'value'       => '',
                'required'    => true,
                'class'       => 'erp-leave-date-field',
                'custom_attr' => [
                    'autocomplete' => 'off',
                ],
            ] ); ?>
        </span>
    </div>

    <div class="erp-hr-leave-req-show-days show-days" style="margin:20px 0px;"></div>

    <div class="row erp-leave-flex">
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'in the Federation', 'erp' ),
                'name'        => 'days_in_federation',
                'type'        => 'number',
                'required'    => false
            ] ); ?>
        </span>
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'outside the Federation', 'erp' ),
                'name'        => 'days_out_federation',
                'type'        => 'number',
                'required'    => false
            ] ); ?>
        </span>
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'Contact No.', 'erp' ),
                'name'        => 'contact_no',
                'required'    => false
            ] ); ?>
        </span>        
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Address while on leave', 'erp' ),
            'tooltip'     => 'if outside the Federation',
            'name'        => 'address_on_leave',
            'type'        => 'textarea',
            'required'    => false,
            'custom_attr' => [ 'cols' => 25, 'rows' => 3 ],
        ] ); ?>
    </div>

    <div class="row erp-leave-flex">
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'Substitude required?', 'erp' ),
                'name'        => 'substitute_required',
                'type'        => 'radio',
                'required'    => false,
                'options'     => $substitute_required
            ] ); ?>
        </span>
        <span>
            <?php erp_html_form_input( [
                'label'       => __( 'Substitude Type', 'erp' ),
                'name'     => 'substitute_type',
                'value'    => '',
                'required' => false,
                'type'     => 'select',
                'options'  => $substitute_type,
            ] ); ?>
        </span>      
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Reason', 'erp' ),
            'name'        => 'leave_reason',
            'type'        => 'textarea',
            'required'    => false,
            'custom_attr' => [ 'cols' => 25, 'rows' => 3 ],
        ] ); ?>
    </div>

    <div class="row">
        <label for="leave_document"><?php echo esc_html__( 'Document', 'wp-erp' ); ?></label>
        <input type="file" name="leave_document[]" id="leave_document" multiple>
    </div>

    <input type="hidden" name="employee_id" id="erp-hr-leave-req-employee-id" value="<?php echo esc_html( get_current_user_id() ); ?>">
    <input type="hidden" name="action" value="erp-hr-leave-req-new">
    <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
</div>
