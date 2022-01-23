<?php

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
    		['id' => 1, 'stripe_public' => 'pk_test_51K6kOMF1nDrlZowxrw8mhkZrU3THtX2tN5deSEzgyL3CV1qN8dlKNulL5oeqUb3Socl3eMowG21euJAbN3A30sLB00a8k9ccpD', 'stripe_secret' => 'sk_test_51K6kOMF1nDrlZowxVrdCsveoCBJYHKMmdou0utST9mNai7MNoJNyBQkjWtjRHaQSyqAwzxHzbS7Qibsvrh7cBcyf00iuu96tgi', 'paypal_public' => 'AZge19imfMY4lwouPFYGEEAeAyCWCqbi3hp_o0tbJLUX9GUzknOzAs58Vha5nf6FJoM4-vSsx4cspOD2', 'paypal_secret' => 'ELGAm6CRpuswus4kNMdXg4s9GRFfTDxKYZxszBOwQcdHAaLxk7Xwm747fkm9WxAye22oj6eMDOXiLKzg']
    	];
    	DB::table('settings')->insert($settings);
    }
}
