<?php

require_once LIBPATH . "Stripe_api/lib/Stripe.php";

class stripe_api
{
    public function __construct()
    {
    }

    public function setApiKey($key)
    {
        Stripe::setApiKey($key);
    }

    public function chargeCreate($params)
    {
        return Stripe_Charge::create($params);
    }
}
