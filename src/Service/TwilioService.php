<?php

namespace App\Service;

use Twilio\Rest\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TwilioService
{
    private $twilio;
    private $from;

    public function __construct(ParameterBagInterface $params)
    {
        $sid = $params->get('twilio_sid');
        $token = $params->get('twilio_auth_token');
        $this->from = $params->get('twilio_phone');
        
        $this->twilio = new Client($sid, $token);
    }

    public function sendSms(string $to, string $message): void
    {
        $this->twilio->messages->create($to, [
            'from' => $this->from,
            'body' => $message
        ]);
    }
}
