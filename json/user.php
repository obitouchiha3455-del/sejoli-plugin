<?php
namespace SejoliSA\JSON;

Class User extends \SejoliSA\JSON
{
    protected $available_roles = array();

    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set user options
     * Hooked via action wp_ajax_sejoli-user-options, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_options() {

        $options = [];
        $args    = wp_parse_args($_GET,[
            'term'    => ''
        ]);

        $users = get_users([
            'search' => $args['term']
        ]);

        foreach((array) $users as $user ) :
            $options[] = [
                'id'   => $user->ID,
                'text' => sprintf( _x(' %s <%s> #%s', 'user-options', 'sejoli'), $user->display_name, $user->user_email, $user->ID)
            ];
        endforeach;


        wp_send_json([
            'results' => $options
        ]);

        exit;
    }

    /**
     * Set table data
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

        $table = $this->set_table_args($_POST);
        $total = 0;
        $data  = [];

        $return = \SejoliSA\Model\User::set_start($table['start'])
                        ->set_sort($table['order'][0])
                        ->set_filter($table['filter'])
                        ->set_total($table['length'])
                        ->get();

        $i = 0;
        foreach($return['data'] as $user) :

            $user      = apply_filters('sejoli/user/meta-data', $user);
            $affiliate = sejolisa_get_affiliate($user, 'wp_user');

            $data[] = array(
                'ID'        => $user->ID,
                'name'      => $user->display_name,
                'email'     => $user->user_email,
                'affiliate' => (is_a($affiliate, 'WP_User')) ? $affiliate->display_name : '-',
                'phone'     => $user->meta->phone,
                'group'     => ( NULL === $user->meta->group ) ? '-' : $user->meta->group,
                'roles'     => apply_filters('sejoli/user/roles', array(), $user->roles),
            );

            $i++;

        endforeach;

        echo wp_send_json([
            'table'           => $table,
            'draw'            => $table['draw'],
            'data'            => $data,
            'recordsTotal'    => $return['total'],
            'recordsFiltered' => $return['total']
        ]);

        exit;
    }

    /**
     * Update user role and group
     * @since   1.3.0
     * @return  array
     */
    public function update_user() {

        $response   = array(
            'valid'   => false,
            'message' => __('Not valid request', 'sejoli')
        );

        $params     = wp_parse_args($_POST, array(
                        'nonce' => NULL,
                        'ID'    => NULL,
                        'group' => NULL,
                        'affiliate_id' => NULL,
                        'role'  => NULL
                      ));

        if(wp_verify_nonce($params['nonce'], 'sejoli-user-update')) :
            if(is_array($params['ID']) && 0 < count($params['ID'])) :

                foreach($params['ID'] as $user_id) :

                    if(!empty($params['group'])) :
                        sejolisa_update_user_group($user_id, intval($params['group']), true);
                    endif;

                    if(!empty($params['affiliate_id'])) :
                        update_user_meta($user_id, '_affiliate_id', intval($params['affiliate_id']));
                    endif;

                    if(!empty($params['role'])) :
                        $user = new \WP_User($user_id);
                        $user->set_role($params['role']);
                    endif;

                endforeach;

                $response['valid'] = true;
                $response['message'] = __('User telah diupdate');

            else :
                $response['message'] = __('User ID is empty', 'sejoli');
            endif;

        endif;

        echo wp_send_json($response);
    }

    /**
     * Prepare for exporting user data
     * Hooked via wp_ajax_sejoli-user-export-prepare, priority 1
     * @since   1.0.2
     * @return  void
     */
    public function prepare_for_exporting() {

        $response = [
            'url'   => admin_url('/'),
            'data'  => [],
        ];

        $post_data = wp_parse_args($_POST,[
            'data'    => array(),
            'nonce'   => NULL,
            'backend' => false
        ]);

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-user-export-prepare')) :

            $request          = array();

            foreach($post_data['data'] as $_data) :
                if(!empty($_data['val'])) :
                    $request[$_data['name']]    = $_data['val'];
                endif;
            endforeach;

            if(false !== $post_data['backend']) :
                $request['backend'] = true;
            endif;

            $response['data'] = $request;
            $response['url']  = wp_nonce_url(
                                    add_query_arg(
                                        $request,
                                        site_url('/sejoli-ajax/sejoli-user-export')
                                    ),
                                    'sejoli-user-export',
                                    'sejoli-nonce'
                                );
        endif;

        echo wp_send_json($response);
        exit;
    }
}
