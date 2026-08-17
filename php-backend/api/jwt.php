<?php
// Pure PHP JWT implementation compatible with standard RFC 7519
class SimpleJWT {
    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padLen = 4 - $remainder;
            $data .= str_repeat('=', $padLen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    public static function encode($payload, $secret, $algo = 'HS256') {
        $header = json_encode(['typ' => 'JWT', 'alg' => $algo]);
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt, $secret) {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            throw new Exception("Invalid JWT token structure");
        }

        list($header64, $payload64, $signature64) = $tokenParts;
        $header = json_decode(self::base64UrlDecode($header64), true);
        $payload = json_decode(self::base64UrlDecode($payload64), true);

        if (!$header || !$payload) {
            throw new Exception("Invalid JWT payload decoding");
        }

        $signature = self::base64UrlDecode($signature64);
        $validSignature = hash_hmac('sha256', $header64 . "." . $payload64, $secret, true);

        if (!hash_equals($validSignature, $signature)) {
            throw new Exception("Invalid JWT signature");
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception("Token has expired");
        }

        return $payload;
    }
}
