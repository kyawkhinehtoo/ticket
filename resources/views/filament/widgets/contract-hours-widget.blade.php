<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach ($this->getCompanies() as $company)
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center mb-2">
                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full fi-wi-stats-overview-stat-description-icon text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                <h3 class="fi-wi-stats-overview-stat-label text-sm font-medium text-gray-500 dark:text-gray-400">{{ $company->name }}</h3>
            </div>
            <div class="text-xl font-bold mb-1 text-primary-500">
                {{ $company->used_hours }} <span class="text-gray-400">/</span> {{ $company->total_hours }} hours
            </div>
            <div class="fi-wi-stats-overview-stat-description text-sm fi-color-custom text-custom-600 dark:text-custom-400 fi-color-success">Used/Total Hours</div>
            <div class="w-full mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                 
                    <div class="h-5 w-5 text-primary-500 bg-primary-500 rounded-full" style="width: {{ min(100, ($company->total_hours > 0 ? ($company->used_hours / $company->total_hours) * 100 : 0)) }}%"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>


