<?php

namespace Parsidev\MaxSms;

use Exception;
use Parsidev\MaxSms\Models\InboxMessage;
use Parsidev\MaxSms\Models\Message;
use Parsidev\MaxSms\Models\PaginationInfo;
use Parsidev\MaxSms\Models\Recipient as RecipientAlias;

class MaxSms
{
    /**
     * Client version for setting in api call user agent header
     * @var string
     */
    const CLIENT_VERSION = "2.0.0";

    /**
     * Default timeout for api call
     * @var int
     */
    const DEFAULT_TIMEOUT = 30;

    /**
     * Api endpoint
     * @var string
     */
    const ENDPOINT = "https://api2.ippanel.com/api/v1";

    /**
     * HTTP client
     * @var HTTPClient
     */
    private $_httpClient;

    /**
     * API key
     * @var string
     */
    private $_apiKey;

    /**
     * Construct MaxSMS client
     * @param string $apiKey api key
     * @param HTTPClient|null $httpClient http client
     */
    public function __construct($apiKey, $httpClient = null)
    {
        $this->_httpClient = $httpClient;
        $this->_apiKey = $apiKey;

        $userAgent = sprintf("MaxSMS/ApiClient/%s PHP/%s", self::CLIENT_VERSION, phpversion());

        if (!$httpClient) {
            $this->_httpClient = new HTTPClient(self::ENDPOINT, self::DEFAULT_TIMEOUT, array(
                sprintf("apikey: %s", $this->_apiKey),
                sprintf("User-Agent: %s", $userAgent),
            ));
        }
    }

    /**
     * Get user credit
     * @return float
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function getCredit()
    {
        $res = $this->_httpClient->get("/sms/accounting/credit/show");

        if (!isset($res->data->credit)) {
            throw new Exception("returned response not valid", 1);
        }

        return $res->data->credit;
    }

    /**
     * Send a message from originator to many recipients.
     * @param string $originator originator number
     * @param array $recipients recipients list
     * @param string $message message body
     * @param string $summary description
     * @return int message tracking code
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function send($originator, $recipients, $message, $summary)
    {
        $res = $this->_httpClient->post("/sms/send/webservice/single", array(
            "sender" => $originator,
            "recipient" => $recipients,
            "message" => $message,
            "description" => [
                "summary" => $summary,
                "count_recipient" => "" . count($recipients)
            ],
        ));

        if (!isset($res->data->message_id)) {
            throw new Exception("returned response not valid", 1);
        }

        return $res->data->message_id;
    }

    /**
     * Get a message brief info
     * @param int $messageId message tracking code
     * @return Message message tracking code
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function getMessage($messageId)
    {
        $res = $this->_httpClient->get("/sms/message/all", [
            'message_id' => $messageId,
        ]);

        if (!isset($res->data) || !is_array($res->data)) {
            throw new Exception("returned response not valid", 1);
        }

        $msg = new Message();
        $msg->fromJSON($res->data[0]);

        return $msg;
    }

    /**
     * Fetch message recipients status
     * @param int $messageId message tracking code
     * @param int $page page number(start from 0)
     * @param int $limit fetch limit
     * @return Models\Recipient[] message tracking code
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function fetchStatuses($messageId, $page = 0, $limit = 10)
    {
        $res = $this->_httpClient->get(sprintf("/sms/message/show-recipient/message-id/%s", $messageId), array(
            'page' => $page,
            'per_page' => $limit,
        ));

        if (!isset($res->data->deliveries) || !is_array($res->data->deliveries)) {
            throw new Exception("returned response not valid", 1);
        }

        $statuses = [];

        foreach ($res->data->deliveries as $r) {
            $status = new RecipientAlias();
            $status->fromJSON($r);
            $statuses[] = $status;
        }

        $paginationInfo = new PaginationInfo();
        $paginationInfo->fromJSON($res->meta);

        return array($statuses, $paginationInfo);
    }

    /**
     * Fetch inbox messages
     * @param int $page page number(starts from 1)
     * @param int $limit fetch limit
     * @return Models\InboxMessage[] messages
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function fetchInbox($page = 1, $limit = 10)
    {
        $res = $this->_httpClient->get("/inbox", array(
            'page' => $page,
            'per_page' => $limit,
        ));

        if (!isset($res->data) || !is_array($res->data)) {
            throw new Exception("returned response not valid", 1);
        }

        $messages = [];

        foreach ($res->data as $r) {
            $msg = new InboxMessage();
            $msg->fromJSON($r);
            $messages[] = $msg;
        }

        $paginationInfo = new PaginationInfo();
        $paginationInfo->fromJSON($res->meta);

        return array($messages, $paginationInfo);
    }

    /**
     * Create a pattern
     * @param string $pattern pattern schema
     * @param string $description pattern description
     * @param array $variables pattern variable names and types
     * @param string $delimiter variable delimiter
     * @param bool $isShared determine that pattern shared or not
     * @return string pattern code
     * @throws Errors\Error
     * @throws Errors\HttpException
     */
    public function createPattern($pattern, $description, $variables, $delimiter = "%", $isShared = false)
    {
        $params = array(
            'message' => $pattern,
            'delimiter' => $delimiter,
            'description' => $description,
            'variable' => [],
            'is_share' => $isShared,
        );

        foreach ($variables as $variableName => $type) {
            $params['variable'][] = ['name' => $variableName, 'type' => $type];
        }

        $res = $this->_httpClient->post("/sms/pattern/normal/store", $params);

        if (!isset($res->data[0]->code)) {
            throw new Exception("returned response not valid", 1);
        }

        return $res->data[0]->code;
    }

    /**
     * Send message with pattern
     * @param string $patternCode pattern code
     * @param string $originator originator number
     * @param string $recipient recipient number
     * @param array $variables pattern values
     * @return int message code
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function sendPattern($patternCode, $originator, $recipient, array $variables)
    {
        $res = $this->_httpClient->post("/sms/pattern/normal/send", array(
            "code" => $patternCode,
            "sender" => $originator,
            "recipient" => $recipient,
            "variable" => $variables,
        ));

        if (!isset($res->data->message_id)) {
            throw new Exception("returned response not valid", 1);
        }

        return $res->data->message_id;
    }
}