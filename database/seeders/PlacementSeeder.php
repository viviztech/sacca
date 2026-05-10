<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PlacementApplication;
use App\Models\PlacementDrive;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlacementSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'IndiGo Airlines', 'industry' => 'Aviation', 'contact_person' => 'Rohit Sharma', 'contact_email' => 'hr@indigo.in', 'contact_phone' => '9876543210', 'website' => 'https://www.goindigo.in'],
            ['name' => 'Air India', 'industry' => 'Aviation', 'contact_person' => 'Anita Singh', 'contact_email' => 'careers@airindia.in', 'contact_phone' => '9876543211'],
            ['name' => 'SpiceJet', 'industry' => 'Aviation', 'contact_person' => 'Vikram Nair', 'contact_email' => 'talent@spicejet.com', 'contact_phone' => '9876543212'],
            ['name' => 'Coimbatore Airport Authority', 'industry' => 'Airport Operations', 'contact_person' => 'K. Murugan', 'contact_email' => 'hr@cbeairport.gov.in', 'contact_phone' => '9876543213'],
            ['name' => 'Blue Dart Aviation', 'industry' => 'Cargo & Logistics', 'contact_person' => 'Suresh Kumar', 'contact_email' => 'hr@bluedart.com', 'contact_phone' => '9876543214'],
        ];

        $createdCompanies = [];
        foreach ($companies as $company) {
            $createdCompanies[] = Company::firstOrCreate(['name' => $company['name']], array_merge($company, ['is_active' => true]));
        }

        $cbe = Branch::where('code', 'CBE')->first();
        $ker = Branch::where('code', 'KER')->first();
        $creator = User::where('role', 'placement_officer')->first() ?? User::where('role', 'branch_admin')->first();

        $drives = [
            ['company' => 'IndiGo Airlines', 'title' => 'Ground Staff Recruitment 2026', 'date' => now()->addDays(15), 'branch' => $cbe, 'positions' => 10, 'package_lpa' => 3.5],
            ['company' => 'Air India', 'title' => 'Cabin Crew Hiring Drive', 'date' => now()->addDays(22), 'branch' => $cbe, 'positions' => 6, 'package_lpa' => 4.2],
            ['company' => 'SpiceJet', 'title' => 'Airport Operations Executive', 'date' => now()->addDays(30), 'branch' => $ker, 'positions' => 8, 'package_lpa' => 3.8],
            ['company' => 'Blue Dart Aviation', 'title' => 'Cargo Coordinator', 'date' => now()->subDays(10), 'branch' => $cbe, 'positions' => 4, 'package_lpa' => 3.2],
        ];

        $students = User::where('role', 'student')->get();

        foreach ($drives as $driveData) {
            $company = collect($createdCompanies)->firstWhere('name', $driveData['company']);
            if (! $company || ! $creator) {
                continue;
            }

            $drive = PlacementDrive::firstOrCreate(
                ['title' => $driveData['title'], 'company_id' => $company->id],
                [
                    'description' => 'Exciting opportunity to join '.$driveData['company'].'. Must have completed aviation training.',
                    'drive_date' => $driveData['date'],
                    'venue' => $driveData['branch']->name,
                    'positions' => $driveData['positions'],
                    'package_lpa' => $driveData['package_lpa'],
                    'eligibility_criteria' => ['min_attendance' => 75],
                    'branch_id' => $driveData['branch']->id,
                    'created_by' => $creator->id,
                    'is_active' => true,
                ]
            );

            // Add applications from students in that branch
            $branchStudents = $students->where('branch_id', $driveData['branch']->id);
            foreach ($branchStudents->take(3) as $student) {
                PlacementApplication::firstOrCreate(
                    ['drive_id' => $drive->id, 'student_id' => $student->id],
                    ['status' => collect(['applied', 'shortlisted', 'selected'])->random(), 'applied_at' => now()->subDays(rand(2, 8))]
                );
            }
        }
    }
}
