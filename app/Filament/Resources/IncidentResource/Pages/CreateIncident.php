<?php

namespace App\Filament\Resources\IncidentResource\Pages;

use App\Filament\Resources\IncidentResource;
use App\Models\Company;
use App\Models\Contract;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIncident extends CreateRecord
{
    protected static string $resource = IncidentResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $user = auth()->user();
        if ($user->role === 'admin') {
            // Admin selects company from form
            $company = Company::where('id', $data['company_id'])->first();
        } else {
            // Non-admin: use user's company
            $company = Company::join('users', 'users.company_id', 'company.id')
                ->select('company.id')
                ->where('users.id', $user->id)
                ->first();
            if ($company) {
                $data['company_id'] = $company->id;
            }
        }
        if (!$company) {
          
            return abort('403', 'Unauthorized Action !');
        }
        $contract = Contract::where('company_id', $company->id)
            ->whereDate('contract_to', '>=', Carbon::now())
            ->first();
        if (!$contract) {
            return abort('403', 'No Active Contract !');
        }
        $data['contract_id'] = $contract->id;
        return $data;
    }
}
