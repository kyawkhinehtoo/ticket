<?php

namespace App\Filament\Client\Resources\IncidentResource\Pages;

use App\Filament\Client\Resources\IncidentResource;
use Filament\Actions;
use App\Models\Company;
use App\Models\Contract;
use App\Models\User;
use App\Notifications\NewIncidentCreated;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateIncident extends CreateRecord
{
    protected static string $resource = IncidentResource::class;

    protected function afterCreate(): void
    {
        // Get all admin users
        $admins = User::where('role', 'admin')->get();
        
        // Send notification to each admin
        foreach ($admins as $admin) {
            $admin->notify(new NewIncidentCreated($this->record));
        }
    }

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
            ->whereDate('contract.contract_to', '<=', Carbon::now())
            ->select('contract.id')
            ->first();

        if (!$contract)
            return abort('403', 'No Active Contract !');

        $data['contract_id'] = $contract->id;
        $data['status'] = 'Open';

        return $data;
    }
}
