<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        DB::statement("ALTER TABLE font_attachments
            ALTER COLUMN created_at TYPE timestamptz
                USING created_at AT TIME ZONE 'UTC',
            ALTER COLUMN updated_at TYPE timestamptz
                USING updated_at AT TIME ZONE 'UTC'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        DB::statement("ALTER TABLE font_attachments
            ALTER COLUMN created_at TYPE timestamp
                USING created_at AT TIME ZONE 'UTC',
            ALTER COLUMN updated_at TYPE timestamp
                USING updated_at AT TIME ZONE 'UTC'
        ");
    }
};
