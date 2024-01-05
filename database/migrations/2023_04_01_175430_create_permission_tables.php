<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;
use Carbon\Carbon;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->rename('zizaco_roles');
            });
        }
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->rename('zizaco_permissions');
            });
        }

        if (Schema::hasTable('role_user')) {
            Schema::table('role_user', function (Blueprint $table) {
                $table->rename('zizaco_role_user');
            });
        }
        if (Schema::hasTable('permission_role')) {
            Schema::table('permission_role', function (Blueprint $table) {
                $table->rename('zizaco_permission_role');
            });
        }


        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id'); // permission id
            $table->string('controller_name', 125)->nullable();       // For MySQL 8.0 use string('name', 125);
            $table->string('name', 125)->nullable();       // For MySQL 8.0 use string('name', 125);
            $table->string('display_name', 125)->nullable();       // For MySQL 8.0 use string('name', 125);
            $table->string('description', 125)->nullable();       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name', 125); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name', 125); // For MySQL 8.0 use string('name', 125);
            $table->string('display_name', 125)->nullable(); // For MySQL 8.0 use string('name', 125);
            $table->string('description', 125)->nullable(); // For MySQL 8.0 use string('name', 125);
            $table->tinyInteger('is_user')->nullable();
            $table->tinyInteger('is_head')->nullable();
            $table->string('guard_name', 125); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([PermissionRegistrar::$pivotPermission, PermissionRegistrar::$pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        /* Transfer Existing Data from Zizaco to Spatie */
        $zizaco_roles = DB::table('zizaco_roles')->get();
        foreach ($zizaco_roles as $zizaco_role) {
            DB::table('roles')->insert([
                'id' => $zizaco_role->id,
                'name' => $zizaco_role->name,
                'display_name' => $zizaco_role->display_name,
                'description' => $zizaco_role->description,
                'is_user' => $zizaco_role->is_user,
                'is_head' => $zizaco_role->is_head,
                'guard_name' => 'web',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $zizaco_permissions = DB::table('zizaco_permissions')->get();
        foreach ($zizaco_permissions as $zizaco_permission) {
            DB::table('permissions')->insert([
                'id' => $zizaco_permission->id,
                'controller_name' => $zizaco_permission->controller_name,
                'name' => $zizaco_permission->name,
                'display_name' => $zizaco_permission->display_name,
                'description' => $zizaco_permission->description,
                'guard_name' => 'web',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $zizaco_role_users = DB::table('zizaco_role_user')->get();
        foreach ($zizaco_role_users as $zizaco_role_user) {
            DB::table('model_has_roles')->insert([
                'role_id' => $zizaco_role_user->role_id,
                'model_type' => 'App\User',
                'model_id' => $zizaco_role_user->user_id,
            ]);
        }

        $zizaco_permission_roles = DB::table('zizaco_permission_role')->get();
        foreach ($zizaco_permission_roles as $zizaco_permission_role) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $zizaco_permission_role->permission_id,
                'role_id' => $zizaco_permission_role->role_id,
            ]);
        }
        /* End of Transfer Existing Data from Zizaco to Spatie */

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);

        if (Schema::hasTable('zizaco_permissions')) {
            Schema::table('zizaco_permissions', function (Blueprint $table) {
                $table->rename('permissions');
            });
        }
        if (Schema::hasTable('zizaco_roles')) {
            Schema::table('zizaco_roles', function (Blueprint $table) {
                $table->rename('roles');
            });
        }
        if (Schema::hasTable('zizaco_role_user')) {
            Schema::table('zizaco_role_user', function (Blueprint $table) {
                $table->rename('role_user');
            });
        }
        if (Schema::hasTable('zizaco_permission_role')) {
            Schema::table('zizaco_permission_role', function (Blueprint $table) {
                $table->rename('permission_role');
            });
        }
    }
}
