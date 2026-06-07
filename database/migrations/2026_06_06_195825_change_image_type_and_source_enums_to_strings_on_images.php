<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $imageTypeMap = [
        0 => 'poster',
        1 => 'banner',
        2 => 'avatar',
    ];

    private array $imageSourceMap = [
        0 => 'generated',
        1 => 'uploaded',
        2 => 'api',
        3 => 'url',
        4 => 'downloaded',
        5 => 'embedded',
        6 => 'legacy',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('images', function (Blueprint $table) {
            $table->string('image_type_str', 50)->nullable();
            $table->string('image_source_str', 50)->nullable();
        });

        foreach ($this->imageTypeMap as $int => $str) {
            DB::table('images')->where('image_type', $int)->update(['image_type_str' => $str]);
        }

        foreach ($this->imageSourceMap as $int => $str) {
            DB::table('images')->where('image_source', $int)->update(['image_source_str' => $str]);
        }

        $unmappedType = DB::table('images')->whereNull('image_type_str')->count();
        $unmappedSource = DB::table('images')->whereNull('image_source_str')->count();

        if ($unmappedType > 0 || $unmappedSource > 0) {
            // abort if some entries failed to copy over
            Schema::table('images', function (Blueprint $table) {
                $table->dropColumn(['image_type_str', 'image_source_str']);
            });

            throw new \RuntimeException(
                "Migration aborted: {$unmappedType} unmapped image_type rows, {$unmappedSource} unmapped image_source rows."
            );
        }

        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['image_type', 'image_source']);
        });

        Schema::table('images', function (Blueprint $table) {
            $table->renameColumn('image_type_str', 'image_type');
            $table->renameColumn('image_source_str', 'image_source');
        });

        // make not nullable
        Schema::table('images', function (Blueprint $table) {
            $table->string('image_type')->change();
            $table->string('image_source')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        $imageTypeMap = array_flip($this->imageTypeMap);
        $imageSourceMap = array_flip($this->imageSourceMap);

        Schema::table('images', function (Blueprint $table) {
            $table->smallInteger('image_type_int')->nullable();
            $table->smallInteger('image_source_int')->nullable();
        });

        foreach ($imageTypeMap as $str => $int) {
            DB::table('images')->where('image_type', $str)->update(['image_type_int' => $int]);
        }

        foreach ($imageSourceMap as $str => $int) {
            DB::table('images')->where('image_source', $str)->update(['image_source_int' => $int]);
        }

        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['image_type', 'image_source']);
        });

        Schema::table('images', function (Blueprint $table) {
            $table->renameColumn('image_type_int', 'image_type');
            $table->renameColumn('image_source_int', 'image_source');
        });

        // make not nullable
        Schema::table('images', function (Blueprint $table) {
            $table->smallInteger('image_type')->change();
            $table->smallInteger('image_source')->change();
        });
    }
};
