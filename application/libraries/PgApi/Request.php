<?php

namespace Pg\Libraries\PgApi;

class Request
{
    const SHELL = 'shell';
    const CURL = 'curl';

    /**
     * Send request to lighthouse
     *
     * @param string $url       request url
     * @param array  $post_data post data
     *
     * @return array
     */
    public static function send($url, array $get_data = null, array $post_data = null, $tool = self::CURL)
    {
        if (!empty($get_data)) {
            $url .= '&' . http_build_query($get_data);
        }

        switch ($tool) {
            case self::SHELL:
                $output = self::shell($url, $post_data);
                break;
            case self::CURL:
            case '':
            case null:
                $output = self::curl($url, $post_data);
                break;
            default:
                throw new \Exception('Wrong tool (' . $tool . ')');
        }

        return (array) json_decode($output, true);
    }

    private static function curl($url, array $post_data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($post_data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, (string) http_build_query($post_data));
        }

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    private static function shell($url, array $post_data = null)
    {
        $data = "";
        if (!empty($post_data)) {
            $data = " -d '" . (string) http_build_query($post_data) . "'";
        }
        $str = 'curl "' . $url . '"' . $data;

        return shell_exec($str);
    }
}
