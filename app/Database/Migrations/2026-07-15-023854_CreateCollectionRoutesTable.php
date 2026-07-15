<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCollectionRoutesTable extends Migration
{
    public function up()
    {
        // Table: collection_routes
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'route_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'route_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Hoạt động',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('route_code');
        $this->forge->addForeignKey('parent_id', 'collection_routes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('collection_routes');

        // Table: route_assignments
        $this->forge->addField([
            'route_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addPrimaryKey(['route_id', 'user_id']);
        $this->forge->addForeignKey('route_id', 'collection_routes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('route_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('route_assignments');
        $this->forge->dropTable('collection_routes');
    }
}
