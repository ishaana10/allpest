<?php
/**
 * modules/fieldwork_clients/fieldwork_clients.php
 * Clients directory View
 */
declare(strict_types=1);
?>
<div class="fw-container p-6 bg-slate-50 min-h-screen text-slate-800 font-sans">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span>Workspace</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span class="text-slate-800 font-semibold">Clients</span>
        </div>
    </div>

    <div class="flex items-start justify-between mb-6">
        <div>
            <div class="text-[11px] font-bold text-teal-700 uppercase tracking-wider mb-1">DIRECTORY / CLIENTS</div>
            <h1 class="text-2xl font-bold text-slate-900">Client Directory</h1>
            <p class="text-sm text-slate-500 mt-1">Manage client records and contact information for job assignments.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <input type="text" placeholder="Search clients..." class="pl-4 pr-4 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-teal-600 w-64">
        </div>
        <table class="w-full text-left text-sm text-slate-700">
            <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3 px-4">Client name</th>
                    <th class="py-3 px-4">Contact person</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <tr class="hover:bg-slate-50">
                    <td class="py-3.5 px-4 font-semibold text-slate-900">Pacific Timber Exports Pte Ltd</td>
                    <td class="py-3.5 px-4 text-slate-700">Ana Kumar</td>
                    <td class="py-3.5 px-4 text-slate-600 font-mono text-xs">operations@pacific-timber.example</td>
                    <td class="py-3.5 px-4 text-slate-500 text-xs">Export operations • Primary contact for this consignment</td>
                </tr>
                <tr class="hover:bg-slate-50">
                    <td class="py-3.5 px-4 font-semibold text-slate-900">Rewa Export Traders</td>
                    <td class="py-3.5 px-4 text-slate-700">Dev Singh</td>
                    <td class="py-3.5 px-4 text-slate-600 font-mono text-xs">logistics@rewa-export.example</td>
                    <td class="py-3.5 px-4 text-slate-500 text-xs">General timber exporter</td>
                </tr>
                <tr class="hover:bg-slate-50">
                    <td class="py-3.5 px-4 font-semibold text-slate-900">Coral Coast Logistics</td>
                    <td class="py-3.5 px-4 text-slate-700">Savenaca Vasi</td>
                    <td class="py-3.5 px-4 text-slate-600 font-mono text-xs">info@coralcoast.example</td>
                    <td class="py-3.5 px-4 text-slate-500 text-xs">Freight and container handling</td>
                </tr>
                <tr class="hover:bg-slate-50">
                    <td class="py-3.5 px-4 font-semibold text-slate-900">Viti Freight Services</td>
                    <td class="py-3.5 px-4 text-slate-700">Litiana Rotu</td>
                    <td class="py-3.5 px-4 text-slate-600 font-mono text-xs">dispatch@vitifreight.example</td>
                    <td class="py-3.5 px-4 text-slate-500 text-xs">Crate and pallet shipments</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
