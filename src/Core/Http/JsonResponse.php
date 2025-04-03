<?php

namespace App\Core\Http;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

final class JsonResponse extends GuzzleResponse
{

    public function __construct(mixed $data, int $status = 200, array $headers = [], string $version = '1.1', ?string $reason = null)
    {
        $body = $this->jsonEncode($data);

        $headers['Content-Type'] = 'application/json';

        parent::__construct($status, $headers, $body, $version, $reason);
    }

    private function jsonEncode($data): string
    {
        if (is_resource($data)) {
            throw new \InvalidArgumentException('Cannot JSON encode resources');
        }

        return json_encode($data);
    }
}