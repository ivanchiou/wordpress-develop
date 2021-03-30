<?php

namespace WeDevs\ERP\HRM\Models;

use WeDevs\ERP\Framework\Model;

/**
 * Class Leave_request
 */
class Leave_Request extends Model {
    protected $table = 'erp_hr_leave_requests';

    protected $fillable = [
        'user_id', 'leave_id', 'leave_entitlement_id', 'day_status_id', 'days', 'taken_year',
        'start_date', 'end_date', 'days_in_federation', 'days_out_federation', 'contact_no', 
        'address_on_leave', 'substitute_required', 'substitute_type', 
        'reason', 'last_status', 'created_by',
    ];

    /**
     * Created at date format
     */
    public function setCreatedAtAttribute() {
        $this->attributes['created_at'] = erp_current_datetime()->getTimestamp();
    }

    /**
     * Updated at date format
     */
    public function setUpdatedAtAttribute() {
        $this->attributes['updated_at'] = erp_current_datetime()->getTimestamp();
    }

    /**
     * Relation to Leave model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function leave() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Leave' );
    }

    /**
     * Relation to Leave_Request_Detail model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function details() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Request_Detail', 'leave_request_id', 'id' );
    }

    /**
     * Relation to Leave_Approval_Status model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function approval_status() {
        return $this->hasMany( 'WeDevs\ERP\HRM\Models\Leave_Approval_Status', 'leave_request_id', 'id' )->orderBy( 'id', 'desc' );
    }

    /**
     * Relation to Leave_Approval_Status model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function latest_approval_status() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leave_Approval_Status', 'leave_request_id', 'id' )->orderBy( 'id', 'desc' );
    }

    /**
     * Relation to Leaves_Unpaid model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function unpaid() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leaves_Unpaid', 'leave_request_id', 'id' );
    }

    /**
     * Relation to Employee model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function employee() {
        return $this->belongsTo( 'WeDevs\ERP\HRM\Models\Employee', 'user_id', 'user_id' );
    }

    /**
     * Relation to Leave Entitlement model
     *
     * @since 1.6.0
     *
     * @return object
     */
    public function entitlement() {
        return $this->hasOne( 'WeDevs\ERP\HRM\Models\Leave_Entitlement', 'id', 'leave_entitlement_id' );
    }

    public static function get_substitute_type_enum()  {
        global $wpdb;
        $table = $wpdb->prefix . 'erp_hr_leave_requests';
        $type = $wpdb->get_results("SHOW COLUMNS FROM {$table} WHERE Field = 'substitute_type'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $values = array();
        foreach(explode(',', $matches[1]) as $value){
            $values[] = trim($value, "'");
        }
        return $values;
    }
}
