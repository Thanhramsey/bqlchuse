<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHouseholdsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'household_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'owner_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'id_card' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'ward_group' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ward' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'household_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'members_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Đang hoạt động',
            ],
            'gps' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'route_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addUniqueKey('household_code');
        $this->forge->addForeignKey('route_id', 'collection_routes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('households');
    }

    public function down()
    {
        $this->forge->dropTable('households');
    }
}
