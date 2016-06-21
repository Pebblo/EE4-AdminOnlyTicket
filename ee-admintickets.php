<?php
/*
Plugin Name: Event Espresso - Admin Only Tickets
Plugin URI: http://crumbls.com
Description: Create admin only tickets.  Useful for cash at the door tickets.
Version: 0.1.0
Author: Chase C. Miller
Author URI: http://crumbls.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

namespace Crumbls\EventEspresso\AdminTickets;


class Plugin {
    public function __construct() {
        add_filter( 'FHEE__ticket_selector_chart_template__do_ticket_entire_row', array(&$this, 'eeTicketShow'), 10, 2);
        add_action('AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end', array(&$this, 'eeTicketMeta'), 10, 2);

        add_action( 'save_post', array(&$this, 'eePostSave'), 10, 2);
    }


    // Ticket handler
    // Might be able to ignore the is_admin side of things if the code is never called.  Check EE4 source.
    public function eeTicketShow($ticket_row_html, \EE_Ticket $ticket) {
        if (is_admin()) {
            return $ticket_row_html;
        }
        $i = $ticket->get_extra_meta('visibility', true);
        if (!$i) {
            return $ticket_row_html;
        }
        return '';
        return $ticket_row_html;
    }

    // Display option in the ticket editor.
    public function eeTicketMeta($tkt, $TKT_ID) {
        global $post;
        if (!$ticket = \EEM_Ticket::instance()->get_one_by_ID($TKT_ID)) {
            return;
        }

        printf('<h4 class="tickets-heading">%s</h4>', __('Event Visibility', __NAMESPACE__));
        echo '<br />';
        $iOption = $ticket->get_extra_meta( 'visibility', true );
        if (!$iOption || !is_numeric($iOption)) {
            $iOption = 0;
        }

        printf('<select name="ticket_visibility[%d]" id="ticket_visibility[%d]">', $TKT_ID, $TKT_ID);
        printf('<option value="0" %s>%s</option>',
            selected($iOption, 0, false),
            __('Public', __NAMESPACE__)
        );
        printf('<option value="1" %s>%s</option>',
            selected($iOption, 1, false),
            __('Admin Only', __NAMESPACE__)
        );
        echo '</select>';
        echo '<br />';
    }

    // Save post handler
    public function eePostSave($iPost, $post) {
        if ($post->post_type != 'espresso_events') {
            return;
        }
        if (!array_key_exists('ticket_visibility', $_POST)) {
            return;
        }
        $tickets = array_filter($_POST['ticket_visibility'], 'is_numeric');
        foreach($tickets as $iTicket => $iOption) {
            if (is_numeric($iTicket)) {
                if ($ticket = \EEM_Ticket::instance()->get_one_by_ID($iTicket)) {
                    if ($iOption == 0 && false) {
                        // delete.
                    } else {
                        $ticket->add_extra_meta('visibility', $iOption, true) || $ticket->update_extra_meta('visibility', $iOption);
                    }
                }

            }
        }
    }
}

new Plugin();