<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed municipalities
        $cot_id = DB::table('municipalities')->insertGetId(['name' => 'City of Tshwane', 'province' => 'Gauteng', 'code' => 'COT', 'created_at' => now(), 'updated_at' => now()]);
        $eku_id = DB::table('municipalities')->insertGetId(['name' => 'Ekurhuleni', 'province' => 'Gauteng', 'code' => 'EKU', 'created_at' => now(), 'updated_at' => now()]);
        $mog_id = DB::table('municipalities')->insertGetId(['name' => 'Mogale', 'province' => 'Gauteng', 'code' => 'MOG', 'created_at' => now(), 'updated_at' => now()]);
        $emf_id = DB::table('municipalities')->insertGetId(['name' => 'Emfuleni', 'province' => 'Gauteng', 'code' => 'EMF', 'created_at' => now(), 'updated_at' => now()]);
        $mer_id = DB::table('municipalities')->insertGetId(['name' => 'Merafong', 'province' => 'Gauteng', 'code' => 'MER', 'created_at' => now(), 'updated_at' => now()]);
        $rwc_id = DB::table('municipalities')->insertGetId(['name' => 'Rand West City', 'province' => 'Gauteng', 'code' => 'RWC', 'created_at' => now(), 'updated_at' => now()]);
        $coj_id = DB::table('municipalities')->insertGetId(['name' => 'Johannesburg', 'province' => 'Gauteng', 'code' => 'COJ', 'created_at' => now(), 'updated_at' => now()]);

        // Seed companies
        DB::table('companies')->insert(['name' => 'Abacus Lending - Cellphones', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Alphasure Underwriting Managers', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'BA DIRA MMOGO SOCIAL CLUB', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Bayport Life Plus - Traffic Insurance', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capital Legacy Solutions Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Cede Capital Pty Ltd - Insurance', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Cede Capital Pty Ltd - Loan', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Clientele Life Assurance Company Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Datacapital Technologies & ISP', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Emerald Life CC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Empower Fin Salary Advance Product', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'FFS Finance - MN Sumbulukwa', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Fundi Capital Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Government Empl Personal Finance Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Happy Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Laerskool Jeugkrag', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Learskool Uniefees - 1052', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Learskool Uniefees - 2081', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Lion Life - Lion of Africa', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'M&H Bridging Finance Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - F Moropa', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - Kadiaka LK', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - KU Mokobane', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - M Mthombeni', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - Mametja TN', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - Mathiba T', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - Morukhu Makolobe J', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - MP Molekoa', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - MS Maila', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - P Bhotya', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - TS Sibeko', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'MTN - VBD Maphosa', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Rand Mutual Assurance Co Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Region 6 Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Retail Financial Services', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'SAMWU Data Wallet Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $cot_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => '21St Century Funeral Brokers Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => '32 Sign Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Abacus Lending - Wellness Loans', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Abacus Lending - Wellness Policies', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ABSA VF - Masweneng AM', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ABSA VF - PJ Radebe', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ABSA VF - S Moeketsi - Toyota', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Aganang Basebetsi Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Allowance - Mojapelo SE', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Amazing Shift Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ASAP Training and Consulting', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'B3 Insurance Brokers CC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Basebenzi Bahlangene Burial Soc', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Bathobatsho Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Boksburg High School', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Boston City Campus Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Brakpan Energy MENS Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capital Legacy Solutions Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capitec Loan - PH Mpateng', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Credit Gateway - Cellphone', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Da Champ Security', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Datacapital Technologies & ISP', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Delta Kempton Group Stokvel', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Development Bank of SA', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Duraform (Pty) Ltd - Training', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Ebony Burial Society', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Emerald Life CC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Empower Fin - Home loans', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'FNB - Nkambule M', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'FNB Card - Mtsweni PS', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Gallagher Combined School', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Gov Emp Personal Finance', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Hi-Tech Training & Consulting', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Imizi Housing', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Joko Investments - J Breytenbach', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Legae Investment - C Ndlovu', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Legal and Tax', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Liberty Corporate', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Mabusa Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Matsose Funeral Undertakers', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Mohlala Financial Services', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Motlhago Investments', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Old Mutual Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Oupa Nkhwedi Tladi', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Phomolong Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Prokato Trading Enterprises', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Rand Mutual Assurance Co Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'SANAC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Service Staff Provident Fund', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Southern Ambition - B Mahlangu', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Soweto Country Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Stangen Limited', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'TS Mphake Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Tswaranang Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'uMalusi Investment Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'We Buy Cars', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Workmens Compensation Fund', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $eku_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Absa Vehicle & Asset Finance', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'AJMS Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Allenridge Secondary School', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'AOG Church', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Bontleng Ba Mmoho Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Buffalo Insurance Brokers', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capital Legacy Solutions Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Clientele Life Assurance Company Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Credit Guarantee Insurance Co', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Emerald Life CC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'First National Bank - Home loan', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Fundi Capital Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Indibano Financial Services', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Kapana Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Liberty Corporate', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Life Sense Financial Services', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Mohlakeng Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Old Mutual Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Rand Mutual Assurance Co Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Saambou', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Stangen Limited', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Transnet', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Tsabedze TS', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'UMALUSI Investment Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Workmens Compensation Fund', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mog_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ABSA VF - G Marule', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $emf_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ABSA VF - M Moloi', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $emf_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ABSA VF - Mtsweni PS', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $emf_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'ABSA VF - Phetla MS', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $emf_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'AlphaSure Underwriting Managers', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $emf_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capital Legacy Solutions Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $emf_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capital Legacy Solutions Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Emerald Life CC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Fundi Capital Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Lion Life - Lion of Africa', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'MMK Financial Solutions', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Old Mutual Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Rand Mutual Assurance Co Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Retail Financial Services', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $mer_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capital Legacy Solutions Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Emerald Life CC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'First National Bank', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Fundi Capital Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Mohlakeng Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Old Mutual Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Rand Mutual Assurance Co Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Retail Financial Services', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Stangen Limited', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Tswaranang Social Club', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Workmens Compensation Fund', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $rwc_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => '21St Century Funeral Brokers Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Adcock Ingram Employee Fund', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Alexander Forbes Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Capital Legacy Solutions Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Credit Guarantee Insurance Co', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Emerald Life CC', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'First National Bank - Home loan', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Fundi Capital Pty Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Government Employees Fund', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Maintenance - L. M Malinga', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Metropolitan Group & Life', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Old Mutual Group', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Rand Mutual Assurance Co Ltd', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Scorpion - Legal Protection', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->insert(['name' => 'Stangen Limited', 'registration_number' => null, 'contact_email' => null, 'status' => 'active', 'municipality_id' => $coj_id, 'created_at' => now(), 'updated_at' => now()]);

        $permissions = [
            'view dashboard',
            'view companies',
            'create company',
            'edit company',
            'delete company',
            'view municipalities',
            'create municipality',
            'edit municipality',
            'delete municipality',
            'view deadlines',
            'create deadline',
            'edit deadline',
            'delete deadline',
            'view submissions',
            'view uploads',
            'create upload',
            'export uploads',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Define roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $companyUserRole = Role::firstOrCreate(['name' => 'company-user', 'guard_name' => 'web']);

        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole->givePermissionTo([
            'view dashboard',
            'view companies',
            'manage companies',
            'view municipalities',
            'view deadlines',
            'manage deadlines',
            'view submissions',
            'view uploads',
            'create uploads',
            'export uploads',
            'view reports',
            'view audits',
            'view audit logs',
            'manage users',
            'manage roles',
            'manage permissions',
        ]);

        $companyUserRole->givePermissionTo([
            'view submissions',
            'view uploads',
            'create uploads',
        ]);


        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'super-admin@example.com'],
            [
                'name' => 'Super Admin',
                'employee_number' => 'superadmin001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Create admin user from factory
        $adminUser = User::factory()->admin()->create();
        $adminUser->assignRole($adminRole);

        // Create some company users
        $companyUser = User::factory()->create([
            'employee_number' => 'companyuser001',
            'name' => 'Company User',
            'email' => 'company@example.com',
        ]);
        $companyUser->assignRole($companyUserRole);
    }
}
