<?php

namespace Parsidev\MaxSms;


class MaxSms
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    private function callApi($param)
    {
        $handler = curl_init($this->config['url']);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);
        curl_close($handler);

        $response = json_decode($response);

        return [
            "status" => $response[0],
            "data" => $response[1]
        ];
    }

    public function JobList()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "jobslist"
        ]);
    }

    public function Credit()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "credit"
        ]);
    }

    public function Send($recipients, $message)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "send",
            "to" => json_encode($recipients),
            "message" => $message,
            "from" => $this->config['from']
        ]);
    }
}