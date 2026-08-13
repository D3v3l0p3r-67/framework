<?php
require_once('./core/Action.php');

class Request
{
    private $accessToken;
    private $actionKey;
    private $actionParameters = array();
    private $requestData = array();

    public function __construct($accessToken, $actionKey, $actionParameters)
    {
        $this->setAccessToken($accessToken);
        $this->setActionKey($actionKey);
        $this->setActionParameters($actionParameters);
    }

    public function getActionKey()
    {
        return $this->actionKey;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function setActionKey($actionKey)
    {
        $this->actionKey = $actionKey;
    }

    public function setActionParameters($actionParameters)
    {
        $this->actionParameters = $actionParameters;
    }

    public function asArray()
    {
        return array(
            'accessToken' => $this->accessToken,
            'actionKey' => $this->actionKey,
            'actionParameters' => $this->redactSensitiveValues($this->actionParameters)
        );
    }

    private function redactSensitiveValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['password', 'token', 'csrf_token'], true)) {
                $values[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $values[$key] = $this->redactSensitiveValues($value);
            }
        }

        return $values;
    }

    public function asJson()
    {
        $this->requestData['accessToken'] = $this->accessToken;
        $this->requestData['actionKey'] = $this->actionKey;
        $this->requestData['actionParameters'] = $this->actionParameters;

        return json_encode($this->requestData);
    }

    public function toString()
    {
        echo '</br>------------------------------------------------------------------------------</br>';
        echo 'accessToken: ' . $this->accessToken . '</br>';
        echo 'actionKey: ' . $this->actionKey . '</br>';
        echo 'actionParameters: ';
        foreach ($this->actionParameters as $key => $value) {
            echo '&nbsp; [' . $key . '] = ' . $this->actionParameters[$key] . ', ';
        }

        echo '</br>------------------------------------------------------------------------------</br>';
    }

    public function Execute()
    {
        return Action::Execute($this->accessToken, $this->actionKey, $this->actionParameters);
    }
}
