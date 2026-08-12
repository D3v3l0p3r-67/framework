<?php

enum MessageKind: string
{
    case LOG = 'log';
    case USER = 'user';
    case ERROR = 'error';
}

class Message implements JsonSerializable
{
    private MessageKind $kind;
    private string $text;

    public function __construct($kind, $text)
    {
        $this->kind = $kind;
        $this->text = $text;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'kind' => $this->kind,
            'text' => $this->text
        ];
    }
}

class MessageLog extends Message
{
    public function __construct($text)
    {
        parent::__construct(MessageKind::LOG, $text);
    }
    public static function GetForName($name)
    {
        return new self($name . ' method was called.');
    }
    public static function GetForGrid()
    {
        return MessageLog::GetForName('Grid');
    }
    public static function GetForEditGrid()
    {
        return MessageLog::GetForName('EditGrid');
    }
    public static function GetForDataGrid()
    {
        return MessageLog::GetForName('DataGrid');
    }

    public static function GetForShow()
    {
        return MessageLog::GetForName('Show');
    }
    public static function GetForNew()
    {
        return MessageLog::GetForName('New');
    }
    public static function GetForEdit()
    {
        return MessageLog::GetForName('Edit');
    }
    public static function GetForDuplicate()
    {
        return MessageLog::GetForName('Duplicate');
    }
}

class MessageUser extends Message
{
    public function __construct($text)
    {
        parent::__construct(MessageKind::USER, $text);
    }

    public static function GetForUpsert()
    {
        return new self('Record was sucessfully upserted.');
    }
}

class MessageError extends Message
{
    public function __construct($text)
    {
        parent::__construct(MessageKind::ERROR, $text);
    }
}

class MessageArray extends ArrayObject implements JsonSerializable
{
    public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
    {
        foreach ($input as $item) {
            if (!$item instanceof Message) {
                throw new InvalidArgumentException('Value must be a Message!');
            }
        }
        parent::__construct($input, $flags, $iterator_class);
    }

    public function offsetSet($key, $val): void
    {
        if ($val instanceof Message) {
            parent::offsetSet($key, $val);
            return;
        }
        throw new InvalidArgumentException('Value must be a Message!');
    }

    public function jsonSerialize(): mixed
    {
        return $this->getArrayCopy();
    }
}

/* 
usage:
------------------------------ 
$message = new MessageArray();
$message[] = new Message();
workWithMessages($foos);
------------------------------
*/
