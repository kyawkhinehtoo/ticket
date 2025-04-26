<x-filament-panels::page>
    <div class="mb-6">
        <h2 class="text-xl font-bold mb-4">Incident Hours Report</h2>
        
        {{ $this->form }}
    </div>
    
    @if($this->companyId)
        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4">
                Hours Report for {{ \App\Models\Company::find($this->companyId)->name }}
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="font-medium text-gray-700 mb-3">Hours by Incident Type</h4>
                    <div id="incident-chart" class="h-64"></div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="font-medium text-gray-700 mb-3">Detailed Breakdown</h4>
                    {{ $this->table }}
                </div>
            </div>
        </div>
        
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Get data from the table
                const tableRows = document.querySelectorAll('table tbody tr');
                const labels = [];
                const data = [];
                
                tableRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 2) {
                        labels.push(cells[0].textContent.trim());
                        data.push(parseInt(cells[1].textContent.trim()) || 0);
                    }
                });
                
                if (labels.length > 0) {
                    const options = {
                        series: [{
                            name: 'Total Hours',
                            data: data
                        }],
                        chart: {
                            type: 'bar',
                            height: 250
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: true,
                            }
                        },
                        dataLabels: {
                            enabled: true
                        },
                        xaxis: {
                            categories: labels,
                        },
                        colors: ['#6366F1']
                    };
                    
                    const chart = new ApexCharts(document.querySelector("#incident-chart"), options);
                    chart.render();
                }
            });
        </script>
        @endpush
    @else
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-gray-600">Please select a company from the dropdown above to view the incident hours report.</p>
        </div>
    @endif

   
</x-filament-panels::page>