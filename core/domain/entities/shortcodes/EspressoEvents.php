<?php
namespace EventEspresso\core\domain\entities\shortcodes;

use EED_Events_Archive;
use EEH_Template;
use EventEspresso\core\domain\services\wp_queries\EventListQuery;
use EventEspresso\core\services\shortcodes\EspressoShortcode;

defined('EVENT_ESPRESSO_VERSION') || exit;



/**
 * Class EspressoEvents
 * ESPRESSO_EVENTS shortcode
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.26
 */
class EspressoEvents extends EspressoShortcode
{



    /**
     * the actual shortcode tag that gets registered with WordPress
     *
     * @return string
     */
    public function getTag()
    {
        return 'ESPRESSO_EVENTS';
    }



    /**
     * the time in seconds to cache the results of the processShortcode() method
     * 0 means the processShortcode() results will NOT be cached at all
     *
     * @return int
     */
    public function cacheExpiration()
    {
        // return MINUTE_IN_SECONDS * 15;
        return 15;
    }



    /**
     * a place for adding any initialization code that needs to run prior to wp_header().
     * this may be required for shortcodes that utilize a corresponding module,
     * and need to enqueue assets for that module
     *
     * @return void
     */
    public function initializeShortcode()
    {
        EED_Events_Archive::instance()->event_list();
    }



    /**
     * callback that runs when the shortcode is encountered in post content.
     * IMPORTANT !!!
     * remember that shortcode content should be RETURNED and NOT echoed out
     *
     * @param array $attributes
     * @return string
     */
    public function processShortcode($attributes = array())
    {
        // grab attributes and merge with defaults
        $attributes = $this->getAttributes((array)$attributes);
        // make sure we use the_excerpt()
        add_filter('FHEE__EES_Espresso_Events__process_shortcode__true', '__return_true');
        // apply query filters
        add_filter('FHEE__EEH_Event_Query__apply_query_filters', '__return_true');
        // run the query
        global $wp_query;
        // yes we have to overwrite the main wp query, but it's ok...
        // we're going to reset it again below, so everything will be Hunky Dory (amazing album)
        $wp_query = new EventListQuery($attributes);
        // check what template is loaded and load filters accordingly
        EED_Events_Archive::instance()->template_include('loop-espresso_events.php');
        // load our template
        $event_list = EEH_Template::locate_template('loop-espresso_events.php', array(), true, true);
        // now reset the query and postdata
        wp_reset_query();
        wp_reset_postdata();
        EED_Events_Archive::remove_all_events_archive_filters();
        // remove query filters
        remove_filter('FHEE__EEH_Event_Query__apply_query_filters', '__return_true');
        // pull our content from the output buffer and return it
        return $event_list;
    }



    /**
     * merge incoming attributes with filtered defaults
     *
     * @param array $attributes
     * @return array
     */
    private function getAttributes(array $attributes)
    {
        return array_merge(
            (array)apply_filters(
                'EES_Espresso_Events__process_shortcode__default_espresso_events_shortcode_atts',
                array(
                    'title'         => null,
                    'limit'         => 10,
                    'css_class'     => null,
                    'show_expired'  => false,
                    'month'         => null,
                    'category_slug' => null,
                    'order_by'      => 'start_date',
                    'sort'          => 'ASC',
                )
            ),
            $attributes
        );
    }


}
// End of file EspressoEvents.php
// Location: EventEspresso\core\domain\entities\shortcodes/EspressoEvents.php