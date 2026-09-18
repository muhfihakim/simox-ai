<?php

namespace App\Http\Controllers;

use App\Models\ServerFisik;
use App\Models\VirtualMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Services\ProxmoxService;

class DashboardController extends Controller
{
    protected ProxmoxService $proxmoxService;

    public function __construct(ProxmoxService $proxmoxService)
    {
        $this->proxmoxService = $proxmoxService;
    }
    public function index()
    {
        // 1. Statistics
        $totalNodes = ServerFisik::count();
        $onlineNodes = ServerFisik::where('status', 'Online')->count();

        $totalVms = VirtualMachine::where('tipe', 'VM')->count();
        $activeVms = VirtualMachine::where('tipe', 'VM')->where('status', 'Running')->count();
        $inactiveVms = VirtualMachine::where('tipe', 'VM')->where('status', '!=', 'Running')->count();

        $totalLxc = VirtualMachine::where('tipe', 'LXC')->count();
        $activeLxc = VirtualMachine::where('tipe', 'LXC')->where('status', 'Running')->count();
        $inactiveLxc = VirtualMachine::where('tipe', 'LXC')->where('status', '!=', 'Running')->count();

        $totalCapacityGb = (int) ServerFisik::sum('storage_fisik');
        $totalAllocatedDiskGb = (int) VirtualMachine::sum('allocated_disk_gb');
        $usagePercent = $totalCapacityGb > 0 ? round(($totalAllocatedDiskGb / $totalCapacityGb) * 100) : 0;

        // 2. VM Table Data & Node Loads
        $vms = VirtualMachine::with('serverFisik')->latest()->paginate(10);
        $nodes = ServerFisik::with('virtualMachines')->orderBy('nama_server', 'asc')->get();
        $nodeLoads = $nodes->map(function ($node) {
            $vmCount = $node->virtualMachines->count();
            $ramUsed = (int) $node->virtualMachines->sum('allocated_ram_gb');
            $ramCap = (int) $node->kapasitas_ram;
            $ramPct = $ramCap > 0 ? round(($ramUsed / $ramCap) * 100) : 0;

            if ($ramPct >= 85) {
                $barColor = 'background-color: var(--danger);';
                $badgeClass = 'text-danger';
            } elseif ($ramPct >= 65) {
                $barColor = 'background-color: var(--warning);';
                $badgeClass = 'text-warning';
            } else {
                $barColor = 'background-color: var(--success);';
                $badgeClass = 'text-success';
            }

            return (object) [
                'id' => $node->id,
                'nama_server' => $node->nama_server,
                'status' => $node->status,
                'vm_count' => $vmCount,
                'ram_used' => $ramUsed,
                'kapasitas_ram' => $ramCap,
                'ram_pct' => $ramPct,
                'bar_style' => $barColor,
                'badge_class' => $badgeClass,
            ];
        });

        // 3. AI Insights (Cached or Rule-based)
        $aiInsights = $this->getAiInsights(false);

        return view('dashboard.index', compact(
            'totalNodes',
            'onlineNodes',
            'totalVms',
            'activeVms',
            'inactiveVms',
            'totalLxc',
            'activeLxc',
            'inactiveLxc',
            'totalCapacityGb',
            'totalAllocatedDiskGb',
            'usagePercent',
            'vms',
            'nodes',
            'nodeLoads',
            'aiInsights'
        ));
    }

    public function aiInsights(Request $request)
    {
        $refresh = $request->boolean('refresh', false);
        $insights = $this->getAiInsights($refresh);

        return response()->json([
            'success' => true,
            'insights' => $insights,
            'source' => Cache::get('dashboard_ai_insights_source', 'rule_based'),
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Endpoint API untuk telemetri trafik jaringan node Proxmox VE
     */
    public function nodeNetworkTraffic(Request $request, $node = null)
    {
        $nodeName = $node ?: $request->query('node', 'pve-01');
        $nodeIp = $request->query('ip');

        if (empty($nodeIp)) {
            $server = ServerFisik::where('nama_server', $nodeName)->first();
            $nodeIp = $server?->alamat_ip;
        }

        $data = $this->proxmoxService->getNodeNetworkTraffic($nodeName, $nodeIp);

        return response()->json($data);
    }

    private function getAiInsights($trigger = false)
    {
        $cacheKey = 'dashboard_ai_insights_data';
        $sourceKey = 'dashboard_ai_insights_source';

        // Manual trigger saja via tombol user
        if ($trigger) {
            Cache::forget($cacheKey);
            Cache::forget($sourceKey);

            $aiData = $this->fetchOpenClawAnalysis();
            if ($aiData && !empty($aiData)) {
                Cache::forever($cacheKey, $aiData);
                Cache::forever($sourceKey, 'openclaw');
                return $aiData;
            }

            // Fallback DB jika AI gagal saat trigger manual
            $fallback = $this->generateRuleBasedInsights();
            Cache::forever($cacheKey, $fallback);
            Cache::forever($sourceKey, 'rule_based');
            return $fallback;
        }

        // Kunjungan halaman biasa: BACA DARI CACHE. TIDAK PERNAH PANGGIL AI OTOMATIS (hemat token)
        return Cache::get($cacheKey, function () use ($cacheKey, $sourceKey) {
            $initial = $this->generateRuleBasedInsights();
            Cache::forever($cacheKey, $initial);
            Cache::forever($sourceKey, 'rule_based');
            return $initial;
        });
    }

    private function fetchOpenClawAnalysis()
    {
        @set_time_limit(120);

        $baseUrl = rtrim(config('services.openclaw.base_url', env('OPENCLAW_BASE_URL', 'http://203.2.151.17:18789')), '/');
        $token = config('services.openclaw.token', env('OPENCLAW_TOKEN'));
        $model = config('services.openclaw.model', env('OPENCLAW_MODEL', 'openclaw'));

        // Query database summary
        $nodes = ServerFisik::with('virtualMachines')->get();
        $stoppedVms = VirtualMachine::where('status', '!=', 'Running')->get();
        $totalRamAllocated = VirtualMachine::sum('allocated_ram_gb');
        $totalDiskAllocated = VirtualMachine::sum('allocated_disk_gb');
        $totalRamCapacity = $nodes->sum('kapasitas_ram');
        $totalDiskCapacity = $nodes->sum('storage_fisik');

        $nodeLines = [];
        foreach ($nodes as $node) {
            $vmCount = $node->virtualMachines->count();
            $ramUsed = $node->virtualMachines->sum('allocated_ram_gb');
            $ramPct = $node->kapasitas_ram > 0 ? round(($ramUsed / $node->kapasitas_ram) * 100) : 0;
            $nodeLines[] = "- {$node->nama_server}: Status {$node->status}, RAM {$node->kapasitas_ram}GB (Alokasi {$ramUsed}GB / {$ramPct}%), VM: {$vmCount}";
        }

        $stoppedList = $stoppedVms->map(function ($vm) {
            return "{$vm->hostname} ({$vm->fungsi_layanan})";
        })->implode(', ');

        $prompt = "Berikut data inventaris server Proxmox Diskominfo:\n"
            . "Total Node: " . $nodes->count() . "\n"
            . implode("\n", $nodeLines) . "\n"
            . "Total RAM: {$totalRamAllocated}GB teralokasi dari {$totalRamCapacity}GB\n"
            . "Total Storage: {$totalDiskAllocated}GB teralokasi dari {$totalDiskCapacity}GB\n"
            . "Total VM/LXC: " . VirtualMachine::count() . " (Running: " . VirtualMachine::where('status', 'Running')->count() . ", Stopped: " . $stoppedVms->count() . ")\n"
            . ($stoppedList ? "VM Stopped: {$stoppedList}\n" : "")
            . "\nTugas Anda: Berikan 2 sampai 3 insight/rekomendasi teknis inventaris paling krusial.\n"
            . "Format WAJIB JSON array murni tanpa markdown lain:\n"
            . "[\n"
            . "  {\n"
            . "    \"level\": \"warning|danger|info|success\",\n"
            . "    \"badge\": \"Rekomendasi|Audit|Peringatan|Optimal\",\n"
            . "    \"title\": \"Judul singkat\",\n"
            . "    \"description\": \"Penjelasan teknis ringkas dan padat\"\n"
            . "  }\n"
            . "]";

        try {
            $client = Http::timeout(45);
            if (!empty($token)) {
                $client = $client->withToken($token);
            }

            $response = $client->post($baseUrl . '/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Anda adalah AI Auditor Khusus Analisis Infrastruktur SIMOX Proxmox VE. Sesi ini khusus untuk melakukan audit dan rekomendasi inventaris berkala pada dashboard SIMOX. Berikan insight paling esensial dalam format JSON array yang diminta."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'user' => 'simox-dashboard-analysis',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                // Parse JSON array from LLM response
                $content = trim($content);
                if (preg_match('/```(?:json)?\s*(\[[\s\S]*?\])\s*```/', $content, $m)) {
                    $content = $m[1];
                } elseif (preg_match('/\[[\s\S]*\]/', $content, $m)) {
                    $content = $m[0];
                }

                $decoded = json_decode($content, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $sanitized = [];
                    foreach ($decoded as $item) {
                        if (!isset($item['title'], $item['description'])) continue;
                        $sanitized[] = [
                            'level' => in_array($item['level'] ?? '', ['warning', 'danger', 'info', 'success']) ? $item['level'] : 'info',
                            'badge' => $item['badge'] ?? 'AI Insight',
                            'title' => $item['title'],
                            'description' => $item['description'],
                        ];
                    }
                    if (!empty($sanitized)) {
                        return $sanitized;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Dashboard OpenClaw Analysis exception: ' . $e->getMessage());
        }

        return null;
    }

    private function generateRuleBasedInsights()
    {
        $insights = [];

        // 1. Cek VM / LXC yang stopped
        $stoppedVms = VirtualMachine::where('status', '!=', 'Running')->get();
        if ($stoppedVms->count() > 0) {
            $sampleNames = $stoppedVms->pluck('hostname')->take(3)->implode(', ');
            $insights[] = [
                'level' => 'warning',
                'badge' => 'Audit VM',
                'title' => "{$stoppedVms->count()} Unit VM/LXC Nonaktif",
                'description' => "Unit ({$sampleNames}) terdeteksi Stopped. Pastikan mesin tidak menahan alokasi resource yang tidak digunakan.",
            ];
        }

        // 2. Cek utilisasi Node RAM/Disk
        $nodes = ServerFisik::with('virtualMachines')->get();
        $overloadedNode = null;
        foreach ($nodes as $node) {
            $ramAllocated = $node->virtualMachines->sum('allocated_ram_gb');
            if ($node->kapasitas_ram > 0 && ($ramAllocated / $node->kapasitas_ram) > 0.8) {
                $overloadedNode = $node;
                break;
            }
        }

        if ($overloadedNode) {
            $insights[] = [
                'level' => 'danger',
                'badge' => 'Peringatan',
                'title' => "Alokasi Node \"{$overloadedNode->nama_server}\" Tinggi",
                'description' => "Alokasi memori melebihi 80%. Pertimbangkan migrasi sebagian beban VM ke node lain.",
            ];
        } else {
            $insights[] = [
                'level' => 'success',
                'badge' => 'Kapasitas',
                'title' => "Distribusi Beban Node Stabil",
                'description' => "Seluruh {$nodes->count()} server node beroperasi dengan pemakaian memori dalam batas aman.",
            ];
        }

        return $insights;
    }
}
