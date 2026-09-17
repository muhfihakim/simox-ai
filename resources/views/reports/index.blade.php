<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Laporan Pemakaian Resource</h1>
                <p>Visualisasi pemakaian CPU, RAM, dan Storage dari VM/LXC di seluruh Node kluster.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="window.print()"><i class="ph ph-printer"></i> Cetak
                    Laporan</button>
                <button onclick="exportPdfWithCharts()" class="btn btn-primary"><i class="ph ph-file-pdf"></i> Ekspor
                    PDF</button>
            </div>
        </div>

        <!-- Stats summary -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total vCPU Alokasi</span>
                        <h3 class="stat-value">{{ $allocatedCpu }} <span class="text-xs text-muted">/
                                {{ $totalCpu }} Cores</span></h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-cpu"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-blue"
                            style="width: {{ $totalCpu > 0 ? ($allocatedCpu / $totalCpu) * 100 : 0 }}%;"></div>
                    </div>
                    <span class="text-muted text-xs mt-1 d-block">Teralokasi
                        {{ $totalCpu > 0 ? round(($allocatedCpu / $totalCpu) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total RAM Alokasi</span>
                        <h3 class="stat-value">{{ $allocatedRam }} <span class="text-xs text-muted">/
                                {{ $totalRam }} GB</span></h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-memory"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-orange"
                            style="width: {{ $totalRam > 0 ? ($allocatedRam / $totalRam) * 100 : 0 }}%;"></div>
                    </div>
                    <span class="text-muted text-xs mt-1 d-block">Teralokasi
                        {{ $totalRam > 0 ? round(($allocatedRam / $totalRam) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Storage Terpakai</span>
                        <h3 class="stat-value">{{ $allocatedStorage }} <span class="text-xs text-muted">/
                                {{ $totalStorage }} GB</span></h3>
                    </div>
                    <div class="stat-icon bg-green"><i class="ph ph-hard-drive"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-green"
                            style="width: {{ $totalStorage > 0 ? ($allocatedStorage / $totalStorage) * 100 : 0 }}%;">
                        </div>
                    </div>
                    <span class="text-muted text-xs mt-1 d-block">Terpakai
                        {{ $totalStorage > 0 ? round(($allocatedStorage / $totalStorage) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Aset Tercatat</span>
                        <h3 class="stat-value">{{ $totalVm }}</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-squares-four"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-muted text-xs">Total VM & LXC yang Terdata</span>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="content-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); margin-bottom: 1.5rem;">


            <!-- RAM Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribusi RAM per Bidang</h3>
                </div>
                <div class="card-body" style="display: flex; justify-content: center; align-items: center;">
                    <div style="width: 100%; max-width: 350px;">
                        <canvas id="ramPieChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Storage Bar/Doughnut Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Penggunaan Storage</h3>
                </div>
                <div class="card-body" style="display: flex; justify-content: center; align-items: center;">
                    <div style="width: 100%; max-width: 350px;">
                        <canvas id="hddDoughnutChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tipe Aset Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Komposisi Tipe Aset</h3>
                </div>
                <div class="card-body" style="display: flex; justify-content: center; align-items: center;">
                    <div style="width: 100%; max-width: 350px;">
                        <canvas id="typePieChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Node Capacity Charts -->
        <div class="content-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">
            <!-- CPU Node Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">vCPU (Terpakai vs Sisa)</h3>
                </div>
                <div class="card-body"><canvas id="cpuNodeBarChart" height="250"></canvas></div>
            </div>
            <!-- RAM Node Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">RAM (Terpakai vs Sisa) GB</h3>
                </div>
                <div class="card-body"><canvas id="ramNodeBarChart" height="250"></canvas></div>
            </div>
            <!-- Storage Node Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Storage (Terpakai vs Sisa) GB</h3>
                </div>
                <div class="card-body"><canvas id="storageNodeBarChart" height="250"></canvas></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tabulasi Instansi</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dense-table">
                        <thead>
                            <tr>
                                <th>Peringkat</th>
                                <th>Bidang / Instansi</th>
                                <th>Total Aset</th>
                                <th>Total vCPU</th>
                                <th>Total RAM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tabulasi as $index => $row)
                                <tr>
                                    <td><strong>#{{ $index + 1 }}</strong></td>
                                    <td>{{ $row->fungsi_layanan ?? 'Unknown' }}</td>
                                    <td>{{ $row->total_aset }}</td>
                                    <td>{{ $row->total_cpu ?? 0 }} Cores</td>
                                    <td>{{ $row->total_ram ?? 0 }} GB</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Inject Chart Logic -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CPU Stacked Bar Chart
            const cpuNodeCanvas = document.getElementById('cpuNodeBarChart');
            if (cpuNodeCanvas) {
                new Chart(cpuNodeCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($cpuPerNodeLabels) !!},
                        datasets: [{
                                label: 'Terpakai (Cores)',
                                data: {!! json_encode($cpuPerNodeData) !!},
                                backgroundColor: '#4f46e5'
                            },
                            {
                                label: 'Sisa (Cores)',
                                data: {!! json_encode($cpuPerNodeSisa) !!},
                                backgroundColor: '#e2e8f0'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // RAM Stacked Bar Chart
            const ramNodeCanvas = document.getElementById('ramNodeBarChart');
            if (ramNodeCanvas) {
                new Chart(ramNodeCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($cpuPerNodeLabels) !!},
                        datasets: [{
                                label: 'Terpakai (GB)',
                                data: {!! json_encode($ramPerNodeData) !!},
                                backgroundColor: '#f97316'
                            },
                            {
                                label: 'Sisa (GB)',
                                data: {!! json_encode($ramPerNodeSisa) !!},
                                backgroundColor: '#e2e8f0'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Storage Stacked Bar Chart
            const storageNodeCanvas = document.getElementById('storageNodeBarChart');
            if (storageNodeCanvas) {
                new Chart(storageNodeCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($cpuPerNodeLabels) !!},
                        datasets: [{
                                label: 'Terpakai (GB)',
                                data: {!! json_encode($storagePerNodeData) !!},
                                backgroundColor: '#10b981'
                            },
                            {
                                label: 'Sisa (GB)',
                                data: {!! json_encode($storagePerNodeSisa) !!},
                                backgroundColor: '#e2e8f0'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // RAM Pie Chart
            const ramPieCanvas = document.getElementById('ramPieChart');
            if (ramPieCanvas) {
                new Chart(ramPieCanvas.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: {!! json_encode($ramLabels) !!},
                        datasets: [{
                            data: {!! json_encode($ramData) !!},
                            backgroundColor: ['#4f46e5', '#f97316', '#0ea5e9', '#8b5cf6',
                                '#10b981'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }

            // Storage Doughnut Chart
            const hddDoughnutCanvas = document.getElementById('hddDoughnutChart');
            if (hddDoughnutCanvas) {
                new Chart(hddDoughnutCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Terpakai (GB)', 'Sisa/Tersedia (GB)'],
                        datasets: [{
                            data: {!! json_encode($storageData) !!},
                            backgroundColor: ['#f59e0b', '#e2e8f0'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }

            // Type Pie Chart
            const typePieCanvas = document.getElementById('typePieChart');
            if (typePieCanvas) {
                new Chart(typePieCanvas.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: {!! json_encode($osLabels) !!},
                        datasets: [{
                            data: {!! json_encode($osData) !!},
                            backgroundColor: ['#8b5cf6', '#0ea5e9', '#10b981', '#f97316',
                                '#f43f5e'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });

        function exportPdfWithCharts() {
            showToast('Menyiapkan dokumen PDF formal...', 'info');

            // Collect all charts
            const chartsToExtract = ['cpuNodeBarChart', 'ramNodeBarChart', 'storageNodeBarChart', 'ramPieChart',
                'hddDoughnutChart', 'typePieChart'
            ];
            const chartData = {};

            chartsToExtract.forEach(id => {
                const canvas = document.getElementById(id);
                if (canvas) {
                    chartData[id] = canvas.toDataURL('image/png');
                }
            });

            // Create a hidden form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('reports.export') }}';

            // Add CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Add charts to form
            for (const [id, base64] of Object.entries(chartData)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `charts[${id}]`;
                input.value = base64;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
</x-layouts.app>
