<?php

require_once('./core/Response.php');

class ResponseFactory
{
    private static $codeMessage = array(
        200 => 'OK - The request was successful',
        201 => 'Created - The request has been fulfilled and a new resource has been created',
        204 => 'No Content - The server successfully processed the request, but is not returning any content',
        400 => 'Bad Request - The server cannot or will not process the request due to an apparent client error',
        401 => 'Unauthorized - Authentication is required and has failed or has not yet been provided',
        403 => 'Forbidden - The client does not have access rights to the content, i.e. they are unauthorized',
        404 => 'Not Found - The requested resource could not be found',
        405 => 'Method Not Allowed - The method specified in the request is not allowed for the resource identified',
        409 => 'Conflict - Indicates that the request could not be processed because of conflict in the current state of the resource',
        500 => 'Internal Server Error - A generic error message, given when an unexpected condition was encountered by the server and no more specific message is suitable',
        503 => 'Service Unavailable - The server is currently unable to handle the request due to a temporary overload or maintenance of the server'
    );

    public static function CreateEmptys()
    {
        return new Response(
            success: true,
            httpStatusCode: 500,
            messages: new MessageArray(),
            data: array(),
            form: array(),
            toCache: false,
            refresh: false
        );
    }


    public static function CreateSuccess(int $code, MessageArray $messages, mixed $data = [], mixed $form = [], bool $toCache = false, $refresh = false)
    {
        return new Response(
            success: true,
            httpStatusCode: $code,
            messages: $messages,
            data: $data,
            form: $form,
            toCache: $toCache,
            refresh: $refresh
        );
    }

    public static function CreateError(int $code, ?MessageArray $messages = null, mixed $data = [], mixed $form = '', bool $toCache = false)
    {
        $messages = $messages ?? new MessageArray();

        return new Response(
            success: false,
            httpStatusCode: $code,
            messages: $messages,
            data: $data,
            form: $form,
            toCache: $toCache,
            refresh: false
        );
    }

    public static function CreateOk(?Message $message = null, mixed $data = [], mixed $form = '', bool $toCache = false, $refresh = false)
    {
        $code = 200;
        $messages = self::GetMessages($code, $message);

        return self::CreateSuccess($code, $messages, $data, $form, $toCache, $refresh);
    }

    public static function CreateBadRequest(?Message $message = null, mixed $data = [], bool $toCache = false)
    {
        $code = 400;
        $messages = self::GetMessages($code, $message);

        return self::CreateError(
            code: $code,
            messages: $messages,
            data: $data,
            toCache: $toCache
        );
    }

    public static function CreateUnauthorized(?Message $message = null, mixed $data = [], bool $toCache = false)
    {
        $code = 401;
        $messages = self::GetMessages($code, $message);

        return self::CreateError(
            code: $code,
            messages: $messages,
            data: $data,
            toCache: $toCache
        );
    }

    public static function CreateForbiden(?MessageUser $message = null, mixed $data = [], bool $toCache = false)
    {
        $code = 403;
        $messages = self::GetMessages($code, $message);

        return self::CreateError(
            code: $code,
            messages: $messages,
            data: $data,
            toCache: $toCache
        );
    }

    public static function CreateNotFound(?Message $message = null, mixed $data = [], bool $toCache = false)
    {
        $code = 404;
        $messages = self::GetMessages($code, $message);

        return self::CreateError(
            code: $code,
            messages: $messages,
            data: $data,
            toCache: $toCache
        );
    }

    public static function CreateMethodNotAllowed(?Message $message = null, mixed $data = [], bool $toCache = false)
    {
        $code = 405;
        $messages = self::GetMessages($code, $message);

        return self::CreateError(
            code: $code,
            messages: $messages,
            data: $data,
            toCache: $toCache
        );
    }

    public static function CreateConflict(?Message $message = null, mixed $data = [], bool $toCache = false)
    {
        $code = 409;
        $messages = self::GetMessages($code, $message);

        return self::CreateError(
            code: $code,
            messages: $messages,
            data: $data,
            toCache: $toCache
        );
    }

    public static function CreateInternalServerError(?Message $message = null, mixed $data = [], bool $toCache = false)
    {
        $code = 500;
        $messages = self::GetMessages($code, $message);

        return self::CreateError(
            code: $code,
            messages: $messages,
            data: $data,
            toCache: $toCache
        );
    }

    private static function GetMessages(int $code, ?Message $message = null)
    {
        $messages = new MessageArray();

        $messages[] = new Message(MessageKind::LOG, self::$codeMessage[$code]);

        if ($message !== null) {
            $messages[] = $message;
        }

        return $messages;
    }
}
