<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Uuid;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Util\Hasher;
use Cicada\Core\Framework\Uuid\Exception\InvalidUuidException;
use Cicada\Core\Framework\Uuid\Exception\InvalidUuidLengthException;
use Ramsey\Uuid\BinaryUtils;
use Ramsey\Uuid\Generator\RandomGeneratorFactory;
use Ramsey\Uuid\Generator\UnixTimeGenerator;

#[Package('core')]
class Uuid
{
    /**
     * Regular expression pattern for matching a valid UUID of any variant.
     */
    final public const VALID_PATTERN = '^[0-9a-f]{32}$';

    private static ?UnixTimeGenerator $generator = null;

    /**
     * @return non-falsy-string
     */
    public static function randomHex(): string
    {
        /** @var non-falsy-string */
        return bin2hex(self::randomBytes());
    }

    /**
     * same as Ramsey\Uuid\UuidFactory->uuidFromBytesAndVersion without using a transfer object
     *
     * @return non-falsy-string
     */
    public static function randomBytes(): string
    {
        if (self::$generator === null) {
            self::$generator = new UnixTimeGenerator((new RandomGeneratorFactory())->getGenerator());
        }
        $bytes = self::$generator->generate();

        $unpackedTime = unpack('n*', substr($bytes, 6, 2));
        \assert(\is_array($unpackedTime));
        $timeHi = (int) $unpackedTime[1];
        $timeHiAndVersion = pack('n*', BinaryUtils::applyVersion($timeHi, 7));

        $unpackedClockSeq = unpack('n*', substr($bytes, 8, 2));
        \assert(\is_array($unpackedClockSeq));
        $clockSeqHi = (int) $unpackedClockSeq[1];
        $clockSeqHiAndReserved = pack('n*', BinaryUtils::applyVariant($clockSeqHi));

        $bytes = substr_replace($bytes, $timeHiAndVersion, 6, 2);
        $bytes = substr_replace($bytes, $clockSeqHiAndReserved, 8, 2);
        \assert(!empty($bytes));

        return $bytes;
    }

    /**
     * @throws InvalidUuidException
     * @throws InvalidUuidLengthException
     *
     * @return non-falsy-string
     */
    public static function fromBytesToHex(string $bytes): string
    {
        if (mb_strlen($bytes, '8bit') !== 16) {
            throw new InvalidUuidLengthException(mb_strlen($bytes, '8bit'), bin2hex($bytes));
        }
        $uuid = bin2hex($bytes);

        if (!self::isValid($uuid)) {
            throw new InvalidUuidException($uuid);
        }

        \assert(!empty($uuid));

        return $uuid;
    }

    /**
     * @param array<string> $bytesList
     *
     * @return array<non-falsy-string>
     */
    public static function fromBytesToHexList(array $bytesList): array
    {
        $converted = [];
        foreach ($bytesList as $key => $bytes) {
            $converted[$key] = self::fromBytesToHex($bytes);
        }

        return $converted;
    }

    /**
     * @param array<array-key, string> $uuids
     *
     * @return array<array-key, non-falsy-string>
     */
    public static function fromHexToBytesList(array $uuids): array
    {
        $converted = [];
        foreach ($uuids as $key => $uuid) {
            $converted[$key] = self::fromHexToBytes($uuid);
        }

        return $converted;
    }

    /**
     * @throws InvalidUuidException
     *
     * @return non-falsy-string
     */
    public static function fromHexToBytes(string $uuid): string
    {
        if ($bin = @hex2bin($uuid)) {
            return $bin;
        }

        throw new InvalidUuidException($uuid);
    }

    /**
     * Generates a md5 binary, to hash the string and returns a UUID in hex
     */
    public static function fromStringToHex(string $string): string
    {
        return self::fromBytesToHex(Hasher::hashBinary($string, 'md5'));
    }

    public static function isValid(string $id): bool
    {
        if (!preg_match('/' . self::VALID_PATTERN . '/', $id)) {
            return false;
        }

        return true;
    }
}
