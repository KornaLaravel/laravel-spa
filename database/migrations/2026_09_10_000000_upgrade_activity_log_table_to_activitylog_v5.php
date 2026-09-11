<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings the activity log table in line with spatie/laravel-activitylog v5.
 *
 * v5 splits tracked model changes out of "properties" into a dedicated
 * "attribute_changes" column and drops the batch system entirely.
 */
return new class extends Migration
{
    public function up(): void
    {
        $connection = config('activitylog.database_connection');
        $table = config('activitylog.table_name', 'activity_log');

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) {
            $blueprint->json('attribute_changes')->nullable()->after('causer_id');
        });

        DB::connection($connection)->table($table)
            ->where(function ($query) {
                $query->whereNotNull('properties->attributes')
                    ->orWhereNotNull('properties->old');
            })
            ->eachById(function ($row) use ($connection, $table) {
                $properties = json_decode($row->properties ?? '[]', true) ?: [];
                $changes = array_intersect_key($properties, array_flip(['attributes', 'old']));
                $remaining = array_diff_key($properties, array_flip(['attributes', 'old']));

                DB::connection($connection)->table($table)->where('id', $row->id)->update([
                    'attribute_changes' => empty($changes) ? null : json_encode($changes),
                    'properties' => empty($remaining) ? null : json_encode($remaining),
                ]);
            });

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('batch_uuid');
        });
    }

    public function down(): void
    {
        $connection = config('activitylog.database_connection');
        $table = config('activitylog.table_name', 'activity_log');

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) {
            $blueprint->uuid('batch_uuid')->nullable()->after('properties');
        });

        DB::connection($connection)->table($table)
            ->whereNotNull('attribute_changes')
            ->eachById(function ($row) use ($connection, $table) {
                $properties = json_decode($row->properties ?? '[]', true) ?: [];
                $changes = json_decode($row->attribute_changes ?? '[]', true) ?: [];

                DB::connection($connection)->table($table)->where('id', $row->id)->update([
                    'properties' => json_encode(array_merge($properties, $changes)),
                ]);
            });

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('attribute_changes');
        });
    }
};
