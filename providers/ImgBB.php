<?php
class ImgBB {
    private string $apiKey;
    private string $uploadUrl;

    public function __construct(string $apiKey, string $uploadUrl = 'https://api.imgbb.com/1/upload') {
        $this->apiKey = $apiKey;
        $this->uploadUrl = $uploadUrl;
    }

    /**
     * Upload image to ImgBB
     * @param string $imagePath Path to temp file
     * @param string $name Optional name
     * @param int $expiration Expiration in seconds (0 = no expiration)
     * @return array|false Response data or false on failure
     */
    public function upload(string $imagePath, string $name = '', int $expiration = 0): array|false {
        $imageData = base64_encode(file_get_contents($imagePath));

        $postData = [
            'key' => $this->apiKey,
            'image' => $imageData,
        ];

        if ($name !== '') {
            $postData['name'] = $name;
        }

        if ($expiration > 0) {
            $postData['expiration'] = $expiration;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->uploadUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return false;
        }

        $result = json_decode($response, true);

        if (!isset($result['success']) || !$result['success'] || !isset($result['data'])) {
            return false;
        }

        $data = $result['data'];

        // Fix domain: i.ibb.co → i.ibb.co.com
        $data['url'] = str_replace('i.ibb.co/', 'i.ibb.co.com/', $data['url']);
        $data['display_url'] = str_replace('i.ibb.co/', 'i.ibb.co.com/', $data['display_url'] ?? $data['url']);
        if (isset($data['thumb']['url'])) $data['thumb']['url'] = str_replace('i.ibb.co/', 'i.ibb.co.com/', $data['thumb']['url']);
        if (isset($data['medium']['url'])) $data['medium']['url'] = str_replace('i.ibb.co/', 'i.ibb.co.com/', $data['medium']['url']);

        return $data;
    }

    /**
     * Delete image via ImgBB delete URL
     * @param string $deleteUrl
     * @return bool
     */
    public function delete(string $deleteUrl): bool {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $deleteUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response !== false;
    }
}
