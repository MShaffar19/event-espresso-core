<?php

namespace EventEspresso\core\domain\services\graphql\enums;

use EventEspresso\core\services\graphql\enums\EnumBase;

/**
 * Class DatetimesConnectionOrderbyEnum
 * Description
 *
 * @package EventEspresso\core\domain\services\graphql\enums
 * @author  Manzoor Wani
 * @since   $VID:$
 */
class DatetimesConnectionOrderbyEnum extends EnumBase
{

    /**
     * DatetimesConnectionOrderbyEnum constructor.
     */
    public function __construct()
    {
        $this->setName($this->namespace . 'DatetimesConnectionOrderbyEnum');
        $this->setDescription(esc_html__('Field to order the connection by', 'event_espresso'));
        parent::__construct();
    }


    /**
     * @return array
     * @since $VID:$
     */
    protected function getValues()
    {
        return [
            'NAME'     => [
                'value'       => 'DTT_name',
                'description' => esc_html__('Order by name', 'event_espresso'),
            ],
            'START_DATE'     => [
                'value'       => 'DTT_EVT_start',
                'description' => esc_html__('Order by start date', 'event_espresso'),
            ],
        ];
    }
}