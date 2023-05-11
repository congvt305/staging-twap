<?php
    namespace Hoolah\Hoolah\Controller;

    class Main
    {
        // static
        private static $configs_loaded = false;

        public static function is_dev()
        {
            self::load_configs();

            return defined('HOOLAH_DISPLAY_OPERATION_MODE') && HOOLAH_DISPLAY_OPERATION_MODE;
        }

        public static function load_configs()
        {
            if (!self::$configs_loaded)
            {
                require_once dirname(__FILE__).'/../config.php';
                if (file_exists(dirname(__FILE__).'/../../config.dev.php'))
                    require_once dirname(__FILE__).'/../../config.dev.php';

                self::$configs_loaded = true;
            }
        }

        public static function inc($path, $variables = null, $echo = true)
        {
            if (strpos($path, '.php') === false)
                $path = $path.'.php';

            if (is_array($variables))
                extract($variables);

            if (!$echo)
                ob_start();

            include dirname(__FILE__).'/../'.$path;

            if (!$echo)
                return ob_get_clean();
        }

        public static function get_cdn_url($cdn_id)
        {
            self::load_configs();

            return sprintf(HOOLAH_WIDGET_URL_CUSTOM, $cdn_id);
        }

        public static function get_cdn_urls($cdn_id)
        {
            self::load_configs();

            return array(
                'main.js' => sprintf(HOOLAH_WIDGET_URL_CUSTOM, $cdn_id),
                'general.css' => sprintf(HOOLAH_WIDGET_URL_GENERAL, $cdn_id)
            );
        }

        public static function data($path)
        {
            return include dirname(__FILE__).'/../data/'.$path;
        }

        public static function get_countries()
        {
            self::load_configs();

            if (defined('HOOLAH_SUPPORTED_COUNTRIES'))
                return explode(',', HOOLAH_SUPPORTED_COUNTRIES);

            return null;
        }

        public static function check_country($countries, $country)
        {
            // if all allowed
            if (!$countries || in_array('ALL', $countries) || in_array('*', $countries))
                return true;

            // clear CNTR:* and CNTR:ALL to CNTR
            foreach($countries as $key => $parts)
            {
                $parts = explode(':', $parts, 2);
                if (count($parts) > 1 && ($parts[1] == 'ALL' || $parts[1] == '*'))
                    $countries[$key] = $parts[0];
            }

            $country = explode(':', $country, 2);

            // case we have CNTR in $countries
            if (in_array($country[0], $countries))
                return true;

            // case we have CNTR, CNTR:ALL or CNTR:* in $country
            if (count($country) == 1 || $country[1] == 'ALL' || $country[1] == '*')
                foreach($countries as $key => $parts)
                {
                    $parts = explode(':', $parts, 2);
                    if ($parts[0] == $country[0])
                        return true;
                }

            // full check
            return in_array(implode(':', $country), $countries);
        }

        public static function remove_emoji($text)
        {
            $clean_text = "";

            // Match Emoticons
            $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
            $clean_text = preg_replace($regexEmoticons, '', $text);

            // Match Miscellaneous Symbols and Pictographs
            $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
            $clean_text = preg_replace($regexSymbols, '', $clean_text);

            // Match Transport And Map Symbols
            $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
            $clean_text = preg_replace($regexTransport, '', $clean_text);

            // Match Miscellaneous Symbols
            $regexMisc = '/[\x{2600}-\x{26FF}]/u';
            $clean_text = preg_replace($regexMisc, '', $clean_text);

            // Match Dingbats
            $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
            $clean_text = preg_replace($regexDingbats, '', $clean_text);

            return $clean_text;
        }
    }
