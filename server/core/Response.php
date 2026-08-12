<?php

require_once('./core/Message.php');
require_once('./core/Form.php');

class Response implements JsonSerializable
{
    private bool $success;
    private int $httpStatusCode;
    private MessageArray $messages;
    private mixed $data;
    private mixed $form;
    private bool $toCache = false;
    private mixed $request;
    private bool $refresh;

    public function __construct(bool $success, int $httpStatusCode, MessageArray $messages, mixed $data, mixed $form, bool $toCache, $refresh)
    {
        $this->success = $success;
        $this->httpStatusCode = $httpStatusCode;
        $this->messages = $messages;
        $this->data = $data;
        $this->form = $form;
        $this->toCache = $toCache;
        $this->refresh = $refresh;
    }

    public function SetRequest(mixed $request)
    {
        $this->request = $request;
    }

    public function Send($type = 'json')
    {
        if($type == 'json'){
            $this->SendAsJson();
        }
    }

    public function SendAsJson()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");

        header('Content-type: application/json;charset=utf-8');

        if ($this->toCache == true) {
            header('Cache-control: max-age=60');
        } else {
            header('Cache-control: no-cache, no-store');
        }
        http_response_code(200);

        echo json_encode($this);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'request' => $this->request ?? '',
            'statusCode' => $this->httpStatusCode,
            'success' => $this->success,
            'messages' => $this->messages,
            'data' => $this->data,
            'form' => $this->form,
            'refresh' => $this->refresh
        ];
    }
}
