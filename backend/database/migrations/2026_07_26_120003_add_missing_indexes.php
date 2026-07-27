<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Blocks: bidirectional lookup
        DB::statement('CREATE INDEX blocks_bidirectional ON blocks (blocked_id, blocker_id)');

        // Tokes: mutual toke detection
        DB::statement('CREATE INDEX tokes_mutual_lookup ON tokes (receiver_id, sender_id) WHERE status = \'ACTIVE\'');

        // Profile field values: composite indexes for discovery filters
        DB::statement('CREATE INDEX profile_field_values_field_number ON profile_field_values (field_id, value_number) WHERE value_number IS NOT NULL');
        DB::statement('CREATE INDEX profile_field_values_field_date ON profile_field_values (field_id, value_date) WHERE value_date IS NOT NULL');
        DB::statement('CREATE INDEX profile_field_values_field_boolean ON profile_field_values (field_id, value_boolean) WHERE value_boolean IS NOT NULL');

        // Matches: active user lookup
        DB::statement('CREATE INDEX matches_active_user ON matches (user_a_id, user_b_id) WHERE status = \'ACTIVE\'');

        // Friendships: active user lookup
        DB::statement('CREATE INDEX friendships_active_user ON friendships (user_a_id, user_b_id) WHERE status = \'ACTIVE\'');

        // Conversations: active user lookup for chat list
        DB::statement('CREATE INDEX conversations_active_user ON conversations (user_a_id, user_b_id) WHERE status = \'ACTIVE\'');

        // Photos: user active with sort_order
        DB::statement('CREATE INDEX photos_user_active_sort ON photos (user_id, sort_order) WHERE status = \'ACTIVE\' AND deleted_at IS NULL');

        // Posts: feed query
        DB::statement('CREATE INDEX posts_feed_public ON posts (expires_at DESC) WHERE status = \'ACTIVE\' AND visibility = \'PUBLIC\' AND requires_verified = false AND deleted_at IS NULL');

        // Verification requests: user active
        DB::statement('CREATE INDEX verification_requests_active ON verification_requests (user_id) WHERE status IN (\'PENDING\', \'APPROVED\')');

        // Discovery preferences: active by field
        DB::statement('CREATE INDEX discovery_prefs_active_field ON discovery_preferences (field_id) WHERE is_active = true');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS blocks_bidirectional');
        DB::statement('DROP INDEX IF EXISTS tokes_mutual_lookup');
        DB::statement('DROP INDEX IF EXISTS profile_field_values_field_number');
        DB::statement('DROP INDEX IF EXISTS profile_field_values_field_date');
        DB::statement('DROP INDEX IF EXISTS profile_field_values_field_boolean');
        DB::statement('DROP INDEX IF EXISTS matches_active_user');
        DB::statement('DROP INDEX IF EXISTS friendships_active_user');
        DB::statement('DROP INDEX IF EXISTS conversations_active_user');
        DB::statement('DROP INDEX IF EXISTS photos_user_active_sort');
        DB::statement('DROP INDEX IF EXISTS posts_feed_public');
        DB::statement('DROP INDEX IF EXISTS verification_requests_active');
        DB::statement('DROP INDEX IF EXISTS discovery_prefs_active_field');
    }
};
