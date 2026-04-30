<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Supabase Storage REST: sunucu tarafı yükleme/silme (service_role).
 * Bucket'ın "Public" olduğundan ve RLS politikalarının uygun olduğundan emin olun.
 */
class SupabaseStorageClient {
    private string $baseUrl;
    private string $serviceKey;
    private string $bucket;

    public function __construct() {
        $url = rtrim((string) (getenv('SUPABASE_URL') ?: ''), '/');
        $this->baseUrl = $url;
        $this->serviceKey = (string) (getenv('SUPABASE_SERVICE_ROLE_KEY') ?: '');
        $this->bucket = (string) (getenv('SUPABASE_STORAGE_BUCKET') ?: 'ilan-fotograflari');
    }

    public function isConfigured(): bool {
        return $this->baseUrl !== '' && $this->serviceKey !== '';
    }

    /**
     * @return array{ok:bool, publicUrl?:string, error?:string, status?:int}
     */
    public function uploadObject(string $localPath, string $objectPath, string $mimeType): array {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'Supabase Storage yapılandırması eksik (SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY).'];
        }
        if (!is_readable($localPath)) {
            return ['ok' => false, 'error' => 'Geçici dosya okunamadı.'];
        }
        $body = file_get_contents($localPath);
        if ($body === false) {
            return ['ok' => false, 'error' => 'Dosya içeriği okunamadı.'];
        }

        $url = $this->baseUrl . '/storage/v1/object/' . rawurlencode($this->bucket) . '/' . $this->encodePathSegments($objectPath);
        $response = $this->request('POST', $url, $body, [
            'Content-Type: ' . $mimeType,
            'x-upsert: false',
        ]);

        if ($response['code'] >= 200 && $response['code'] < 300) {
            $public = $this->publicUrlForObject($objectPath);
            return ['ok' => true, 'publicUrl' => $public];
        }

        $msg = $this->formatApiError($response['body'], $response['code']);
        $this->log(sprintf(
            'Upload failed object=%s HTTP=%d body=%s',
            $objectPath,
            $response['code'],
            substr($response['body'], 0, 500)
        ));
        return ['ok' => false, 'error' => $msg, 'status' => $response['code']];
    }

    public function deleteObjectByPublicUrl(string $publicUrl): bool {
        if (!$this->isConfigured() || $publicUrl === '') {
            return false;
        }
        $objectPath = $this->objectPathFromPublicUrl($publicUrl);
        if ($objectPath === null) {
            return false;
        }
        $url = $this->baseUrl . '/storage/v1/object/' . rawurlencode($this->bucket) . '/' . $this->encodePathSegments($objectPath);
        $response = $this->request('DELETE', $url, '', []);
        return $response['code'] >= 200 && $response['code'] < 300;
    }

    public function publicUrlForObject(string $objectPath): string {
        return $this->baseUrl . '/storage/v1/object/public/'
            . rawurlencode($this->bucket) . '/' . $this->encodePathSegments($objectPath);
    }

    private function objectPathFromPublicUrl(string $publicUrl): ?string {
        $marker = '/storage/v1/object/public/' . $this->bucket . '/';
        $pos = strpos($publicUrl, $marker);
        if ($pos === false) {
            return null;
        }
        $tail = substr($publicUrl, $pos + strlen($marker));
        return rawurldecode($tail) !== '' ? rawurldecode($tail) : null;
    }

    private function encodePathSegments(string $path): string {
        $path = str_replace('\\', '/', $path);
        $parts = array_filter(explode('/', $path), static fn ($p) => $p !== '' && $p !== '.' && $p !== '..');
        return implode('/', array_map('rawurlencode', $parts));
    }

    /**
     * @param list<string> $extraHeaders Content-Type hariç (POST için)
     * @return array{code:int, body:string}
     */
    private function request(string $method, string $url, string $body, array $extraHeaders): array {
        $headers = array_merge([
            'Authorization: Bearer ' . $this->serviceKey,
            'apikey: ' . $this->serviceKey,
        ], $extraHeaders);

        $ch = curl_init($url);
        if ($ch === false) {
            return ['code' => 0, 'body' => 'curl_init failed'];
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 120,
        ]);
        if ($body !== '' && $method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $responseBody = curl_exec($ch);
        $curlErr = $responseBody === false ? curl_error($ch) : '';
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr !== '') {
            $this->log('cURL: ' . $curlErr . ' | URL=' . $this->redactUrl($url));
            return ['code' => 0, 'body' => $curlErr];
        }

        return [
            'code' => $code,
            'body' => is_string($responseBody) ? $responseBody : '',
        ];
    }

    private function formatApiError(string $body, int $code): string {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $m = $decoded['message'] ?? $decoded['error'] ?? $decoded['msg'] ?? null;
            if (is_string($m) && $m !== '') {
                return "Supabase Storage (HTTP {$code}): {$m}";
            }
        }
        $trim = trim($body);
        if ($trim !== '') {
            return "Supabase Storage (HTTP {$code}): {$trim}";
        }
        return "Supabase Storage yükleme başarısız (HTTP {$code}).";
    }

    private function log(string $line): void {
        error_log('[SupabaseStorage] ' . $line);
    }

    /** Log için tam URL yerine host + path özeti */
    private function redactUrl(string $url): string {
        $p = parse_url($url);
        if (!is_array($p)) {
            return '(geçersiz-url)';
        }
        $host = $p['host'] ?? '';
        $path = $p['path'] ?? '';
        return $host . $path;
    }
}
