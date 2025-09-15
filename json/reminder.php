<?php
namespace SejoliSA\JSON;

Class Reminder extends \SejoliSA\JSON
{
    /**
     * Set confirmation table data
     * Hooked via action wp_ajax_sejoli-confirmation-table, priority 1
     * @since   1.1.9
     * @return  json
     */
    public function set_for_table() {

        $table    = $this->set_table_args($_POST);
        $data     = [];

        $response = sejolisa_get_reminders($table['filter'], $table);

        if(false !== $response['valid']) :
            $data = $response['reminders'];
        endif;

        echo wp_send_json([
            'table'           => $table,
            'draw'            => $table['draw'],
            'data'            => $data,
            'recordsTotal'    => $response['recordsTotal'],
            'recordsFiltered' => $response['recordsTotal'],
        ]);

        exit;
    }

    /**
     * Resend reminder
     * Hooked via action wp_ajax_sejoli-reminder-resend, priority 1
     * @since   1.1.9
     * @return  json
     */
    public function resend_reminder() {

        $response = array();
        $table    = $this->set_table_args($_POST);
        $data     = [];

        $table['filter'] = array(
            'reminder.ID' => $_POST['reminders']
        );

        $table['length'] = count($_POST['reminders']);

        $response = sejolisa_get_reminders($table['filter'], $table);

        if(false !== $response['valid']) :

            $reminder_ids = array();

            foreach($response['reminders'] as $reminder_data) :

                $reminder_ids[] = $reminder_data->ID;
                $recipient      = sejolisa_get_user($reminder_data->user_id);

                if(in_array($reminder_data->media_type, array('sms', 'whatsapp')) ) :
                    $reminder_data->recipient = $recipient->meta->phone;
                else :
                    $reminder_data->recipient = $recipient->user_email;
                endif;

                sejolisa_update_reminder_status($reminder_ids);

                do_action('sejoli/notification/reminder', $reminder_data);

            endforeach;

            $response = array(
                'valid'     => true,
                'message'   => sprintf( __('Pengingat %s sudah diproses', 'sejoli'), implode(',', $reminder_ids) )
            );
        else :
            $response = array(
                'valid' => false,
                'message' => __('Proses tidak bisa dilakukan, data tidak ditemukan', 'sejoli')
            );
        endif;

        wp_send_json($response);
        exit;
    }
}
