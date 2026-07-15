<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVnptFieldsToPayments extends Migration
{
    public function up()
    {
        // Add VNPT e-invoice tracking columns to payments table
        $this->forge->addColumn('payments', [
            'vnpt_fkey' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'qr_code_url',
            ],
            'vnpt_inv_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'vnpt_fkey',
            ],
            'vnpt_issue_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'vnpt_inv_no',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payments', ['vnpt_fkey', 'vnpt_inv_no', 'vnpt_issue_date']);
    }
}
