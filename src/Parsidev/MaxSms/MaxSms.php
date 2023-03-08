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

        return $response;
    }

    public function JobList()
    {
        $param = [
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "joblist"
        ];
        return $this->callApi($param);
    }
}