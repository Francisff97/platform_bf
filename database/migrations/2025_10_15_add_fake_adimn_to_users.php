<?php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('user')->after('email');
        $table->boolean('is_demo')->default(false)->after('role');
    });
}
public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['role','is_demo']);
    });
}