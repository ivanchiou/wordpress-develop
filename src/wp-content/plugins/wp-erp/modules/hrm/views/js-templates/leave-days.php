<# if ( data.days ) { #>
    <div class="table-wrap">
        <table class="list-days">
        <# _.each( data.days, function(day, index) { #>
            <tr class="request-date-row">
                <td>{{ day.date }}<input type="hidden" name="days_{{ index }}_date" value="{{ day.date }}"></td>
                <td><input type="text" name="days_{{ index }}_count" value="{{ day.count }}" readonly="readonly" size="1"> <?php esc_html_e( 'day', 'erp' ); ?></td>
                <td class="remove-request-date notice-dismiss"></td>
            </tr>
        <# }) #>
        </table>
        <input type="hidden" name="from_to_total_days" value="{{ data.total }}">
        <div class="total"><?php esc_html_e( 'Total: ', 'erp' ); ?> <input type="text" class="total-days" name="total_days" value="{{ data.total }}" readonly="readonly" size="2"></div>
    </div>
<# } #>
