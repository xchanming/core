<?php declare(strict_types=1);

namespace Cicada\Core\Framework\MessageQueue;

use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\HttpException;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('core')]
class MessageQueueException extends HttpException
{
    public const NO_VALID_RECEIVER_NAME_PROVIDED = 'FRAMEWORK__NO_VALID_RECEIVER_NAME_PROVIDED';
    public const QUEUE_CANNOT_UNSERIALIZE_MESSAGE = 'FRAMEWORK__QUEUE_CANNOT_UNSERIALIZE_MESSAGE';
    public const WORKER_IS_LOCKED = 'FRAMEWORK__WORKER_IS_LOCKED';
    public const CANNOT_FIND_SCHEDULED_TASK = 'FRAMEWORK__CANNOT_FIND_SCHEDULED_TASK';
    public const QUEUE_MESSAGE_SIZE_EXCEEDS = 'FRAMEWORK__QUEUE_MESSAGE_SIZE_EXCEEDS';

    public static function validReceiverNameNotProvided(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::NO_VALID_RECEIVER_NAME_PROVIDED,
            'No receiver name provided.',
        );
    }

    public static function cannotUnserializeMessage(string $message): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::QUEUE_CANNOT_UNSERIALIZE_MESSAGE,
            'Cannot unserialize message {{ message }}',
            ['message' => $message]
        );
    }

    public static function workerIsLocked(string $receiver): self
    {
        return new self(
            Response::HTTP_CONFLICT,
            self::WORKER_IS_LOCKED,
            'Another worker is already running for receiver: "{{ receiver }}"',
            ['receiver' => $receiver]
        );
    }

    public static function cannotFindTaskByName(string $name): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CANNOT_FIND_SCHEDULED_TASK,
            self::$couldNotFindMessage,
            ['entity' => 'scheduled task', 'field' => 'name', 'value' => $name]
        );
    }

    /**
     * @deprecated tag:v6.7.0 - Parameter float $size will be added
     */
    public static function queueMessageSizeExceeded(string $messageName/* , float $size */): self
    {
        if (\func_num_args() === 1) {
            Feature::triggerDeprecationOrThrow('v6.7.0.0', 'The second parameter $size is missing. It will be required from 6.7.0.0 on');
            $size = null;
        } else {
            $size = func_get_arg(1);
        }
        $message = 'The message "{{ message }}" exceeds the 256 kB size limit';
        if ($size !== null) {
            $message .= ' with its size of {{ size }} kB.';
        }

        return new self(
            Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
            self::QUEUE_MESSAGE_SIZE_EXCEEDS,
            $message,
            [
                'message' => $messageName,
                'size' => $size,
            ]
        );
    }
}
