<?php

class JWT {
    private static $secret_key = "SARANYA256";
    private static $algorithm = "HS256";

    public static function encode($payload) {
        $header = json_encode(["alg" => self::$algorithm, "typ" => "JWT"]);
        $payload = json_encode($payload);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        $signature = hash_hmac("sha256", "$base64UrlHeader.$base64UrlPayload", self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    public static function decode($token) {
        $parts = explode(".", $token);
        if (count($parts) !== 3) {
            throw new Exception("Invalid token format.");
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        $signature = self::base64UrlDecode($base64UrlSignature);

        $expectedSignature = hash_hmac("sha256", "$base64UrlHeader.$base64UrlPayload", self::$secret_key, true);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new Exception("Invalid token signature.");
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception("Token has expired.");
        }

        return $payload;
    }

    private static function base64UrlEncode($data) {
        return str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($data));
    }

    private static function base64UrlDecode($data) {
        $data = str_replace(["-", "_"], ["+", "/"], $data);
        return base64_decode($data);
    }
}
