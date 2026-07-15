<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeeRatesTable extends Migration
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
            'household_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'vat' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 10.00,
            ],
            'effective_date' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Đang hiệu lực',
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
        $this->forge->createTable('fee_rates');
    }

    public function down()
    {
        $this->forge->dropTable('fee_rates');
    }
}
