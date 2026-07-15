<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
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
            'household_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'billing_month' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'billing_from_month' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'null'       => true,
            ],
            'billing_to_month' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'fee_rate_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'payment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Chưa thanh toán',
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'payment_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'collected_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'receipt_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'qr_code_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('billing_month');
        $this->forge->addKey('payment_status');
        $this->forge->addForeignKey('household_id', 'households', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('fee_rate_id', 'fee_rates', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('collected_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments');
    }
}
