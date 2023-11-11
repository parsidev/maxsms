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
    const ENDPOINT = "http://rest.ippanel.com/v1";

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
    public function __construct(string $apiKey, HTTPClient $httpClient = null)
    {
        $this->_httpClient = $httpClient;
        $this->_apiKey = $apiKey;

        $userAgent = sprintf("MaxSMS/ApiClient/%s PHP/%s", self::CLIENT_VERSION, phpversion());

        if (!$httpClient) {
            $this->_httpClient = new HTTPClient(self::ENDPOINT, self::DEFAULT_TIMEOUT, [
                sprintf("Authorization: AccessKey %s", $this->_apiKey),
                sprintf("User-Agent: %s", $userAgent),
            ]);
        }
    }

    /**
     * Get user credit
     * @return float
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function getCredit(): float
    {
        $res = $this->_httpClient->get("/credit");

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
     * @return int message tracking code
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function send(string $originator, array $recipients, string $message): int
    {
        $res = $this->_httpClient->post("/messages", [
            "originator" => $originator,
            "recipients" => $recipients,
            "message" => $message
        ]);

        if (!isset($res->data->bulk_id)) {
            throw new Exception("returned response not valid", 1);
        }

        return $res->data->bulk_id;
    }

    /**
     * Get a message brief info
     * @param int $messageId message tracking code
     * @return Message message tracking code
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function getMessage(int $messageId): Message
    {
        $res = $this->_httpClient->get(sprintf("/messages/%s", $messageId));

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
    public function fetchStatuses(int $messageId, int $page = 0, int $limit = 10): array
    {
        $res = $this->_httpClient->get(sprintf("/messages/%s/recipients", $messageId), [
            'page' => $page,
            'limit' => $limit,
        ]);

        if (!isset($res->data->recipients) || !is_array($res->data->recipients)) {
            throw new Exception("returned response not valid", 1);
        }

        $statuses = [];

        foreach ($res->data->recipients as $r) {
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
    public function fetchInbox(int $page = 1, int $limit = 10): array
    {
        $res = $this->_httpClient->get("/messages/inbox", [
            'page' => $page,
            'limit' => $limit,
        ]);

        if (!isset($res->data->messages) || !is_array($res->data->messages)) {
            throw new Exception("returned response not valid", 1);
        }

        $messages = [];

        foreach ($res->data->messages as $r) {
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
     * @param bool $isShared determine that pattern shared or not
     * @return string pattern code
     * @throws Errors\Error
     * @throws Errors\HttpException
     */
    public function createPattern(string $pattern, string $description, bool $isShared = false): string
    {
        $params = [
            'pattern' => $pattern,
            'description' => $description,
            'is_shared' => $isShared,
        ];

        $res = $this->_httpClient->post("/messages/patterns", $params);

        if (!isset($res->data->pattern->code)) {
            throw new Exception("returned response not valid", 1);
        }

        return $res->data->pattern->code;
    }

    /**
     * Send message with pattern
     * @param string $patternCode pattern code
     * @param string $originator originator number
     * @param string $recipient recipient number
     * @param array $values pattern values
     * @return int message code
     * @throws Errors\HttpException
     * @throws Errors\Error
     * @throws Exception
     */
    public function sendPattern(string $patternCode, string $originator, string $recipient, array $values): int
    {
        $res = $this->_httpClient->post("/messages/patterns/send", [
            "pattern_code" => $patternCode,
            "originator" => $originator,
            "recipient" => $recipient,
            "values" => $values,
        ]);

        if (!isset($res->data->bulk_id)) {
            throw new Exception("returned response not valid", 1);
        }

        return $res->data->bulk_id;
    }
}