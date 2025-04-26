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
        $company = Company::join('users', 'users.company_id', 'company.id')
            ->where('users.id', auth()->id())
            ->first();

        if (!$company)
            return abort('403', 'Unauthorized Action !');

        $data['company_id'] = $company->id;


        $contract = Contract::join('company', 'company.id', 'contract.company_id')
            ->join('users', 'users.company_id', 'company.id')
            ->where('users.id', auth()->id())
            ->whereDate('contract_to', '>=', Carbon::now())
            ->first();
        if (!$contract)
            return abort('403', 'No Active Contract !');

        $data['contract_id'] = $contract->id;
        return $data;
    }
}
