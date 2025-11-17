<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use ToshY\BunnyNet\BunnyHttpClient;
use ToshY\BunnyNet\Enum\Endpoint;
use Symfony\Component\HttpClient\Psr18Client;
use ToshY\BunnyNet\Model\Api\Stream\ManageVideos\CreateVideo;

// Load .env into environment for this script (simple parser)
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // remove surrounding quotes
        if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
            $v = substr($v, 1, -1);
        }
        putenv("$k=$v");
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
}

$apiKey = getenv('BUNNY_STREAM_API_KEY') ?: getenv('BUNNY_API_KEY');
$libraryId = getenv('BUNNY_STREAM_LIBRARY_ID');

if (! $apiKey || ! $libraryId) {
    echo "MISSING_ENV\n";
    echo "BUNNY_STREAM_API_KEY=" . ($apiKey ?: 'EMPTY') . "\n";
    echo "BUNNY_STREAM_LIBRARY_ID=" . ($libraryId ?: 'EMPTY') . "\n";
    exit(1);
}

$client = new BunnyHttpClient(
    client: new Psr18Client(),
    apiKey: $apiKey,
    baseUrl: Endpoint::STREAM,
);

try {
    $resp = $client->request(new CreateVideo(
        libraryId: (int)$libraryId,
        body: ['title' => 'debug-test-' . time()],
    ));

    echo "STATUS:" . $resp->getStatusCode() . "\n";
    $body = (string)$resp->getBody();
    echo "BODY:" . $body . "\n";
} catch (\Throwable $e) {
    echo "EXCEPTION:" . $e->getMessage() . "\n";
}
