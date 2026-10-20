<?php
/**
 * modules/fieldwork_job_orders/job_review.php
 * Completed Job Review view layout matching screenshot 'complete job review.png'
 */
declare(strict_types=1);

$jobId = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 1);
?>
<div class="fw-container p-6 bg-slate-50 min-h-screen text-slate-800 font-sans" id="fwJobReviewApp">
    <!-- Header breadcrumb -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="javascript:void(0)" onclick="NuApp.loadModule('fieldwork_job_orders')" class="hover:underline">Workspace</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="javascript:void(0)" onclick="NuApp.loadModule('fieldwork_job_orders')" class="hover:underline">Job orders</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span class="text-slate-800 font-semibold" id="fwRevBreadcrumbRef">JO-2026-0142 / Review</span>
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

    <!-- Title Bar -->
    <div class="flex items-start justify-between mb-6">
        <div>
            <div class="text-[11px] font-bold text-teal-700 uppercase tracking-wider mb-1">03 / AUTHORIZED REVIEW</div>
            <h1 class="text-3xl font-bold text-slate-900 font-mono" id="fwRevJobRef">JO-2026-0142</h1>
            <p class="text-sm text-slate-500 mt-1" id="fwRevSubHeader">Pacific Timber Exports Pte Ltd • Sawn timber • FCIU 482671-3</p>
        </div>
        <button class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-md shadow-sm flex items-center gap-1.5">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            View job details
        </button>
    </div>

    <!-- Decision Banner -->
    <div class="p-4 bg-teal-50 border border-teal-200 rounded-lg flex items-start gap-3 mb-6" id="fwRevBanner">
        <div class="w-5 h-5 bg-teal-600 text-white rounded-full flex items-center justify-center shrink-0 mt-0.5">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="text-xs text-teal-900">
            <span class="font-bold">Successful – recorded by the authorized fumigator</span>
            <p class="text-teal-800 mt-0.5" id="fwRevBannerText">Mere Naidu recorded a successful decision on 19 Oct 2026 at 16:45 FJT, after reviewing the underlying record. This is a professional decision in an illustrative completed state, not an automated compliance determination.</p>
        </div>
    </div>

    <!-- Two-column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column (2 cols wide) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Job and Treatment Summary Card -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 mb-4">Job and treatment summary</h3>
                <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Client</div>
                        <div class="font-bold text-slate-800" id="fwRevClient">Pacific Timber Exports Pte Ltd</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Job reference</div>
                        <div class="font-bold text-slate-800 font-mono" id="fwRevRefCode">JO-2026-0142</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Consignment</div>
                        <div class="text-slate-700" id="fwRevConsignment">Sawn timber • 1 × 20 ft container</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Container number</div>
                        <div class="text-slate-700 font-mono" id="fwRevContainer">FCIU 482671-3</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Treatment location</div>
                        <div class="text-slate-700" id="fwRevLocation">18 Industrial Road, Lautoka, Fiji</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Target of fumigation</div>
                        <div class="text-slate-700" id="fwRevTarget">Timber pests</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Treatment type</div>
                        <div class="text-slate-700" id="fwRevType">Methyl bromide • Non-perishable commodity</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Treatment date</div>
                        <div class="text-slate-700" id="fwRevDate">19 Oct 2026</div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                    Dose, readings and measurements are not reproduced in this illustrative summary. No treatment values or acceptance thresholds have been invented.
                </div>
            </div>

            <!-- Authorized Decision Card -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Authorized decision</h3>
                    <span class="text-[11px] text-slate-400">Explicit fumigator decision</span>
                </div>
                <div class="mb-4">
                    <span class="px-3 py-1 bg-teal-100 text-teal-800 font-bold text-xs rounded-full inline-flex items-center gap-1.5" id="fwRevDecisionBadge">
                        <span class="w-2 h-2 bg-teal-600 rounded-full"></span> Successful
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Recorded by</div>
                        <div class="font-bold text-slate-800" id="fwRevRecordedBy">Mere Naidu</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Role</div>
                        <div class="text-slate-700">Authorized fumigator-in-charge</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Provider</div>
                        <div class="text-slate-700">Allpest and hygiene solutions Pte ltd</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Provider ID</div>
                        <div class="text-slate-700 font-mono">FJ0025MB</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Decision timestamp</div>
                        <div class="text-slate-700" id="fwRevTimestamp">19 Oct 2026 • 10:45 FJT</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Accreditation number, if applicable</div>
                        <div class="text-slate-700">—</div>
                    </div>
                </div>
            </div>

            <!-- Declaration and signatures Card -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 mb-2">Declaration and signatures</h3>
                <div class="text-[11px] text-slate-400 mb-4">Illustrative completed state of the linked record.</div>

                <div class="p-3 bg-teal-50 border border-teal-200 rounded text-xs text-teal-800 flex items-center gap-2 mb-4">
                    <svg width="16" height="16" class="text-teal-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span class="font-semibold">Declaration accepted • Mandatory signatures recorded</span>
                </div>

                <p class="text-xs text-slate-600 italic mb-6 leading-relaxed">
                    I, the fumigator-in-charge declare that the fumigation was conducted in accordance with the treatment schedule and all the requirements in the Methyl Bromide Fumigation Methodology, and the information I have provided is true and correct.
                </p>

                <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <div class="text-slate-400 font-medium mb-1">GAS IN signature</div>
                        <div class="font-semibold text-slate-800">Mere Naidu • Recorded</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">GAS OUT signature</div>
                        <div class="font-semibold text-slate-800">Mere Naidu • Recorded</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Declaration date</div>
                        <div class="text-slate-700">19/10/2026</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium mb-1">Government supervision</div>
                        <div class="text-slate-700">Not recorded • Optional</div>
                    </div>
                </div>
            </div>

            <!-- Lock explanation panel -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 mb-1">When certificate generation stays locked</h3>
                <p class="text-xs text-slate-500 mb-4">The action is unavailable until the required record and declaration are complete and an authorized fumigator explicitly records Successful.</p>
                <div class="space-y-2 text-xs">
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 bg-slate-200 text-slate-700 font-bold rounded">● Draft</span>
                            <span class="text-slate-600">Complete the mandatory record and declaration before submitting.</span>
                        </div>
                        <svg width="14" height="14" class="text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold rounded">● In progress</span>
                            <span class="text-slate-600">Finish treatment recording and obtain an authorized outcome.</span>
                        </div>
                        <svg width="14" height="14" class="text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold rounded">● Unsuccessful</span>
                            <span class="text-slate-600">No certificate can be generated for this outcome.</span>
                        </div>
                        <svg width="14" height="14" class="text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column (1 col wide) -->
        <div class="space-y-6">

            <!-- Certificate Action Box -->
            <div class="bg-teal-50/50 rounded-lg border border-teal-200 p-6 shadow-sm">
                <div class="flex items-center gap-2 text-teal-800 font-bold text-sm mb-3">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Ready for a certificate preview
                </div>
                <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                    The authorized Successful decision unlocks generation for this job.
                </p>
                <div class="space-y-2 text-xs text-slate-700 mb-6">
                    <div class="flex items-center gap-2"><svg width="14" height="14" class="text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Mandatory record complete</div>
                    <div class="flex items-center gap-2"><svg width="14" height="14" class="text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Declaration and signatures complete</div>
                    <div class="flex items-center gap-2"><svg width="14" height="14" class="text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Authorized Successful decision recorded</div>
                </div>

                <button onclick="NuApp.loadModule('fieldwork_certificates', 'preview', 'inline', 'inline', {job_id: <?= $jobId ?>})" class="w-full py-2.5 bg-teal-700 hover:bg-teal-800 text-white font-semibold text-xs rounded-md shadow flex items-center justify-center gap-2 transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Generate certificate
                </button>
                <div class="text-[10px] text-slate-400 mt-3 text-center">Creates a proposed Preview | Not issued document.</div>
            </div>

            <!-- Linked Fumigation Record Box -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Linked fumigation record</h3>
                <div class="text-xs text-slate-400 mb-1">Record reference</div>
                <div class="font-bold text-slate-800 font-mono text-sm mb-3" id="fwRevRecordRef">RF-2026-0142</div>

                <div class="mb-4">
                    <span class="px-2.5 py-0.5 bg-teal-100 text-teal-800 font-bold text-xs rounded-full inline-flex items-center gap-1">
                        ● Completed
                    </span>
                </div>

                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Original observations, declaration and recorded signatures remain with the treatment record.
                </p>

                <button onclick="NuApp.loadModule('fieldwork_records', 'draft', 'inline', 'inline', {job_id: <?= $jobId ?>})" class="w-full py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-md flex items-center justify-center gap-1.5">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Open completed record
                </button>
            </div>

            <!-- Activity Feed Card -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Activity</h3>
                <div class="space-y-4 text-xs">
                    <div class="flex items-start justify-between border-l-2 border-teal-600 pl-3">
                        <div>
                            <div class="font-bold text-slate-800">Successful decision recorded</div>
                            <div class="text-slate-500 text-[11px]">Mere Naidu • Authorized fumigator</div>
                        </div>
                        <div class="text-slate-400 font-mono text-[11px]">16:45</div>
                    </div>
                    <div class="flex items-start justify-between border-l-2 border-slate-300 pl-3">
                        <div>
                            <div class="font-bold text-slate-800">Record submitted for review</div>
                            <div class="text-slate-500 text-[11px]">RF-2026-0142</div>
                        </div>
                        <div class="text-slate-400 font-mono text-[11px]">10:58</div>
                    </div>
                    <div class="flex items-start justify-between border-l-2 border-slate-300 pl-3">
                        <div>
                            <div class="font-bold text-slate-800">Job order created</div>
                            <div class="text-slate-500 text-[11px]">19 Oct 2026</div>
                        </div>
                        <div class="text-slate-400 font-mono text-[11px]">10:04</div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
(function() {
    fetch(`api/fieldwork.php?action=get_job&id=<?= $jobId ?>`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.job) {
                const j = data.job;
                const r = data.record;
                document.getElementById('fwRevJobRef').innerText = j.job_ref;
                document.getElementById('fwRevBreadcrumbRef').innerText = `${j.job_ref} / Review`;
                document.getElementById('fwRevSubHeader').innerText = `${j.client_name} • ${j.consignment_desc || ''} • ${j.container_number || ''}`;
                document.getElementById('fwRevClient').innerText = j.client_name;
                document.getElementById('fwRevRefCode').innerText = j.job_ref;
                document.getElementById('fwRevConsignment').innerText = j.consignment_desc || '—';
                document.getElementById('fwRevContainer').innerText = j.container_number || '—';
                document.getElementById('fwRevLocation').innerText = `${j.street_address || ''}, ${j.suburb_city || ''}, ${j.country || ''}`;
                document.getElementById('fwRevTarget').innerText = j.target_fumigation || '—';
                document.getElementById('fwRevType').innerText = j.treatment_type || 'Methyl bromide';
                document.getElementById('fwRevDate').innerText = j.planned_treatment_date || '—';
                if (r) {
                    document.getElementById('fwRevRecordRef').innerText = r.record_ref;
                }
            }
        });
})();
</script>
