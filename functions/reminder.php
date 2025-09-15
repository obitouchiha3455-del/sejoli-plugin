<?php

function sejolisa_add_reminder_queue(array $args) {

    $args = wp_parse_args($args, array(
        'order_id'      => NULL,
        'recipient'     => NULL,
        'title'         => NULL,
        'content'       => NULL,
        'send_day'      => 0,
        'media_type'    => NULL,
        'reminder_type' => NULL,
    ));

    $response   = SejoliSa\Model\Reminder::reset()
                        ->set_order_id($args['order_id'])
                        ->set_recipient($args['recipient'])
                        ->set_title($args['title'])
                        ->set_content($args['content'])
                        ->set_send_day($args['send_day'])
                        ->set_send_hour($args['send_hours'])
                        ->set_media_type($args['media_type'])
                        ->set_reminder_type($args['reminder_type'])
                        ->add()
                        ->respond();

    return $response;
}

/**
 * Get all orders that need to be reminded
 * @since   1.1.9
 * @param   string  $date       Date with format Y-m-d
 * @param   integer $day        Day
 * @return  array   $response
 */
function sejolisa_get_orders_for_reminder($interval, $date, $day) {
    
    if($interval === "reminder_per_day"):

        $response   = SejoliSa\Model\Reminder::reset()
                            ->set_date($date)
                            ->set_day($day)
                            ->set_interval($interval)
                            ->get_by_order()
                            ->respond();

    else:

        $response   = SejoliSa\Model\Reminder::reset()
                        ->set_date($date)
                        ->set_hour($day)
                        ->set_interval($interval)
                        ->get_by_order()
                        ->respond();

    endif;

    return $response;

}

/**
 * Get all subscriptions that need to be reminded
 * @since   1.1.9
 * @param   string  $date       Date with format Y-m-d
 * @param   integer $day        Day
 * @return  array   $response
 */
function sejolisa_get_subscriptions_for_reminder($interval, $date, $day) {

    if($interval === "reminder_per_day"):

        $response   = SejoliSa\Model\Reminder::reset()
                        ->set_date($date)
                        ->set_day($day)
                        ->set_interval($interval)
                        ->get_by_subscription()
                        ->respond();

    else:

        $response   = SejoliSa\Model\Reminder::reset()
                        ->set_date($date)
                        ->set_hour($day)
                        ->set_interval($interval)
                        ->get_by_subscription()
                        ->respond();

    endif;

    return $response;
}

/**
 * Get all reminders
 * @since   1.1.6
 * @param   array  $args
 * @param   array  $table
 * @return
 * - valid      boolean
 * - reminder   array
 * - messages   array
 */
function sejolisa_get_reminders(array $args, $table = array()) {

    $args = wp_parse_args($args, [
        'reminder.ID'     => NULL,
        'reminder.status' => NULL
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => []
    ]);

    $query = SejoliSA\Model\Reminder::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

    if(0 < $table['length']) :
        $query->set_data_length($table['length']);
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $response = $query->get()->respond();
    $temp     = array();
    $i        = 0;

    foreach($response['reminders'] as $_data) :

        $set_sent_at = ($_data->sent_at === '0000-00-00 00:00:00') ? date('Y M d, H:i:s', strtotime($_data->created_at)) : date('Y M d, H:i:s', strtotime($_data->sent_at));
        $temp[$i]             = $_data;
        $temp[$i]->created_at = date('Y M d, H:i:s', strtotime($_data->created_at));
        $temp[$i]->sent_at    = $set_sent_at;
        $temp[$i]->is_sent    = (true === boolval($_data->status)) ? __('Sudah Dikirim', 'sejoli') : __('Dalam antrian', 'sejoli');
        $i++;
    endforeach;

    $response['reminders'] = $temp;

    return $response;
}

/**
 * Update multiple reminder status data
 * @since   1.1.9
 * @param   array  $ids     Multiple reminder ID
 * @return  array  return with respond format
 */
function sejolisa_update_reminder_status(array $ids) {

    $response = SejoliSA\Model\Reminder::reset()
                    ->set_multiple_id($ids)
                    ->update_send_status()
                    ->respond();

    return $response;
}

/**
 * Delete sent reminder data
 * @param  int      $day
 * @return array    Response
 */
function sejolisa_delete_sent_reminder(int $day) {

    $day = 7;

    $response = SejoliSA\Model\Reminder::reset()
                    ->set_filter(
                        'sent_at',
                        date('Y-m-d 00:00:00', strtotime('-' . $day. ' days')),
                        '<'
                    )
                    ->set_filter(
                        'status',
                        true
                    )
                    ->delete()
                    ->respond();

    return $response;

}
