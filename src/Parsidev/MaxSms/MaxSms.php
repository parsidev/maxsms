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

    public function GetCredit()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "credit"
        ]);
    }

    public function GetCreditPanel()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "usertime"
        ]);
    }

    public function GetAccessList()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "accesslist"
        ]);
    }

    public function GetBookList()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "booklist"
        ]);
    }

    public function GetLines()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "lines"
        ]);
    }

    public function GetNews()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "news"
        ]);
    }

    public function GetInboxList()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "inboxlist"
        ]);
    }

    public function GetJobList()
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "jobslist"
        ]);
    }

    public function CountryCity($stateId)
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "countrycity",
            "state_id" => $stateId
        ]);
    }

    public function GetDelivery($uniqueId)
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "delivery",
            "uinqid" => $uniqueId
        ]);
    }

    public function GetMessageStatus($messageId)
    {
        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "op" => "checkmessage",
            "messageid" => $messageId
        ]);
    }

    public function BookSend($bookId, $message)
    {
        if (!is_array($bookId)) {
            $bookId = [$bookId];
        }

        return $this->callApi([
            "uname" => $this->config['username'],
            "pass" => $this->config['password'],
            "bookid" => json_encode($bookId),
            "message" => $message,
            "from" => $this->config['from'],
            "op" => "booksend"
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