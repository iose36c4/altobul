<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BackendApiService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected ?string $userToken = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('admin.api_base_url'), '/');
        $this->apiKey = config('admin.api_key') ?? '';
    }

    public function withUserToken(string $token): self
    {
        $this->userToken = $token;

        return $this;
    }

    protected function headers(): array
    {
        $headers = [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];

        if ($this->userToken) {
            $headers['Authorization'] = 'Bearer '.$this->userToken;
        } elseif (auth()->check() && auth()->user()->api_token ?? false) {
            $headers['Authorization'] = 'Bearer '.auth()->user()->api_token;
        }

        return $headers;
    }

    public function get(string $endpoint, array $params = []): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(config('admin.timeout', 30))
            ->get($this->baseUrl.'/api/admin'.$endpoint, $params);
    }

    public function post(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(config('admin.timeout', 30))
            ->post($this->baseUrl.'/api/admin'.$endpoint, $data);
    }

    public function put(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(config('admin.timeout', 30))
            ->put($this->baseUrl.'/api/admin'.$endpoint, $data);
    }

    public function delete(string $endpoint): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(config('admin.timeout', 30))
            ->delete($this->baseUrl.'/api/admin'.$endpoint);
    }

    // === Dashboard ===
    public function getDashboardMetrics(): array
    {
        $response = $this->get('/dashboard/metrics');

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getDashboardCharts(): array
    {
        $response = $this->get('/dashboard/charts');

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Users ===
    public function getUsers(array $filters = []): array
    {
        $response = $this->get('/users', $filters);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getUser(string $id): array
    {
        $response = $this->get("/users/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function createUser(array $data): array
    {
        $response = $this->post('/users', $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function updateUser(string $id, array $data): array
    {
        $response = $this->put("/users/{$id}", $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function suspendUser(string $id): array
    {
        $response = $this->post("/users/{$id}/suspend");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function activateUser(string $id): array
    {
        $response = $this->post("/users/{$id}/activate");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function banUser(string $id): array
    {
        $response = $this->post("/users/{$id}/ban");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteUser(string $id): array
    {
        $response = $this->delete("/users/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function changeUserRole(string $id, string $role): array
    {
        $response = $this->post("/users/{$id}/change-role", ['role' => $role]);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Content Moderation ===

    public function getUserPosts(string $userId): array
    {
        $response = $this->get("/moderation/users/{$userId}/posts");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deletePost(string $postId): array
    {
        $response = $this->delete("/moderation/posts/{$postId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getUserPhotos(string $userId): array
    {
        $response = $this->get("/moderation/users/{$userId}/photos");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deletePhoto(string $photoId): array
    {
        $response = $this->delete("/moderation/photos/{$photoId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getUserTokes(string $userId): array
    {
        $response = $this->get("/moderation/users/{$userId}/tokes");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteToke(string $tokeId): array
    {
        $response = $this->delete("/moderation/tokes/{$tokeId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getUserMatches(string $userId): array
    {
        $response = $this->get("/moderation/users/{$userId}/matches");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteMatch(string $matchId): array
    {
        $response = $this->delete("/moderation/matches/{$matchId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getUserFriendships(string $userId): array
    {
        $response = $this->get("/moderation/users/{$userId}/friendships");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteFriendship(string $friendshipId): array
    {
        $response = $this->delete("/moderation/friendships/{$friendshipId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getUserConversations(string $userId): array
    {
        $response = $this->get("/moderation/users/{$userId}/conversations");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getConversationMessages(string $conversationId): array
    {
        $response = $this->get("/moderation/conversations/{$conversationId}/messages");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteConversation(string $conversationId): array
    {
        $response = $this->delete("/moderation/conversations/{$conversationId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteMessage(string $messageId): array
    {
        $response = $this->delete("/moderation/messages/{$messageId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Geo Zones ===
    public function getGeoZones(array $params = []): array
    {
        $response = $this->get('/geo-zones', $params);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getGeoZone(string $id): array
    {
        $response = $this->get("/geo-zones/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function createGeoZone(array $data): array
    {
        $response = $this->post('/geo-zones', $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function updateGeoZone(string $id, array $data): array
    {
        $response = $this->put("/geo-zones/{$id}", $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteGeoZone(string $id): array
    {
        $response = $this->delete("/geo-zones/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function addPolygon(string $zoneId, array $data): array
    {
        $response = $this->post("/geo-zones/{$zoneId}/polygons", $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function updatePolygon(string $zoneId, string $polygonId, array $data): array
    {
        $response = $this->put("/geo-zones/{$zoneId}/polygons/{$polygonId}", $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deletePolygon(string $zoneId, string $polygonId): array
    {
        $response = $this->delete("/geo-zones/{$zoneId}/polygons/{$polygonId}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Profile Fields ===
    public function getProfileFields(): array
    {
        $response = $this->get('/profile-fields');

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getProfileField(string $id): array
    {
        $response = $this->get("/profile-fields/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function createProfileField(array $data): array
    {
        $response = $this->post('/profile-fields', $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function updateProfileField(string $id, array $data): array
    {
        $response = $this->put("/profile-fields/{$id}", $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function deleteProfileField(string $id): array
    {
        $response = $this->delete("/profile-fields/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function reorderProfileFields(array $ids): array
    {
        $response = $this->post('/profile-fields/reorder', ['ids' => $ids]);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Verifications ===
    public function getVerificationRequests(array $params = []): array
    {
        $response = $this->get('/verification-requests', $params);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getVerificationRequest(string $id): array
    {
        $response = $this->get("/verification-requests/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function approveVerification(string $id): array
    {
        $response = $this->post("/verification-requests/{$id}/approve");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function rejectVerification(string $id, string $reason): array
    {
        $response = $this->post("/verification-requests/{$id}/reject", ['rejection_reason' => $reason]);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Config ===
    public function getConfig(): array
    {
        $response = $this->get('/config');

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function updateConfig(array $config): array
    {
        $response = $this->put('/config', $config);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === API Keys ===
    public function getApiKeys(array $params = []): array
    {
        $response = $this->get('/api-keys', $params);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function createApiKey(array $data): array
    {
        $response = $this->post('/api-keys', $data);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function revokeApiKey(string $id): array
    {
        $response = $this->delete("/api-keys/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Reports ===
    public function getReports(array $filters = []): array
    {
        $response = $this->get('/reports', $filters);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function getReport(string $id): array
    {
        $response = $this->get("/reports/{$id}");

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function dismissReport(string $id, ?string $notes = null): array
    {
        $response = $this->post("/reports/{$id}/dismiss", array_filter(['admin_notes' => $notes]));

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    public function actionReport(string $id, ?string $notes = null): array
    {
        $response = $this->post("/reports/{$id}/action", array_filter(['admin_notes' => $notes]));

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }

    // === Audit Logs ===
    public function getAuditLogs(array $params = []): array
    {
        $response = $this->get('/audit-logs', $params);

        return $response->ok() ? $response->json() : ['error' => $response->json('message')];
    }
}
