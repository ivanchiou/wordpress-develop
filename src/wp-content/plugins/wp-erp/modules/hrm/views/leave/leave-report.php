<?php

use WeDevs\ERP\HRM\Models\Financial_Year;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\HRM\Models\Employee;
use WeDevs\ERP\HRM\Models\Leave;
use WeDevs\ERP\HRM\Models\Leave_Request;
use WeDevs\ERP\HRM\Models\Leave_Approval_Status;
use WeDevs\ORM\WP\User;

if(isset( $_GET['user_id'] ) && isset( $_GET['leave_id'] )) {

    $user_id = sanitize_text_field( wp_unslash( $_GET['user_id'] ));
    $leave_id = sanitize_text_field( wp_unslash( $_GET['leave_id'] ));

    $erp_user = WeDevs\ORM\WP\User::where( 'ID', $user_id)->first();
    $erp_employee_user = WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $user_id)->first();
    $erp_employee_object = new \WeDevs\ERP\HRM\Employee( intval($user_id) );
    $erp_leave_1st_approval = WeDevs\ERP\HRM\Models\Leave_Approval_Status::where( 'leave_request_id', $leave_id)->where( 'approval_status_id', 5 )->first();
    if ( ! empty( $erp_leave_1st_approval )) {
        $erp_leave_1st_approval_user = WeDevs\ORM\WP\User::where( 'ID', $erp_leave_1st_approval->approved_by)->first();
        $erp_leave_1st_approval_object = new \WeDevs\ERP\HRM\Employee( intval($erp_leave_1st_approval->approved_by) );
    }
    $erp_leave_final_approval = WeDevs\ERP\HRM\Models\Leave_Approval_Status::where( 'leave_request_id', $leave_id)->where( 'approval_status_id', 1 )->first();
    if ( ! empty( $erp_leave_final_approval )) {
        $erp_leave_final_approval_user = WeDevs\ORM\WP\User::where( 'ID', $erp_leave_final_approval->approved_by)->first();
        $erp_leave_final_approval_object = new \WeDevs\ERP\HRM\Employee( intval($erp_leave_final_approval->approved_by) );
    }
    $employee_types     = erp_hr_get_assign_policy_from_entitlement($user_id);
    $types              = $employee_types ? array_unique( $employee_types ) : [];
    $financial_years    = [];
    $leave_policy_options = [];
    $department = '';
    $designation = '';
    $substitute_required = array(1 => "Yes", 0 => "No");
    $substitute_type    = Leave_Request::get_substitute_type_enum();
    $substitute_type_options = [];
    foreach($substitute_type as $key=>$value) {
        $substitute_type_options[$value] = $value;
    }

    $leave = WeDevs\ERP\HRM\Models\Leave_Request::where( 'id', $leave_id)->first();
    $leave_policy = WeDevs\ERP\HRM\Models\Leave::where( 'id', $leave->leave_id)->first();

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

    foreach ( Leave::all() as $f_leave ) {
        $leave_policy_options[ $f_leave['id'] ] = $f_leave['name'];
    }    
} else {
    echo '<script> history.go(-1) </script>';
    exit();
}

?>
<style>
    #wpfooter {
        display: none;
    }
</style>
<script>
    jQuery("#wpadminbar").hide();
    jQuery("#adminmenumain").hide();
    jQuery(".notice-warning").hide();
    jQuery("body").css("background", "#ffffff");
    jQuery("#wpcontent").css("margin-left", "0px");
    jQuery("#wpcontent").css("padding-right", "10px");
</script>
<div class="erp-hr-leave-request-new erp-hr-leave-reqs-wrap erp-hr-leave-report">
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

    <?php do_action( 'erp_hr_leave_request_form_middle' ); ?>
    <div class="erp-text-center erp-font-bold">
        FEDERATION OF ST CHRISTOPHER AND NEVIS<br/> 
        HUMAN RESOURCE MANAGEMENT DEPARTMENT
    </div>
    <div class="row erp-text-center erp-leave-report-flex erp-font-bold">
        <?php erp_html_form_input( [
            'label'       => __( 'Leave Applying For', 'erp' ),
            'name'        => 'policy',
            'value'       => strval($leave_policy->id),
            'type'        => 'multicheckbox',
            'options'     => $leave_policy_options,
            'required'    => false,
            'disabled'    => true,
            'readonly'    => true
        ] ); ?>
    </div>
    <table class="erp-table erp-report-table">
        <tr>
            <th colspan="5">Section A: Employee Information</th>
        </tr>
        <tr>
            <td class="row" colspan="2">
                <?php erp_html_form_input( [
                    'label'       => __( 'Name', 'erp' ),
                    'name'        => 'name',
                    'value'       => $erp_user->display_name,
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>
            <td class="row" colspan="2">
                <?php erp_html_form_label(
                    __( 'Signature', 'erp' )
                ); ?>
                <?php echo $erp_employee_object->get_signature( 150 ); ?>
            </td>
            <td class="row">    
                <?php erp_html_form_input( [
                    'label'       => __( 'Date', 'erp' ),
                    'name'        => 'date',
                    'value'       => $leave->created_at->format( 'Y-m-d' ),
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>
        </tr>
        <tr>
            <td class="row" colspan="2">
                <?php erp_html_form_input( [
                    'label'       => __( 'Post', 'erp' ),
                    'name'        => 'employee_position',
                    'value'       => $designation,
                    'required'    => false,
                    'readonly'    => true    
                ] ); ?>
            </td>
            <td class="row" colspan="3">
                <?php erp_html_form_input( [
                    'label'       => __( 'Ministry/Department', 'erp' ),
                    'name'        => 'employee_department',
                    'value'       => $department,
                    'required'    => false,
                    'readonly'    => true                          
                ] ); ?>
            </td>
        </tr>
        <tr>
            <th colspan="5">Section B: Vacation Details</th>
        </tr>
        <tr>
            <td class="row" colspan="3">
                <?php erp_html_form_label(
                    __( 'No. of Days', 'erp' )
                ); ?>
            </td>
            <td class="row" colspan="2">
                <?php erp_html_form_label(
                    __( 'Vacation Dates', 'erp' )
                ); ?>
            </td>
        </tr>
        <tr>
            <td class="row" colspan="2">
                <?php erp_html_form_input( [
                    'label'       => __( 'Taken for the year', 'erp' ),
                    'name'        => 'taken_year',
                    'value'       => $leave->taken_year,
                    'type'        => 'number',
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>
            <td class="row">
                <?php erp_html_form_input( [
                    'label'       => __( 'Applying for', 'erp' ),
                    'name'        => 'days',
                    'value'       => $leave->days,
                    'type'        => 'number',
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>            
            <td class="row">
                <?php erp_html_form_input( [
                    'label'       => __( 'From', 'erp' ),
                    'name'        => 'leave_from',
                    'id'          => 'erp-hr-leave-report-from-date',
                    'value'       => date('Y-m-d', $leave->start_date),
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>
            <td class="row">
                <?php erp_html_form_input( [
                    'label'       => __( 'To', 'erp' ),
                    'name'        => 'leave_to',
                    'id'          => 'erp-hr-leave-report-to-date',
                    'value'       => date('Y-m-d', $leave->end_date),
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>            
        </tr>
        <tr>
            <td class="row" colspan="2">
                <?php erp_html_form_input( [
                    'label'       => __( 'in the Federation', 'erp' ),
                    'name'        => 'days_in_federation',
                    'type'        => 'number',
                    'value'       => $leave->days_in_federation,
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>
            <td class="row" colspan="2">
                <?php erp_html_form_input( [
                    'label'       => __( 'outside the Federation', 'erp' ),
                    'name'        => 'days_out_federation',
                    'type'        => 'number',
                    'value'       => $leave->days_out_federation,
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>
            <td class="row">
                <?php erp_html_form_input( [
                    'label'       => __( 'Contact No.', 'erp' ),
                    'name'        => 'contact_no',
                    'value'       => $leave->contact_no,
                    'required'    => false,
                    'readonly'    => true
                ] ); ?>
            </td>          
        </tr>
        <tr>
            <td class="row" colspan="5">
                <?php erp_html_form_input( [
                    'label'       => __( 'Address while on leave', 'erp' ),
                    'tooltip'     => 'if outside the Federation',
                    'name'        => 'address_on_leave',
                    'type'        => 'textarea',
                    'value'       => $leave->address_on_leave,
                    'required'    => false,
                    'readonly'    => true,
                    'custom_attr' => [ 'cols' => 100, 'rows' => 3 ],
                ] ); ?>
            </td>
        </tr>
        <tr>
            <th colspan="5">Section C: Arrangements proposed for the performance of the officer's duties</th>
        </tr>
        <tr>
            <td class="row">
                <?php erp_html_form_input( [
                    'label'       => __( 'Substitute required?', 'erp' ),
                    'name'        => 'substitute_required',
                    'type'        => 'radio',
                    'value'       => $leave->substitute_required,
                    'required'    => false,
                    'disabled'    => true,
                    'readonly'    => true,
                    'options'     => $substitute_required
                ] ); ?>
            </td>
            <td class="row" colspan="4">
                <?php erp_html_form_input( [
                    'label'       => __( 'Substitute Type', 'erp' ),
                    'name'        => 'substitute_type',
                    'type'        => 'radio',
                    'value'       => $leave->substitute_type,
                    'required'    => false,
                    'disabled'    => true,
                    'readonly'    => true,
                    'options'     => $substitute_type_options
                ] ); ?>
            </td>
        </tr>
        <tr>
            <th colspan="5">Section D: Approvals and Sign-Offs on leave not exceeding a total of 30 days</th>
        </tr>
        <tr>
            <td class="row">
                <?php erp_html_form_input( [
                    'label'       => __( 'Leave Recommended', 'erp' ),
                    'name'        => 'leave_recommended_head_of_department',
                    'type'        => 'radio',
                    'value'       => '1',
                    'required'    => false,
                    'disabled'    => true,
                    'readonly'    => true,
                    'options'     => $substitute_required
                ] ); ?>
                <br/>
                <span>(if no use notes box below to explain)</span>
            </td>
            <td class="row" colspan="2">
                <?php
                    if(!empty($erp_leave_1st_approval_user)) {
                        erp_html_form_input( [
                            'label'       => __( 'Name of Head of Department', 'erp' ),
                            'name'        => 'sign_head_of_department',
                            'value'       => $erp_leave_1st_approval_user->display_name,
                            'required'    => false,
                            'readonly'    => true
                        ] );
                    }
                ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Signature', 'erp' )
                ); ?>
                <?php
                    if(!empty($erp_leave_1st_approval_object)) {
                        echo $erp_leave_1st_approval_object->get_signature( 150 );
                    }
                ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php
                    if(!empty($erp_leave_1st_approval)) {
                        erp_html_form_input( [
                            'label'       => __( 'Date(dd/mm/yy)', 'erp' ),
                            'name'        => 'date_head_of_department',
                            'value'       => $erp_leave_1st_approval->created_at->format( 'Y-m-d' ),
                            'required'    => false,
                            'readonly'    => true
                        ] );
                    }
                ?>
            </td>            
        </tr>
        <tr>
            <td class="row">
                <?php erp_html_form_input( [
                    'label'       => __( 'Leave', 'erp' ),
                    'name'        => 'leave_recommended_head_of_PS',
                    'type'        => 'multicheckbox',
                    'value'       => '1',
                    'required'    => false,
                    'disabled'    => true,
                    'readonly'    => true,
                    'options'     => ["0"=>"Recommended", "1"=>"Approved"]
                ] ); ?>
            </td>
            <td class="row" colspan="2">
                <?php 
                    if(!empty($erp_leave_final_approval_user)) {
                        erp_html_form_input( [
                        'label'       => __( 'Name of Permanent Secretary', 'erp' ),
                        'name'        => 'sign_head_of_PS',
                        'value'       => $erp_leave_final_approval_user->display_name,
                        'required'    => false,
                        'readonly'    => true
                        ] );
                    } 
                ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Signature', 'erp' )
                ); ?>
                <?php 
                    if(!empty($erp_leave_final_approval_object)) {
                        echo $erp_leave_final_approval_object->get_signature( 150 ); 
                    }
                ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php 
                    if(!empty($erp_leave_final_approval)) {
                        erp_html_form_input( [
                            'label'       => __( 'Date(dd/mm/yy)', 'erp' ),
                            'name'        => 'date_head_of_PS',
                            'value'       => $erp_leave_final_approval->created_at->format( 'Y-m-d' ),
                            'required'    => false,
                            'readonly'    => true
                        ] );
                    }
                ?>
            </td>            
        </tr>        
        <tr>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Examined', 'erp' )
                ); ?>
            </td>            
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'HRMD Leave Officer', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Name', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Signature', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Date(dd/mm/yy)', 'erp' )
                ); ?>
            </td>
        </tr>        
        <tr>
            <th colspan="5">Section E: Approvals for leave exceeding 30 days/maternity/paternity and leave without pay</th>
        </tr>
        <tr>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Approved', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value" colspan="2">
                <?php erp_html_form_label(
                    __( 'Name of Chief Personnel Officer', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Signature', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Date(dd/mm/yy)', 'erp' )
                ); ?>
            </td>
        </tr>        
        <tr>
            <th colspan="5">FOR HUMAN RESOURCE MANAGEMENT DEPARTMENT USE ONLY</th>
        </tr>
        <tr>
            <td class="row" rowspan="2">
                <?php erp_html_form_input( [
                    'label'       => __( 'Salary Scale', 'erp' ),
                    'name'        => 'salary_scale',
                    'type'        => 'multicheckbox',
                    'options'     => [__('**K21 and below(14/15 days)', 'erp'), '**K22-K32(18/21 days)', '**K33 and above(24/27 days)'],
                    'required'    => false
                ] ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Balance of entitlement brought forward', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Entitlement earned for the year', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Number of days applied for', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'Balance carried forward', 'erp' )
                ); ?>
            </td>
        </tr>
        <tr>
            <td class="row" colspan="4">
                <?php erp_html_form_input( [
                    'label'       => __( 'Status', 'erp' ),
                    'name'        => 'status',
                    'type'        => 'radio',
                    'options'     => [__('Approved', 'erp'), __('Not Approved', 'erp'), __('Deferred', 'erp')],
                    'required'    => false
                ] ); ?>
            </td>
        </tr>
        <tr>
            <td class="row erp-report-empty-value" colspan="4">
                <?php erp_html_form_label(
                    __( 'Notes', 'erp' )
                ); ?>
            </td>
            <td class="row erp-report-empty-value">
                <?php erp_html_form_label(
                    __( 'PF/PFL No.', 'erp' )
                ); ?>
            </td>               
        </tr>
    </table>
    <div>
        <p>
            *The expected date of delivery form from the doctor is to be submitted when applying for maternity and paternity leave.
        </p>
        <p></p>
        <p>
            **Persons employed/appointed prior to 2013 are eligible for 15, 21 and 27 days respectively based on the salary scale they are in on entry; however persons employed/appointed after 2013 are eligible for 14, 18, and 24 days respectively and are entitled to I day every three(3) years until the maximum of 15, 21 and 27 is reached.
        </p>
    </div>
    <input type="hidden" name="employee_id" id="erp-hr-leave-req-employee-id" value="<?php echo esc_html($user_id); ?>">
    <input type="hidden" name="action" value="erp-hr-leave-approved-report">
    <?php wp_nonce_field( 'erp-leave-report' ); ?>
</div>