<?php

class Intercom
{

    /**
     * Product name
     *
     * @var string
     */
    public $product = 'dating';

    /**
     * Is used
     *
     * @var boolean
     */
    public $isUsed = true;

    /**
     * Intercom api url
     *
     * @var string
     */
    private $INTERCOM_URL = 'https://api.intercom.io/';

    /**
     * Intercom auth user
     *
     * @var string
     */
    public $INTERCOM_USER = 'fslaoeto';

    /**
     * Intercom auth password
     *
     * @var string
     */
    private $INTERCOM_API_KEY = '1a7abfc93e8cb225549753732bdfe41b10b2e495';

    /**
     * Intercom user hash
     *
     * @var string
     */
    public $INTERCOM_USER_HASH = 'sFUwaiO8XVPZAfRkYc2APUKpNQMrJvJK5vU0DtME';

    /**
     * Constructor
     *
     * @return Intercom
     */
    public function __construct()
    {
        
    }

    /**
     * Create user
     *
     * @return array
     */
    public function createUser($user_data)
    {
        return $this->sendRequest("users",$user_data);
    }

    /**
     * Create event
     *
     * @return array
     */
    public function createEvent($event_name, $user_email, array $metadata = null)
    {
        $event_data = [
            "event_name" => $event_name,
            "created_at" => time(),
            "email" => $user_email,
        ];

        if (!empty($metadata)) {
            $event_data["metadata"] = $metadata;
        }

        return $this->sendRequest("events", $event_data);
    }

     /**
     * Send request to intercom api
     *
     * @param string $url
     * @param array $data request data
     * @return array
     */
    private function sendRequest($url, $data, $method = "POST")
    {
        $output  = shell_exec("curl " . $this->INTERCOM_URL . $url . " -u " . $this->INTERCOM_USER . ":" . $this->INTERCOM_API_KEY . " -H 'Accept: application/json' -H Content-type:application/json -X " . $method . " -d '" . json_encode($data) . "'");
        $decoded = (array) json_decode($output);
        return $decoded;
    }

}