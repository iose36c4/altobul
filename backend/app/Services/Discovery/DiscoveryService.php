<?php

namespace App\Services\Discovery;

use App\Models\User;
use App\Models\Profile;
use App\Models\ProfileFieldValue;
use App\Models\ProfileFieldDefinition;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscoveryService
{
    public function __construct(
        private AuthorizationService $authorization,
    ) {}
    
    public function discover(User $viewer, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->buildCandidateQuery($viewer);
        
        // Apply field filters
        if (!empty($filters['fields'])) {
            $this->applyFieldFilters($query, $filters['fields']);
        }
        
        // Apply verified only filter
        if (!empty($filters['verified_only'])) {
            $query->where('verification_status', 'verified');
        }
        
        // Apply ordering
        $order = $filters['order'] ?? 'recent';
        $this->applyOrdering($query, $viewer, $order);
        
        // Exclude self
        $query->where('users.id', '!=', $viewer->id);
        
        return $query->paginate($perPage);
    }
    
    public function discoverOnline(User $viewer, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->buildCandidateQuery($viewer)
            ->where('last_seen_at', '>', now()->subMinutes(2));
            
        return $this->applyAndPaginate($query, $viewer, $filters, $perPage);
    }
    
    public function discoverRecent(User $viewer, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->buildCandidateQuery($viewer)
            ->where('last_seen_at', '>', now()->subHours(24));
            
        return $this->applyAndPaginate($query, $viewer, $filters, $perPage);
    }
    
    public function discoverNearby(User $viewer, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->buildCandidateQuery($viewer);
        
        if ($viewer->profile && $viewer->profile->location) {
            $query->selectRaw('users.*, ST_Distance(profiles.location, ?) as distance_m', 
                [$viewer->profile->location])
                  ->orderBy('distance_m');
        }
        
        return $this->applyAndPaginate($query, $viewer, $filters, $perPage);
    }
    
    private function buildCandidateQuery(User $viewer): Builder
    {
        return User::query()
            ->select('users.*')
            ->where('users.status', 'active')
            ->whereNotNull('users.email_verified_at')
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            
            // 1. GEOGRAPHY
            ->whereRaw('ST_Within(profiles.location, (
                SELECT geom FROM geo_polygons gp
                JOIN geo_zones gz ON gz.id = gp.zone_id
                WHERE gz.is_active = true
                LIMIT 1
            ))')
            
            // 2. BLOCKS (bidirectional)
            ->whereNotExists(function($q) use ($viewer) {
                $q->from('blocks')
                  ->whereRaw('(blocker_id = ? AND blocked_id = users.id) 
                              OR (blocked_id = ? AND blocker_id = users.id)', 
                              [$viewer->id, $viewer->id]);
            })
            
            // 3. DISCOVERABLE
            ->where('profiles.discoverable', true)
            
            // 4. VERIFICATION REQUIREMENT (profile level)
            ->where(function($q) use ($viewer) {
                $q->where('profiles.profile_requires_verified', false)
                  ->orWhere(function($sq) use ($viewer) {
                      $sq->where('profiles.profile_requires_verified', true)
                         ->where('users.verification_status', 'verified');
                  });
            });
    }
    
    private function applyFieldFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $filter) {
            $field = ProfileFieldDefinition::find($filter['field_id']);
            if (!$field || !$field->is_filterable) {
                continue;
            }
            
            $query->whereHas('fieldValues', function($q) use ($field, $filter) {
                $q->where('field_id', $field->id);
                $this->applyOperator($q, $field, $filter);
            });
        }
    }
    
    private function applyOperator(Builder $q, ProfileFieldDefinition $field, array $filter): void
    {
        $column = match($field->type) {
            'TEXT', 'TEXTAREA' => 'value_text',
            'NUMBER' => 'value_number',
            'DATE' => 'value_date',
            'BOOLEAN' => 'value_boolean',
            'SELECT', 'RADIO', 'MULTISELECT' => 'value_json',
        };
        
        $op = $filter['operator'];
        $val = $filter['value'];
        $val2 = $filter['value_2'] ?? null;
        
        match($op) {
            'eq' => $q->where($column, $val),
            'neq' => $q->where($column, '!=', $val),
            'gt' => $q->where($column, '>', $val),
            'gte' => $q->where($column, '>=', $val),
            'lt' => $q->where($column, '<', $val),
            'lte' => $q->where($column, '<=', $val),
            'between' => $q->whereBetween($column, [$val, $val2]),
            'in' => $q->whereJsonContains($column, $val),
            'nin' => $q->whereNot(function($sq) use ($column, $val) {
                $sq->whereJsonContains($column, $val);
            }),
            'is_null' => $q->whereNull($column),
            'is_not_null' => $q->whereNotNull($column),
        };
    }
    
    private function applyOrdering(Builder $query, User $viewer, string $order): void
    {
        match($order) {
            'distance' => $query->selectRaw('ST_Distance(profiles.location, ?) as distance_m', 
                [$viewer->profile->location])->orderBy('distance_m'),
            'recent' => $query->orderBy('last_seen_at', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('last_seen_at', 'desc'),
        };
    }
    
    private function applyAndPaginate(Builder $query, User $viewer, array $filters, int $perPage): LengthAwarePaginator
    {
        // Apply field filters
        if (!empty($filters['fields'])) {
            $this->applyFieldFilters($query, $filters['fields']);
        }
        
        if (!empty($filters['verified_only'])) {
            $query->where('verification_status', 'verified');
        }
        
        $order = $filters['order'] ?? 'recent';
        $this->applyOrdering($query, $viewer, $order);
        
        $query->where('users.id', '!=', $viewer->id);
        
        return $query->paginate($perPage);
    }
}