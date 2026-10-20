<?php
/**
 * modules/fieldwork_job_orders/fieldwork_job_orders.php
 * Job Orders List & Creation Router View
 */
declare(strict_types=1);
?>
<div class="fw-container p-6 bg-slate-50 min-h-screen text-slate-800 font-sans">

    <!-- Header breadcrumb and top bar -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span>Workspace</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span class="text-slate-800 font-semibold">Job orders</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 bg-slate-200 text-slate-700 text-xs font-semibold rounded-full">● Illustrative data</span>
            <div class="flex items-center gap-2 text-xs">
                <div class="w-7 h-7 bg-teal-100 text-teal-800 font-bold rounded-full flex items-center justify-center">MN</div>
                <div>
                    <div class="font-bold text-slate-800 leading-tight">Mere Naidu</div>
                    <div class="text-[10px] text-slate-500">Authorized fumigator</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Title and Action -->
    <div class="flex items-start justify-between mb-6">
        <div>
            <div class="text-[11px] font-bold text-teal-700 uppercase tracking-wider mb-1">OPERATIONS / JOB ORDERS</div>
            <h1 class="text-2xl font-bold text-slate-900">Every job, accounted for.</h1>
            <p class="text-sm text-slate-500 mt-1">Create job orders, track treatment records and prepare certificates in one place.</p>
        </div>
        <button onclick="FwJobOrders.openCreateModal()" class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white font-medium text-sm rounded-md shadow-sm transition flex items-center gap-1.5">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New job order
        </button>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" id="fwKpiContainer">
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-500 font-medium mb-1 flex items-center justify-between">
                All job orders
                <svg width="16" height="16" class="text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
            </div>
            <div class="text-3xl font-bold text-slate-900" id="kpiTotal">24</div>
            <div class="text-[11px] text-slate-400 mt-1">This month</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-500 font-medium mb-1 flex items-center justify-between">
                Draft
                <svg width="16" height="16" class="text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div class="text-3xl font-bold text-slate-900" id="kpiDraft">06</div>
            <div class="text-[11px] text-slate-400 mt-1">Awaiting treatment record</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-500 font-medium mb-1 flex items-center justify-between">
                In progress
                <svg width="16" height="16" class="text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="text-3xl font-bold text-slate-900" id="kpiInProgress">04</div>
            <div class="text-[11px] text-slate-400 mt-1">Treatment underway</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-500 font-medium mb-1 flex items-center justify-between">
                Successful
                <svg width="16" height="16" class="text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="text-3xl font-bold text-slate-900" id="kpiSuccessful">12</div>
            <div class="text-[11px] text-slate-400 mt-1">Authorized decisions recorded</div>
        </div>
    </div>

    <!-- Filter & Search Bar Container -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-1 overflow-x-auto text-sm font-medium text-slate-600">
                <button onclick="FwJobOrders.setFilter('All')" class="px-3 py-1.5 rounded-md font-semibold text-teal-800 bg-teal-50 border-b-2 border-teal-700 fw-tab-btn" data-status="All">All jobs <span id="tabCountAll" class="text-xs text-slate-500 ml-1">24</span></button>
                <button onclick="FwJobOrders.setFilter('Draft')" class="px-3 py-1.5 rounded-md hover:text-slate-900 fw-tab-btn" data-status="Draft">Draft</button>
                <button onclick="FwJobOrders.setFilter('In progress')" class="px-3 py-1.5 rounded-md hover:text-slate-900 fw-tab-btn" data-status="In progress">In progress</button>
                <button onclick="FwJobOrders.setFilter('Successful')" class="px-3 py-1.5 rounded-md hover:text-slate-900 fw-tab-btn" data-status="Successful">Successful</button>
                <button onclick="FwJobOrders.setFilter('Unsuccessful')" class="px-3 py-1.5 rounded-md hover:text-slate-900 fw-tab-btn" data-status="Unsuccessful">Unsuccessful</button>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <svg width="16" height="16" class="absolute left-3 top-2.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="fwJobSearchInput" onkeyup="FwJobOrders.handleSearch(event)" placeholder="Search jobs or clients" class="pl-9 pr-4 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-teal-600 w-64">
                </div>
                <button class="px-3 py-1.5 text-sm border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50 flex items-center gap-1.5">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
            </div>
        </div>

        <!-- Jobs Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Jobs reference</th>
                        <th class="py-3 px-4">Client / consignment</th>
                        <th class="py-3 px-4">Container</th>
                        <th class="py-3 px-4">Treatment date</th>
                        <th class="py-3 px-4">Record status</th>
                        <th class="py-3 px-4">Certificate</th>
                        <th class="py-3 px-4 text-right"></th>
                    </tr>
                </thead>
                <tbody id="fwJobTableBody" class="divide-y divide-slate-200">
                    <!-- Populated dynamically -->
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
            <div>Showing 1–6 of 24 job orders</div>
            <div class="flex items-center gap-1">
                <button class="px-2.5 py-1 border border-slate-300 bg-white rounded text-slate-600 hover:bg-slate-100 disabled:opacity-50">Previous</button>
                <button class="px-2.5 py-1 bg-teal-700 text-white font-bold rounded">1</button>
                <button class="px-2.5 py-1 border border-slate-300 bg-white rounded text-slate-600 hover:bg-slate-100">2</button>
                <button class="px-2.5 py-1 border border-slate-300 bg-white rounded text-slate-600 hover:bg-slate-100">3</button>
                <button class="px-2.5 py-1 border border-slate-300 bg-white rounded text-slate-600 hover:bg-slate-100">4</button>
                <button class="px-2.5 py-1 border border-slate-300 bg-white rounded text-slate-600 hover:bg-slate-100">Next</button>
            </div>
        </div>
    </div>

    <!-- Informational Footer -->
    <div class="p-4 bg-teal-50 border border-teal-200 rounded-lg flex items-start gap-3 text-xs text-teal-800">
        <svg width="16" height="16" class="text-teal-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <div>
            <span class="font-semibold">Certificates follow an authorized Successful decision</span>
            <p class="text-teal-700 mt-0.5">Draft, in-progress and unsuccessful records are not eligible. Mandatory record fields and the declaration must be complete before an authorized fumigator can record success.</p>
        </div>
    </div>
</div>

<!-- Modal: New Job Order Creation Wizard -->
<div id="fwNewJobModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl overflow-hidden my-8">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-teal-700 uppercase tracking-wider">01 / CREATE THE JOB</div>
                <h2 class="text-xl font-bold text-slate-900">New job order</h2>
            </div>
            <button onclick="FwJobOrders.closeCreateModal()" class="text-slate-400 hover:text-slate-600">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="fwNewJobForm" onsubmit="FwJobOrders.submitNewJob(event)" class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
            <!-- Job identification -->
            <div>
                <h3 class="font-bold text-slate-800 text-sm mb-3">Job identification</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Job identification number *</label>
                        <input type="text" value="JO-2026-AUTO" disabled class="w-full bg-slate-100 border border-slate-300 rounded px-3 py-2 text-xs text-slate-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Planned treatment date</label>
                        <input type="date" id="fwFormDate" value="2026-10-19" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            <!-- Client details -->
            <div>
                <h3 class="font-bold text-slate-800 text-sm mb-3">Client details</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Client name *</label>
                        <input type="text" id="fwFormClientName" required placeholder="e.g. Pacific Timber Exports Pte Ltd" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Contact person</label>
                            <input type="text" id="fwFormContactPerson" placeholder="e.g. Ana Kumar" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Contact email</label>
                            <input type="email" id="fwFormContactEmail" placeholder="e.g. operations@pacific-timber.example" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consignment details -->
            <div>
                <h3 class="font-bold text-slate-800 text-sm mb-3">Consignment</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Description of consignment *</label>
                        <input type="text" id="fwFormConsignmentDesc" required placeholder="e.g. Sawn timber • 1 × 20 ft container" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Target of fumigation *</label>
                            <input type="text" id="fwFormTargetFumigation" required placeholder="e.g. Timber pests" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Container number *</label>
                            <input type="text" id="fwFormContainerNumber" required placeholder="e.g. FCIU 482671-3" class="w-full border border-slate-300 rounded px-3 py-2 text-xs font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-teal-50 border border-teal-200 rounded text-xs text-teal-800 flex items-center gap-2">
                <svg width="16" height="16" class="text-teal-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Treatment values belong in the fumigation record. No dose, exposure period or measurement is inferred from the job order.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="FwJobOrders.closeCreateModal()" class="px-4 py-2 border border-slate-300 text-slate-700 text-xs font-semibold rounded hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-teal-700 text-white text-xs font-semibold rounded hover:bg-teal-800 shadow-sm">Create job order</button>
            </div>
        </form>
    </div>
</div>

<script>
window.FwJobOrders = {
    currentStatus: 'All',
    searchQuery: '',

    init: function() {
        this.loadJobs();
    },

    setFilter: function(status) {
        this.currentStatus = status;
        document.querySelectorAll('.fw-tab-btn').forEach(btn => {
            if (btn.getAttribute('data-status') === status) {
                btn.className = 'px-3 py-1.5 rounded-md font-semibold text-teal-800 bg-teal-50 border-b-2 border-teal-700 fw-tab-btn';
            } else {
                btn.className = 'px-3 py-1.5 rounded-md hover:text-slate-900 fw-tab-btn text-slate-600';
            }
        });
        this.loadJobs();
    },

    handleSearch: function(e) {
        this.searchQuery = e.target.value;
        this.loadJobs();
    },

    loadJobs: function() {
        const url = `api/fieldwork.php?action=list_jobs&status=${encodeURIComponent(this.currentStatus)}&search=${encodeURIComponent(this.searchQuery)}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.renderKpis(data.kpis);
                    this.renderTable(data.jobs);
                }
            });
    },

    renderKpis: function(kpis) {
        if (!kpis) return;
        document.getElementById('kpiTotal').innerText = String(kpis.total).padStart(2, '0');
        document.getElementById('kpiDraft').innerText = String(kpis.draft).padStart(2, '0');
        document.getElementById('kpiInProgress').innerText = String(kpis.in_progress).padStart(2, '0');
        document.getElementById('kpiSuccessful').innerText = String(kpis.successful).padStart(2, '0');
        document.getElementById('tabCountAll').innerText = kpis.total;
    },

    renderTable: function(jobs) {
        const tbody = document.getElementById('fwJobTableBody');
        if (!jobs || jobs.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-400">No job orders found.</td></tr>`;
            return;
        }

        tbody.innerHTML = jobs.map(j => {
            let statusBadge = '';
            if (j.status === 'Successful') {
                statusBadge = `<span class="px-2.5 py-0.5 bg-teal-100 text-teal-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit"><span class="w-1.5 h-1.5 bg-teal-600 rounded-full"></span> Successful</span>`;
            } else if (j.status === 'In progress') {
                statusBadge = `<span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit"><span class="w-1.5 h-1.5 bg-amber-600 rounded-full"></span> In progress</span>`;
            } else if (j.status === 'Unsuccessful') {
                statusBadge = `<span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit"><span class="w-1.5 h-1.5 bg-rose-600 rounded-full"></span> Unsuccessful</span>`;
            } else {
                statusBadge = `<span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-semibold rounded-full flex items-center gap-1 w-fit"><span class="w-1.5 h-1.5 bg-slate-500 rounded-full"></span> Draft</span>`;
            }

            let certCell = '<span class="text-slate-400 text-xs">Not eligible</span>';
            if (j.status === 'Successful') {
                certCell = `<a href="javascript:void(0)" onclick="NuApp.loadModule('fieldwork_certificates', 'preview', 'inline', 'inline', {job_id:${j.job_id}})" class="text-teal-700 hover:underline font-semibold text-xs">Ready to generate</a>`;
            }

            return `
                <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="NuApp.loadModule('fieldwork_job_orders', 'review', 'inline', 'inline', {job_id:${j.job_id}})">
                    <td class="py-3.5 px-4 font-bold text-teal-700 font-mono text-xs">${j.job_ref}<br><span class="text-[10px] text-slate-400 font-sans font-normal">Methyl bromide</span></td>
                    <td class="py-3.5 px-4"><div class="font-semibold text-slate-900">${j.client_name}</div><div class="text-xs text-slate-500">${j.consignment_desc || ''}</div></td>
                    <td class="py-3.5 px-4 font-mono text-xs text-slate-600">${j.container_number || '—'}</td>
                    <td class="py-3.5 px-4 text-xs text-slate-600">${j.planned_treatment_date || '—'}</td>
                    <td class="py-3.5 px-4">${statusBadge}</td>
                    <td class="py-3.5 px-4">${certCell}</td>
                    <td class="py-3.5 px-4 text-right text-slate-400"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></td>
                </tr>
            `;
        }).join('');
    },

    openCreateModal: function() {
        document.getElementById('fwNewJobModal').classList.remove('hidden');
    },

    closeCreateModal: function() {
        document.getElementById('fwNewJobModal').classList.add('hidden');
    },

    submitNewJob: function(e) {
        e.preventDefault();
        const payload = {
            client_name: document.getElementById('fwFormClientName').value,
            contact_person: document.getElementById('fwFormContactPerson').value,
            contact_email: document.getElementById('fwFormContactEmail').value,
            consignment_desc: document.getElementById('fwFormConsignmentDesc').value,
            target_fumigation: document.getElementById('fwFormTargetFumigation').value,
            container_number: document.getElementById('fwFormContainerNumber').value,
            planned_treatment_date: document.getElementById('fwFormDate').value
        };

        fetch('api/fieldwork.php?action=create_job', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(res => res.json()).then(data => {
            if (data.success) {
                this.closeCreateModal();
                NuApp.loadModule('fieldwork_records', 'draft', 'inline', 'inline', {job_id: data.job_id});
            }
        });
    }
};

FwJobOrders.init();
</script>
