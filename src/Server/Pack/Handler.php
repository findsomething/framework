<?php

namespace FSth\Framework\Server\Pack;

class Handler implements HandlerInterface
{
    private $salt;

    public function __construct()
    {
        $this->salt = $this->createSalt();
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function encode($data)
    {
        // TODO: Implement encode() method.
        $encode = serialize($data);
        $sign = pack('N', crc32($encode . $this->salt));
        return pack('N', strlen($encode) + 4 + 32) . $sign . $this->salt . $encode;
    }

    public function decode($string)
    {
        // TODO: Implement decode() method.
        try {
            $target = $this->parse($string);
        } catch (\Exception $e) {
            return $this->format($e->getMessage(), $e->getCode());
        }

        return $this->format('OK', 0, unserialize($target));
    }

    private function format($msg = 'Ok', $code = 0, $data = [])
    {
        return [
            'msg' => $msg,
            'code' => $code,
            'data' => $data,
        ];
    }

    private function parse($string)
    {
        if (!is_string($string) || strlen($string) <= 40) {
            $this->triggerError("packer string invalid", 100001);
        }
        $header = substr($string, 0, 4);
        $unpackHeader = unpack('Nlen', $header);
        $len = $unpackHeader['len'];

        $sign = substr($string, 4, 4);
        $salt = substr($string, 8, 32);

        $target = substr($string, 40);

        if ($salt != $this->salt) {
            $this->triggerError("packer salt invalid", 100001);
        }

        if (pack("N", crc32($target . $this->salt)) != $sign) {
            $this->triggerError("packer sign invalid", 100001);
        }

        if ($len - 32 - 4 != strlen($target)) {
            $this->triggerError("packer length invalid", 100001);
        }
        return $target;
    }

    private function triggerError($msg, $code = 0)
    {
        throw new PackerException($msg, $code);
    }

    private function createSalt()
    {
        return hash('md5', uniqid('', true));
    }
}