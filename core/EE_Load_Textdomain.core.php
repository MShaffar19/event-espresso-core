<?php
/**
 * EE_Load_Textdomain
 *
 * @package        Event Espresso
 * @subpackage     /includes/core/EE_Load_Textdomain.core.php
 * @author         Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class EE_Load_Textdomain extends EE_Base
{

    /**
     * holds the current lang in WP
     *
     * @var string
     */
    private static $_lang;


    /**
     * this takes care of retrieving a matching textdomain for event espresso for the current WPLANG from EE GitHub
     * repo (if necessary) and then loading it for translations. should only be called in wp plugins_loaded callback
     *
     * @return void
     */
    public static function load_textdomain()
    {
        self::_maybe_get_langfiles();
        // now load the textdomain
        if (! empty(self::$_lang) && is_readable(EE_LANGUAGES_SAFE_DIR . 'event_espresso-' . self::$_lang . '.mo')) {
            load_plugin_textdomain('event_espresso', false, EE_LANGUAGES_SAFE_LOC);
        } elseif (! empty(self::$_lang)
                  && is_readable(EE_LANGUAGES_SAFE_DIR . 'event-espresso-4-' . self::$_lang . '.mo')
        ) {
            load_textdomain('event_espresso', EE_LANGUAGES_SAFE_DIR . 'event-espresso-4-' . self::$_lang . '.mo');
        } else {
            load_plugin_textdomain('event_espresso', false, dirname(EE_PLUGIN_BASENAME) . '/languages/');
        }
    }


    /**
     * The purpose of this method is to sideload all of the lang files for EE, this includes the POT file and also the PO/MO files for the given WPLANG locale (if necessary).
     *
     * @access private
     * @static
     * @return void
     */
    private static function _maybe_get_langfiles()
    {
        self::$_lang = get_locale();
        if ($has_check = get_option('ee_lang_check_' . self::$_lang . '_' . EVENT_ESPRESSO_VERSION)
                         || empty(self::$_lang)
        ) {
            return;
        }

        // load sideloader and sideload the .POT file as this is should always be included.
        $sideloader_args = array(
            '_upload_to'     => EE_PLUGIN_DIR_PATH . 'languages/',
            '_download_from'   => 'https://github.com/eventespresso/languages-ee4/blob/master/event_espresso.pot?raw=true',
            '_new_file_name' => 'event_espresso.pot',
        );
        $sideloader = EE_Registry::instance()->load_helper('Sideloader', $sideloader_args, false);
        $sideloader->sideload();

        // if lang is en_US or empty then lets just get out.  (Event Espresso core is en_US)
        if (empty(self::$_lang) || self::$_lang == 'en_US') {
            return;
        }

        // made it here so let's get the language files from the github repo, first the .mo file
        $sideloader->set_download_from('https://github.com/eventespresso/languages-ee4/blob/master/event_espresso-' . self::$_lang . '.mo?raw=true');
        $sideloader->set_new_file_name('event_espresso-' . self::$_lang . '.mo');
        $sideloader->sideload();

        // now the .po file:
        $sideloader->set_download_from('https://github.com/eventespresso/languages-ee4/blob/master/event_espresso-' . self::$_lang . '.po?raw=true');
        $sideloader->set_new_file_name('event_espresso-' . self::$_lang . '.po');
        $sideloader->sideload();

        // set option so the above only runs when EE updates.
        update_option('ee_lang_check_' . self::$_lang . '_' . EVENT_ESPRESSO_VERSION, 1);
    }
}
